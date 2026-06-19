<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Footer;
use App\Models\Title;
use App\Models\Concentration;
use App\Models\Festival;
use App\Models\Product;
use Illuminate\Support\Facades\View;

/**
 * HomeController — Xử lý trang chủ và các trang danh sách sản phẩm frontend.
 *
 * Các tính năng:
 *   - Trang chủ: lọc theo nồng độ
 *   - Trang danh mục: lọc theo brand, concentration, giá; sort
 *   - Trang thương hiệu: lọc theo category, concentration, giá; sort
 *   - Trang chi tiết sản phẩm
 *   - Trang festival: lọc theo quy tắc discount cao nhất
 *   - Tìm kiếm + gợi ý tìm kiếm (AJAX)
 */
class HomeController extends Controller
{
    public function __construct()
    {
        // Chia sẻ danh sách category đang active sang TẤT CẢ views
        // để navbar luôn hiển thị đúng danh mục
        View::share('categories', Category::where('status', '1')->get());
    }

    // =========================================================================
    // TRANG CHỦ
    // =========================================================================

    public function index(Request $request)
    {
        // Lấy các nồng độ đang active để hiển thị filter
        $concentrations = Concentration::where("status", '1')->get();

        // Query sản phẩm đang bán, chỉ hiện SP thuộc danh mục active
        $query = Product::where('status', 1)
            ->whereHas('category', function ($q) {
                $q->where('status', 1);
            });

        // Filter theo nồng độ nếu người dùng chọn
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }

        $products = $query->get();
        $title    = Title::all();
        $footers  = Footer::all();

        return view('index', compact("products", "footers", "title", "concentrations"));
    }

    // =========================================================================
    // ĐĂNG XUẤT
    // =========================================================================

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    // =========================================================================
    // TRANG DANH MỤC
    // =========================================================================

    public function category_product(Request $request, $id)
    {
        $category          = Category::find($id);
        $title             = Title::all();
        $footers           = Footer::all();
        $all_concentrations = Concentration::where('status', '1')->get();
        $all_brands        = Brand::where('status', '1')->get();

        // Query SP theo danh mục
        $query = Product::where('idCategory', $id)->where('status', '1');

        // Filter nồng độ
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }

        // Filter thương hiệu
        if ($request->has('brands') && is_array($request->brands)) {
            $query->whereIn('idBrand', $request->brands);
        }

        // Eager load festivals để tính giá giảm
        $products = $query->with('festivals')->get();

        // Filter giá — phải filter SAU khi get() vì giá có thể bị giảm bởi festival
        if ($request->has('min_price') && $request->has('max_price')) {
            $min      = (int) $request->min_price;
            $max      = (int) $request->max_price;
            $products = $products->filter(function ($product) use ($min, $max) {
                $finalPrice = $product->getDiscountedPrice();
                return $finalPrice >= $min && $finalPrice <= $max;
            });
        }

        // Sort theo giá sau giảm
        if ($request->sort == 'price_asc') {
            $products = $products->sortBy(fn($p) => $p->getDiscountedPrice())->values();
        } elseif ($request->sort == 'price_desc') {
            $products = $products->sortByDesc(fn($p) => $p->getDiscountedPrice())->values();
        } else {
            $products = $products->sortByDesc('created_at')->values(); // mặc định: mới nhất
        }

        return view('layout.category_product', compact(
            'all_concentrations', 'all_brands', 'products', 'category', 'title', 'footers'
        ));
    }

    // =========================================================================
    // TRANG THƯƠNG HIỆU
    // =========================================================================

    public function brand_product(Request $request, $id)
    {
        $brand              = Brand::find($id);
        $title              = Title::all();
        $footers            = Footer::all();
        $all_concentrations = Concentration::where('status', '1')->get();
        $categories         = Category::where('status', '1')->get();

        $query = Product::where('idBrand', $id)->where('status', '1');

        // Filter nồng độ
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }

        // Filter danh mục
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('idCategory', $request->categories);
        }

        $products = $query->with('festivals')->get();

        // Filter giá sau giảm
        if ($request->has('min_price') && $request->has('max_price')) {
            $min      = (int) $request->min_price;
            $max      = (int) $request->max_price;
            $products = $products->filter(function ($product) use ($min, $max) {
                $finalPrice = $product->getDiscountedPrice();
                return $finalPrice >= $min && $finalPrice <= $max;
            });
        }

        // Sort
        if ($request->sort == 'price_asc') {
            $products = $products->sortBy(fn($p) => $p->getDiscountedPrice())->values();
        } elseif ($request->sort == 'price_desc') {
            $products = $products->sortByDesc(fn($p) => $p->getDiscountedPrice())->values();
        } else {
            $products = $products->sortByDesc('created_at')->values();
        }

        return view('layout.brand_product', compact(
            'all_concentrations', 'categories', 'products', 'brand', 'title', 'footers'
        ));
    }

    // =========================================================================
    // CHI TIẾT SẢN PHẨM
    // =========================================================================

    public function single_product($id)
    {
        // Load comment để hiển thị đánh giá, concentration để hiển thị thẻ nồng độ
        $product = Product::with(['comment', 'concentration'])
            ->where('status', 1)
            ->findOrFail($id);

        return view('layout.single_product', compact('product'));
    }

    // =========================================================================
    // TÌM KIẾM
    // =========================================================================

    /**
     * Gợi ý tìm kiếm — AJAX trả về JSON, tối đa 3 sản phẩm.
     * Dùng cho thanh search live (live-search.js).
     */
    public function suggest(Request $request)
    {
        $keyword = $request->keyword;
        if (empty($keyword)) return response()->json([]);

        $products = Product::where('title', 'LIKE', "%{$keyword}%")
            ->where('status', 1)
            ->select('id', 'title', 'image', 'price')
            ->take(3)
            ->get();

        return response()->json($products);
    }

    /**
     * Trang kết quả tìm kiếm — hỗ trợ filter + sort giống trang danh mục.
     */
    public function search(Request $request)
    {
        $keyword            = $request->keyword;
        $all_brands         = Brand::where('status', '1')->get();
        $all_concentrations = Concentration::where('status', '1')->get();
        $categories         = Category::where('status', '1')->get();
        $title              = Title::all();
        $footers            = Footer::all();

        $query = Product::where('title', 'LIKE', "%{$keyword}%")->where('status', 1);

        if ($request->has('brands') && is_array($request->brands)) {
            $query->whereIn('idBrand', $request->brands);
        }
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('idCategory', $request->categories);
        }

        $products = $query->with('festivals')->get();

        // Filter giá sau giảm
        if ($request->has('min_price') && $request->has('max_price')) {
            $min      = (int) $request->min_price;
            $max      = (int) $request->max_price;
            $products = $products->filter(fn($product) =>
                $product->getDiscountedPrice() >= $min &&
                $product->getDiscountedPrice() <= $max
            );
        }

        if ($request->sort == 'price_asc') {
            $products = $products->sortBy(fn($p) => $p->getDiscountedPrice())->values();
        } elseif ($request->sort == 'price_desc') {
            $products = $products->sortByDesc(fn($p) => $p->getDiscountedPrice())->values();
        } else {
            $products = $products->sortByDesc('created_at')->values();
        }

        return view('search_result', compact(
            'products', 'keyword', 'all_brands', 'all_concentrations', 'categories', 'title', 'footers'
        ));
    }

    // =========================================================================
    // TRANG FESTIVAL
    // =========================================================================

    /**
     * Trang danh sách sản phẩm trong festival.
     *
     * Logic đặc biệt: Nếu 1 sản phẩm có 2 festival đang active,
     * chỉ hiển thị ở festival có discount CAO NHẤT.
     * Festival có discount thấp hơn sẽ ẩn SP đó.
     * → Khi festival cao hơn hết hạn, SP sẽ hiện lại ở festival thấp hơn.
     */
    public function festival_product(Request $request, $id)
    {
        $festival = Festival::findOrFail($id);

        // Không cho vào trang nếu festival đã hết hạn hoặc chưa bắt đầu
        if (!$festival->isActive()) {
            return redirect()->route('show_products')
                ->with('error', 'Chương trình khuyến mãi đã kết thúc hoặc chưa diễn ra.');
        }

        $title              = Title::all();
        $footers            = Footer::all();
        $all_concentrations = Concentration::where('status', '1')->get();
        $categories         = Category::where('status', '1')->get();

        // Lấy SP đang bán VÀ thuộc festival này
        $query = Product::where('status', '1')
            ->whereHas('festivals', function ($q) use ($id) {
                $q->where('festivals.id', $id);
            });

        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('idCategory', $request->categories);
        }

        $products = $query->with('festivals')->get();

        // ── LỌC THEO DISCOUNT CAO NHẤT ───────────────────────────────
        $today    = \Carbon\Carbon::today()->toDateString();
        $products = $products->filter(function ($product) use ($festival, $today) {
            // Tìm discount cao nhất từ tất cả festival active của SP này
            $maxActiveDiscount = $product->festivals
                ->where('status', 1)
                ->filter(fn($f) =>
                    $f->start_date->toDateString() <= $today &&
                    $f->end_date->toDateString() >= $today
                )
                ->max('discount') ?? 0;

            // Chỉ hiển thị nếu festival đang xem có discount = cao nhất
            // (bằng nhau cũng OK để tránh trường hợp 2 festival cùng discount)
            return $festival->discount >= $maxActiveDiscount;
        })->values();

        // Filter giá — dùng giá của đúng festival đang xem (không phải discount cao nhất)
        if ($request->has('min_price') && $request->has('max_price')) {
            $min      = (int) $request->min_price;
            $max      = (int) $request->max_price;
            $products = $products->filter(function ($product) use ($min, $max, $festival) {
                $finalPrice = $product->getDiscountedPrice($festival);
                return $finalPrice >= $min && $finalPrice <= $max;
            });
        }

        // Sort theo giá của festival đang xem
        if ($request->sort == 'price_asc') {
            $products = $products->sortBy(fn($p) => $p->getDiscountedPrice($festival))->values();
        } elseif ($request->sort == 'price_desc') {
            $products = $products->sortByDesc(fn($p) => $p->getDiscountedPrice($festival))->values();
        } else {
            $products = $products->sortByDesc('created_at')->values();
        }

        return view('layout.festival_product', compact(
            'festival', 'all_concentrations', 'categories', 'products', 'title', 'footers'
        ));
    }
}
