@extends('layout.home')

@section('body')
<div class="container py-5">
    <div class="mb-5">
        <h2 class="fw-bold text-dark mb-2">Giỏ hàng của bạn</h2>
        <p class="text-muted small col-md-6 px-0">
            Mỗi hương thơm là một hành trình tinh thần. Cảm ơn bạn đã lựa chọn sản phẩm của chúng tôi.
        </p>
    </div>

    @if(session('error')) <div class="alert alert-danger rounded-0 mb-4 small py-2">⚠️ {{ session('error') }}</div> @endif
    @if(session('status')) <div class="alert alert-success rounded-0 mb-4 small py-2">✅ {{ session('status') }}</div> @endif

    <div class="row g-5">
        <div class="col-lg-7">
            @forelse($carts as $cart)
            @php
            $product = $cart->product;
            $isOutOfStock = ($product->quantity <= 0 || $product->status == 0);
                $originalPrice = $product->price;
                $finalPrice = $product->getDiscountedPrice();
                @endphp

                <div class="row pb-4 mb-4 border-bottom align-items-center">
                    {{-- 1. CHECKBOX & ẢNH --}}
                    <div class="col-4 col-md-3 flex-shrink-0 d-flex align-items-center">
                        <input type="checkbox" class="form-check-input cart-item-checkbox me-3 border-dark shadow-none"
                            value="{{ $cart->id }}" data-price="{{ $finalPrice }}"
                            data-quantity="{{ $cart->quantity }}" data-stock="{{ $product->quantity }}"
                            {{ $isOutOfStock ? 'disabled' : '' }} style="transform: scale(1.3); cursor: pointer;">

                        <div class="position-relative bg-light d-flex align-items-center justify-content-center rounded border" style="width: 85px; height: 85px; overflow: hidden;">
                            <img src="{{ $product->image }}" class="img-fluid {{ $isOutOfStock ? 'opacity-25' : '' }}" style="max-height: 100%; object-fit: contain;">
                            @if($isOutOfStock)
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: rgba(255, 255, 255, 0.4);">
                                <span class="badge bg-danger">HẾT HÀNG</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- 2. CHI TIẾT SẢN PHẨM --}}
                    <div class="col-8 col-md-9">
                        <div class="d-flex justify-content-between mb-1">
                            <h5 class="fw-bold text-dark mb-0">{{ $product->title }}</h5>
                            <form action="{{ route('carts.destroy', $cart->id) }}" method="POST" onsubmit="return confirm('Xóa sản phẩm này?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-close shadow-none"></button>
                            </form>
                        </div>

                        {{-- Hiển thị giá: Gốc (gạch) và Giảm (đỏ) --}}
                        <div class="mb-2">
                            <span class="text-danger fw-bold fs-5">{{ number_format($finalPrice) }}đ</span>
                            @if($finalPrice < $originalPrice)
                                <span class="text-muted text-decoration-line-through small ms-2">{{ number_format($originalPrice) }}đ</span>
                                @endif
                        </div>

                        <div class="small text-muted mb-2">Dung tích: <strong>{{ $product->volume }}</strong></div>

                        {{-- 3. TĂNG GIẢM SỐ LƯỢNG --}}
                        <div class="input-group input-group-sm border border-dark rounded-0" style="max-width: 100px;">
                            <button type="button" class="btn btn-light btn-qty-change" data-id="{{ $cart->id }}" data-action="down" {{ $isOutOfStock ? 'disabled' : '' }}>-</button>
                            <input type="text" id="qty-{{ $cart->id }}" class="form-control text-center border-0" value="{{ $cart->quantity }}" readonly>
                            <button type="button" class="btn btn-light btn-qty-change" data-id="{{ $cart->id }}" data-action="up" data-stock="{{ $product->quantity }}" {{ $isOutOfStock ? 'disabled' : '' }}>+</button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <h5 class="text-muted">Giỏ hàng trống.</h5>
                </div>
                @endforelse
        </div>

        {{-- 4. TÓM TẮT ĐƠN HÀNG --}}
        <div class="col-lg-5">
            <div class="bg-light p-4 rounded sticky-top">
                <h4 class="fw-bold mb-4">Tóm tắt đơn hàng</h4>
                <div class="d-flex justify-content-between mb-3"><span>Tạm tính</span><span id="display-subtotal">0đ</span></div>
                <div class="d-flex justify-content-between mb-3 small text-muted">
                    <span>Thuế (VAT 10%)</span>
                    <span id="display-vat">0đ</span>
                </div>
                <div class="d-flex justify-content-between mb-4"><span>Tổng cộng</span><span id="display-total" class="fs-3 text-danger fw-bold">0đ</span></div>
                <button type="button" id="btn-checkout" class="btn btn-dark w-100" disabled>THANH TOÁN (<span id="display-count">0</span>)</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('js/cart.js') }}"></script>
@endsection