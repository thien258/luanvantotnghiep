<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Category;
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
        $query = Product::where([
            ['idCategory', '=', $id],
            ['status', '=', '1']
        ]);

        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }


        $products = $query->get();

    
       

        // 5. Trả về đúng cái file show-product mà ông đang làm
        return view('show-product', compact('all_concentrations', 'products', 'category', 'title', 'footers'));
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
        $all_concentrations = Concentration::where('status', '1')->get();

        // 2. Khởi tạo Query lấy sản phẩm
        $query = Product::where('status', 1);

        // 3. Logic lọc Nồng độ
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }


        $products = $query->get();

        $categories = Category::where('status', '1')->get();
        $title = Title::all();
        $footers = Footer::all();

        // 5. Trả về đúng cái file show-product mà ông đang làm
        return view('show-product', compact('all_concentrations', 'products', 'categories', 'title', 'footers'));
    }
}
