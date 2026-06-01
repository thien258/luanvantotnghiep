<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận nhận hàng — Aura & Essence</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f9f9f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #fff;
            border: 1px solid #e0e0e0;
            max-width: 420px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
        }
        .brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            letter-spacing: 2px;
            margin-bottom: 4px;
        }
        .brand-sub {
            font-size: 0.65rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 32px;
        }
        .icon-wrap {
            width: 72px; height: 72px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
        .icon-pending  { background: #fff8e1; color: #f59e0b; }
        .icon-done     { background: #e8f5e9; color: #22c55e; }
        .icon-shipping { background: #e3f2fd; color: #3b82f6; }
        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 400;
            margin-bottom: 8px;
        }
        .sub { font-size: 0.8rem; color: #888; margin-bottom: 28px; line-height: 1.6; }
        .info-box {
            background: #fafafa;
            border: 1px solid #eee;
            padding: 16px 20px;
            text-align: left;
            margin-bottom: 28px;
            font-size: 0.82rem;
            line-height: 2;
        }
        .info-box strong { color: #333; }
        .btn-confirm {
            display: block; width: 100%;
            background: #111; color: #fff;
            border: none; padding: 14px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.8rem; font-weight: 600;
            letter-spacing: 2px; text-transform: uppercase;
            cursor: pointer;
            margin-bottom: 12px;
        }
        .btn-confirm:hover { background: #333; }
        .note { font-size: 0.7rem; color: #aaa; letter-spacing: 0.5px; }
        .divider { border: none; border-top: 1px solid #eee; margin: 24px 0; }
    </style>
</head>
<body>
<div class="card">
    <div class="brand">Aura & Essence</div>
    <div class="brand-sub">Atelier — Delivery Confirmation</div>

    @if($order->status == 4)
        {{-- ĐÃ XÁC NHẬN TRƯỚC ĐÓ --}}
        <div class="icon-wrap icon-done">✓</div>
        <h2>Đơn hàng đã hoàn tất</h2>
        <p class="sub">Cảm ơn bạn đã xác nhận.<br>Đơn hàng <strong>#DH{{ $order->id }}</strong> đã được ghi nhận thành công.</p>

    @elseif($order->status == 3)
        {{-- ĐANG GIAO — CHO PHÉP XÁC NHẬN --}}
        <div class="icon-wrap icon-shipping">📦</div>
        <h2>Xác nhận đã nhận hàng</h2>
        <p class="sub">Bạn vừa nhận được gói hàng từ <strong>Aura & Essence</strong>.<br>Vui lòng kiểm tra và xác nhận bên dưới.</p>

        <div class="info-box">
            <div><strong>Mã đơn:</strong> #DH{{ $order->id }}</div>
            <div><strong>Người nhận:</strong> {{ $order->fullname }}</div>
            <div><strong>Địa chỉ:</strong> {{ $order->address }}</div>
            <div><strong>Tổng tiền:</strong> {{ number_format($order->total_price) }}đ</div>
        </div>

        <form action="{{ route('order.submit-confirm-delivery', $order->tracking_code) }}" method="POST">
            @csrf
            <button type="submit" class="btn-confirm">
                ✓ &nbsp; Tôi đã nhận được hàng
            </button>
        </form>
        <p class="note">Bằng cách xác nhận, bạn đồng ý đã nhận đủ hàng theo đơn.</p>

    @else
        {{-- TRẠNG THÁI KHÁC --}}
        <div class="icon-wrap icon-pending">⏳</div>
        <h2>Đơn hàng chưa sẵn sàng</h2>
        <p class="sub">Đơn hàng <strong>#DH{{ $order->id }}</strong> hiện chưa ở trạng thái giao hàng.<br>Vui lòng liên hệ shop nếu có thắc mắc.</p>
    @endif

    <hr class="divider">
    <p class="note">© Aura & Essence Atelier &nbsp;·&nbsp; <a href="{{ route('welcome') }}" style="color:#999;">Về trang chủ</a></p>
</div>
</body>
</html>
