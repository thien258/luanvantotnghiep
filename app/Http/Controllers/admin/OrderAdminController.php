<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    // 1. Giao diện danh sách đơn hàng
    public function index(Request $request)
    {
        $keyword = $request->input('q', '');
        $status = $request->input('status', '');

        $query = Order::where('status', '!=', 0)
            ->orderByRaw("FIELD(status, 1, 3, 4, 5, 6) ASC")
            ->orderByRaw("FIELD(payment_method, 'BANK TRANSFER', 'COD') ASC")
            ->orderBy('created_at', 'ASC');

        if ($status != '') {
            $query->where('status', $status);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $numericId = preg_replace('/^(dh|#dh|#)/i', '', trim($keyword));
                if (is_numeric($numericId)) {
                    $q->orWhere('id', $numericId);
                }
                $q->orWhere('fullname', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('tracking_code', 'like', "%{$keyword}%");
            });
        }

        $orders = $query->get();

        // AJAX request → trả về HTML rows + count
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.order.partials.order-rows', compact('orders'))->render();
            return response()->json(['html' => $html, 'count' => $orders->count()]);
        }

        return view('admin.order.order-list', compact('orders', 'keyword', 'status'));
    }

    // 2. Giao diện chi tiết đơn hàng
    public function show($id)
    {
        $order = Order::findOrFail($id);
        $orderdetail = OrderDetail::where('idOrder', $id)->with('product')->get();

        return view('admin.order.show', compact('order', 'orderdetail'));
    }

    // 3. Cập nhật trạng thái và xử lý xuất kho biến thể
    public function updateStatus(Request $request, $id)
    {
        // [VALIDATION] Chỉ chấp nhận status hợp lệ, tránh inject giá trị lạ
        $request->validate([
            'status'      => 'nullable|integer|in:1,3,4,5,6',
            'action_type' => 'nullable|in:export_warehouse',
        ], [
            'status.integer' => 'Trạng thái không hợp lệ.',
            'status.in'      => 'Trạng thái đơn hàng không hợp lệ.',
            'action_type.in' => 'Hành động không được hỗ trợ.',
        ]);

        $order = Order::findOrFail($id);
        $nextStatus = $request->input('status');
        $actionType = $request->input('action_type');

        // HÀNH ĐỘNG: BẤM NÚT XUẤT KHO TỪ TRANG CHI TIẾT
        if ($actionType === 'export_warehouse' && $order->status == 1) {
            $orderDetails = OrderDetail::where('idOrder', $id)->with('product')->get();
            foreach ($orderDetails as $detail) {
                if ($detail->product) {
                    $detail->product->decrement('quantity', $detail->quantity);
                }
            }
            $order->status = 3;
            $order->save();
            return redirect()->back()->with('success', 'Đã xuất kho và chuyển giao shipper!');
        }

        $order->status = $nextStatus;
        $order->save();

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }
    // 4. Trang xử lý hoàn hàng — Hiện khi đơn đang giao (3) hoặc hỗ trợ kiểm tra đơn đã hoàn (5)
    public function returnOrder($id)
    {
        // Tìm đơn hàng bằng ID để tránh lỗi lệch tên tham số Route
        $order = Order::findOrFail($id);

        // Kiểm tra điều kiện trạng thái của đơn hàng
        if ($order->status != 3 && $order->status != 5) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Đơn hàng không đủ điều kiện xử lý hoàn trả.');
        }

        // Lấy chi tiết đơn hàng chuẩn tên biến $orderdetail
        $orderdetail = OrderDetail::where('idOrder', $id)->with('product')->get();

        return view('admin.order.return', compact('order', 'orderdetail'));
    }

    // 5. XỬ LÝ SUBMIT HOÀN HÀNG — tất cả chờ trả nhà sản xuất, không nhập kho
    public function processReturn(Request $request, $id)
    {
       // 1. Tìm đơn hàng dựa vào ID nhận từ Form gửi lên
        $order = Order::findOrFail($id);

        $request->validate([
            'condition' => 'required|in:intact,damaged',
        ], [
            'condition.required' => 'Vui lòng chọn tình trạng hàng hoàn.',
            'condition.in'       => 'Tình trạng hàng không hợp lệ.',
        ]);

        // Tất cả hàng hoàn chuyển sang hàng hỏng (status = 6)
        $finalStatus = 6;

        // 2. Cập nhật trạng thái, GIỮ NGUYÊN VẸN trường note cũ của khách hàng, không thêm thắt bất cứ gì
        $order->update([
            'status' => $finalStatus,
            'note'   => $order->note // Giữ nguyên lý do gốc của khách hàng
        ]);

        return redirect()->route('admin.orders.damaged')
            ->with('success', "Đã ghi nhận và chuyển đơn #{$order->id} sang danh sách hàng hỏng.");
    }

    // 6. Danh sách hàng hỏng (status = 6)
    public function damagedList()
    {
        $orders = Order::with(['detatil.product'])
            ->where('status', 6)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.order.damaged', compact('orders'));
    }
}
