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
use Illuminate\Support\Facades\View;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        View::share('categories', Category::where('status', '1')->get());
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


        return view('show-product', compact('all_concentrations', 'categories', 'products', 'brand', 'title', 'footers'));
    }
    public function single_product($id)
    {


        $product = Product::with(['comment', 'variants.volume', 'concentration'])
            ->where('status', 1)
            ->findOrFail($id);


        return view('layout.single_product', compact('product'));
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
    public function suggest(Request $request)
    {
        $keyword = $request->keyword;

        if (empty($keyword)) {
            return response()->json([]);
        }

        // 1. Lấy sản phẩm và kéo kèm theo cột idProduct và price từ bảng variants
        $products = Product::where('title', 'LIKE', "%{$keyword}%")
            ->where('status', 1)
            ->select('id', 'title', 'image')
            ->with(['variants' => function ($query) {
                $query->select('idProduct', 'price');
            }])
            ->take(3)
            ->get();

        $results = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'title' => $product->title,
                'image' => $product->image,
                'price' => $product->variants->min('price') ?? 0 // Lấy giá thấp nhất
            ];
        });
        return response()->json($results);
    }
    public function search(Request $request)
    {
     $keyword = $request->keyword;

        // 1. Tìm sản phẩm giống từ khóa
        $products = Product::where('title', 'LIKE', "%{$keyword}%")
                           ->where('status', 1)
                           ->with('variants.volume')
                           ->paginate(12);

        // 2. LẤY THÊM DỮ LIỆU CHO HEADER VÀ FOOTER (Giống hệt các hàm khác của ông)
        $brands = Brand::where('status', '1')->get(); 
        $title = Title::all();
        $footers = Footer::all();

        // 3. Trả về view kèm theo đầy đủ "hành trang"
        return view('search_result', compact('products', 'keyword', 'brands', 'title', 'footers'));
    }
}
