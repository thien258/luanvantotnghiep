@extends('layout/admin')
@section('body')
<div class="card-footer small text-muted">
    <h3>Quản lý Sản phẩm</h3>

    <div class="mb-3 d-flex align-items-center">
        <a href="{{ route('admin.product.create') }}" class="btn btn-warning">
            <i class="fas fa-plus"></i> Thêm Sản phẩm
        </a>
    </div>

    <div class="position-relative mb-3" style="width: 350px;">
        <input type="text" id="admin-search-input" class="form-control shadow-none" placeholder="Lọc nhanh tên sản phẩm..." autocomplete="off">
        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="top: 50%; right: 15px; transform: translateY(-50%);"></i>
    </div>

    @if(session('success'))
    <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif
</div>
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th scope="col">Title</th>
            <th scope="col">Description</th>
            <th scope="col">Category</th>
            <th scope="col">Brand</th>
            <th scope="col">Image</th>
            <th scope="col">Giá bán</th>
            <th scope="col">Dung tích</th>
            <th scope="col">Nồng độ</th>
            <th scope="col">Kho hàng</th>
            <th scope="col">Sự kiện</th>
            <th scope="col">Status</th>
            <th scope="col">Option</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $object)
        <tr>
            <td class="fw-bold">{{$object->title}}</td>
            <td>{{$object->decription}}</td>
            <td>{{$object->category?->name ?? 'Trống'}}</td>
            <td>{{$object->brand?->name ?? 'Trống'}}</td>
            <td><img src="{{ $object->image }}" width="80" alt="" class="img-thumbnail"></td>

            {{-- Hiển thị giá tiền trực tiếp từ bảng products --}}
            <td class="text-danger fw-bold text-nowrap">
                {{ $object->price > 0 ? number_format($object->price) . ' đ' : 'Chưa có giá' }}
            </td>

            {{-- Hiển thị dung tích cố định dạng Badge --}}
            <td>
                @if($object->volume)
                <span class="badge text-white mb-1" style="background-color: #6c757d; font-size: 13px; padding: 5px 8px;">
                    {{ $object->volume }}
                </span>
                @else
                <span class="text-muted small">Trống</span>
                @endif
            </td>

            <td>{{ $object->concentration?->concentration ?? 'Trống' }}</td>

            {{-- Hiển thị số lượng kho — đỏ nếu < 5, vàng nếu < 10, bình thường nếu >= 10 --}}
            <td class="text-center fw-bold
                @if($object->quantity < 5) text-danger
                @elseif($object->quantity < 10) text-warning
                @else text-dark @endif">
                {{ $object->quantity }}
            </td>

            <td>
                <div class="d-flex flex-wrap gap-1">
                    @forelse($object->festivals as $festival)
                    @if($festival->status == 1)
                    <span class="badge bg-success text-white p-2 small" title="Đang diễn ra">
                        <i class="fa-solid fa-gift me-1"></i> {{ $festival->name }}
                        <strong class="ms-1">(-{{ $festival->discount }}%)</strong>
                    </span>
                    @else
                    <span class="badge bg-secondary text-white p-2 small" title="Đã tắt hoặc hết hạn">
                        <i class="fa-solid fa-circle-minus me-1"></i> {{ $festival->name }}
                    </span>
                    @endif
                    @empty
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
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle shadow-none" type="button" id="dropdownMenu{{ $object->id }}" data-toggle="dropdown" aria-expanded="false">
                        Tùy chọn
                    </button>
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
            <td colspan="12" class="text-center py-4 text-muted">Không tìm thấy sản phẩm nào.</td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
@endsection
@section('script')
<script src="{{ asset('js/admin/adminProduct_search.js') }}"></script>
@endsection