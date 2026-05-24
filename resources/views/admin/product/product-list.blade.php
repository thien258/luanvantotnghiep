@extends('layout/admin')
@section('body')
<div class="card-footer small text-mutted">
    <h3>Quản lý Sản phẩm</h3>
    <a href="{{ route('admin.product.create') }}" class="btn btn-warning mb-3">Thêm Sản phẩm</a>
    <div class="position-relative" style="width: 350px;">
        <input type="text" id="admin-search-input" class="form-control shadow-none" placeholder="Lọc nhanh tên sản phẩm..." autocomplete="off">
        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="top: 50%; right: 15px; transform: translateY(-50%);"></i>
    </div>
</div>
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
        <tr>
            <!-- <th scope="col">#</th> -->
            <th scope="col">Title</th>
            <th scope="col">Description</th>
            <th scope="col">Category</th>
            <th scope="col">Brand</th>
            <th scope="col">Image</th>
            <th scope="col">Khoảng Giá</th>
            <th scope="col">Nồng độ</th>
            <th scope="col">Kho (Tổng)</th>
            <th scope="col">Các Dung tích</th>
            <th scope="col">Sự kiện</th>
            <th scope="col">Status</th>
            <th scope="col">Option</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $object)
        <tr>
            <!-- <th scope="row">{{ $object->id }}</th> -->
            <td class="fw-bold">{{$object->title}}</td>
            <td>{{$object->decription}}</td>
            <td>{{$object->category?->name ?? 'Trống'}}</td>
            <td>{{$object->brand?->name ?? 'Trống'}}</td>
            <td><img src="{{ $object->image }}" width="80" alt="" class="img-thumbnail"></td>

            {{-- Lấy giá thấp nhất và cao nhất của các dung tích --}}
            <td class="text-danger fw-bold text-nowrap">
                @if($object->variants->count() > 0)
                {{ number_format($object->variants->min('price')) }} đ - {{ number_format($object->variants->max('price')) }} đ
                @else
                Chưa có giá
                @endif
            </td>

            <td>{{ $object->concentration?->concentration ?? 'Trống' }}</td>

            {{-- Tổng kho của tất cả các dung tích (biến thể) --}}
            <td class="text-center fw-bold">{{ $object->variants->sum('stock') }}</td>

            {{-- Hiển thị các dung tích dạng Badge (Đã fix lỗi chữ tàng hình) --}}
            <td>
                @forelse($object->variants as $variant)
                <span class="badge text-white mb-1" style="background-color: #6c757d; font-size: 13px; padding: 5px 8px;">
                    {{ $variant->volume?->name ?? 'N/A' }}
                </span>
                @empty
                <span class="text-muted small">Trống</span>
                @endforelse
            </td>
            <td>
                <div class="d-flex flex-wrap gap-1">
                    @forelse($object->festivals as $festival)
                    @if($festival->status == 1)
                    {{-- Lễ hội đang bật (Active) thì hiện màu xanh --}}
                    <span class="badge bg-success text-white p-2 small" title="Đang diễn ra">
                        <i class="fa-solid fa-gift me-1"></i> {{ $festival->name }}
                        <strong class="ms-1">(-{{ $festival->discount }}%)</strong>
                    </span>
                    @else
                    {{-- Lễ hội đang tắt (Inactive) thì hiện màu xám mờ --}}
                    <span class="badge bg-secondary text-white p-2 small" title="Đã tắt hoặc hết hạn">
                        <i class="fa-solid fa-circle-minus me-1"></i> {{ $festival->name }}
                    </span>
                    @endif
                    @empty
                    {{-- Nếu sản phẩm không tham gia chương trình nào --}}
                    <span class="text-muted small"><em>Không áp dụng</em></span>
                    @endforelse
                </div>
            </td>
            <td class="text-center">
                @if($object->status==1)
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="green" class="bi bi-check-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                    <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05" />
                </svg>
                @else
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                    <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05" />
                </svg>
                @endif
            </td>

            <td class="text-center">
                <div class="dropdown">
                    {{-- Đã fix: Trả data-toggle về đúng chuẩn Bootstrap 4 của đồ án --}}
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle shadow-none" type="button" id="dropdownMenu{{ $object->id }}" data-toggle="dropdown" aria-expanded="false">
                        Tùy chọn
                    </button>

                    {{-- Đã fix: Thêm dropdown-menu-right để menu xổ xuống không bị rớt khung --}}
                    <ul class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="dropdownMenu{{ $object->id }}">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.product.edit',['product' =>$object->id]) }}">
                                <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Chỉnh sửa
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(confirm('Bạn có chắc chắn muốn xóa sản phẩm: {{ $object->title }}?')) { document.getElementById('product-delete-{{ $object->id }}').submit(); }">
                                <i class="far fa-trash-alt me-2"></i> Xóa
                            </a>
                            <form action="{{ route('admin.product.destroy', ['product' => $object->id]) }}" method="post" id="product-delete-{{ $object->id }}" class="d-none">
                                {{ csrf_field() }}
                                {{ method_field('delete') }}
                            </form>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="11" class="text-center py-4 text-muted">Không tìm thấy sản phẩm nào.</td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
@endsection
@section('script')

<script src="{{ asset('js/adminProduct_search.js') }}"></script>
@endsection