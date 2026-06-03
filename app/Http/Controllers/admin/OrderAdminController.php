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

        $query = Order::orderBy('id', 'desc');

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
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

        return view('admin.order.order-list', compact('orders', 'keyword'));
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
            // Stock đã trừ lúc khách đặt hàng — chỉ cần chuyển trạng thái
            $order->status = 3;
            $order->save();
            return redirect()->back()->with('success', 'Đã xuất kho và chuyển giao shipper!');
        }

        // Cập nhật trạng thái thông thường từ select box
        $order->status = $nextStatus;
        $order->save();

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }

    // 4. Trang xử lý hoàn hàng — hiện khi đơn đang giao (status = 3)
    public function returnOrder(Order $order)
    {
        if ($order->status != 3) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Chỉ xử lý hoàn hàng khi đơn đang ở trạng thái Đang Giao Hàng.');
        }
        $orderdetail = OrderDetail::where('idOrder', $order->id)->with('product')->get();
        return view('admin.order.return', compact('order', 'orderdetail'));
    }

    // 5. Xử lý submit hoàn hàng
    public function processReturn(Request $request, Order $order)
    {
        $request->validate(['condition' => 'required|in:intact,damaged']);

        if ($request->condition === 'intact') {
            // Hàng nguyên vẹn → cộng lại stock vào products.quantity
            $orderDetails = OrderDetail::where('idOrder', $order->id)->with('product')->get();
            foreach ($orderDetails as $detail) {
                if ($detail->product) {
                    $detail->product->increment('quantity', $detail->quantity);
                }
            }
            $order->update(['status' => 5, 'note' => ($order->note ? $order->note . ' | ' : '') . 'Hoàn hàng nguyên vẹn.']);
            return redirect()->route('admin.orders.index')
                ->with('success', "Hoàn hàng nguyên vẹn — đã cộng lại tồn kho đơn #{$order->id}.");
        } else {
            $order->update(['status' => 6, 'note' => ($order->note ? $order->note . ' | ' : '') . 'Hoàn hàng lỗi/hỏng — không nhập kho.']);
            return redirect()->route('admin.orders.damaged')
                ->with('success', "Đã ghi nhận hàng hỏng đơn #{$order->id}.");
        }
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
