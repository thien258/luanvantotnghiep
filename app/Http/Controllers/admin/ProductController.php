<?php

namespace App\Http\Controllers\admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Concentration;
use App\Models\Brand;
use App\Models\Festival;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
        // Chia sẻ danh sách sản phẩm phẳng (giá, số lượng lấy trực tiếp từ sản phẩm)
        $products = Product::orderBy('id', 'desc')->get();
        View::share('products', $products);
    }

    public function index()
    {
        // Không còn variants.volume, chỉ lấy sản phẩm và sự kiện đi kèm
        $products = Product::with('festivals')->get();
        return view('admin.product.product-list', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $concentrations = Concentration::all();
        $festivals = Festival::where('status', 1)->get();
        $brands = Brand::all();

        // Đã loại bỏ biến $volumes vì dung tích giờ nhập text trực tiếp
        return view('admin.product.add', compact('categories', 'concentrations', 'brands', 'festivals'));
    }

    public function store(Request $request)
    {
        // Thực hiện lưu trực tiếp cấu hình phẳng vào bảng products
        $product = Product::create([
            'title' => $request->title,
            'image' => $request->image,
            'decription' => $request->decription,
            'status' => $request->status ?? 1,
            'price' => $request->price ?? 0,        // Lưu trực tiếp giá bán
            'quantity' => $request->quantity ?? 0,  // Lưu trực tiếp số lượng kho
            'volume' => $request->volume,          // Lưu trực tiếp chữ dung tích (vd: 100ml)
            'idConcentration' => $request->idConcentration,
            'idCategory' => $request->idCategory,
            'idBrand' => $request->idBrand,
        ]);

        if ($product) {
            // Lưu liên kết lễ hội chung toàn sản phẩm từ mảng checkbox [idFestival] sang bảng trung gian
            if ($request->has('idFestival') && is_array($request->idFestival)) {
                $product->festivals()->attach($request->idFestival);
            }

            return redirect()->route('admin.product.index');
        } else {
            return back();
        }
    }

    public function edit($id)
    {
        // Loại bỏ load quan hệ variants cũ
        $product = Product::findOrFail($id);

        $categories = Category::all();
        $concentrations = Concentration::all();
        $brands = Brand::all();
        $festivals = Festival::where('status', 1)->get();

        // Lấy danh sách ID các lễ hội sản phẩm đang tham gia để giữ trạng thái checked trên Form
        $selectedFestivalIds = $product->festivals()->pluck('festivals.id')->toArray();
        
        return view('admin.product.edit', compact('product', 'categories', 'concentrations', 'brands', 'festivals', 'selectedFestivalIds'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Cập nhật tất cả dữ liệu phẳng trực tiếp vào dòng sản phẩm
        $product->update([
            'title' => $request->title,
            'image' => $request->image,
            'decription' => $request->decription,
            'status' => $request->status,
            'price' => $request->price ?? 0,
            'quantity' => $request->quantity ?? 0,
            'volume' => $request->volume,
            'idConcentration' => $request->idConcentration,
            'idCategory' => $request->idCategory,
            'idBrand' => $request->idBrand,
        ]);

        // Đồng bộ hóa mối quan hệ Lễ hội qua bảng trung gian phẳng festival_product
        $festivalIds = $request->input('idFestival', []);
        $product->festivals()->sync($festivalIds);

        return redirect()->route('admin.product.index');
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return back()->with('error', 'Sản phẩm không tồn tại.');
        }

        // Đã xóa bỏ phần gỡ bỏ dữ liệu bảng ProductVariant con
        $product->delete();

        return redirect()->route('admin.product.index');
    }

    public function suggest(Request $request)
    {
        $keyword = $request->keyword;

        if (empty($keyword)) {
            return response()->json([]);
        }

        $products = Product::where('title', 'LIKE', "%{$keyword}%")
            ->select('id', 'title', 'image', 'status')
            ->take(5)
            ->get();

        return response()->json($products);
    }
}