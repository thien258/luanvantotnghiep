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

class HomeController extends Controller
{
    public function __construct()
    {
        View::share('categories', Category::where('status', '1')->get());
    }

    public function index(Request $request)
    {
        $concentrations = Concentration::where("status", '1')->get();
        $query = Product::where('status', 1)
            ->whereHas('category', function ($q) {
                $q->where('status', 1);
            });

        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }

        $products = $query->get();
        $title = Title::all();
        $footers = Footer::all();

        return view('index', compact("products", "footers", "title", "concentrations"));
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function category_product(Request $request, $id)
    {
        $category = Category::find($id);

        $title = Title::all();

        $footers = Footer::all();

        $all_concentrations =
            Concentration::where('status', '1')->get();

        $all_brands =
            Brand::where('status', '1')->get();

        $query = Product::where('idCategory', $id)
            ->where('status', '1');

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

        // SORT
        if ($request->sort == 'price_asc') {

            $query->orderBy('price', 'asc');
        } elseif ($request->sort == 'price_desc') {

            $query->orderBy('price', 'desc');
        } else {

            $query->latest();
        }

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

        return view(
            'layout.category_product',
            compact(
                'all_concentrations',
                'all_brands',
                'products',
                'category',
                'title',
                'footers'
            )
        );
    }

    public function brand_product(Request $request, $id)
    {
        $brand = Brand::find($id);

        $title = Title::all();

        $footers = Footer::all();

        $all_concentrations =
            Concentration::where('status', '1')->get();

        $categories =
            Category::where('status', '1')->get();

        $query = Product::where('idBrand', $id)
            ->where('status', '1');

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

        // SORT
        if ($request->sort == 'price_asc') {

            $query->orderBy('price', 'asc');
        } elseif ($request->sort == 'price_desc') {

            $query->orderBy('price', 'desc');
        } else {

            $query->latest();
        }

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

        return view(
            'layout.brand_product',
            compact(
                'all_concentrations',
                'categories',
                'products',
                'brand',
                'title',
                'footers'
            )
        );
    }


    public function single_product($id)
    {
        // Không còn variants, chỉ load product và các thông tin liên quan
        $product = Product::with(['comment', 'concentration'])
            ->where('status', 1)
            ->findOrFail($id);

        return view('layout.single_product', compact('product'));
    }

    // public function showProducts(Request $request)
    // {
    //     $categories = Category::where('status', '1')->get();
    //     $all_concentrations = Concentration::where('status', '1')->get();
    //     $all_brands = Brand::where('status', '1')->get();

    //     $query = Product::where('status', 1);

    //     if ($request->has('concentrations') && is_array($request->concentrations)) {
    //         $query->whereIn('idConcentration', $request->concentrations);
    //     }
    //     if ($request->has('categories') && is_array($request->categories)) {
    //         $query->whereIn('idCategory', $request->categories);
    //     }
    //     if ($request->has('brands') && is_array($request->brands)) {
    //         $query->whereIn('idBrand', $request->brands);
    //     }

    //     $products = $query
    //         ->with('festivals')
    //         ->get();

    //     // =========================
    //     // FILTER PRICE AFTER DISCOUNT
    //     // =========================
    //     if ($request->has('min_price') && $request->has('max_price')) {

    //         $min = (int) $request->min_price;
    //         $max = (int) $request->max_price;

    //         $products = $products->filter(function ($product) use ($min, $max) {

    //             $finalPrice = $product->getDiscountedPrice();

    //             return $finalPrice >= $min && $finalPrice <= $max;
    //         });
    //     }

    //     // =========================
    //     // SORT
    //     // =========================
    //     $sort = $request->sort;

    //     if ($sort == 'price_asc') {

    //         $products = $products->sortBy(function ($product) {

    //             return (float) $product->getDiscountedPrice();
    //         })->values();
    //     } elseif ($sort == 'price_desc') {

    //         $products = $products->sortByDesc(function ($product) {

    //             return (float) $product->getDiscountedPrice();
    //         })->values();
    //     } else {

    //         $products = $products->sortByDesc('created_at')->values();
    //     }
    //     $title = Title::all();

    //     $footers = Footer::all();

    //     return view('show-product', compact('all_concentrations', 'all_brands', 'categories', 'products', 'title', 'footers'));
    // }

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

    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $products = Product::where('title', 'LIKE', "%{$keyword}%")
            ->where('status', 1)
            ->paginate(12);

        $brands = Brand::where('status', '1')->get();
        $title = Title::all();
        $footers = Footer::all();

        return view('search_result', compact('products', 'keyword', 'brands', 'title', 'footers'));
    }

    public function festival_product(Request $request, $id)
{
    $festival = Festival::find($id);

    $title = Title::all();

    $footers = Footer::all();

    $all_concentrations =
        Concentration::where('status', '1')->get();

    $categories =
        Category::where('status', '1')->get();

    $query = Product::where('status', '1')
        ->whereHas('festivals', function ($q) use ($id) {

            $q->where('festivals.id', $id);

        });

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

    // SORT
    if ($request->sort == 'price_asc') {

        $query->orderBy('price', 'asc');

    } elseif ($request->sort == 'price_desc') {

        $query->orderBy('price', 'desc');

    } else {

        $query->latest();

    }

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

    return view(
        'layout.festival_product',
        compact(
            'festival',
            'all_concentrations',
            'categories',
            'products',
            'title',
            'footers'
        )
    );
}
}
