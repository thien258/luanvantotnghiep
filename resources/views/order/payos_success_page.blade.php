@extends('layout/home')
@section('body')
<div class="container py-5 text-center" style="max-width: 500px; font-family: 'Montserrat', sans-serif;">
    <div class="card border border-dark rounded-0 p-5 bg-white shadow-sm">
        <div class="text-success mb-4">
            <i class="fa-solid fa-circle-check" style="font-size: 4rem;"></i>
        </div>
        <h3 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">Thanh Toán Hoàn Tất</h3>
        <p class="text-muted small mb-4">Hệ thống Aroma đã tự động ghi nhận khoản tiền chuyển khoản của bạn thông qua cổng kết nối PayOS. Đơn hàng đã được chuyển trạng thái sang Đã Thanh Toán.</p>
        <a href="{{ route('welcome') }}" class="btn btn-dark rounded-0 w-100 text-uppercase small fw-bold py-2">
            Tiếp tục mua sắm
        </a>
    </div>
</div>
@endsection 