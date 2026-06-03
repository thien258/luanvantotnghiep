<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

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
            // Tất cả đơn hàng đều vào trạng thái chờ xuất kho, bất kể COD hay Bank Transfer
            $order = Order::create([
                'idUser'         => Auth::id(),
                'fullname'       => $request->fullname,
                'phone'          => $request->phone,
                'address'        => $request->address,
                'payment_method' => $request->payment_method,
                'total_price'    => $total,
                // Bank Transfer = 0 (chờ PayOS xác nhận), COD = 1 (đang lấy hàng)
                'status'         => $request->payment_method === 'BANK TRANSFER' ? 0 : 1,
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

                // Trừ tồn kho ngay khi đặt hàng
                $cart->product->decrement('quantity', $cart->quantity);
            }

            // Xóa các cart items đã đặt
            Cart::whereIn('id', $cartIds)->delete();

            // Xóa session checkout
            session()->forget('checkout_cart_ids');

            DB::commit();

            if ($request->payment_method === 'BANK TRANSFER') {
                // Thử gọi PayOS nếu đã config key
                if (env('PAYOS_CLIENT_ID') && env('PAYOS_API_KEY') && env('PAYOS_CHECKSUM_KEY')) {
                    $checkoutUrl = $this->createPayOSLink($order);
                    if ($checkoutUrl) {
                        return redirect($checkoutUrl);
                    }
                }
                // Fallback: dùng trang QR VietQR nếu chưa config PayOS
                return redirect()->route('order.payment', ['id' => $order->id]);
            }

            return redirect()->route('welcome')
                ->with('success', 'Đặt hàng thành công! Đơn hàng sẽ được giao trong 3-5 ngày.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('placeOrder exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
    // Trang hiển thị mã VietQR động theo đơn hàng
    public function paymentForm($id)
    {
        // 1. Tìm đơn hàng hoặc báo lỗi 404 nếu cố tình gõ bậy ID trên URL
        $order = Order::where('id', $id)->where('idUser', Auth::id())->firstOrFail();

        $BANK_ID      = 'TPBANK';
        $ACCOUNT_NO   = '07178497601';
        $ACCOUNT_NAME = 'Tran Quang Thien';
        $amount       = $order->total_price;
        $addInfo      = "DH{$order->id} Thanh toan nuoc hoa";

        // VietQR API — QR chuyển khoản ngân hàng
        $qrCodeUrl = "https://img.vietqr.io/image/{$BANK_ID}-{$ACCOUNT_NO}-qr_only.png"
            . "?amount={$amount}"
            . "&addInfo=" . urlencode($addInfo)
            . "&accountName=" . urlencode($ACCOUNT_NAME)
            . "&t=" . time(); // cache-bust

        return view('order.order_payment', compact('order', 'qrCodeUrl', 'amount', 'addInfo', 'BANK_ID', 'ACCOUNT_NO'));
    }
    public function history()
    {
        $orders = Order::where('idUser', Auth::id())
            ->where('status', '!=', 0) // Ẩn đơn chờ thanh toán PayOS
            ->orderBy('id', 'desc')
            ->get();
        return view('order.history', compact('orders'));
    }
    public function historyDetail($id)
    {
        $order = Order::where('id', $id)->where('idUser', Auth::id())->firstOrFail();
        $orderDetails = OrderDetail::where('idOrder', $id)->with('product')->get();
        return view('order.history_detail', compact('order', 'orderDetails'));
    }

    // Khách xác nhận đã chuyển khoản → redirect về trang chủ, admin sẽ kiểm tra và xuất kho
    public function confirmPaid($id)
    {
        $order = Order::where('id', $id)
            ->where('idUser', Auth::id())
            ->firstOrFail();

        return redirect()->route('welcome')
            ->with('success', "Cảm ơn bạn! Đơn hàng #{$id} đang chờ xác nhận từ shop.");
    }

    // Khách hủy đơn → xóa đơn, hoàn sản phẩm vào giỏ hàng
    public function cancelOrder($id)
    {
        $order = Order::where('id', $id)
            ->where('idUser', Auth::id())
            ->where('status', 1)
            ->with('detatil')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            foreach ($order->detatil as $detail) {
                Cart::create([
                    'idUser'     => Auth::id(),
                    'product_id' => $detail->idProduct,
                    'quantity'   => $detail->quantity,
                ]);
            }
            $order->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        return redirect()->route('carts.index')
            ->with('status', 'Đã hủy đơn hàng. Sản phẩm đã được hoàn lại vào giỏ hàng.');
    }

    // Trang xác nhận nhận hàng — khách quét QR trên thùng hàng
    public function confirmDelivery($code)
    {
        $order = Order::where('tracking_code', $code)->firstOrFail();
        return view('order.confirm-delivery', compact('order'));
    }

    // Khách bấm xác nhận → status = 4 (Hoàn tất)
    public function submitConfirmDelivery($code)
    {
        $order = Order::where('tracking_code', $code)
            ->where('status', 3)
            ->firstOrFail();

        $order->update(['status' => 4]);

        return view('order.confirm-delivery', compact('order'));
    }
    /**
     * TỰ ĐỘNG 1: GỌI API PAYOS TẠO LINK QUET MÃ QR THANH TOÁN THẬT
     */
    private function createPayOSLink($order)
    {
        $clientId    = env('PAYOS_CLIENT_ID');
        $apiKey      = env('PAYOS_API_KEY');
        $checksumKey = env('PAYOS_CHECKSUM_KEY');

        $orderCode   = intval($order->id);
        $amount      = intval($order->total_price);
        $description = 'AROMA DH' . $order->id;
        $returnUrl   = route('payos.success');
        $cancelUrl   = route('order.payos-cancel', ['id' => $order->id]);

        // PayOS signature: các field theo đúng thứ tự alphabet
        $signatureString = "amount={$amount}&cancelUrl={$cancelUrl}&description={$description}&orderCode={$orderCode}&returnUrl={$returnUrl}";
        $signature = hash_hmac('sha256', $signatureString, $checksumKey);

        $payload = [
            'orderCode'   => $orderCode,
            'amount'      => $amount,
            'description' => $description,
            'returnUrl'   => $returnUrl,
            'cancelUrl'   => $cancelUrl,
            'signature'   => $signature,
        ];

        \Illuminate\Support\Facades\Log::info('PayOS request', [
            'orderCode'   => $orderCode,
            'amount'      => $amount,
            'description' => $description,
            'clientId'    => substr($clientId, 0, 8) . '...',
        ]);

        $response = Http::withoutVerifying()->withHeaders([
            'x-client-id'  => $clientId,
            'x-api-key'    => $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api-merchant.payos.vn/v2/payment-requests', $payload);

        if ($response->successful()) {
            $result = $response->json();
            \Illuminate\Support\Facades\Log::info('PayOS response', $result);
            if (isset($result['code']) && $result['code'] === '00') {
                return $result['data']['checkoutUrl'];
            }
            \Illuminate\Support\Facades\Log::error('PayOS error: ' . json_encode($result));
        } else {
            \Illuminate\Support\Facades\Log::error('PayOS HTTP error: ' . $response->status() . ' - ' . $response->body());
        }

        return null;
    }

    /**
     * PayOS hủy thanh toán → xóa đơn, hoàn cart, cộng lại tồn kho
     */
    public function payosCancel($id)
    {
        $order = Order::where('id', $id)
            ->where('status', 0)
            ->with('detatil')
            ->first();

        if ($order) {
            DB::beginTransaction();
            try {
                foreach ($order->detatil as $detail) {
                    Product::where('id', $detail->idProduct)
                        ->increment('quantity', $detail->quantity);

                    Cart::create([
                        'idUser'     => $order->idUser,
                        'product_id' => $detail->idProduct,
                        'quantity'   => $detail->quantity,
                    ]);
                }
                OrderDetail::where('idOrder', $order->id)->delete();
                $order->delete();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Illuminate\Support\Facades\Log::error('payosCancel error: ' . $e->getMessage());
            }
        }

        return redirect()->route('carts.index')
            ->with('status', 'Đã hủy thanh toán. Sản phẩm đã được hoàn lại vào giỏ hàng.');
    }

    /**
     * WEBHOOK: PayOS xác nhận thanh toán xong → status 0 → 1
     */
    public function payosWebhook(Request $request)
    {
        $body = $request->all();

        if (isset($body['code']) && $body['code'] == '00' && isset($body['data'])) {
            $description = $body['data']['description'];
            preg_match('/DH(\d+)/', $description, $matches);
            if (isset($matches[1])) {
                $order = Order::find($matches[1]);
                if ($order && $order->status == 0) {
                    $order->update(['status' => 1]);
                    \Illuminate\Support\Facades\Log::info("PayOS webhook: order #{$order->id} → status 1");
                }
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Trang thông báo thanh toán thành công
     */
    public function payosSuccess()
    {
        return view('order.payos_success_page');
    }
}
