<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderAdminController extends Controller
{
    // 1. Giao diện danh sách đơn hàng
    public function index()
    {
        $orders = Order::orderBy('id', 'desc')->get();
        return view('admin.order.order-list', compact('orders'));
    }

    // 2. Giao diện chi tiết đơn hàng (Đồng bộ chuẩn tên biến)
    public function show($id)
    {
        $order = Order::findOrFail($id);

        // Đảm bảo tên biến viết liền là $orderdetail để file Blade đọc trúng
        $orderdetail = OrderDetail::where('idOrder', $id)->with('product')->get();

        return view('admin.order.show', compact('order', 'orderdetail'));
    }
    // 3. Cập nhật trạng thái và xử lý xuất kho biến thể
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $nextStatus = $request->input('status');
        $actionType = $request->input('action_type');

        // HÀNH ĐỘNG 1: BẤM NÚT XUẤT KHO TỪ TRANG CHI TIẾT
        if ($actionType === 'export_warehouse' && $order->status == 1) {
            $orderDetails = OrderDetail::where('idOrder', $id)->get();

            foreach ($orderDetails as $detail) {
                // Nếu hệ thống đồ án của bạn có lưu cột idVariant trong bảng order_details
                if (!empty($detail->idVariant)) {
                    DB::table('product_variants')
                        ->where('id', $detail->idVariant)
                        ->decrement('stock', $detail->quantity);
                } else {
                    // Phương án dự phòng: Nếu chưa kịp map bảng biến thể, hệ thống sẽ trừ cột quantity của bảng product gốc
                    if ($detail->product) {
                        $detail->product->decrement('quantity', $detail->quantity);
                    }
                }
            }

            $order->status = 3; // Ép trạng thái nhảy sang: 3. Đang giao hàng
            $order->save();
            return redirect()->back()->with('success', 'Đã xuất kho thành công các biến thể nước hoa và chuyển giao shipper!');
        }

        // HÀNH ĐỘNG 2: BIẾN CỐ HỦY ĐƠN (Cập nhật nhanh từ Select Box bảng ngoài)
        if ($nextStatus == 4 && $order->status != 4 && $order->status != 3) {
            $orderDetails = OrderDetail::where('idOrder', $id)->get();
            foreach ($orderDetails as $detail) {
                if (!empty($detail->idVariant)) {
                    DB::table('product_variants')
                        ->where('id', $detail->idVariant)
                        ->increment('stock', $detail->quantity);
                } else {
                    // Dự phòng hoàn kho lại cho bảng product gốc
                    if ($detail->product) {
                        $detail->product->increment('quantity', $detail->quantity);
                    }
                }
            }
        }

        // Cập nhật trạng thái thông thường từ select box
        $order->status = $nextStatus;
        $order->save();

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái tiến trình đơn hàng thành công.');
    }
}
