@extends('layout/home')
@section('body')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">

<div class="py-5 bg-white text-dark" style="font-family: 'Montserrat', sans-serif;">
    <div class="container" style="max-width: 1140px;">
        
        <div class="mb-5">
            <h1 class="display-5 fw-normal m-0" style="font-family: 'Playfair Display', serif;">Lịch sử đơn hàng</h1>
            <p class="text-muted mt-2 small text-uppercase" style="letter-spacing: 2px;">Danh sách các đơn hàng hương nước hoa bạn đã đặt</p>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-light text-uppercase small" style="letter-spacing: 1px; font-size: 0.75rem;">
                    <tr>
                        <th class="py-3 ps-4">Mã Đơn</th>
                        <th class="py-3">Ngày Đặt</th>
                        <th class="py-3">Phương Thức</th>
                        <th class="py-3">Tổng Tiền</th>
                        <th class="py-3">Trạng Thái</th>
                        <th class="py-3 pe-4 text-end">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($orders as $order)
                        <tr class="border-bottom">
                            <td class="py-3 ps-4 fw-bold">#DH{{ $order->id }}</td>
                            <td class="py-3 text-secondary">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 text-uppercase font-monospace" style="font-size: 0.8rem;">{{ $order->payment_method }}</td>
                            <td class="py-3 fw-medium text-danger">{{ number_format($order->total_price) }}đ</td>
                            <td class="py-3">
                                @if($order->status == 0)
                                    <span class="badge rounded-0 bg-warning text-dark px-2 py-1 text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Chờ xử lý</span>
                                @elseif($order->status == 1)
                                    <span class="badge rounded-0 bg-success text-white px-2 py-1 text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Đang giao hàng</span>
                                @elseif($order->status == 2)
                                    <span class="badge rounded-0 bg-secondary text-white px-2 py-1 text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Đã hoàn thành</span>
                                @else
                                    <span class="badge rounded-0 bg-danger text-white px-2 py-1 text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Đã hủy</span>
                                @endif
                            </td>
                            <td class="py-3 pe-4 text-end">
                                <a href="{{ route('order.history.detail', $order->id) }}" class="btn btn-dark btn-sm rounded-0 text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 1px; padding: 6px 12px;">
                                    Xem chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Bạn chưa đặt đơn hàng nào tại Atelier Scent.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection