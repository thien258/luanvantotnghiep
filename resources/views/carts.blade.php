@extends('layout.home')

@section('body')
<div class="container py-5">
    <div class="mb-5">
        <h2 class="fw-bold text-dark mb-2">Giỏ hàng của bạn</h2>
        <p class="text-muted small col-md-6 px-0">
            Mỗi hương thơm là một hành trình tinh thần. Cảm ơn bạn đã lựa chọn sản phẩm của chúng tôi để đồng hành cùng không gian sống.
        </p>
    </div>

    {{-- Khối hiển thị thông báo lỗi/thành công từ hệ thống --}}
    @if(session('error'))
    <div class="alert alert-danger rounded-0 mb-4 small py-2">⚠️ {{ session('error') }}</div>
    @endif
    @if(session('status'))
    <div class="alert alert-success rounded-0 mb-4 small py-2">✅ {{ session('status') }}</div>
    @endif

    <div class="row g-5">
        <div class="col-lg-7">
            @forelse($carts as $cart)
            {{-- 🌟 Đối chiếu chuẩn xác theo mối quan hệ productVariant của Model Cart --}}
            @if($cart->productVariant)
            @php
                $variant = $cart->productVariant;
                // Kiểm tra trạng thái tồn kho thực tế
                $isOutOfStock = ($variant->stock <= 0 || $variant->product?->status == 0);
                
                // Lấy giá chuẩn từ bộ não Model ProductVariant
                $originalPrice = $variant->price;
                $finalPrice = $variant->final_price; 
            @endphp

                <div class="row pb-4 mb-4 border-bottom align-items-center">

                    {{-- 1. Ô CHECKBOX CHỌN MÓN VÀ HÌNH ẢNH --}}
                    <div class="col-4 col-md-3 flex-shrink-0 d-flex align-items-center">
                        {{-- Ô Checkbox chứa dữ liệu giá giảm để JS tự động bốc đi tính tổng --}}
                        <input type="checkbox" class="form-check-input cart-item-checkbox me-3 border-dark shadow-none"
                            value="{{ $cart->id }}"
                            data-price="{{ $finalPrice }}" 
                            data-quantity="{{ $cart->quantity }}"
                            data-stock="{{ $variant->stock }}"
                            {{ $isOutOfStock ? 'disabled' : '' }}
                            style="transform: scale(1.3); cursor: pointer; margin-top: 0;">

                        <div class="position-relative bg-light d-flex align-items-center justify-content-center rounded border" style="width: 85px; height: 85px; overflow: hidden;">
                            <img src="{{ $variant->product?->image }}" class="img-fluid {{ $isOutOfStock ? 'opacity-25' : '' }}" style="max-height: 100%; object-fit: contain;">

                            @if($isOutOfStock)
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: rgba(255, 255, 255, 0.4); z-index: 10;">
                                <span class="badge bg-danger text-white px-2 py-1 shadow-sm" style="font-size: 10px; transform: rotate(-15deg); border: 1px solid white;">HẾT HÀNG</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- 2. CHI TIẾT SẢN PHẨM & ĐƠN GIÁ --}}
                    <div class="col-8 col-md-9">
                        <div class="d-flex justify-content-between mb-1">
                            <h5 class="fw-bold {{ $isOutOfStock ? 'text-muted text-decoration-line-through' : 'text-dark' }} mb-0">
                                {{ $variant->product?->title }}
                            </h5>
                            <form action="{{ route('carts.destroy', $cart->id) }}" method="POST" onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ hàng?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-close shadow-none" style="width: 12px; height: 12px;"></button>
                            </form>
                        </div>

                        {{-- Khối hiển thị Đơn giá --}}
                        <div class="mb-2">
                            @if($finalPrice < $originalPrice)
                                <p class="text-danger fw-bold mb-0 d-inline-block me-2" style="font-size: 16px;">
                                    {{ number_format($finalPrice) }}đ
                                </p>
                                <span class="text-muted text-decoration-line-through small" style="font-size: 13px;">
                                    {{ number_format($originalPrice) }}đ
                                </span>
                            @else
                                <p class="text-danger fw-bold mb-0" style="font-size: 16px;">
                                    {{ number_format($originalPrice) }}đ
                                </p>
                            @endif
                        </div>

                        {{-- Chọn lựa đổi Dung tích nước hoa động --}}
                        <form action="{{ route('carts.update', $cart->id) }}" method="POST" class="mb-2">
                            @csrf @method('PUT')
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted fw-bold">Dung tích:</span>
                                <select name="newIdVariant" class="form-select form-select-sm w-auto rounded-0 shadow-none border-dark" onchange="this.form.submit()">
                                    @foreach($variant->product->variants as $v)
                                    <option value="{{ $v->id }}"
                                        {{ $cart->idPV == $v->id ? 'selected' : '' }}
                                        {{ $v->stock <= 0 && $cart->idPV != $v->id ? 'disabled' : '' }}>
                                        {{ $v->volume?->name ?? 'N/A' }}
                                        {{ $v->stock <= 0 ? '(Hết hàng)' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>

                        @if($isOutOfStock)
                        <div class="text-danger small fw-bold mb-2">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> Phân loại này đã hết, vui lòng chọn mức dung tích khác!
                        </div>
                        @endif

                        {{-- 3. CỤM NÚT TĂNG GIẢM SỐ LƯỢNG CHUẨN ĐƯỜNG TRUYỀN DATA-STOCK --}}
                        <div class="mt-2">
                            <div class="input-group input-group-sm border border-dark rounded-0" style="max-width: 100px;">
                                <button type="button" class="btn btn-light border-0 px-2 fw-bold btn-qty-change" data-id="{{ $cart->id }}" data-action="down" {{ $isOutOfStock ? 'disabled' : '' }}>-</button>

                                <input type="text" id="qty-{{ $cart->id }}" class="form-control text-center border-0 bg-white fw-bold" value="{{ $cart->quantity }}" readonly>

                                <button type="button" class="btn btn-light border-0 px-2 fw-bold btn-qty-change" 
                                    data-id="{{ $cart->id }}" 
                                    data-action="up" 
                                    data-stock="{{ $variant->stock }}"
                                    {{ $isOutOfStock ? 'disabled' : '' }}>+</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @empty
                <div class="text-center py-5 border-bottom">
                    <h5 class="text-muted fw-normal">Giỏ hàng của bạn đang trống.</h5>
                </div>
            @endforelse
        </div>

        {{-- 4. CỘT TÓM TẮT ĐƠN HÀNG --}}
        <div class="col-lg-5">
            <div class="bg-light p-4 p-md-5 rounded border sticky-top" style="top: 20px;">
                <h4 class="fw-bold text-dark mb-4">Tóm tắt đơn hàng</h4>

                <div class="d-flex justify-content-between mb-3 border-bottom pb-2 small">
                    <span class="text-muted">Tạm tính</span>
                    <span class="text-dark fw-bold" id="display-subtotal">0đ</span>
                </div>

                <div class="d-flex justify-content-between mb-3 border-bottom pb-2 small">
                    <span class="text-muted">Thuế (VAT 10%)</span>
                    <span class="text-dark fw-bold" id="display-vat">0đ</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold text-uppercase small">Tổng cộng</span>
                    <span class="fs-3 fw-bold text-danger" id="display-total">0đ</span>
                </div>

                {{-- FORM THANH TOÁN CHUYỂN DỮ LIỆU ĐI CHECKOUT --}}
                <form action="{{ route('carts.store') }}" method="POST" id="form-checkout">
                    @csrf
                    <div id="hidden-cart-inputs"></div>

                    <button type="button" id="btn-checkout" class="btn btn-dark w-100 rounded-0 py-3 text-uppercase fw-bold small mb-2 shadow-none" disabled>
                        Tiến hành thanh toán (<span id="display-count">0</span>)
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('js/cart.js') }}"></script>
@endsection