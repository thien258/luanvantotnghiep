<?php

namespace App\Http\Controllers\admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Concentration;
use App\Models\Volume;
use App\Models\Brand;
use App\Models\Festival;
use App\Models\FestivalProductVariant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\View;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
        $products = Product::orderBy('id', 'desc')->get();
        View::share('products', $products);
    }
    public function index()
    {
        $products = Product::with('variants.volume')->get();
        return view('admin.product.product-list', compact('products'));
    }
    public function create()
    {
        $categories = Category::all();
        $concentrations = Concentration::all();
        $volumes = Volume::all();
        $festivals = Festival::where('status', 1)->get();
        $brands = Brand::all();

        return view('admin.product.add', compact('categories', 'concentrations', 'volumes', 'brands', 'festivals'));
    }
    public function store(Request $request)
    {
        $product = Product::create([
            'title' => $request->title,
            'image' => $request->image,
            'decription' => $request->decription,
            'status' => $request->status ?? 1,
            'idConcentration' => $request->idConcentration,
            'idCategory' => $request->idCategory,
            'idBrand' => $request->idBrand,
        ]);

        if ($product) {
            // Quét qua mảng variants được tích chọn từ Form
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $volumeId => $data) {

                    // Chỉ lưu vào Database nếu checkbox của dung tích đó được tích
                    if (isset($data['checked']) && $data['checked'] == 1) {
                        $variant = ProductVariant::create([
                            'idProduct' => $product->id,
                            'idVolume'  => $volumeId,
                            'price'     => $data['price'] ?? 0,
                            'stock'     => $data['stock'] ?? 0,
                        ]);

                        // LOGIC MỚI: Sau khi tạo variant xong, kiểm tra xem có chọn lễ hội riêng không
                        if ($request->has("variant_festivals.{$volumeId}")) {
                            // Dùng attach để lưu vào bảng trung gian
                            $variant->specificFestivals()->attach($request->variant_festivals[$volumeId]);
                        }
                    }
                }
            }

            // Lưu lễ hội chung toàn sản phẩm
            if (($request->has('idFestival')) && is_array($request->idFestival)) {
                $product->festivals()->attach($request->idFestival);
            }

            return redirect()->route('admin.product.index');
        } else {
            return back();
        }
    }
    public function edit($id)
    {

        $product = Product::with('variants')->findOrFail($id);

        $categories = Category::all();
        $concentrations = Concentration::all();
        $volumes = Volume::all();
        $brands = Brand::all();
        $festivals = Festival::where('status', 1)->get();

        $selectedFestivalIds = $product->festivals()->pluck('festivals.id')->toArray();
        return view('admin.product.edit', compact('product', 'categories', 'concentrations', 'volumes', 'brands', 'festivals', 'selectedFestivalIds'));
    }
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // 1. Cập nhật thông tin chung
        $product->update([
            'title' => $request->title,
            'image' => $request->image,
            'decription' => $request->decription,
            'status' => $request->status,
            'idConcentration' => $request->idConcentration,
            'idCategory' => $request->idCategory,
            'idBrand' => $request->idBrand,
        ]);

        // 2. LOGIC MỚI: Cập nhật danh sách các biến thể dung tích
        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $volumeId => $data) {
                if (isset($data['checked']) && $data['checked'] == 1) {

                    // CHÚ Ý CHỖ NÀY: Tuyệt đối không có trường 'status' ở đây nhé
                    ProductVariant::updateOrCreate(
                        ['idProduct' => $product->id, 'idVolume' => $volumeId],
                        [
                            'price' => $data['price'] ?? 0,
                            'stock' => $data['stock'] ?? 0
                        ]
                    );
                } else {
                    // Nếu bỏ tích chọn: Xóa dung tích này khỏi sản phẩm
                    ProductVariant::where('idProduct', $product->id)
                        ->where('idVolume', $volumeId)
                        ->delete();
                }
            }
        }
        // ... [Đoạn cập nhật Product và Variant ở trên giữ nguyên] ...

        // LOGIC MỚI: Cập nhật nhiều lễ hội cho TỪNG dung tích
        if ($request->has('variant_festivals')) {
            foreach ($request->variant_festivals as $volumeId => $festivalIds) {
                $variant = ProductVariant::where('idProduct', $product->id)->where('idVolume', $volumeId)->first();
                if ($variant) {
                    // Tự động xóa cái cũ, thêm cái mới (giống y hệt sản phẩm)
                    $variant->specificFestivals()->sync($festivalIds);
                }
            }
        } else {
            // Nếu Admin bỏ tích sạch mọi lễ hội riêng của tất cả dung tích
            foreach ($product->variants as $variant) {
                $variant->specificFestivals()->detach();
            }
        }

        // Sync Lễ hội chung toàn sản phẩm
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
        ProductVariant::where('idProduct', $product->id)->delete();

        $product->delete();

        return redirect()->route('admin.product.index');
    }
    public function suggest(Request $request)
    {
        $keyword = $request->keyword;

        if (empty($keyword)) {
            return response()->json([]);
        }

        // Admin thì lấy cả sản phẩm đang Tắt hoặc Bật (không cần where status)
        $products = Product::where('title', 'LIKE', "%{$keyword}%")
            ->select('id', 'title', 'image', 'status')
            ->take(5) // Lấy tối đa 5 cái cho Admin
            ->get();

        return response()->json($products);
    }
}
