<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request) {}

    // Nhận cart IDs từ trang cart, lưu vào session rồi redirect sang trang order
    public function checkout(Request $request)
    {
        $cartIds = $request->input('cart_ids', []);

        if (empty($cartIds)) {
            return redirect()->route('carts.index')->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm!');
        }

        session(['checkout_cart_ids' => $cartIds]);

        return redirect()->route('order.index');
    }

    // Trang thanh toán — lấy cart items từ DB theo IDs đã lưu trong session
    public function index()
    {
        $cartIds = session('checkout_cart_ids', []);

        if (empty($cartIds)) {
            return redirect()->route('carts.index')->with('error', 'Vui lòng chọn sản phẩm trước khi thanh toán!');
        }

        $carts = Cart::whereIn('id', $cartIds)
            ->where('idUser', Auth::id())
            ->with('product.festivals')
            ->get();

        $orderItems = $carts->map(function ($cart) {
            $product = $cart->product;
            return [
                'id'       => $product->id,
                'cart_id'  => $cart->id,
                'title'    => $product->title,
                'image'    => $product->image,
                'volume'   => $product->volume,
                'price'    => $product->getDiscountedPrice(),
                'quantity' => $cart->quantity,
            ];
        });

        $total = $orderItems->sum(fn($item) => $item['price'] * $item['quantity']);

        $orders = Order::where('idUser', Auth::id())->orderBy('id', 'desc')->get();

        return view('order.index', compact('orders', 'orderItems', 'total'));
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'fullname'       => 'required|string',
            'phone'          => 'required|string',
            'address'        => 'required|string',
            'payment_method' => 'required|string',
        ], [
            'fullname.required'       => 'Vui lòng nhập họ tên người nhận.',
            'phone.required'          => 'Vui lòng nhập số điện thoại.',
            'address.required'        => 'Vui lòng nhập địa chỉ giao hàng.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        $cartIds = session('checkout_cart_ids', []);

        if (empty($cartIds)) {
            return redirect()->route('carts.index')->with('error', 'Giỏ hàng của bạn đang trống!');
        }

        $carts = Cart::whereIn('id', $cartIds)
            ->where('idUser', Auth::id())
            ->with('product')
            ->get();

        if ($carts->isEmpty()) {
            return redirect()->route('carts.index')->with('error', 'Không tìm thấy sản phẩm trong giỏ hàng!');
        }

        $total = $carts->sum(fn($cart) => $cart->product->getDiscountedPrice() * $cart->quantity);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'idUser'         => Auth::id(),
                'fullname'       => $request->fullname,
                'phone'          => $request->phone,
                'address'        => $request->address,
                'payment_method' => $request->payment_method,
                'total_price'    => $total,
                'status'         => 0,
                'note'           => $request->note,
                'tracking_code'  => 'TRACK-' . strtoupper(Str::random(10)),
            ]);

            foreach ($carts as $cart) {
                OrderDetail::create([
                    'idOrder'   => $order->id,
                    'idProduct' => $cart->product->id,
                    'name'      => $cart->product->title,
                    'quantity'  => $cart->quantity,
                    'price'     => $cart->product->getDiscountedPrice(),
                ]);
                // Không trừ tồn kho ở đây — chỉ trừ khi khách xác nhận đã thanh toán
            }

            // Xóa các cart items đã đặt
            Cart::whereIn('id', $cartIds)->delete();

            // Xóa session checkout
            session()->forget('checkout_cart_ids');

            DB::commit();

            if ($request->payment_method === 'COD') {
                return redirect()->route('welcome')->with('success', 'Đặt hàng thành công! Đơn hàng sẽ được giao trong 3-5 ngày.');
            } else {
                return redirect()->route('order.payment', ['id' => $order->id]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('placeOrder exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
    // Khách xác nhận đã quét QR / đã thanh toán → trừ tồn kho, status = 1
    public function confirmPaid($id)
    {
        $order = Order::where('id', $id)
            ->where('idUser', Auth::id())
            ->where('status', 0)
            ->with('detatil.product')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            foreach ($order->detatil as $detail) {
                if ($detail->product) {
                    $detail->product->decrement('quantity', $detail->quantity);
                }
            }

            $order->update(['status' => 1]);
            DB::commit();

            return redirect()->route('welcome')
                ->with('success', 'Cảm ơn bạn đã thanh toán! Đơn hàng #' . $id . ' đang được xử lý.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Khách hủy đơn (chưa thanh toán) → status = -1, hoàn cart, redirect về cart
    public function cancelOrder($id)
    {
        $order = Order::where('id', $id)
            ->where('idUser', Auth::id())
            ->where('status', 0)
            ->with('detatil')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Hoàn lại sản phẩm vào giỏ hàng
            foreach ($order->detatil as $detail) {
                Cart::create([
                    'idUser'     => Auth::id(),
                    'product_id' => $detail->idProduct,
                    'quantity'   => $detail->quantity,
                ]);
            }

            $order->update(['status' => -1]);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
        }

        return redirect()->route('carts.index')
            ->with('status', 'Đã hủy đơn hàng. Sản phẩm đã được hoàn lại vào giỏ hàng.');
    }

    // Trang hiển thị mã VietQR động theo đơn hàng
    public function paymentForm($id)
    {
        // 1. Tìm đơn hàng hoặc báo lỗi 404 nếu cố tình gõ bậy ID trên URL
        $order = Order::where('id', $id)->where('idUser', Auth::id())->firstOrFail();

        // 2. THAY THÔNG TIN NGÂN HÀNG THỰC TẾ CỦA BẠN VÀO ĐÂY
        $BANK_ID      = 'TPBANK';            // Mã ngân hàng (MB, VCB, TCB, ACB, VietinBank...)
        $ACCOUNT_NO   = '07178497601';    // Số tài khoản ngân hàng nhận tiền
        $ACCOUNT_NAME = 'Tran Quang Thien';  // Tên chủ tài khoản (Viết hoa, không dấu)

        // 3. Thiết lập thông tin đơn hàng tự động
        $amount  = $order->total_price;                          // Lấy tổng tiền thực tế của đơn hàng
        $addInfo = 'DH' . $order->id . ' Thanh toan nuoc hoa';   // Nội dung chuyển khoản tự động

        // 4. Gọi API miễn phí của VietQR.io để sinh link ảnh QR động
        $qrCodeUrl = "https://img.vietqr.io/image/{$BANK_ID}-{$ACCOUNT_NO}-qr_only.png?amount={$amount}&addInfo=" . urlencode($addInfo) . "&accountName=" . urlencode($ACCOUNT_NAME);

        // 5. Trả dữ liệu ra file view thanh toán
        return view('order.order_payment', compact('order', 'qrCodeUrl', 'amount', 'addInfo'));
    }
}
