<?php

namespace App\Http\Controllers\admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Concentration;
use App\Models\Brand;
use App\Models\Festival;
use App\Models\ManuFacturer;
use App\Models\ProcurementRequest;
use App\Models\ProcurementRequestItem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

/**
 * ProductController — Quản lý CRUD sản phẩm trong admin.
 *
 * Chỉ xử lý tạo / sửa / xóa / liệt kê sản phẩm.
 * Logic kho và nhập kho đã được tách sang WarehouseController.
 *
 * Quan hệ many-to-many được xử lý:
 *   - festivals: SP thuộc festival nào (qua festival_product)
 *   - manufacturers: SP do NSX nào cung cấp (qua manufacturers_product)
 */
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Chia sẻ danh sách sản phẩm sang view (dùng cho các dropdown trong admin)
        View::share('products', Product::orderBy('id', 'desc')->get());
    }

    // =========================================================================
    // INDEX — Danh sách sản phẩm
    // =========================================================================

    public function index()
    {
        // Eager load festivals để hiển thị badge festival trong bảng
        $products = Product::with('festivals')->get();
        return view('admin.product.product-list', compact('products'));
    }

    // =========================================================================
    // CREATE — Form thêm sản phẩm mới
    // =========================================================================

    public function create()
    {
        return view('admin.product.add', [
            'categories'     => Category::all(),
            'concentrations' => Concentration::all(),
            'brands'         => Brand::all(),
            'manufacturers'  => ManuFacturer::all(),
            'festivals'      => Festival::where('status', 1)->get(), // chỉ festival đang active
        ]);
    }

    // =========================================================================
    // STORE — Lưu sản phẩm mới
    // =========================================================================

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
            // Gán SP vào festival nếu có chọn
            if ($request->has('idFestival') && is_array($request->idFestival)) {
                $product->festivals()->attach($request->idFestival);
            }

            // Gán SP vào danh bạ NSX nếu có chọn
            if ($request->has('idManufacturer') && is_array($request->idManufacturer)) {
                $product->manufacturers()->attach($request->idManufacturer);
            }

            return redirect()->route('admin.product.index');
        }

        return back();
    }

    // =========================================================================
    // EDIT — Form chỉnh sửa sản phẩm
    // =========================================================================

    public function edit($id)
    {
        $product = Product::findOrFail($id);

        return view('admin.product.edit', [
            'product'                 => $product,
            'categories'              => Category::all(),
            'concentrations'          => Concentration::all(),
            'brands'                  => Brand::all(),
            'festivals'               => Festival::where('status', 1)->get(),
            // IDs festival hiện tại của SP (để pre-check checkbox)
            'selectedFestivalIds'     => $product->festivals()->pluck('festivals.id')->toArray(),
            'manufacturers'           => ManuFacturer::all(),
            // IDs NSX hiện tại của SP (để pre-check checkbox)
            'selectedManufacturerIds' => $product->manufacturers()->pluck('manufacturers.id')->toArray(),
        ]);
    }

    // =========================================================================
    // UPDATE — Lưu thay đổi sản phẩm
    // =========================================================================

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
            'price.required'           => 'Vui lòng nhập giá bán.',
            'quantity.required'        => 'Vui lòng nhập số lượng.',
            'status.required'          => 'Vui lòng chọn trạng thái.',
            'idConcentration.required' => 'Vui lòng chọn nồng độ.',
            'idCategory.required'      => 'Vui lòng chọn danh mục.',
            'idBrand.required'         => 'Vui lòng chọn thương hiệu.',
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

        // sync() = đồng bộ: xóa quan hệ cũ, thêm quan hệ mới
        // (khác attach() chỉ thêm, khác detach() chỉ xóa)
        $product->festivals()->sync($request->input('idFestival', []));
        $product->manufacturers()->sync($request->input('idManufacturer', []));

        return redirect()->route('admin.product.index');
    }

    // =========================================================================
    // DESTROY — Xóa sản phẩm
    // =========================================================================

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
    // CREATE ORDER REQUEST — Tạo yêu cầu nhập hàng từ modal trang sản phẩm
    // =========================================================================

    /**
     * Nhận danh sách SP tick trong modal → tạo ProcurementRequest công khai
     * → tất cả NSX đều thấy và có thể chào giá
     */
    public function createOrderRequest(Request $request)
    {
        $productIds = $request->input('product_ids', []);

        if (empty($productIds)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm.');
        }

        $products = Product::whereIn('id', $productIds)->get();

        if ($products->isEmpty()) {
            return redirect()->back()->with('error', 'Không tìm thấy sản phẩm.');
        }

        // Sinh request_code: PRQ-YYYYMMDD-001
        $count = ProcurementRequest::whereDate('created_at', today())->count() + 1;
        $code  = 'PRQ-' . now()->format('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        // Tạo yêu cầu thu mua (status = open → NSX có thể thấy và chào giá)
        $req = ProcurementRequest::create([
            'request_code' => $code,
            'status'       => 'open',
            'note'         => $request->input('note'),
            'deadline'     => $request->input('deadline') ?? now()->addDays(7)->toDateString(),
            'created_by'   => Auth::id(),
        ]);

        $qtySuggest = $request->input('qty_suggest', []);

        foreach ($products as $product) {
            ProcurementRequestItem::create([
                'request_id'   => $req->id,
                'product_id'   => $product->id,
                'product_name' => $product->title,
                'qty_needed'   => (int)($qtySuggest[$product->id] ?? 10),
                'note'         => 'Tồn kho hiện tại: ' . $product->quantity,
            ]);
        }

        return redirect()->route('admin.procurement.show', $req->id)
            ->with('success', 'Đã đăng yêu cầu nhập hàng ' . $code . '. NSX có thể xem và chào giá.');
    }

    // =========================================================================
    // SUGGEST — Gợi ý sản phẩm (AJAX)
    // =========================================================================

    /**
     * Trả về JSON tối đa 5 sản phẩm khớp với keyword.
     * Dùng cho thanh tìm kiếm nhanh trong admin (adminProduct_search.js).
     */
    public function suggest(Request $request)
    {
        if (empty($request->keyword)) return response()->json([]);

        return response()->json(
            Product::where('title', 'LIKE', "%{$request->keyword}%")
                ->select('id', 'title', 'image', 'status')
                ->take(5)
                ->get()
        );
    }
}
