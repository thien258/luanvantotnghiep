@extends('layout/home')
@section('body')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">

<div class="py-5 bg-white text-dark" style="font-family: 'Montserrat', sans-serif;">
    <div class="container" style="max-width: 900px;">
        
        <div class="mb-4">
            <a href="{{ route('order.history') }}" class="text-dark small text-decoration-underline text-uppercase fw-bold" style="letter-spacing:1px;">← Trở về lịch sử</a>
        </div>

        <div class="border border-dark p-5 bg-white">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h2 class="m-0" style="font-family: 'Playfair Display', serif;">Chi tiết đơn hàng #DH{{ $order->id }}</h2>
                    <p class="text-muted small m-0 mt-1">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <p class="text-muted small m-0">Mã vận đơn: <span class="font-monospace text-dark fw-medium">{{ $order->tracking_code }}</span></p>
                </div>
                <div class="text-end">
                    <span class="text-uppercase small border border-dark px-3 py-1.5 fw-semibold">
                        @if($order->status == 0) 
                            Chờ xử lý 
                        @elseif($order->status == 1) 
                            Đang giao 
                        @elseif($order->status == 2) 
                            Hoàn thành 
                        @else 
                            Đã hủy 
                        @endif
                    </span>
                </div>
            </div>

            <div class="row g-4 mb-5 small">
                <div class="col-md-6">
                    <div class="text-uppercase fw-bold text-muted mb-2" style="font-size:0.7rem; letter-spacing:1px;">Địa chỉ nhận hàng</div>
                    <strong>{{ $order->fullname }}</strong><br>
                    SĐT: {{ $order->phone }}<br>
                    Địa chỉ: {{ $order->address }}
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="text-uppercase fw-bold text-muted mb-2" style="font-size:0.7rem; letter-spacing:1px;">Thông tin thanh toán</div>
                    Phương thức: <strong class="text-uppercase">{{ $order->payment_method }}</strong><br>
                    Ghi chú đơn: <span class="text-secondary">{{ $order->note ?? 'Không có ghi chú' }}</span>
                </div>
            </div>

            <div class="text-uppercase fw-bold text-muted mb-3 border-bottom pb-2" style="font-size:0.7rem; letter-spacing:1px;">Sản phẩm đã chọn</div>
            
            <div class="mb-4">
                @foreach($orderDetails as $detail)
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom border-light">
                        <div class="d-flex align-items-center gap-3">
                            
                            {{-- Kiểm tra ảnh an toàn bằng toán tử ?-> --}}
                            @if(!empty($detail->product?->image))
                                <img src="{{ $detail->product->image }}" class="border bg-white" alt="{{ $detail->name }}" style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="border bg-white d-flex align-items-center justify-content-center text-muted text-uppercase" style="width: 60px; height: 60px; font-size: 0.6rem;">No Img</div>
                            @endif
                            
                            <div>
                                <div class="fw-bold" style="font-family: 'Playfair Display', serif; font-size:0.95rem;">
                                    {{ $detail->product?->title ?? $detail->name }}
                                </div>
                                <div class="text-muted mt-0.5" style="font-size: 0.7rem;">
                                    Dung tích: {{ $detail->product?->volume ?? 'Mặc định' }}
                                </div>
                                <div class="text-secondary" style="font-size: 0.75rem;">
                                    Số lượng: {{ $detail->quantity }}
                                </div>
                            </div>
                        </div>
                        <div class="fw-medium">{{ number_format($detail->price * $detail->quantity) }}đ</div>
                    </div>
                @endforeach
            </div>

            <div class="row justify-content-end small">
                <div class="col-md-5">
                    <div class="d-flex justify-content-between mb-2 text-muted">
                        <span>Tạm tính</span>
                        <span>{{ number_format($order->total_price) }}đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-muted">
                        <span>Phí vận chuyển</span>
                        <span>0đ</span>
                    </div>
                    <hr class="my-2 text-secondary-subtle">
                    <div class="d-flex justify-content-between align-items-baseline mt-2">
                        <span class="h5 m-0" style="font-family: 'Playfair Display', serif;">Tổng cộng</span>
                        <span class="h4 m-0 fw-bold text-danger" style="font-family: 'Playfair Display', serif;">{{ number_format($order->total_price) }}đ</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection