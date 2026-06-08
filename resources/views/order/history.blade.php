@extends('layout/home')
@section('body')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">

<div class="py-5 bg-white text-dark" style="font-family: 'Montserrat', sans-serif;">
    <div class="container" style="max-width: 1140px;">

        <div class="mb-5">
            <h1 class="display-5 fw-normal m-0" style="font-family: 'Playfair Display', serif;">Lịch sử đơn hàng</h1>
            <p class="text-muted mt-2 small text-uppercase" style="letter-spacing: 2px;">Danh sách các đơn hàng hương nước hoa bạn đã đặt</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-0 py-2 small mb-3">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-0 py-2 small mb-3">
                {{ session('error') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-light text-uppercase small" style="letter-spacing: 1px; font-size: 0.75rem;">
                    <tr>
                        <th class="py-3 ps-4">Mã Đơn</th>
                        <th class="py-3">Ngày Đặt</th>
                        <th class="py-3">Phương Thức</th>
                        <th class="py-3">Tổng Tiền</th>
                        <th class="py-3">Trạng Thái</th>
                        <th class="py-3 pe-4 text-end" style="width: 240px;">Hành Động</th>
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
                            @if($order->status == 1)
                            <span class="badge rounded-0 bg-warning text-dark px-2 py-1 text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Đang lấy hàng</span>
                            @elseif($order->status == 3)
                            <span class="badge rounded-0 bg-primary text-white px-2 py-1 text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Đang giao hàng</span>
                            @elseif($order->status == 4)
                            <span class="badge rounded-0 bg-success text-white px-2 py-1 text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Hoàn tất</span>
                            @elseif($order->status == 5 || $order->status == 6)
                            <span class="badge rounded-0 bg-secondary text-white px-2 py-1 text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Hoàn hàng</span>
                            @else
                            <span class="badge rounded-0 bg-danger text-white px-2 py-1 text-uppercase" style="font-size:0.65rem; letter-spacing:0.5px;">Đã hủy</span>
                            @endif
                        </td>
                        
                        <td class="py-3 pe-4">
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <a href="{{ route('order.history.detail', $order->id) }}" class="btn btn-dark btn-sm rounded-0 text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 1px; padding: 6px 12px; white-space: nowrap;">
                                    Xem chi tiết
                                </a>

                                @if($order->status == 4)
                                <button type="button" 
                                        class="btn btn-outline-secondary btn-sm rounded-0 text-uppercase fw-semibold btn-trigger-return" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#returnOrderModal"
                                        data-order-id="{{ $order->id }}"
                                        style="font-size: 0.65rem; letter-spacing: 1px; padding: 6px 12px; white-space: nowrap;">
                                    Hoàn hàng
                                </button>
                                @endif 
                            </div>
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

<div class="modal fade" id="returnOrderModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="returnOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-0 border-dark">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark text-uppercase" id="returnOrderModalLabel" style="font-family: 'Playfair Display', serif; font-size: 1.1rem; letter-spacing: 1px;">Yêu cầu hoàn trả đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="returnOrderForm" action="" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <p class="text-muted small mb-3">Vui lòng cho Atelier Scent biết lý do bạn muốn hoàn trả đơn hàng <strong id="modal-order-text">#DH</strong> này để chúng tôi hỗ trợ xử lý sớm nhất.</p>
                    
                    <div class="mb-3">
                        <label for="return_reason" class="form-label text-uppercase small fw-bold text-secondary" style="font-size: 0.7rem; letter-spacing: 0.5px;">Lý do hoàn hàng <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-0 border-secondary-subtle" id="return_reason" name="reason" rows="4" placeholder="Ví dụ: Sản phẩm bị rò rỉ, không đúng dung tích, giao sai mùi hương..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light rounded-0 text-uppercase fw-semibold" data-bs-dismiss="modal" style="font-size:0.7rem; padding: 8px 16px;">Hủy bỏ</button>
                    <button type="submit" class="btn btn-sm btn-dark rounded-0 text-uppercase fw-semibold" style="font-size:0.7rem; padding: 8px 16px; letter-spacing: 0.5px;">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Config từ Blade — không thể tách vì dùng route()
    window.returnRouteTemplate = "{{ route('order.customer-return', ':id') }}";
</script>
<script src="{{ asset('js/order-history.js') }}"></script>

@endsection