<?php

namespace App\Http\Controllers\admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Concentration;
use App\Models\Brand;
use App\Models\Festival;
use App\Models\ManuFacturer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

/**
 * ProductController — Quản lý CRUD sản phẩm.
 * Logic kho và nhập kho đã được chuyển sang WarehouseController.
 */
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        View::share('products', Product::orderBy('id', 'desc')->get());
    }

    // =========================================================================
    // RESOURCE CRUD SẢN PHẨM
    // =========================================================================

    public function index()
    {
        $products = Product::with('festivals')->get();
        return view('admin.product.product-list', compact('products'));
    }

    public function create()
    {
        return view('admin.product.add', [
            'categories'     => Category::all(),
            'concentrations' => Concentration::all(),
            'brands'         => Brand::all(),
            'manufacturers'  => ManuFacturer::all(),
            'festivals'      => Festival::where('status', 1)->get(),
        ]);
    }

    public function store(Request $request)
    {



        $request->validate([
            'title'           => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',
            'quantity'        => 'required|integer|min:0',
            'status'          => 'required|in:0,1',
            'volume'          => 'nullable|string|max:50',
            'image'           => 'nullable|string|max:500',
            'decription'      => 'nullable|string|max:5000',
            'idConcentration' => 'required|integer|exists:concentrations,id',
            'idCategory'      => 'required|integer|exists:categories,id',
            'idBrand'         => 'required|integer|exists:brands,id',
        ], [
            'title.required'           => 'Vui lòng nhập tên sản phẩm.',
            'title.max'                => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'price.required'           => 'Vui lòng nhập giá bán.',
            'price.numeric'            => 'Giá bán phải là số.',
            'price.min'                => 'Giá bán không được nhỏ hơn 0.',
            'quantity.required'        => 'Vui lòng nhập số lượng.',
            'quantity.integer'         => 'Số lượng phải là số nguyên.',
            'quantity.min'             => 'Số lượng không được nhỏ hơn 0.',
            'status.required'          => 'Vui lòng chọn trạng thái.',
            'status.in'                => 'Trạng thái không hợp lệ.',
            'volume.max'               => 'Dung tích không được vượt quá 50 ký tự.',
            'image.max'                => 'Đường dẫn ảnh không được vượt quá 500 ký tự.',
            'decription.max'           => 'Mô tả không được vượt quá 5000 ký tự.',
            'idConcentration.required' => 'Vui lòng chọn nồng độ.',
            'idConcentration.exists'   => 'Nồng độ không tồn tại trong hệ thống.',
            'idCategory.required'      => 'Vui lòng chọn danh mục.',
            'idCategory.exists'        => 'Danh mục không tồn tại trong hệ thống.',
            'idBrand.required'         => 'Vui lòng chọn thương hiệu.',
            'idBrand.exists'           => 'Thương hiệu không tồn tại trong hệ thống.',
        ]);

        $product = Product::create([
            'title'           => $request->title,
            'image'           => $request->image,
            'decription'      => $request->decription,
            'status'          => $request->status ?? 1,
            'price'           => $request->price ?? 0,
            'quantity'        => $request->quantity ?? 0,
            'volume'          => $request->volume,
            'idConcentration' => $request->idConcentration,
            'idCategory'      => $request->idCategory,
            'idBrand'         => $request->idBrand,
        ]);

        if ($product) {
            if ($request->has('idFestival') && is_array($request->idFestival)) {
                $product->festivals()->attach($request->idFestival);
            }
            if ($request->has('idManufacturer') && is_array($request->idManufacturer)) {
                $product->manufacturers()->attach($request->idManufacturer);
            }
            return redirect()->route('admin.product.index');
        }
        return back();
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.product.edit', [
            'product'             => $product,
            'categories'          => Category::all(),
            'concentrations'      => Concentration::all(),
            'brands'              => Brand::all(),
            'festivals'           => Festival::where('status', 1)->get(),
            'selectedFestivalIds' => $product->festivals()->pluck('festivals.id')->toArray(),
            'manufacturers'       => ManuFacturer::all(),
            'selectedManufacturerIds' => $product->manufacturers()->pluck('manufacturers.id')->toArray(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'title'           => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',
            'quantity'        => 'required|integer|min:0',
            'status'          => 'required|in:0,1',
            'volume'          => 'nullable|string|max:50',
            'image'           => 'nullable|string|max:500',
            'decription'      => 'nullable|string|max:5000',
            'idConcentration' => 'required|integer|exists:concentrations,id',
            'idCategory'      => 'required|integer|exists:categories,id',
            'idBrand'         => 'required|integer|exists:brands,id',
        ], [
            'title.required'           => 'Vui lòng nhập tên sản phẩm.',
            'title.max'                => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'price.required'           => 'Vui lòng nhập giá bán.',
            'price.numeric'            => 'Giá bán phải là số.',
            'price.min'                => 'Giá bán không được nhỏ hơn 0.',
            'quantity.required'        => 'Vui lòng nhập số lượng.',
            'quantity.integer'         => 'Số lượng phải là số nguyên.',
            'quantity.min'             => 'Số lượng không được nhỏ hơn 0.',
            'status.required'          => 'Vui lòng chọn trạng thái.',
            'status.in'                => 'Trạng thái không hợp lệ.',
            'idConcentration.required' => 'Vui lòng chọn nồng độ.',
            'idConcentration.exists'   => 'Nồng độ không tồn tại trong hệ thống.',
            'idCategory.required'      => 'Vui lòng chọn danh mục.',
            'idCategory.exists'        => 'Danh mục không tồn tại trong hệ thống.',
            'idBrand.required'         => 'Vui lòng chọn thương hiệu.',
            'idBrand.exists'           => 'Thương hiệu không tồn tại trong hệ thống.',
        ]);

        $product->update([
            'title'           => $request->title,
            'image'           => $request->image,
            'decription'      => $request->decription,
            'status'          => $request->status,
            'price'           => $request->price ?? 0,
            'quantity'        => $request->quantity ?? 0,
            'volume'          => $request->volume,
            'idConcentration' => $request->idConcentration,
            'idCategory'      => $request->idCategory,
            'idBrand'         => $request->idBrand,
        ]);

        $product->festivals()->sync($request->input('idFestival', []));
        $product->manufacturers()->sync($request->input('idManufacturer', []));
        return redirect()->route('admin.product.index');
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return back()->with('error', 'Sản phẩm không tồn tại.');
        }
        $product->delete();
        return redirect()->route('admin.product.index');
    }

    // =========================================================================
    // GỢI Ý SẢN PHẨM (AJAX)
    // =========================================================================

    public function suggest(Request $request)
    {
        if (empty($request->keyword)) return response()->json([]);
        return response()->json(
            Product::where('title', 'LIKE', "%{$request->keyword}%")
                ->select('id', 'title', 'image', 'status')
                ->take(5)->get()
        );
    }
}
