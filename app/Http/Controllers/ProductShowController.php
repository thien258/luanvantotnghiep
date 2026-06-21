<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Footer;
use App\Models\Title;
use App\Models\Concentration;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ProductShowController — Trang hiển thị TẤT CẢ sản phẩm (không lọc theo danh mục/thương hiệu).
 *
 * Hỗ trợ filter:
 *   - Nồng độ (concentrations[])
 *   - Danh mục (categories[])
 *   - Thương hiệu (brands[])
 *   - Khoảng giá (min_price, max_price) — tính theo giá sau discount festival
 *
 * Hỗ trợ sort:
 *   - price_asc  : giá tăng dần (theo giá sau discount)
 *   - price_desc : giá giảm dần
 *   - mặc định   : mới nhất
 *
 * Route: GET /show-products → ProductShowController@showProducts
 */
class ProductShowController extends Controller
{
    /** Phương thức cũ — không dùng, giữ lại để tránh lỗi nếu route vẫn trỏ vào */
    public function index()
    {
        $products = Product::all();
        return view('show-product', compact('products'));
    }

    /**
     * Trang danh sách tất cả sản phẩm với đầy đủ filter và sort.
     * Tương tự category_product/brand_product nhưng không giới hạn danh mục/thương hiệu.
     */
    public function showProducts(Request $request)
    {
        $categories         = Category::where('status', '1')->get();
        $all_concentrations = Concentration::where('status', '1')->get();
        $all_brands         = Brand::where('status', '1')->get();

        $query = Product::where('status', 1);

        // ── Filter nồng độ ──────────────────────────────────────────
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }

        // ── Filter danh mục ─────────────────────────────────────────
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('idCategory', $request->categories);
        }

        // ── Filter thương hiệu ──────────────────────────────────────
        if ($request->has('brands') && is_array($request->brands)) {
            $query->whereIn('idBrand', $request->brands);
        }

        // Eager load festivals để tính giá giảm và filter giá
        $products = $query->with('festivals')->get();

        // ── Filter giá — phải filter SAU get() vì giá phụ thuộc festival ──
        if ($request->has('min_price') && $request->has('max_price')) {
            $min      = (int) $request->min_price;
            $max      = (int) $request->max_price;
            $products = $products->filter(function ($product) use ($min, $max) {
                $finalPrice = $product->getDiscountedPrice();
                return $finalPrice >= $min && $finalPrice <= $max;
            });
        }

        // ── Sort ────────────────────────────────────────────────────
        if ($request->sort == 'price_asc') {
            $products = $products->sortBy(fn($p) => $p->getDiscountedPrice())->values();
        } elseif ($request->sort == 'price_desc') {
            $products = $products->sortByDesc(fn($p) => $p->getDiscountedPrice())->values();
        } else {
            $products = $products->sortByDesc('created_at')->values();
        }

        $title   = Title::all();
        $footers = Footer::all();

        // Manual paginate — giữ filter giá discount vì filter chạy trên Collection sau get()
        // $total phải lưu TRƯỚC forPage() để có tổng đúng sau filter
        $perPage  = 12;                          // số SP mỗi trang
        $page     = (int) $request->input('page', 1);
        $products = $products->values();         // reset index Collection sau filter/sort
        $total    = $products->count();          // tổng SP sau khi filter (dùng để tính số trang)

        $products = new LengthAwarePaginator(
            $products->forPage($page, $perPage), // items của trang hiện tại
            $total,                              // tổng đúng sau filter
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->except('page')]
        );

        return view('show-product', compact(
            'all_concentrations', 'all_brands', 'categories', 'products', 'title', 'footers'
        ));
    }
}
