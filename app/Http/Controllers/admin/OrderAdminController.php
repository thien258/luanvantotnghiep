<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use \App\Models\WarehouseStockLog;

/**
 * OrderAdminController — Quản lý đơn hàng khách trong trang admin.
 *
 * Luồng trạng thái đơn hàng:
 *   0 → Chờ thanh toán PayOS (không hiện trong danh sách)
 *   1 → Đã thanh toán / COD đã đặt, chờ xuất kho
 *   3 → Đã xuất kho (trừ tồn kho), đang giao shipper
 *   4 → Giao thành công (khách xác nhận qua QR)
 *   5 → Khách yêu cầu hoàn hàng
 *   6 → Hàng hỏng / chờ trả nhà sản xuất
 */
class OrderAdminController extends Controller
{
    // =========================================================================
    // INDEX — Danh sách đơn hàng, hỗ trợ tìm kiếm + lọc trạng thái
    // =========================================================================
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $role = auth()->user()->role;
            // orders-damaged: admin và warehouse vào được
            // orders thường: chỉ warehouse
            if ($role === 'admin' && request()->routeIs('admin.orders.damaged')) {
                return $next($request);
            }
            if ($role !== 'warehouse') {
                abort(403);
            }
            return $next($request);
        });
    }
    public function index(Request $request)
    {
        // Lấy từ khóa tìm kiếm và filter trạng thái từ query string
        $keyword = $request->input('q', '');
        $status  = $request->input('status', '');

        // Query cơ bản: bỏ đơn status=0 (chờ PayOS chưa thanh toán)
        // Sắp xếp: ưu tiên đơn chờ xử lý (1) lên đầu, rồi theo thanh toán
        $query = Order::where('status', '!=', 0)
            ->orderByRaw("FIELD(status, 1, 3, 4, 5, 6) ASC")
            ->orderByRaw("FIELD(payment_method, 'BANK TRANSFER', 'COD') ASC")
            ->orderBy('created_at', 'ASC');

        // Lọc theo trạng thái nếu có chọn
        if ($status != '') {
            $query->where('status', $status);
        }

        // Tìm kiếm theo ID đơn, tên, SĐT, tracking code
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                // Cho phép tìm theo "#DH85", "DH85", "#85", "85"
                $numericId = preg_replace('/^(dh|#dh|#)/i', '', trim($keyword));
                if (is_numeric($numericId)) {
                    $q->orWhere('id', $numericId);
                }
                $q->orWhere('fullname', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('tracking_code', 'like', "%{$keyword}%");
            });
        }

        $orders = $query->paginate(20);

        // AJAX request (từ filter JS) → trả về HTML rows + count để cập nhật bảng không reload trang
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.order.partials.order-rows', compact('orders'))->render();
            return response()->json(['html' => $html, 'count' => $orders->count()]);
        }

        return view('admin.order.order-list', compact('orders', 'keyword', 'status'));
    }

    // =========================================================================
    // SHOW — Chi tiết 1 đơn hàng
    // =========================================================================

    public function show($id)
    {
        $order = Order::findOrFail($id);

        // Lấy các dòng sản phẩm, eager load product để tránh N+1
        $orderdetail = OrderDetail::where('idOrder', $id)->with('product')->get();

        return view('admin.order.show', compact('order', 'orderdetail'));
    }

    // =========================================================================
    // UPDATE STATUS — Cập nhật trạng thái + xuất kho khi giao shipper
    // =========================================================================

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'      => 'nullable|integer|in:1,3,4,5,6',
            'action_type' => 'nullable|in:export_warehouse',
        ], [
            'status.integer' => 'Trạng thái không hợp lệ.',
            'status.in'      => 'Trạng thái đơn hàng không hợp lệ.',
            'action_type.in' => 'Hành động không được hỗ trợ.',
        ]);

        $order      = Order::findOrFail($id);
        $nextStatus = $request->input('status');
        $actionType = $request->input('action_type');

        // HÀNH ĐỘNG ĐẶC BIỆT: Xuất kho → giao shipper
        // Chỉ thực hiện khi đơn đang ở status=1 (đã thanh toán, chờ xuất)
        if ($actionType === 'export_warehouse' && $order->status == 1) {
            $orderDetails = OrderDetail::where('idOrder', $id)->with('product')->get();

            foreach ($orderDetails as $detail) {
                if (!$detail->product) continue;

                $product      = $detail->product;
                $qtyToExport  = $detail->quantity; // tổng cần trừ cho SP này

                // ── FIFO: trừ từ lô HSD gần nhất trước ──────────────────
                // Gom tổng import theo (product_id, expiry_date) để biết mỗi lô có bao nhiêu
                $batchTotals = WarehouseStockLog::where('product_id', $product->id)
                    ->where('type', 'import')
                    ->selectRaw('expiry_date, SUM(quantity) as total_import')
                    ->groupBy('expiry_date')
                    ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END ASC')
                    // → lô có HSD lên trước, lô NULL xuống sau
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                // Gom tổng export theo (product_id, expiry_date) — đã xuất bao nhiêu từ mỗi lô
                $exportedByBatch = WarehouseStockLog::where('product_id', $product->id)
                    ->where('type', 'export')
                    ->selectRaw('expiry_date, SUM(quantity) as total_export')
                    ->groupBy('expiry_date')
                    ->get()
                    ->keyBy(fn($r) => (string)($r->expiry_date ?? 'null'));
                // keyBy → chuyển thành mảng tra cứu nhanh theo expiry_date
                // VD: { "2027-01-01": {total_export: 20}, "null": {total_export: 5} }

                $remaining = $qtyToExport; // số còn cần trừ

                foreach ($batchTotals as $batch) {
                    if ($remaining <= 0) break;

                    $batchKey     = (string)($batch->expiry_date ?? 'null');
                    $totalImport  = (int) $batch->total_import;
                    $totalExported = (int)($exportedByBatch[$batchKey]->total_export ?? 0);
                    $qtyLeft      = $totalImport - $totalExported; // còn lại trong lô này

                    if ($qtyLeft <= 0) continue; // lô này đã hết

                    // Số lấy từ lô này: không vượt quá qty còn lại của lô
                    $takeFromBatch = min($remaining, $qtyLeft);

                    // Ghi log xuất kho cho lô này
                    WarehouseStockLog::create([
                        'receipt_id'  => null,
                        'product_id'  => $product->id,
                        'type'        => 'export',
                        'quantity'    => $takeFromBatch,
                        'stock_after' => max(0, $product->quantity - $takeFromBatch),
                        'reason'      => "Xuất kho cho đơn hàng #{$id}",
                        'expiry_date' => $batch->expiry_date ?: null, // gắn HSD của lô bị trừ
                    ]);

                    $remaining -= $takeFromBatch;
                }

                // Nếu còn thừa (SP không có log import nào — nhập thủ công): ghi log export chung
                if ($remaining > 0) {
                    WarehouseStockLog::create([
                        'receipt_id'  => null,
                        'product_id'  => $product->id,
                        'type'        => 'export',
                        'quantity'    => $remaining,
                        'stock_after' => max(0, $product->quantity - $remaining),
                        'reason'      => "Xuất kho cho đơn hàng #{$id} (không có lô HSD)",
                        'expiry_date' => null,
                    ]);
                }

                // Trừ tồn kho product (giữ nguyên như cũ)
                $product->decrement('quantity', $qtyToExport);
            }

            // Chuyển đơn sang status=3 (đang giao)
            $order->status = 3;
            $order->save();

            return redirect()->back()->with('success', 'Đã xuất kho và chuyển giao shipper!');
        }

        // Cập nhật trạng thái thông thường
        $order->status = $nextStatus;
        $order->save();

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }

    // =========================================================================
    // RETURN ORDER — Trang xử lý hoàn hàng
    // =========================================================================

    /**
     * Hiện form hoàn hàng.
     * Chỉ cho phép đơn ở status=3 (đang giao) hoặc status=5 (khách yêu cầu hoàn).
     */
    public function returnOrder($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status != 3 && $order->status != 5) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Đơn hàng không đủ điều kiện xử lý hoàn trả.');
        }

        $orderdetail = OrderDetail::where('idOrder', $id)->with('product')->get();

        return view('admin.order.return', compact('order', 'orderdetail'));
    }

    /**
     * Xử lý submit form hoàn hàng.
     * Tất cả hàng hoàn đều chuyển sang status=6 (hàng hỏng/chờ trả NSX).
     * Không nhập lại tồn kho — hàng chờ bên kho NSX xử lý.
     */
    public function processReturn(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'condition' => 'required|in:intact,damaged',
        ], [
            'condition.required' => 'Vui lòng chọn tình trạng hàng hoàn.',
            'condition.in'       => 'Tình trạng hàng không hợp lệ.',
        ]);

        // Dù hàng nguyên vẹn hay hỏng → đều đẩy vào hàng hỏng (status=6)
        // để chờ trả về nhà sản xuất, không nhập kho lại
        $order->update([
            'status' => 6,
            'note'   => $order->note, // giữ nguyên ghi chú gốc của khách
        ]);

        return redirect()->route('admin.orders.damaged')
            ->with('success', "Đã ghi nhận và chuyển đơn #{$order->id} sang danh sách hàng hỏng.");
    }

    // =========================================================================
    // DAMAGED LIST — Danh sách hàng hỏng (status = 6)
    // =========================================================================

    public function damagedList()
    {
        // Eager load details.product để hiện tên SP trong bảng
        $orders = Order::with(['details.product'])
            ->where('status', 6)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.order.damaged', compact('orders'));
    }
}
