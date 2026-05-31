@extends('layout/home')
@section('body')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="py-5 bg-white text-dark" style="font-family: 'Montserrat', sans-serif;">
    <div class="container" style="max-width: 1140px;">

        <div class="mb-5">
            <h1 class="display-5 fw-normal m-0" style="font-family: 'Playfair Display', serif;">Checkout</h1>
            <p class="text-muted mt-2 fst-italic" style="font-family: 'Playfair Display', serif; font-size: 1.05rem;">
                Refining your selection. Every scent is a curated journey, and every detail is handled with the utmost discretion.
            </p>
        </div>

        @if($errors->any())
        <div class="alert alert-danger rounded-0 mb-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('order.place') }}" method="POST" id="checkout-form">
            @csrf

            <input type="hidden" name="fullname" id="input-fullname" value="{{ Auth::user()->name }}">
            <input type="hidden" name="phone" id="input-phone" value="{{ Auth::user()->phone ?? '' }}">
            <input type="hidden" name="address" id="input-address" value="{{ Auth::user()->address ?? '' }}">
            <input type="hidden" name="payment_method" id="input-payment" value="CREDIT CARD">

            <div class="row g-5">

                {{-- CỘT TRÁI --}}
                <div class="col-lg-7">

                    {{-- 01. Shipping Information --}}
                    <div class="text-uppercase fw-bold pt-2 pb-3 mb-3 border-bottom text-muted" style="font-size: 0.75rem; letter-spacing: 2px;">
                        01. Shipping Information
                    </div>

                    {{-- Card hiển thị địa chỉ đang chọn --}}
                    <div id="selected-address-card" class="card rounded-0 border-dark p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-bold small text-uppercase mb-1" style="letter-spacing: 1px;" id="selected-name">
                                    {{ Auth::user()->name }}
                                </div>
                                <div class="text-secondary small lh-base">
                                    SĐT: <strong id="selected-phone">{{ Auth::user()->phone ?? '—' }}</strong><br>
                                    Địa chỉ: <strong id="selected-address-text">{{ Auth::user()->address ?? '—' }}</strong>
                                </div>
                            </div>
                            <button type="button"
                                    class="btn btn-sm btn-outline-dark rounded-0 flex-shrink-0"
                                    onclick="openAddressModal()"
                                    style="font-size: 0.7rem; letter-spacing: 1px; white-space: nowrap;">
                                Thay đổi
                            </button>
                        </div>
                    </div>

                    {{-- 02. Shipping Method --}}
                    <div class="text-uppercase fw-bold pt-2 pb-3 mb-3 border-bottom text-muted" style="font-size: 0.75rem; letter-spacing: 2px;">
                        02. Shipping Method
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-4 mb-4 border bg-light">
                        <div class="d-flex align-items-center gap-3">
                            <input type="radio" name="shipping_fake" id="ship-standard" class="form-check-input border-dark" checked style="accent-color: #000;">
                            <label for="ship-standard" class="m-0" style="cursor:pointer">
                                <div class="fw-bold small text-uppercase" style="letter-spacing: 0.5px; font-size: 0.8rem;">Standard Atelier Delivery</div>
                                <div class="text-muted text-uppercase mt-1" style="font-size: 0.65rem;">3-5 Business Days</div>
                            </label>
                        </div>
                        <div class="small text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Miễn phí</div>
                    </div>

                    {{-- 03. Payment Selection --}}
                    <div class="text-uppercase fw-bold pt-2 pb-3 mb-3 border-bottom text-muted" style="font-size: 0.75rem; letter-spacing: 2px;">
                        03. Payment Selection
                    </div>
                    <div class="row g-3 mb-4">
                    
                        <div class="col-4">
                            <div class="card rounded-0 p-4 text-center border-light-subtle bg-white btn-payment" style="cursor: pointer;" onclick="selectPayment(this, 'BANK TRANSFER')">
                                <div class="text-secondary mb-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="3" y1="22" x2="21" y2="22"></line><line x1="6" y1="18" x2="6" y2="11"></line><line x1="10" y1="18" x2="10" y2="11"></line><line x1="14" y1="18" x2="14" y2="11"></line><line x1="18" y1="18" x2="18" y2="11"></line><polygon points="12 2 20 7 4 7 12 2"></polygon></svg>
                                </div>
                                <div class="text-uppercase fw-bold text-secondary" style="font-size: 0.7rem; letter-spacing: 1px;">Bank Transfer</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card rounded-0 p-4 text-center border-light-subtle bg-white btn-payment" style="cursor: pointer;" onclick="selectPayment(this, 'COD')">
                                <div class="text-secondary mb-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4v-6Z"></path></svg>
                                </div>
                                <div class="text-uppercase fw-bold text-secondary" style="font-size: 0.7rem; letter-spacing: 1px;">Ship COD</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="note" class="text-uppercase fw-bold text-muted small d-block mb-2" style="font-size:0.7rem; letter-spacing:1px;">Order Note (Optional)</label>
                        <textarea class="form-control rounded-0 border-secondary-subtle" name="note" id="note" rows="2" placeholder="Write any special requests for shipping..."></textarea>
                    </div>

                </div>

                {{-- CỘT PHẢI --}}
                <div class="col-lg-5">
                    <div class="card p-5 border-0 rounded-0" style="background-color: #fbfbfb;">
                        <h3 class="mb-4 pb-2" style="font-family: 'Playfair Display', serif; font-size: 1.8rem;">Order Summary</h3>

                        <div class="mb-4 checkout-item-list" style="max-height: 400px; overflow-y: auto;">
                            @forelse($orderItems as $item)
                            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3 cart-item-row" data-price="{{ $item['price'] }}">
                                <div class="d-flex align-items-center gap-3 w-100 position-relative">
                                    @if(!empty($item['image']))
                                        <img src="{{ $item['image'] }}" class="border bg-white" alt="perfume" style="width: 75px; height: 75px; object-fit: cover; flex-shrink: 0;">
                                    @else
                                        <div class="border bg-white d-flex align-items-center justify-content-center text-muted small fw-light" style="width: 75px; height: 75px; flex-shrink: 0;">No Img</div>
                                    @endif

                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="fw-bold text-dark pe-3" style="font-family: 'Playfair Display', serif; font-size: 1rem; letter-spacing: 0.5px;">
                                                {{ $item['title'] }}
                                            </div>
                                            <button type="button" class="btn-close btn-sm shadow-none remove-checkout-item" onclick="removeRow(this)"></button>
                                        </div>
                                        <div class="text-muted text-uppercase mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                            Dung tích: {{ $item['volume'] ?? 'Chưa rõ' }}
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-2">
                                            <div class="input-group input-group-sm border border-dark rounded-0" style="max-width: 90px;">
                                                <button type="button" class="btn btn-light rounded-0 py-0 px-2 btn-qty-down" onclick="changeQty(this, 'down')">-</button>
                                                <input type="text" name="quantities[{{ $item['id'] }}]" class="form-control text-center border-0 bg-transparent py-0 px-1 item-qty-input" value="{{ $item['quantity'] }}" readonly style="font-size: 0.8rem;">
                                                <button type="button" class="btn btn-light rounded-0 py-0 px-2 btn-qty-up" onclick="changeQty(this, 'up')">+</button>
                                            </div>
                                            <div class="fw-medium text-dark line-total-price" style="font-size: 0.9rem;">
                                                {{ number_format($item['price'] * $item['quantity']) }}đ
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-4 text-muted small empty-cart-msg">Giỏ hàng thanh toán trống rỗng.</div>
                            @endforelse
                        </div>

                        <hr class="text-secondary-subtle my-4">

                        <div class="d-flex justify-content-between mb-2 text-uppercase text-muted" style="letter-spacing: 0.5px; font-size: 0.75rem;">
                            <span>Tạm tính</span>
                            <span id="summary-subtotal">{{ number_format($total) }}đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-uppercase text-muted" style="letter-spacing: 0.5px; font-size: 0.75rem;">
                            <span>Phí vận chuyển</span>
                            <span>0đ</span>
                        </div>

                        <hr class="text-secondary-subtle">

                        <div class="d-flex justify-content-between align-items-baseline mb-4 mt-2">
                            <span style="font-family: 'Playfair Display', serif; font-size: 1.75rem;">Tổng tiền</span>
                            <span class="fw-bold text-danger" style="font-family: 'Playfair Display', serif; font-size: 1.75rem;" id="summary-total">
                                {{ number_format($total) }}đ
                            </span>
                        </div>

                        <button type="submit" id="btn-submit-payment" class="btn btn-dark w-100 rounded-0 py-3 text-uppercase fw-semibold" style="font-size: 0.85rem; letter-spacing: 2px;" {{ $total <= 0 ? 'disabled' : '' }}>
                            Confirm Payment
                        </button>

                        <div class="text-center text-muted mt-3 text-uppercase" style="font-size: 0.6rem; letter-spacing: 1px; line-height: 1.5;">
                            Secure SSL Encryption — Payments are processed<br>via our encrypted gateway.
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL CHỌN ĐỊA CHỈ ===== --}}
<div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-0 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 2px;">
                    Địa chỉ giao hàng
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Danh sách địa chỉ hiển thị ở đây --}}
                <div id="address-list" class="mb-4">
                    <div class="text-center text-muted small py-3">Đang tải...</div>
                </div>

                {{-- Form thêm / sửa ẩn hiện --}}
                <div id="address-form-wrap" class="border-top pt-4" style="display: none;">
                    <div class="fw-bold small text-uppercase mb-3" style="letter-spacing: 1px;" id="form-title">Thêm địa chỉ mới</div>
                    <input type="hidden" id="edit-address-id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" id="form-name" class="form-control rounded-0 border-dark" placeholder="Họ và tên người nhận">
                        </div>
                        <div class="col-md-6">
                            <input type="text" id="form-phone" class="form-control rounded-0 border-dark" placeholder="Số điện thoại">
                        </div>
                        <div class="col-12">
                            <input type="text" id="form-address" class="form-control rounded-0 border-dark" placeholder="Địa chỉ cụ thể (số nhà, đường, phường, quận, tỉnh)">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="form-is-default">
                                <label class="form-check-label small" for="form-is-default">Đặt làm địa chỉ mặc định</label>
                            </div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="button" class="btn btn-dark rounded-0 px-4" onclick="saveAddress()">Lưu</button>
                            <button type="button" class="btn btn-outline-secondary rounded-0 px-4" onclick="cancelForm()">Hủy</button>
                        </div>
                    </div>
                </div>

                {{-- Nút thêm mới --}}
                <div id="btn-add-wrap" class="mt-3">
                    <button type="button" class="btn btn-outline-dark rounded-0 w-100" onclick="showAddForm()">
                        <i class="fa-solid fa-plus me-2"></i>Thêm địa chỉ mới
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
window.LaravelConfig = {
    csrfToken: '{{ csrf_token() }}',
    addrIndex: '{{ route("addresses.index") }}',
    addrStore: '{{ route("addresses.store") }}',
};
</script>
<script src="{{ asset('js/checkout.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('btn-submit-payment');
    console.log('btn disabled:', btn ? btn.disabled : 'not found');
    console.log('cart rows:', document.querySelectorAll('.cart-item-row').length);
    console.log('total:', document.getElementById('summary-total') ? document.getElementById('summary-total').innerText : 'not found');
});
</script>
@endsection