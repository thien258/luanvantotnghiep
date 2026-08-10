@extends('layout.home')
@section('body')

<section class="blog-banner-area py-5 bg-light border-bottom">
    <div class="container">
        <div class="text-center py-4">
            <h1 class="display-5 fw-bold text-dark">SẢN PHẨM</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('welcome') ?? '#' }}" class="text-decoration-none text-muted">Home</a></li>
                    <li class="breadcrumb-item active text-dark" aria-current="page">Chi tiết sản phẩm</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<div class="product_image_area py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="d-flex justify-content-center align-items-center bg-light p-4 rounded border">
                    <img class="img-fluid w-100" src="{{$product->image}}" alt="{{$product->title}}" style="object-fit: contain; max-height: 500px;">
                </div>
            </div>

            <div class="col-lg-5 offset-lg-1">
                <div class="s_product_text">
                    <h3 class="display-6 fw-bold mb-2">{{$product->title}}</h3>

                    {{-- 1. HIỂN THỊ GIÁ TIỀN PHẲNG --}}
                    <div class="mb-4">
                        @php
                            // Gọi hàm trong Model Product đã sửa ở bước trước
                            $finalPrice = $product->getDiscountedPrice();
                            $originalPrice = $product->price;
                        @endphp

                        <div class="d-flex align-items-baseline gap-2 flex-wrap">
                            @if($finalPrice < $originalPrice)
                                <h2 class="text-danger fw-bold m-0 d-inline">
                                    {{ number_format($finalPrice) }} VNĐ
                                </h2>
                                <span class="text-muted text-decoration-line-through small ms-2" style="font-size: 16px;">
                                    {{ number_format($originalPrice) }} VNĐ
                                </span>
                            @else
                                <h2 class="text-danger fw-bold m-0 d-inline">
                                    {{ number_format($originalPrice) }} VNĐ
                                </h2>
                            @endif
                        </div>
                    </div>

                    <p class="text-secondary lh-lg mb-4">{{$product->decription}}</p>

                    <div class="mb-4 py-3 border-top border-bottom">
                        <div class="text-uppercase small tracking-widest text-muted mb-2">
                            Nồng độ: <span class="text-dark fw-bold ms-2">{{$product->concentration->concentration ?? 'N/A'}}</span>
                        </div>
                        
                        {{-- 2. HIỂN THỊ DUNG TÍCH CỐ ĐỊNH --}}
                        <div class="text-uppercase small tracking-widest text-muted mb-3">
                            Dung tích: <span class="badge bg-secondary text-white">{{ $product->volume }}</span>
                        </div>

                        {{-- 3. HIỂN THỊ TỒN KHO --}}
                        <div class="text-uppercase small tracking-widest text-muted">
                            Trạng thái: 
                            <span class="fw-bold ms-2 {{ $product->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $product->quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}
                            </span>
                            ({{ $product->quantity }} chai có sẵn)
                        </div>
                    </div>

                    {{-- 4. FORM THÊM VÀO GIỎ HÀNG --}}
                    <form action="{{ route('carts.store') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        
                        {{-- Chọn số lượng --}}
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <label class="text-uppercase small fw-bold mb-0">Số lượng:</label>
                            <div class="input-group" style="width: 150px;">
                                <button type="button" class=" btn btn-outline-secondary rounded-0" onclick="decreaseQty()">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <input type="number" name="quantity" id="quantityInput" value="1" min="1" 
                                       max="{{ $product->quantity }}" data-max="{{ $product->quantity }}" 
                                       class=" text-center form-control text-center rounded-0" readonly>
                                <button type="button" class="btn btn-outline-secondary rounded-0" onclick="increaseQty()">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <span class="text-muted small">(Còn {{ $product->quantity }} sản phẩm)</span>
                        </div>

                        <button type="submit" class="btn btn-dark btn-lg rounded-0 px-5 text-uppercase fw-bold shadow-none w-100"
                            {{ $product->quantity <= 0 ? 'disabled' : '' }}>
                            <i class="fa-solid fa-shopping-cart me-2"></i> THÊM VÀO GIỎ
                        </button>
                    </form>
                    
                    <script>
                    function decreaseQty() {
                        const input = document.getElementById('quantityInput');
                        if (input.value > 1) {
                            input.value = parseInt(input.value) - 1;
                        }
                    }
                    
                    function increaseQty() {
                        const input = document.getElementById('quantityInput');
                        const max = parseInt(input.getAttribute('data-max'));
                        if (parseInt(input.value) < max) {
                            input.value = parseInt(input.value) + 1;
                        }
                    }
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Phần đánh giá giữ nguyên vì không phụ thuộc cấu trúc sản phẩm --}}
<div class="bg-light py-5 border-top">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h4 class="fw-bold mb-4 border-bottom pb-2">Đánh giá khách hàng</h4>
                @php
                    $avgRating = $product->comment->whereNotNull('rating')->avg('rating');
                    $reviewCount = $product->comment->whereNotNull('rating')->count();
                @endphp
                @if($reviewCount > 0)
                <div class="mb-3 d-flex align-items-center gap-2">
                    <span class="text-warning fs-5">
                        @for($i = 1; $i <= 5; $i++)
                            {{ $i <= round($avgRating) ? '★' : '☆' }}
                        @endfor
                    </span>
                    <span class="fw-bold">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-muted small">({{ $reviewCount }} đánh giá)</span>
                </div>
                @endif
                <div class="row">
                    @forelse($product->comment as $c)
                    @if($loop->index < 3)
                    <div class="col-md-6 col-lg-4">
                        <div class="bg-white p-3 mb-3 shadow-sm border-start border-dark border-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <p class="fw-bold mb-0 text-uppercase small">{{ $c->name }}</p>
                                @if($c->rating)
                                <span class="text-warning small">
                                    @for($i = 1; $i <= 5; $i++)
                                        {{ $i <= $c->rating ? '★' : '☆' }}
                                    @endfor
                                </span>
                                @endif
                            </div>
                            @if($c->chat)
                            <p class="text-muted mb-0 small">"{{ $c->chat }}"</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    @empty
                    <div class="col-12">
                        <div class="p-4 bg-white shadow-sm border text-center text-muted">
                            <p class="mb-0 fst-italic">Chưa có đánh giá nào.</p>
                        </div>
                    </div>
                    @endforelse
                </div>

                @if($product->comment->count() > 3)
                <div class="text-center mt-2">
                    <button class="btn btn-outline-dark btn-sm rounded-0 px-4" onclick="document.getElementById('modalAllReviews').style.display='flex'">
                        Xem thêm ({{ $product->comment->count() - 3 }}) <i class="fa-solid fa-chevron-down ms-1"></i>
                    </button>
                </div>

                {{-- Modal tất cả đánh giá --}}
                <div id="modalAllReviews" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                    <div style="background:#fff; width:100%; max-width:700px; max-height:85vh; display:flex; flex-direction:column; margin:16px;">
                        <div style="padding:20px 24px; border-bottom:1px solid #dee2e6; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <strong style="font-size:1.1rem;">Tất cả đánh giá</strong>
                                @if($reviewCount > 0)
                                <span class="text-warning ms-2">
                                    @for($i = 1; $i <= 5; $i++){{ $i <= round($avgRating) ? '★' : '☆' }}@endfor
                                </span>
                                <span class="text-muted small ms-1">{{ number_format($avgRating, 1) }}/5 ({{ $reviewCount }} đánh giá)</span>
                                @endif
                            </div>
                            <button onclick="document.getElementById('modalAllReviews').style.display='none'" style="background:none;border:none;font-size:1.5rem;cursor:pointer;line-height:1;">&times;</button>
                        </div>
                        {{-- Filter theo số sao --}}
                        @php
                            $starCounts = [];
                            foreach([5,4,3,2,1] as $s) {
                                $starCounts[$s] = $product->comment->where('rating', $s)->count();
                            }
                        @endphp
                        <div style="padding:12px 24px; border-bottom:1px solid #f0f0f0; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                            <span class="small text-muted me-1">Lọc:</span>
                            <button onclick="filterReviews(0)" id="filter-btn-0" class="btn btn-sm btn-dark rounded-0 px-3" style="font-size:12px;">Tất cả</button>
                            <button onclick="filterReviews(5)" id="filter-btn-5" class="btn btn-sm btn-outline-dark rounded-0 px-3" style="font-size:12px;">5 ★ <span class="text-muted">({{ $starCounts[5] }})</span></button>
                            <button onclick="filterReviews(4)" id="filter-btn-4" class="btn btn-sm btn-outline-dark rounded-0 px-3" style="font-size:12px;">4 ★ <span class="text-muted">({{ $starCounts[4] }})</span></button>
                            <button onclick="filterReviews(3)" id="filter-btn-3" class="btn btn-sm btn-outline-dark rounded-0 px-3" style="font-size:12px;">3 ★ <span class="text-muted">({{ $starCounts[3] }})</span></button>
                            <button onclick="filterReviews(2)" id="filter-btn-2" class="btn btn-sm btn-outline-dark rounded-0 px-3" style="font-size:12px;">2 ★ <span class="text-muted">({{ $starCounts[2] }})</span></button>
                            <button onclick="filterReviews(1)" id="filter-btn-1" class="btn btn-sm btn-outline-dark rounded-0 px-3" style="font-size:12px;">1 ★ <span class="text-muted">({{ $starCounts[1] }})</span></button>
                        </div>
                        <div style="overflow-y:auto; padding:20px 24px; flex:1;" id="review-list-modal">
                            @foreach($product->comment as $c)
                            <div class="review-modal-item" data-rating="{{ $c->rating ?? 0 }}" style="border-bottom:1px solid #f0f0f0; padding-bottom:16px; margin-bottom:16px;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-uppercase small">{{ $c->name }}</span>
                                    @if($c->rating)
                                    <span class="text-warning small">
                                        @for($i = 1; $i <= 5; $i++){{ $i <= $c->rating ? '★' : '☆' }}@endfor
                                    </span>
                                    @endif
                                </div>
                                @if($c->chat)
                                <p class="text-muted mb-1 small">"{{ $c->chat }}"</p>
                                @endif
                                <span class="text-muted" style="font-size:11px;">{{ $c->created_at->format('d/m/Y') }}</span>
                            </div>
                            @endforeach
                            <p id="no-review-msg" style="display:none;" class="text-center text-muted fst-italic py-3">Không có đánh giá nào.</p>
                        </div>
                        <div style="padding:16px 24px; border-top:1px solid #dee2e6; text-align:right;">
                            <button onclick="document.getElementById('modalAllReviews').style.display='none'" class="btn btn-dark rounded-0 px-4">Đóng</button>
                        </div>
                    </div>
                </div>
                <script>
                // Đóng modal khi click ra ngoài
                document.getElementById('modalAllReviews').addEventListener('click', function(e) {
                    if (e.target === this) this.style.display = 'none';
                });

                function filterReviews(star) {
                    // Cập nhật trạng thái nút
                    [0,1,2,3,4,5].forEach(function(s) {
                        var btn = document.getElementById('filter-btn-' + s);
                        if (!btn) return;
                        btn.className = s === star
                            ? 'btn btn-sm btn-dark rounded-0 px-3'
                            : 'btn btn-sm btn-outline-dark rounded-0 px-3';
                        btn.style.fontSize = '12px';
                    });

                    // Lọc các item
                    var items = document.querySelectorAll('.review-modal-item');
                    var count = 0;
                    items.forEach(function(item) {
                        var rating = parseInt(item.getAttribute('data-rating')) || 0;
                        var show = (star === 0) || (rating === star);
                        item.style.display = show ? '' : 'none';
                        if (show) count++;
                    });

                    // Hiện thông báo nếu không có kết quả
                    document.getElementById('no-review-msg').style.display = count === 0 ? '' : 'none';
                }
                </script>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Sản phẩm tương tự cùng thương hiệu --}}
@if($relatedProducts->count() > 0)
<div class="py-5 border-top">
    <div class="container">
        <h4 class="fw-bold mb-4 border-bottom pb-2">
            Sản phẩm tương tự
            <span class="text-muted fw-normal fs-6 ms-2">{{ $product->brand->title ?? '' }}</span>
        </h4>
        <div class="row g-3">
            @foreach($relatedProducts as $related)
            @php
                $relatedFinalPrice    = $related->getDiscountedPrice();
                $relatedOriginalPrice = $related->price;
            @endphp
            <div class="col-6 col-md-3">
                <a href="{{ route('single_product', $related->id) }}" class="text-decoration-none text-dark">
                    <div class="bg-white border shadow-sm h-100 d-flex flex-column">
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px; overflow: hidden;">
                            <img src="{{ $related->image }}" alt="{{ $related->title }}"
                                 class="img-fluid" style="max-height: 190px; object-fit: contain;">
                        </div>
                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <p class="fw-bold mb-1 small" style="min-height: 40px; line-height: 1.4;">{{ $related->title }}</p>
                            @if($relatedFinalPrice < $relatedOriginalPrice)
                                <span class="text-danger fw-bold">{{ number_format($relatedFinalPrice) }} VNĐ</span>
                                <span class="text-muted text-decoration-line-through small">{{ number_format($relatedOriginalPrice) }} VNĐ</span>
                            @else
                                <span class="text-danger fw-bold">{{ number_format($relatedOriginalPrice) }} VNĐ</span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection