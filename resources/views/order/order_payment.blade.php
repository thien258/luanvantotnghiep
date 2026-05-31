@extends('layout/home')
@section('body')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">

<div class="py-5 bg-white text-dark" style="font-family: 'Montserrat', sans-serif;">
    <div class="container" style="max-width: 850px;">
        
        <div class="text-center mb-5">
            <h1 class="display-6 fw-normal m-0" style="font-family: 'Playfair Display', serif;">Thanh Toán Đơn Hàng</h1>
            <p class="text-muted mt-2 small text-uppercase" style="letter-spacing: 2px;">Mã hóa đơn: #DH{{ $order->id }}</p>
        </div>

        <div class="card rounded-0 border-dark p-5 bg-white shadow-sm">
            <div class="row g-5 align-items-center">
                
                <div class="col-md-5 text-center border-end">
                    <div class="p-3 d-inline-block bg-white border border-secondary-subtle">
                        <img src="{{ $qrCodeUrl }}" alt="VietQR Thanh Toan" class="img-fluid" style="max-width: 220px; width: 100%;">
                    </div>
                    <div class="mt-3 small text-muted px-2" style="font-size: 0.75rem; line-height: 1.4;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><rect x="7" y="7" width="3" height="3"></rect><rect x="14" y="7" width="3" height="3"></rect><rect x="7" y="14" width="3" height="3"></rect></svg>
                        Mở App Ngân hàng bất kỳ để quét mã chuyển khoản nhanh
                    </div>
                </div>

                <div class="col-md-7 ps-md-4">
                    <h4 class="mb-4 text-uppercase fw-semibold" style="font-family: 'Playfair Display', serif; font-size: 1.1rem; letter-spacing: 1px;">Thông tin tài khoản</h4>
                    
                    <table class="table table-borderless small mb-4">
                        <tbody>
                            <tr class="border-bottom">
                                <td class="text-muted py-2 px-0">Số tiền cần thanh toán:</td>
                                <td class="fw-bold text-danger py-2 text-end" style="font-size: 1.1rem;">{{ number_format($amount) }}đ</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2 px-0">Nội dung chuyển khoản:</td>
                                <td class="py-2 text-end">
                                    <span class="fw-bold px-2 py-1 bg-warning-subtle text-dark border border-warning" style="font-size: 0.85rem;">
                                        {{ $addInfo }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="p-3 bg-light border text-secondary small" style="line-height: 1.5; font-size: 0.75rem;">
                        <strong class="text-dark">⚠️ Lưu ý đồ án:</strong> Khi quét mã bằng ứng dụng ngân hàng thật, hệ thống API sẽ tự động điền đúng số tiền <span class="text-danger fw-bold">{{ number_format($amount) }}đ</span> và nội dung <span class="fw-bold">{{ $addInfo }}</span> mà không cần nhập tay.
                    </div>

                    <div class="mt-4 pt-2 d-flex flex-column gap-2">
                        {{-- Đã thanh toán: trừ tồn kho, về trang chủ --}}
                        <form action="{{ route('order.confirmPaid', $order->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-dark rounded-0 w-100 py-2 text-uppercase fw-semibold"
                                    style="font-size: 0.75rem; letter-spacing: 2px;">
                                <i class="fa-solid fa-check me-2"></i>Tôi đã thanh toán
                            </button>
                        </form>

                        {{-- Chưa thanh toán: hủy đơn, về giỏ hàng --}}
                        <form action="{{ route('order.cancel', $order->id) }}" method="POST"
                              onsubmit="return confirm('Hủy đơn hàng này? Sản phẩm sẽ được giữ lại trong giỏ hàng.')">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary rounded-0 w-100 py-2 text-uppercase"
                                    style="font-size: 0.75rem; letter-spacing: 1px;">
                                Chưa thanh toán — Quay lại
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection