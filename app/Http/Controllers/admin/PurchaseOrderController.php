<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ManuFacturer;
use App\Models\ProcurementRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SupplierOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * PurchaseOrderController — Quản lý đơn đặt hàng NSX.
 *
 * Luồng:
 *   1. store()        — Tạo PO từ báo giá (admin tick SP + điền qty trong trang báo giá)
 *   2. index()        — Danh sách tất cả đơn đặt hàng
 *   3. show()         — Chi tiết đơn + nút cập nhật trạng thái
 *   4. updateStatus() — Cập nhật trạng thái: pending → confirmed → delivering
 *   5. receive()      — Xác nhận nhận hàng (status = received), KHÔNG tự cộng kho
 *   6. exportCsv()    — Xuất CSV để upload vào trang Nhập Kho (cộng tồn kho thủ công)
 *   7. exportExcel()  — Xuất Đơn Mua Hàng dạng Excel có format đẹp để gửi NSX
 */
class PurchaseOrderController extends Controller
{
    // =========================================================================
    // INDEX — Danh sách đơn đặt hàng
    // =========================================================================

    public function index()
    {
        // Lấy tất cả đơn, kèm tên NSX, sắp theo mới nhất
        $orders = PurchaseOrder::with('manufacturer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.purchase-order.purchase-order-list', compact('orders'));
    }

    // =========================================================================
    // STORE — Tạo PO từ báo giá (nhận form từ supplier-offer/show.blade.php)
    // =========================================================================

    public function store(Request $request)
    {
        // Validate: phải có offer_id hợp lệ, ít nhất 1 item được chọn với đủ thông tin
        $request->validate([
            'offer_id'              => 'required|exists:supplier_offers,id',
            'items'                 => 'required|array|min:1',
            'items.*.offer_item_id' => 'required|exists:supplier_offer_items,id',
            'items.*.product_id'    => 'nullable|exists:products,id',
            'items.*.product_name'  => 'required|string',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.quantity'      => 'required|integer|min:1',
        ], [
            'offer_id.required'         => 'Thiếu thông tin báo giá.',
            'items.required'            => 'Vui lòng chọn ít nhất 1 sản phẩm.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'items.*.quantity.min'      => 'Số lượng phải ít nhất là 1.',
        ]);

        $offer = SupplierOffer::findOrFail($request->offer_id);

        // Tính tổng tiền: sum(số lượng × đơn giá) cho từng item được chọn
        $totalAmount = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);

        // Sinh order_code: PO-YYYYMMDD-001, 002, ...
        $count     = PurchaseOrder::whereDate('created_at', today())->count() + 1;
        $orderCode = 'PO-' . now()->format('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        // Dùng transaction để đảm bảo tất cả bước thành công hoặc rollback hết
        DB::transaction(function () use ($request, $offer, $totalAmount, $orderCode) {

            // Bước 1: Tạo đầu phiếu đơn đặt hàng
            $po = PurchaseOrder::create([
                'offer_id'        => $offer->id,
                'manufacturer_id' => $offer->manufacturer_id,
                'order_code'      => $orderCode,
                'total_amount'    => $totalAmount,
                'status'          => 'pending',          // mặc định: chờ xác nhận
                'expected_date'   => $request->expected_date ?? null,
                'note'            => $request->note ?? null,
                'created_by'      => Auth::id(),         // admin nào tạo đơn
            ]);

            // Bước 2: Tạo từng dòng sản phẩm trong đơn
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $item['product_id'] ?? null,
                    'product_name'      => $item['product_name'],
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $item['unit_price'],
                ]);
            }

            // Bước 3: Sync SP vào danh bạ manufacturers_product
            // Chỉ sync những SP đã có product_id (tức là SP tồn tại trong hệ thống)
            // syncWithoutDetaching = thêm mới, không xóa quan hệ cũ
            $productIds = collect($request->items)
                ->pluck('product_id')
                ->filter()   // bỏ null
                ->unique()
                ->values()
                ->toArray();

            if (!empty($productIds)) {
                $manufacturer = ManuFacturer::find($offer->manufacturer_id);
                $manufacturer?->products()->syncWithoutDetaching($productIds);
            }

            // Bước 4: Đánh dấu báo giá đã được chấp nhận
            $offer->update(['status' => 'accepted']);

            // Bước 5: Tự động đóng yêu cầu nhập hàng (nếu có)
            if ($offer->request_id) {
                $procRequest = ProcurementRequest::find($offer->request_id);
                if ($procRequest && $procRequest->status !== 'closed') {
                    $procRequest->update(['status' => 'closed']);
                }
            }
        });

        return redirect()->route('admin.purchase-orders.index')
            ->with('success', 'Đã tạo đơn đặt hàng ' . $orderCode . ' thành công.');
    }

    // =========================================================================
    // SHOW — Chi tiết đơn đặt hàng
    // =========================================================================

    public function show(string $id)
    {
        // Eager load: items.product để có thể xuất CSV, offer để có link quay lại báo giá gốc
        $purchaseOrder = PurchaseOrder::with(['manufacturer', 'items.product', 'offer'])
            ->findOrFail($id);

        return view('admin.purchase-order.show', compact('purchaseOrder'));
    }

    // =========================================================================
    // UPDATE STATUS — Cập nhật trạng thái đơn hàng theo từng bước
    // =========================================================================

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            // Chỉ cho phép các trạng thái hợp lệ (không cho nhảy về received ở đây)
            'status' => 'required|in:pending,confirmed,delivering,cancelled',
        ], [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in'       => 'Trạng thái không hợp lệ.',
        ]);

        $po = PurchaseOrder::findOrFail($id);
        $po->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }

    // =========================================================================
    // RECEIVE — Xác nhận đã nhận hàng (chỉ đổi status, KHÔNG tự cộng kho)
    // =========================================================================

    public function receive(string $id)
    {
        $po = PurchaseOrder::findOrFail($id);

        // Ngăn xác nhận 2 lần
        if ($po->status === 'received') {
            return redirect()->back()->with('error', 'Đơn hàng này đã được xác nhận trước đó.');
        }

        // Chỉ đổi trạng thái → tồn kho được cập nhật qua luồng Nhập Kho riêng
        $po->update(['status' => 'received']);

        return redirect()->back()->with('success', 'Đã xác nhận nhận hàng thành công!');
    }

    // =========================================================================
    // EXPORT EXCEL — Xuất Đơn Mua Hàng dạng Excel đẹp để gửi NSX
    // =========================================================================

    public function exportExcel(string $id)
    {
        // Load đầy đủ quan hệ để PurchaseOrderExport có thể truy cập thông tin SP
        $po = PurchaseOrder::with(
            'items.product.category',
            'items.product.brand',
            'items.product.concentration',
            'manufacturer'
        )->findOrFail($id);

        // Delegate sang class Export riêng (app/Exports/PurchaseOrderExport.php)
        return (new \App\Exports\PurchaseOrderExport($po))->download();
    }

    // =========================================================================
    // EXPORT CSV — Xuất file CSV để nhập vào trang Nhập Kho
    // =========================================================================

    public function exportCsv(string $id)
    {
        $po = PurchaseOrder::with(
            'items.product.category',
            'items.product.brand',
            'items.product.concentration'
        )->findOrFail($id);

        $filename = 'nhap-kho-' . $po->order_code . '-' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Dùng stream để xuất CSV không cần lưu file tạm
        $callback = function () use ($po) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8: giúp Excel mở file và hiển thị đúng tiếng Việt
            fputs($handle, "\xEF\xBB\xBF");

            // Dòng header — khớp đúng format WarehouseController::mapRow()
            // Bao gồm cả sl_order (đã đặt) và quantity (thực nhận) + unit_price (giá nhập)
            fputcsv($handle, ['title', 'image', 'decription', 'unit_price', 'sl_order', 'quantity', 'volume', 'category', 'brand', 'concentration']);

            foreach ($po->items as $item) {
                $product = $item->product;
                fputcsv($handle, [
                    $item->product_name,                          // Tên SP
                    $product?->image ?? '',                       // URL ảnh
                    $product?->decription ?? '',                  // Mô tả
                    $item->unit_price,                            // Giá nhập (từ báo giá NSX)
                    $item->quantity,                              // SL đã order
                    '',                                           // SL thực tế - để trống cho NV kho điền
                    $product?->volume ?? '',                      // Dung tích
                    $product?->category?->name ?? '',             // Danh mục
                    $product?->brand?->name ?? '',                // Thương hiệu
                    $product?->concentration?->concentration ?? '', // Nồng độ
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
