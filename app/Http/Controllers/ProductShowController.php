<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Footer;
use App\Models\Title;
use App\Models\Concentration;
use App\Models\Festival;


class ProductShowController extends Controller
{
    //
    public function index()
    {
        $products = Product::all();
        return view('show-product', compact('products'));
    }
    // public function showProducts(Request $request)
    // {
    //     // 1. Khởi tạo query và áp dụng bộ lọc chung từ hàm getFilteredQuery
    //     $query = $this->getFilteredQuery($request);

    //     // 2. Xử lý logic sắp xếp dựa vào giá trị ô select "sort" gửi lên
    //     if ($request->has('sort') && !empty($request->sort)) {
    //         $sort = $request->sort;

    //         if ($sort == 'price_asc') {
    //             // Sắp xếp giá từ THẤP đến CAO
    //             $query->orderBy('price', 'ASC');
    //         } elseif ($sort == 'price_desc') {
    //             // Sắp xếp giá từ CAO đến THẤP
    //             $query->orderBy('price', 'DESC');
    //         } else {
    //             // Mặc định: Mới nhất
    //             $query->orderBy('created_at', 'DESC');
    //         }
    //     } else {
    //         // Nếu không chọn gì thì mặc định sắp xếp theo sản phẩm mới nhất
    //         $query->orderBy('created_at', 'DESC');
    //     }

    //     // 3. Thực hiện phân trang (Mỗi trang 12 sản phẩm)
    //     $products = $query->paginate(12);

    //     // 4. Trả về đúng view của ông
    //     return view('show-product', compact('products'));
    // }
    public function showProducts(Request $request)
    {
        $categories = Category::where('status', '1')->get();

        $all_concentrations =
            Concentration::where('status', '1')->get();

        $all_brands =
            Brand::where('status', '1')->get();

        $query = Product::where('status', 1);

        // FILTER CONCENTRATION
        if (
            $request->has('concentrations') &&
            is_array($request->concentrations)
        ) {

            $query->whereIn(
                'idConcentration',
                $request->concentrations
            );
        }

        // FILTER CATEGORY
        if (
            $request->has('categories') &&
            is_array($request->categories)
        ) {

            $query->whereIn(
                'idCategory',
                $request->categories
            );
        }

        // FILTER BRAND
        if (
            $request->has('brands') &&
            is_array($request->brands)
        ) {

            $query->whereIn(
                'idBrand',
                $request->brands
            );
        }

        // GET PRODUCTS
        $products = $query
            ->with('festivals')
            ->get();

        // FILTER PRICE
        if (
            $request->has('min_price') &&
            $request->has('max_price')
        ) {

            $min = (int) $request->min_price;

            $max = (int) $request->max_price;

            $products = $products->filter(
                function ($product) use ($min, $max) {

                    $finalPrice =
                        $product->getDiscountedPrice();

                    return
                        $finalPrice >= $min &&
                        $finalPrice <= $max;
                }
            );
        }

        // SORT
        // SORT
        if ($request->sort == 'price_asc') {

            $products = $products
                ->sortBy(function ($product) {

                    return $product->getDiscountedPrice();
                })
                ->values();
        } elseif ($request->sort == 'price_desc') {

            $products = $products
                ->sortByDesc(function ($product) {

                    return $product->getDiscountedPrice();
                })
                ->values();
        } else {

            $products = $products
                ->sortByDesc('created_at')
                ->values();
        }

        $title = Title::all();

        $footers = Footer::all();

        return view(
            'show-product',
            compact(
                'all_concentrations',
                'all_brands',
                'categories',
                'products',
                'title',
                'footers'
            )
        );
    }
}
