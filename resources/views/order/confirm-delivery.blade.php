<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận nhận hàng — Aura & Essence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center p-3" style="font-family: 'Montserrat', sans-serif;">

    <div class="card border-light shadow-sm p-4 p-sm-5 text-center bg-white rounded-0" style="max-width: 440px; width: 100%;">

        <div class="mb-5">
            <div class="fs-3 fw-normal text-dark mb-1" style="font-family: 'Playfair Display', serif; letter-spacing: 2px;">
                Aura & Essence
            </div>
            <div class="text-muted text-uppercase small" style="font-size: 0.65rem; letter-spacing: 3px;">
                Atelier — Delivery Confirmation
            </div>
        </div>

        @if($order->status == 4)
        {{-- TH 1: ĐÃ XÁC NHẬN TRƯỚC ĐÓ --}}
        <div class="d-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mx-auto mb-4" style="width: 72px; height: 72px; font-size: 2rem;">
            ✓
        </div>
        <h2 class="text-dark fw-normal mb-2 fs-4" style="font-family: 'Playfair Display', serif;">
            Đơn hàng đã hoàn tất
        </h2>
        <p class="text-muted small mb-4 lh-base">
            Cảm ơn bạn đã xác nhận.<br>Đơn hàng <strong>#DH{{ $order->id }}</strong> đã được ghi nhận thành công.
        </p>

        <div class="bg-light border p-3 text-start mb-4 small lh-lg">
            <div class="text-secondary"><strong>Mã đơn:</strong> <span class="text-dark">#DH{{ $order->id }}</span></div>
            <div class="text-secondary"><strong>Người nhận:</strong> <span class="text-dark">{{ $order->fullname }}</span></div>
            <div class="text-secondary"><strong>Tổng tiền:</strong> <span class="text-dark fw-semibold text-danger">{{ number_format($order->total_price) }}đ</span></div>
        </div>

        <div class="mb-4">
            @foreach($order->details as $detail)
            <div class="d-flex align-items-center gap-3 py-2 border-bottom border-light text-start">
                @if($detail->product?->image)
                <img src="{{ $detail->product->image }}" class="border bg-white" alt="" style="width: 52px; height: 52px; object-fit: cover; flex-shrink: 0;">
                @else
                <div class="border bg-light text-muted d-flex align-items-center justify-content-center small text-uppercase fw-medium" style="width: 52px; height: 52px; font-size: 0.6rem; flex-shrink: 0;">No Img</div>
                @endif
                <div>
                    <div class="fw-semibold text-dark small">{{ $detail->name }}</div>
                    <div class="text-muted extra-small mt-1" style="font-size: 0.75rem;">SL: {{ $detail->quantity }} &nbsp;·&nbsp; {{ number_format($detail->price) }}đ</div>
                </div>
            </div>
            @endforeach
        </div>

        @elseif($order->status == 3)
        {{-- TH 2: ĐANG GIAO — CHO PHÉP XÁC NHẬN --}}
        <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mx-auto mb-4" style="width: 72px; height: 72px; font-size: 1.8rem;">
            📦
        </div>
        <h2 class="text-dark fw-normal mb-2 fs-4" style="font-family: 'Playfair Display', serif;">
            Xác nhận đã nhận hàng
        </h2>
        <p class="text-muted small mb-4 lh-base">
            Bạn vừa nhận được gói hàng từ <strong>Aura & Essence</strong>.<br>Vui lòng kiểm tra và xác nhận bên dưới.
        </p>

        <div class="bg-light border p-3 text-start mb-4 small lh-lg">
            <div class="text-secondary"><strong>Mã đơn:</strong> <span class="text-dark">#DH{{ $order->id }}</span></div>
            <div class="text-secondary"><strong>Người nhận:</strong> <span class="text-dark">{{ $order->fullname }}</span></div>
            <div class="text-secondary"><strong>Địa chỉ:</strong> <span class="text-dark">{{ $order->address }}</span></div>
            <div class="text-secondary"><strong>Tổng tiền:</strong> <span class="text-dark fw-semibold text-danger">{{ number_format($order->total_price) }}đ</span></div>
        </div>

        <div class="mb-4">
            @foreach($order->details as $detail)
            <div class="d-flex align-items-center gap-3 py-2 border-bottom border-light text-start">
                @if($detail->product?->image)
                <img src="{{ $detail->product->image }}" class="border bg-white" alt="" style="width: 52px; height: 52px; object-fit: cover; flex-shrink: 0;">
                @else
                <div class="border bg-light text-muted d-flex align-items-center justify-content-center small text-uppercase fw-medium" style="width: 52px; height: 52px; font-size: 0.6rem; flex-shrink: 0;">No Img</div>
                @endif
                <div>
                    <div class="fw-bold text-dark small">{{ $detail->name }}</div>

                    <div class="text-dark fw-semibold mt-1 " style="font-size: 0.75rem;">
                        SL: {{ $detail->quantity }} &nbsp;·&nbsp; {{ number_format($detail->price) }}đ
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <form action="{{ route('order.submit-confirm-delivery', $order->tracking_code) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 text-uppercase fw-semibold mb-3 small" style="letter-spacing: 2px;">
                ✓ &nbsp; Tôi đã nhận được hàng
            </button>
        </form>
        <p class="text-muted mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Bằng cách xác nhận, bạn đồng ý đã nhận đủ hàng theo đơn.</p>

        @else
        {{-- TH 3: TRẠNG THÁI KHÁC --}}
        <div class="d-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle mx-auto mb-4" style="width: 72px; height: 72px; font-size: 1.8rem;">
            ⏳
        </div>
        <h2 class="text-dark fw-normal mb-2 fs-4" style="font-family: 'Playfair Display', serif;">
            Đơn hàng chưa sẵn sàng
        </h2>
        <p class="text-muted small mb-0 lh-base">
            Đơn hàng <strong>#DH{{ $order->id }}</strong> hiện chưa ở trạng thái giao hàng.<br>Vui lòng liên hệ shop nếu có thắc mắc.
        </p>
        @endif

        <hr class="my-4 text-muted opacity-25">
        <p class="text-muted mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">
            © Aura & Essence Atelier &nbsp;·&nbsp;
            <a href="{{ route('welcome') }}" class="text-secondary text-decoration-underline">Về trang chủ</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>