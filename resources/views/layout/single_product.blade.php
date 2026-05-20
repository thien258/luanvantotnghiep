@extends('layout/home')
@section('body')

<section class="blog-banner-area py-5 bg-light border-bottom">
    <div class="container">
        <div class="text-center py-4">
            <h1 class="display-5 fw-bold text-dark">Exclusive Fragrance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('welcome') ?? '#' }}" class="text-decoration-none text-muted">Home</a></li>
                    <li class="breadcrumb-item active text-dark" aria-current="page">Shop Single</li>
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

                    {{-- 1. NƠI HIỂN THỊ GIÁ TIỀN (SẼ NHẢY SỐ TỰ ĐỘNG BẰNG JS) --}}
                    <h2 class="text-danger fw-bold mb-4">
                        <span id="display-price">{{ number_format($product->variants->first()?->price ?? 0) }}</span> VNĐ
                    </h2>

                    <p class="text-secondary lh-lg mb-4">{{$product->decription}}</p>

                    <div class="mb-4 py-3 border-top border-bottom">
                        <div class="text-uppercase small tracking-widest text-muted mb-3">
                            Concentration: <span class="text-dark fw-bold ms-2">{{$product->concentration->concentration ?? 'N/A'}}</span>
                        </div>
                        
                        {{-- 2. DÀN OPTION BUTTON ĐỂ CHỌN DUNG TÍCH (Shopee Style - Giữ nguyên nút rời) --}}
                        <div class="text-uppercase small tracking-widest text-muted mb-2">Chọn dung tích:</div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @forelse($product->variants as $key => $variant)
                                <button type="button" 
                                        class="btn btn-outline-dark rounded-0 px-3 py-2 variant-btn {{ $key == 0 && $variant->stock > 0 ? 'active btn-dark text-white' : '' }}"
                                        data-id="{{ $variant->id }}"
                                        data-price="{{ $variant->price }}"
                                        data-stock="{{ $variant->stock }}"
                                        {{-- KHÓA NÚT KHI HẾT HÀNG --}}
                                        {{ $variant->stock <= 0 ? 'disabled' : '' }}
                                        {{-- LÀM MỜ VÀ GẠCH CHỮ KHI HẾT HÀNG --}}
                                        style="{{ $variant->stock <= 0 ? 'opacity: 0.4; text-decoration: line-through; cursor: not-allowed;' : '' }}">
                                    {{ $variant->volume?->name ?? 'N/A' }}
                                </button>
                            @empty
                                <span class="text-danger small">Đang cập nhật dung tích...</span>
                            @endforelse
                        </div>

                        {{-- 3. NƠI HIỂN THỊ TỒN KHO TƯƠNG ỨNG VỚI NÚT ĐANG CHỌN --}}
                        <div class="text-uppercase small tracking-widest text-muted">
                            Trạng thái: 
                            <span id="display-stock-status" class="fw-bold ms-2 text-success">Còn hàng</span> 
                            (<span id="display-stock" class="text-dark fw-bold">{{ $product->variants->first()?->stock ?? 0 }}</span> chai có sẵn)
                        </div>
                    </div>

                    {{-- 4. FORM THÊM VÀO GIỎ HÀNG --}}
                    <form action="{{ route('carts.store') }}" method="POST" class="m-0">
                        @csrf
                        {{-- Quan trọng: Ô ẩn này chứa ID dung tích được chọn --}}
                        <input type="hidden" name="idVariant" id="hidden-variant-id" value="{{ $product->variants->first()?->id ?? '' }}">
                        <input type="hidden" name="quantity" value="1">

                        <button type="submit" id="btn-add-cart" class="btn btn-dark btn-lg rounded-0 px-5 text-uppercase fw-bold shadow-none w-100"
                            {{ $product->variants->where('stock', '>', 0)->count() == 0 ? 'disabled' : '' }}>
                            <i class="fa-solid fa-shopping-cart me-2"></i> THÊM VÀO GIỎ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-light py-5 border-top">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h4 class="fw-bold mb-4 border-bottom pb-2">Customer Feedback</h4>
                @forelse($product->comment as $c)
                <div class="bg-white p-3 mb-3 shadow-sm border-start border-dark border-4">
                    <p class="fw-bold mb-1 text-uppercase small">{{ $c->name }}</p>
                    <p class="text-muted mb-0 small">"{{ $c->chat }}"</p>
                </div>
                @empty
                <div class="p-4 bg-white shadow-sm border text-center text-muted">
                    <p class="mb-0 fst-italic">Chưa có đánh giá nào. Hãy là người đầu tiên nhận xét!</p>
                </div>
                @endforelse
            </div>

            <div class="col-lg-5 offset-lg-1">
                <div class="p-4 bg-white shadow-sm border">
                    <h4 class="fw-bold mb-4">Leave a Review</h4>
                    <form action="{{ route('comments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="idProduct" value="{{ $product->id }}">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Your Name</label>
                            <input type="text" class="form-control rounded-0 shadow-none" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Your Experience</label>
                            <textarea name="chat" class="form-control rounded-0 shadow-none" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 rounded-0 py-3 text-uppercase fw-bold">Submit Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
    <script src="{{ asset('js/single-product.js') }}"></script>
@endsection