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
                            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                <a href="{{ route('order.history.detail', $order->id) }}" class="btn btn-dark btn-sm rounded-0 text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 1px; padding: 6px 12px; white-space: nowrap;">
                                    Xem chi tiết
                                </a>

                                @if($order->status == 4)
                                @php
                                    // Kiểm tra còn sản phẩm nào chưa đánh giá không
                                    $hasUnreviewed = $order->details->contains(fn($d) => $d->comments->isEmpty());
                                    // Không hiện nút đánh giá nếu đã ghi nhận [REVIEWED]
                                    $alreadyReviewed = str_contains((string) $order->note, '[REVIEWED]');
                                @endphp
                                @if($hasUnreviewed && !$alreadyReviewed)
                                @php
                                    // Chuẩn bị data sản phẩm chưa đánh giá cho modal
                                    $unreviewedDetails = $order->details->filter(fn($d) => $d->comments->isEmpty())->map(fn($d) => [
                                        'id'    => $d->id,
                                        'name'  => $d->product?->title ?? $d->name,
                                        'image' => $d->product?->image ?? '',
                                    ])->values();
                                @endphp
                                <button type="button"
                                        class="btn btn-outline-dark btn-sm rounded-0 text-uppercase fw-semibold btn-trigger-review"
                                        data-bs-toggle="modal"
                                        data-bs-target="#reviewOrderModal"
                                        data-order-id="{{ $order->id }}"
                                        data-details="{{ json_encode($unreviewedDetails) }}"
                                        style="font-size: 0.65rem; letter-spacing: 1px; padding: 6px 12px; white-space: nowrap;">
                                    ⭐ Đánh giá
                                </button>
                                @endif
                                @endif

                                @if($order->status == 4 && $order->updated_at->diffInDays(now()) <= 3
                                    && !str_contains((string) $order->note, '[RETURN_REJECTED]')
                                    && !str_contains((string) $order->note, '[REVIEWED]'))
                                <button type="button" 
                                        class="btn btn-outline-secondary btn-sm rounded-0 text-uppercase fw-semibold btn-trigger-return" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#returnOrderModal"
                                        data-order-id="{{ $order->id }}"
                                        style="font-size: 0.65rem; letter-spacing: 1px; padding: 6px 12px; white-space: nowrap;">
                                    Hoàn hàng
                                </button>
                                @endif 

                                @if($order->status == 1)
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm rounded-0 text-uppercase fw-semibold btn-trigger-cancel"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cancelOrderModal"
                                        data-order-id="{{ $order->id }}"
                                        style="font-size: 0.65rem; letter-spacing: 1px; padding: 6px 12px; white-space: nowrap;">
                                    Hủy đơn
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Bạn chưa đặt đơn hàng nào tại Aroma.</td>
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
                    <p class="text-muted small mb-3">Vui lòng cho Aroma biết lý do bạn muốn hoàn trả đơn hàng <strong id="modal-order-text">#DH</strong> này để chúng tôi hỗ trợ xử lý sớm nhất.</p>
                    
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
document.addEventListener('DOMContentLoaded', function () {
    var returnBase = "{{ url('order/history/return') }}";
    var cancelBase = "{{ url('order') }}";

    // Hoàn hàng modal
    document.querySelectorAll('.btn-trigger-return').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.orderId;
            document.getElementById('modal-order-text').textContent = '#DH' + id;
            document.getElementById('returnOrderForm').action = returnBase + '/' + id;
        });
    });

    // Hủy đơn modal
    document.querySelectorAll('.btn-trigger-cancel').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.orderId;
            document.getElementById('cancel-order-text').textContent = '#DH' + id;
            document.getElementById('cancelOrderForm').action = cancelBase + '/' + id + '/cancel';
        });
    });
});
</script>

{{-- Modal hủy đơn hàng --}}
<div class="modal fade" id="cancelOrderModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-0 border-dark">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark text-uppercase" style="font-family: 'Playfair Display', serif; font-size: 1.1rem; letter-spacing: 1px;">Hủy đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelOrderForm" action="" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <p class="text-muted small mb-3">Bạn đang yêu cầu hủy đơn hàng <strong id="cancel-order-text">#DH</strong>. Vui lòng cho biết lý do.</p>
                    <div class="mb-3">
                        <label class="form-label text-uppercase small fw-bold text-secondary" style="font-size: 0.7rem; letter-spacing: 0.5px;">Lý do hủy <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-0 border-secondary-subtle" name="reason" rows="3" placeholder="Ví dụ: Đặt nhầm sản phẩm, muốn thay đổi địa chỉ..." required minlength="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light rounded-0 text-uppercase fw-semibold" data-bs-dismiss="modal" style="font-size:0.7rem; padding: 8px 16px;">Quay lại</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-0 text-uppercase fw-semibold" style="font-size:0.7rem; padding: 8px 16px; letter-spacing: 0.5px;">Xác nhận hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal đánh giá sản phẩm --}}
<div class="modal fade" id="reviewOrderModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-0 border-dark">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark text-uppercase" style="font-family: 'Playfair Display', serif; font-size: 1.1rem; letter-spacing: 1px;">
                    Đánh giá sản phẩm — Đơn <span id="review-order-text">#DH</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewOrderForm" action="" method="POST">
                @csrf
                <div class="modal-body py-3" id="review-products-container">
                    {{-- Sản phẩm sẽ được render bởi JS --}}
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light rounded-0 text-uppercase fw-semibold" data-bs-dismiss="modal" style="font-size:0.7rem; padding: 8px 16px;">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-dark rounded-0 text-uppercase fw-semibold" style="font-size:0.7rem; padding: 8px 16px; letter-spacing: 0.5px;">Gửi đánh giá</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.star-rating { display: flex; gap: 4px; flex-direction: row-reverse; justify-content: flex-end; }
.star-rating input { display: none; }
.star-rating label { font-size: 1.6rem; color: #ccc; cursor: pointer; transition: color 0.15s; }
.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label { color: #f5a623; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var reviewBase = "{{ url('order') }}";

    document.querySelectorAll('.btn-trigger-review').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var orderId = this.dataset.orderId;
            var details = JSON.parse(this.dataset.details || '[]');

            document.getElementById('review-order-text').textContent = '#DH' + orderId;
            document.getElementById('reviewOrderForm').action = reviewBase + '/' + orderId + '/review';

            var container = document.getElementById('review-products-container');
            container.innerHTML = '';

            details.forEach(function(detail, idx) {
                var imgHtml = detail.image
                    ? '<img src="' + detail.image + '" style="width:56px;height:56px;object-fit:cover;" class="border me-3 flex-shrink-0">'
                    : '<div class="border bg-light me-3 flex-shrink-0 d-flex align-items-center justify-content-center text-muted" style="width:56px;height:56px;font-size:0.6rem;">No Img</div>';

                var stars = '';
                // Render sao theo thứ tự ngược (CSS trick)
                for (var s = 5; s >= 1; s--) {
                    var sid = 'star_' + detail.id + '_' + s;
                    stars += '<input type="radio" id="' + sid + '" name="reviews[' + detail.id + '][rating]" value="' + s + '">';
                    stars += '<label for="' + sid + '" title="' + s + ' sao">★</label>';
                }

                container.innerHTML += `
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex align-items-start">
                            ${imgHtml}
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark small mb-2">${detail.name}</div>
                                <div class="star-rating mb-2">${stars}</div>
                                <textarea name="reviews[${detail.id}][chat]" class="form-control rounded-0 shadow-none" rows="2" placeholder="Cảm nhận của bạn (không bắt buộc)..." style="font-size:0.82rem;"></textarea>
                            </div>
                        </div>
                    </div>`;
            });
        });
    });
});
</script>

@endsection