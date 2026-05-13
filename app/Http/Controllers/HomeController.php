<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Footer;
use App\Models\Title;
use App\Models\Concentration;
use App\Models\Love;
use App\Models\Product;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        view()->share('categories', Category::where('status', '1')->get());
    }

    /**
     * Show the application dashboard.
     * 
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $categories = Category::where("status", '1')->get();
        $concentrations = Concentration::where("status", '1')->get();
        $query = Product::where('status', 1)
            ->whereHas('category', function ($q) {
                $q->where('status', 1);
            });
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }

        // 4. Chốt query và lấy sản phẩm
        $products = $query->get();
        $title = Title::All();
        $footers = Footer::All();

        return view('index', compact("categories", "products", "footers", "title", "concentrations"));
    }
    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
        }
        return redirect('/');
    }
    public function category_product(Request $request, $id)
    {

        $category = Category::find($id);
         $title = Title::all();
        $footers = Footer::all();

        // lọc nồng dộ
        $all_concentrations = Concentration::where('status', '1')->get();
        $all_brands = Brand::where('status', '1')->get();
        $query = Product::where([
            ['idCategory', '=', $id],
            ['status', '=', '1']
        ]);

        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }
        if ($request->has('brands') && is_array($request->brands)) {
            $query->whereIn('idBrand', $request->brands);
        }

        $products = $query->get();
        // 5. Trả về đúng cái file show-product mà ông đang làm
        return view('show-product', compact('all_concentrations', 'all_brands', 'products', 'category', 'title', 'footers'));
    }
   public function brand_product(Request $request, $id)
    {
        $brand = Brand::find($id);
        $title = Title::all();
        $footers = Footer::all();

      
        $all_concentrations = Concentration::where('status', '1')->get();
        $categories = Category::where('status', '1')->get();

        $query = Product::where([
            ['idBrand', '=', $id],
            ['status', '=', '1']
        ]);

        // Logic bộ lọc kép
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('idCategory', $request->categories);
        }

        $products = $query->get();

        // CHỖ NÀY PHẢI CÓ 'all_concentrations' VÀ 'categories'
        return view('show-product', compact('all_concentrations', 'categories', 'products', 'brand', 'title', 'footers'));
    }
    public function single_product($id)
    {

        $products = Product::with('comment')
            ->where('id', $id)
            ->where('status', 1)
            ->get();
        return view('layout.single_product', compact('products'));
    }
    public function showProducts(Request $request)
    {
        // 1. Lấy danh sách Nồng độ để in ra bộ lọc (Đây là cái ông đang bị thiếu)
        $categories = Category::where('status', '1')->get();
       
        $all_concentrations = Concentration::where('status', '1')->get();
        $all_brands = Brand::where('status', '1')->get();
        // 2. Khởi tạo Query lấy sản phẩm
        $query = Product::where('status', 1);

        // 3. Logic lọc Nồng độ
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        
        }
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('idCategory', $request->categories);
        }
        if ($request->has('brands') && is_array($request->brands)) {
            $query->whereIn('idBrand', $request->brands);
        }

        $products = $query->get();
        $title = Title::all();
        $footers = Footer::all();
        // 5. Trả về đúng cái file show-product mà ông đang làm
        return view('show-product', compact('all_concentrations', 'all_brands', 'categories', 'products', 'title', 'footers'));
    }
}
