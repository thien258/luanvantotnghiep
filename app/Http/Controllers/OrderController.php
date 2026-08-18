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
use Illuminate\Support\Facades\Log;

use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\Mail;

/**
 * OrderController — Xử lý đơn hàng phía khách hàng.
 *
 * Luồng đặt hàng:
 *   1. checkout()    — Lưu cart IDs vào session, redirect sang trang thanh toán
 *   2. index()       — Trang nhập thông tin giao hàng + chọn phương thức
 *   3. placeOrder()  — Tạo Order + OrderDetail, xử lý PayOS hoặc COD
 *
 * Luồng PayOS (Bank Transfer):
 *   placeOrder → createPayOSLink() → redirect sang PayOS QR
 *   → khách thanh toán → payosWebhook() cập nhật status 0→1
 *   → hoặc hủy → payosCancel() xóa đơn + hoàn giỏ hàng
 *
 * Lưu ý:
 *   - Tồn kho KHÔNG trừ khi đặt hàng
 *   - Tồn kho chỉ trừ khi admin bấm "Xuất kho" (OrderAdminController::updateStatus)
 *   - tracking_code: mã QR trên thùng hàng để khách xác nhận nhận hàng
 */
class OrderController extends Controller
{
    // =========================================================================
    // CHECKOUT — Lưu giỏ hàng đã chọn vào session
    // =========================================================================

    /**
     * Nhận danh sách cart_ids từ trang giỏ hàng (checkbox),
     * lưu vào session để trang thanh toán biết cần mua gì.
     */
    public function checkout(Request $request)
    {
        $cartIds = $request->input('cart_ids', []);

        if (empty($cartIds)) {
            return redirect()->route('carts.index')->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm!');
        }

        // Lưu session để index() đọc được
        session(['checkout_cart_ids' => $cartIds]);

        return redirect()->route('order.index');
    }

    // =========================================================================
    // INDEX — Trang thanh toán
    // =========================================================================

    public function index()
    {
        // Lấy IDs đã chọn từ session
        $cartIds = session('checkout_cart_ids', []);

        if (empty($cartIds)) {
            return redirect()->route('carts.index')->with('error', 'Vui lòng chọn sản phẩm trước khi thanh toán!');
        }

        // Query cart items của user hiện tại, kèm festivals để tính giá giảm
        $carts = Cart::whereIn('id', $cartIds)
            ->where('idUser', Auth::id())
            ->with('product.festivals')
            ->get();

        // Chuẩn bị dữ liệu hiển thị — giá đã áp discount festival nếu có
        $orderItems = $carts->map(function ($cart) {
            $product = $cart->product;
            return [
                'id'       => $product->id,
                'cart_id'  => $cart->id,
                'title'    => $product->title,
                'image'    => $product->image,
                'volume'   => $product->volume,
                'price'    => $product->getDiscountedPrice(), // giá sau giảm
                'quantity' => $cart->quantity,
            ];
        });

        $total = $orderItems->sum(fn($item) => $item['price'] * $item['quantity']);

        // Lịch sử đơn hàng cũ để hiển thị bên cạnh
        $orders = Order::where('idUser', Auth::id())->orderBy('id', 'desc')->get();

        return view('order.index', compact('orders', 'orderItems', 'total'));
    }

    // =========================================================================
    // PLACE ORDER — Tạo đơn hàng
    // =========================================================================

    public function placeOrder(Request $request)
    {
        $request->validate([
            'fullname'       => 'required|string|max:255',
            'phone'          => 'required|string|max:20|regex:/^[0-9]{9,11}$/',
            'address'        => 'required|string|max:500',
            'payment_method' => 'required|in:COD,BANK TRANSFER',
            'note'           => 'nullable|string|max:1000',
        ], [
            'fullname.required'       => 'Vui lòng nhập họ tên người nhận.',
            'phone.required'          => 'Vui lòng nhập số điện thoại.',
            'phone.regex'             => 'Số điện thoại phải từ 9-11 chữ số.',
            'address.required'        => 'Vui lòng nhập địa chỉ giao hàng.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'       => 'Phương thức thanh toán không hợp lệ.',
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
                // Bank Transfer → status=0 (chờ PayOS), COD → status=1 (đã đặt)
                'status'         => $request->payment_method === 'BANK TRANSFER' ? 0 : 1,
                'note'           => $request->note,
                // Mã QR duy nhất để khách xác nhận nhận hàng bằng cách quét
                'tracking_code'  => 'TRACK-' . strtoupper(Str::random(10)),
            ]);

            foreach ($carts as $cart) {
                OrderDetail::create([
                    'idOrder'   => $order->id,
                    'idProduct' => $cart->product->id,
                    'name'      => $cart->product->title,
                    'quantity'  => $cart->quantity,
                    'price'     => $cart->product->getDiscountedPrice(), // snapshot giá lúc mua
                ]);
                // Tồn kho KHÔNG trừ ở đây — trừ khi admin xuất kho
            }

            // Xóa cart items đã được đặt hàng
            Cart::whereIn('id', $cartIds)->delete();
            session()->forget('checkout_cart_ids');

            DB::commit();

            if ($request->payment_method === 'BANK TRANSFER') {
                $checkoutUrl = $this->createPayOSLink($order);
                if ($checkoutUrl) {
                    return redirect($checkoutUrl);
                }
                // PayOS thất bại → fallback trang QR tĩnh
                return redirect()->route('order.payment', ['id' => $order->id]);
            }
            $payosUrl = $this->createPayOSLink($order);
            try {
                $order->load(['details', 'user']);
                Mail::to($order->user->email)
                    ->send(new OrderConfirmationMail($order, $payosUrl));
            } catch (\Exception $e) {
                Log::error('send COD email failed: ' . $e->getMessage());
            }

            return redirect()->route('welcome')
                ->with('success', 'Đặt hàng COD thành công! Để được giao hàng sớm và ưu tiên hơn, vui lòng chuyển khoản qua link trong email vừa gửi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('placeOrder exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // HISTORY — Lịch sử đơn hàng của khách
    // =========================================================================

    public function history()
    {
        // Ẩn đơn status=0 (chưa thanh toán PayOS) khỏi lịch sử
        $orders = Order::where('idUser', Auth::id())
            ->where('status', '!=', 0)
            ->orderBy('id', 'desc')
            ->with(['details.product', 'details.comments' => function ($q) {
                $q->where('user_id', Auth::id());
            }])
            ->get();

        return view('order.history', compact('orders'));
    }

    /**
     * Trang chi tiết một đơn hàng của khách.
     *
     * Bảo mật: query kèm idUser để user A không xem được đơn của user B.
     * Nếu không tìm thấy → tự động 404 (firstOrFail).
     *
     * Timeline logic:
     *   - status 1 → Đang xử lý
     *   - status 3 → Đang giao hàng
     *   - status 4 → Đã giao hàng
     *   - status 5/6 → Hoàn hàng / Hàng hỏng (effectiveStatus = 4, thêm bước đặc biệt)
     *
     * Mỗi bước trong $timelineSteps đã được tính sẵn dotClass/textClass/lineClass
     * để blade chỉ việc render, không chứa logic PHP.
     */
    public function historyDetail($id)
    {
        // Chỉ lấy đơn thuộc về user đang đăng nhập
        $order        = Order::where('id', $id)->where('idUser', Auth::id())->firstOrFail();
        $orderDetails = OrderDetail::where('idOrder', $id)->with('product')->get();

        $currentStatus   = $order->status;
        $isReturn        = in_array($currentStatus, [5, 6]); // hoàn hàng hoặc hàng hỏng
        $effectiveStatus = $isReturn ? 4 : $currentStatus;   // nếu hoàn hàng vẫn tô đủ 3 bước

        // Build timeline — xử lý màu sắc tại đây, blade chỉ đọc
        $timelineSteps = collect([
            ['status' => 1, 'icon' => 'fa-box',         'label' => 'Đang xử lý'],
            ['status' => 3, 'icon' => 'fa-truck',        'label' => 'Đang giao hàng'],
            ['status' => 4, 'icon' => 'fa-check-circle', 'label' => 'Đã giao hàng'],
        ])->map(function ($step) use ($effectiveStatus) {
            $isDone   = $effectiveStatus >= $step['status']; // bước đã qua
            $isActive = $effectiveStatus == $step['status']; // bước hiện tại

            return array_merge($step, [
                'isDone'    => $isDone,
                'isActive'  => $isActive,
                // dot: xanh = đang ở đây | đen = đã qua | xám = chưa tới
                'dotClass'  => $isActive ? 'bg-success text-white border-success'
                    : ($isDone  ? 'bg-dark text-white border-dark'
                        : 'bg-white text-secondary border-secondary'),
                // label text
                'textClass' => $isActive ? 'text-success fw-semibold'
                    : ($isDone  ? 'text-dark fw-semibold'
                        : 'text-secondary'),
                // đường nối sang bước tiếp theo
                'lineClass' => $isDone   ? 'bg-dark' : 'bg-secondary-subtle',
            ]);
        });

        return view('order.history_detail', compact('order', 'orderDetails', 'timelineSteps', 'isReturn', 'currentStatus'));
    }

    // =========================================================================
    // REPAY — Tạo lại link PayOS cho đơn COD
    // =========================================================================

    public function repay($id)
    {
        $order = Order::where('id', $id)
            ->where('idUser', Auth::id())
            ->where('payment_method', 'COD')
            ->where('status', 1)
            ->firstOrFail();

        // Tạo orderCode mới = orderId * 10000 + giây hiện tại (tránh trùng với lần trước)
        $checkoutUrl = $this->createPayOSLink($order, true);

        if ($checkoutUrl) {
            return redirect($checkoutUrl);
        }

        Log::warning('repay: createPayOSLink returned null for order ' . $order->id);
        return redirect()->back()->with('error', 'Không thể tạo link thanh toán. Vui lòng thử lại sau.');
    }

    // =========================================================================
    // CONFIRM PAID — Khách xác nhận đã chuyển khoản (manual)
    // =========================================================================

    public function confirmPaid($id)
    {
        // Chỉ redirect về trang chủ — admin sẽ kiểm tra bank statement và xuất kho
        $order = Order::where('id', $id)->where('idUser', Auth::id())->firstOrFail();

        return redirect()->route('welcome')
            ->with('success', "Cảm ơn bạn! Đơn hàng #{$id} đang chờ xác nhận từ shop.");
    }

    // =========================================================================
    // CANCEL ORDER — Khách hủy đơn (chỉ hủy được khi status=1)
    // =========================================================================

    public function cancelOrder(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ], [
            'reason.required' => 'Vui lòng nhập lý do hủy.',
            'reason.min'      => 'Lý do hủy phải có ít nhất 5 ký tự.',
        ]);

        // Chỉ cho hủy đơn status=1 (chưa xuất kho)
        $order = Order::where('id', $id)
            ->where('idUser', Auth::id())
            ->where('status', 1)
            ->with('details')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Đổi status thành -1 (đã hủy), lưu lý do vào note
            $order->status = -1;
            $order->note   = ($order->note ? $order->note . ' | ' : '') . 'Lý do hủy: ' . trim($request->input('reason'));
            $order->save();

            // Hoàn sản phẩm về giỏ hàng
            foreach ($order->details as $detail) {
                Cart::create([
                    'idUser'     => Auth::id(),
                    'product_id' => $detail->idProduct,
                    'quantity'   => $detail->quantity,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('cancelOrder error: ' . $e->getMessage());
        }

        return redirect()->route('order.history')
            ->with('success', 'Đã hủy đơn hàng thành công. Sản phẩm đã được hoàn lại vào giỏ hàng.');
    }

    // =========================================================================
    // CONFIRM DELIVERY — Khách xác nhận nhận hàng qua QR
    // =========================================================================

    /**
     * Trang hiển thị khi khách quét QR trên thùng hàng.
     * Không yêu cầu đăng nhập (public route).
     */
    public function confirmDelivery($code)
    {
        $order = Order::where('tracking_code', $code)
            ->with('details.product')
            ->firstOrFail();

        return view('order.confirm-delivery', compact('order'));
    }

    /**
     * Khách bấm xác nhận → đơn chuyển sang status=4 (Hoàn tất).
     */
    public function submitConfirmDelivery($code)
    {
        $order = Order::where('tracking_code', $code)
            ->where('status', 3) // chỉ xác nhận khi đang giao
            ->firstOrFail();

        $order->update(['status' => 4]);

        // Redirect về trang confirm-delivery để hiển thị trạng thái đã hoàn tất
        return redirect()->route('order.confirm-delivery', $code);
    }

    // =========================================================================
    // CUSTOMER RETURN — Khách yêu cầu hoàn hàng
    // =========================================================================

    public function customerReturn(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ], [
            'reason.required' => 'Vui lòng nhập lý do hoàn hàng.',
            'reason.min'      => 'Lý do hoàn hàng phải có ít nhất 5 ký tự.',
        ]);

        $order = Order::findOrFail($id);

        // Chỉ cho hoàn khi đơn đã giao thành công (status=4) và trong vòng 3 ngày
        if ($order->status == 4 && $order->updated_at->diffInDays(now()) <= 3) {
            $order->status = 5; // Chuyển sang "Yêu cầu hoàn hàng"

            // Nối thêm lý do vào ghi chú, giữ nguyên ghi chú cũ của khách
            $oldNote    = $order->note ? $order->note . " | " : "";
            $order->note = $oldNote . "Lý do hoàn: " . trim($request->input('reason'));
            $order->save();

            return redirect()->back()->with('success', 'Yêu cầu hoàn trả đơn hàng #DH' . $id . ' đã được gửi thành công!');
        }

        return redirect()->back()->with('error', 'Đơn hàng không hợp lệ hoặc không đủ điều kiện hoàn trả.');
    }

    // =========================================================================
    // PAYOS — Tích hợp thanh toán PayOS
    // =========================================================================

    /**
     * Tạo link thanh toán PayOS.
     * Gọi API PayOS merchant, nhận về checkoutUrl để redirect khách sang trang QR PayOS.
     *
     * Signature: HMAC-SHA256 của chuỗi "amount=...&cancelUrl=...&description=...&orderCode=...&returnUrl=..."
     * (sắp xếp theo alphabet, đây là yêu cầu của PayOS)
     */
    public function createPayOSLink($order, bool $forceNew = false)
    {
        $clientId    = env('PAYOS_CLIENT_ID');
        $apiKey      = env('PAYOS_API_KEY');
        $checksumKey = env('PAYOS_CHECKSUM_KEY');

        // forceNew: thêm suffix seconds để tránh trùng orderCode với lần tạo trước
        $orderCode   = $forceNew
            ? intval($order->id) * 10000 + (int)(now()->timestamp % 10000)
            : intval($order->id);
        $amount      = intval($order->total_price);
        $description = 'AROMADH' . $order->id; // max 25 ký tự, KHÔNG có dấu cách (yêu cầu PayOS)
        $returnUrl   = route('payos.success');
        // COD: cancelUrl về trang chủ → bấm "Hủy" trên PayOS chỉ về home, không xóa đơn
        $cancelUrl   = $order->payment_method === 'COD'
            ? route('welcome')
            : route('order.payos-cancel', ['id' => $order->id]);

        // Chuỗi ký theo đúng thứ tự alphabet của PayOS
        $signatureString = "amount={$amount}&cancelUrl={$cancelUrl}&description={$description}&orderCode={$orderCode}&returnUrl={$returnUrl}";
        $signature       = hash_hmac('sha256', $signatureString, $checksumKey);

        $payload = [
            'orderCode'   => $orderCode,
            'amount'      => $amount,
            'description' => $description,
            'returnUrl'   => $returnUrl,
            'cancelUrl'   => $cancelUrl,
            'signature'   => $signature,
        ];

        $response = Http::withoutVerifying()->withHeaders([
            'x-client-id'  => $clientId,
            'x-api-key'    => $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api-merchant.payos.vn/v2/payment-requests', $payload);

        if ($response->successful()) {
            $result = $response->json();
            // code='00' là thành công theo PayOS
            if (isset($result['code']) && $result['code'] === '00') {
                return $result['data']['checkoutUrl'] ?? null;
            }
            Log::warning('PayOS response code not 00: ' . json_encode($result));
        } else {
            Log::warning('PayOS HTTP error: ' . $response->status() . ' ' . $response->body());
        }

        return null; // thất bại → fallback về VietQR
    }

    /**
     * PayOS hủy thanh toán → xóa đơn, hoàn sản phẩm về giỏ.
     * Không cộng lại tồn kho vì chưa xuất kho.
     */
    public function payosCancel($id)
    {
        $order = Order::where('id', $id)
            ->with('details')
            ->first();

        if (!$order) {
            return redirect()->route('carts.index')
                ->with('status', 'Đã hủy thanh toán.');
        }

        // Đơn COD → khách hủy thanh toán nâng cấp, đơn vẫn giữ nguyên, có thể thanh toán lại sau
        if ($order->payment_method === 'COD') {
            return redirect()->route('order.history')
                ->with('status', 'Bạn đã hủy thanh toán. Đơn COD vẫn còn hiệu lực, bạn có thể thanh toán online lại sau.');
        }

        // Đơn BANK TRANSFER chưa thanh toán (status=0) → xóa đơn, hoàn giỏ hàng
        if ($order->status == 0) {
            DB::beginTransaction();
            try {
                foreach ($order->details as $detail) {
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
                Log::error('payosCancel error: ' . $e->getMessage());
            }

            return redirect()->route('carts.index')
                ->with('status', 'Đã hủy thanh toán. Sản phẩm đã được hoàn lại vào giỏ hàng.');
        }

        return redirect()->route('order.history');
    }

    /**
     * Webhook từ PayOS sau khi khách thanh toán xong.
     * PayOS gọi POST về server → cập nhật status 0→1.
     * Route public, không cần auth.
     */
    public function payosWebhook(Request $request)
    {
        $body = $request->all();

        // Log webhook để debug
        Log::info('PayOS Webhook received', ['body' => $body]);

        // code='00' = thanh toán thành công
        if (isset($body['code']) && $body['code'] == '00' && isset($body['data'])) {
            $description = $body['data']['description'];

            // Lấy ID đơn hàng từ description (VD: "AROMA DH85")
            preg_match('/DH(\d+)/', $description, $matches);

            if (isset($matches[1])) {
                $orderId = $matches[1];
                $order = Order::find($orderId);

                if ($order) {
                    // Cho phép xử lý:
                    //   status=0 → BANK TRANSFER chờ thanh toán
                    //   status=1 + COD → COD đã đặt nhưng khách quét QR trong email để chuyển khoản
                    $isBankTransferPending = $order->status == 0;
                    $isCodUpgrade = $order->status == 1 && $order->payment_method === 'COD';

                    if ($isBankTransferPending || $isCodUpgrade) {
                        $updateData = ['status' => 1];
                        if ($order->payment_method === 'COD') {
                            // Đơn COD → khách đã chuyển khoản → đổi sang BANK TRANSFER
                            $updateData['payment_method'] = 'BANK TRANSFER';
                        }
                        $order->update($updateData);
                        Log::info("Order #{$orderId} updated: payment confirmed, method={$order->fresh()->payment_method}");
                        try {
                            $order->load(['details', 'user']);
                            Mail::to($order->user->email)
                                ->send(new OrderConfirmationMail($order));
                        } catch (\Exception $e) {
                            Log::error('send webhook email failed: ' . $e->getMessage());
                        }
                    } else {
                        Log::warning("Order #{$orderId} already processed (status={$order->status})");
                    }
                } else {
                    Log::error("Order #{$orderId} not found");
                }
            } else {
                Log::error("Cannot extract order ID from description: {$description}");
            }
        } else {
            Log::warning('PayOS Webhook: Invalid payload or code != 00');
        }

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // PAYMENT FORM — Fallback khi PayOS không tạo được link
    // =========================================================================

    /**
     * Trang hiển thị thông tin đơn hàng để khách chuyển khoản thủ công.
     * Được gọi khi PayOS trả về lỗi (vd: 423 ngân hàng từ chối).
     */
    public function paymentForm($id)
    {
        $order = Order::where('id', $id)
            ->where('idUser', Auth::id())
            ->with('details.product')
            ->firstOrFail();

        $amount  = intval($order->total_price);
        $addInfo = 'AROMADH' . $order->id;
        // QR VietQR tĩnh — dùng khi PayOS không tạo được link
        $qrCodeUrl = "https://img.vietqr.io/image/970418-8889065472-compact2.jpg"
            . "?amount={$amount}&addInfo=" . urlencode($addInfo) . "&accountName=TRAN+QUANG+THIEN";

        return view('order.order_payment', compact('order', 'amount', 'addInfo', 'qrCodeUrl'));
    }

    /**
     * Trang thông báo sau khi đặt hàng COD thành công.
     */
    public function codSuccess()
    {
        return view('order.cod_success_page');
    }

    /**
     * Trang thông báo sau khi PayOS thanh toán thành công.
     */
    public function payosSuccess()
    {
        return view('order.payos_success_page');
    }
}
