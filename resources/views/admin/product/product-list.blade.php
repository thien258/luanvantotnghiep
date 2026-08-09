@extends('layout/admin')
@section('body')
<div class="card-footer small text-muted">
    <h3>Quản lý Sản phẩm</h3>

    <div class="mb-3 d-flex align-items-center gap-2">
        <a href="{{ route('admin.product.create') }}" class="btn btn-warning">
            <i class="fas fa-plus"></i> Thêm Sản phẩm
        </a>
        <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#lowStockModal">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i> Đăng yêu cầu nhập hàng
        </button>
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
            <th scope="col">Tên</th>
            <th scope="col">Mô tả</th>
            <th scope="col">Danh mục</th>
            <th scope="col">Thương hiệu</th>
            <th scope="col">Hình ảnh</th>
            <th scope="col">Giá bán</th>
            <th scope="col">Dung tích</th>
            <th scope="col">Nồng độ</th>
            <th scope="col">Kho hàng</th>
            <th scope="col">Sự kiện</th>
            <th scope="col">Trạng thái</th>
            <th scope="col">Lựa chọn</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $object)
        @php
            $expiry   = $expiryMap[$object->id] ?? null;
            $daysLeft = $expiry ? $expiry['days_left'] : null;
        @endphp
        <tr class="@if($object->quantity < 5) table-danger @elseif($object->quantity < 10) table-warning @endif @if($daysLeft !== null && $daysLeft <= 30) border-left-danger @elseif($daysLeft !== null && $daysLeft <= 90) border-left-warning @endif">
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
                @if($object->quantity < 5) bg-danger text-white
                @elseif($object->quantity < 10) bg-warning
                @else text-dark @endif">
                {{ $object->quantity }}
                @if($expiry)
                <br><small class="fw-normal @if($daysLeft <= 30) text-danger @elseif($daysLeft <= 90) text-warning @else text-muted @endif"
                    style="font-size:0.65rem;">
                    ⏰ {{ \Carbon\Carbon::parse($expiry['date'])->format('d/m/Y') }}
                </small>
                @endif
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
<div class="d-flex justify-content-center mt-3">
    {{ $products->links() }}
</div>
</div>
@endsection

{{-- ── MODAL YÊU CẦU NHẬP HÀNG ──────────────────────────────── --}}
<div class="modal fade" id="lowStockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content rounded-0">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title font-weight-bold text-uppercase small" style="letter-spacing:1px;">
                    <i class="fa-solid fa-triangle-exclamation text-danger mr-2"></i>
                    SP hết / sắp hết hàng — Đăng yêu cầu để NSX chào giá
                </h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <form action="{{ route('admin.procurement.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    {{-- Thông tin yêu cầu --}}
                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-2">
                            <label class="small font-weight-bold">Hạn chót NSX chào giá</label>
                            <input type="date" name="deadline"
                                   value="{{ now()->addDays(7)->format('Y-m-d') }}"
                                   class="form-control form-control-sm rounded-0">
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <label class="small font-weight-bold">Ghi chú</label>
                            <input type="text" name="note" class="form-control form-control-sm rounded-0"
                                   placeholder="Ghi chú thêm về yêu cầu...">
                        </div>
                    </div>

                    {{-- Tất cả SP — admin chọn cái nào muốn nhập thêm --}}
            
                    @php
                        $lowStockItems = $allProducts;
                    @endphp

                    @if($lowStockItems->isEmpty())
                        <div class="text-center text-muted py-3">Chưa có sản phẩm nào.</div>
                    @else
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">{{ $lowStockItems->count() }} sản phẩm</span>
                            <label class="small text-muted mb-0">
                                <input type="checkbox" id="selectAllLow" class="mr-1">Chọn tất cả
                            </label>
                        </div>
                        <div style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-sm small table-hover mb-0 border">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="pl-3 py-2" style="width:5%">✓</th>
                                    <th style="width:7%">Ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th class="text-center" style="width:10%">Tồn kho</th>
                                    <th class="text-center" style="width:13%">HSD gần nhất</th>
                                    <th class="text-center" style="width:14%">Cần nhập</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockItems as $p)
                                @php
                                    $pExpiry    = $expiryMap[$p->id] ?? null;
                                    $pDaysLeft  = $pExpiry ? $pExpiry['days_left'] : null;
                                    $speedInfo  = $saleSpeedMap[$p->id] ?? null;
                                    $speedStatus = $speedInfo['status'] ?? null;
                                    $isSlow     = $speedStatus === 'slow';
                                    $isFast     = $speedStatus === 'fast';
                                    $isWatching = $speedStatus === 'watching';
                                    $isExpiring = $pDaysLeft !== null && $pDaysLeft <= 90;
                                    $isDoubleWarning = $isSlow && $isExpiring;
                                @endphp
                                <tr class="@if($isDoubleWarning) table-danger @elseif($p->quantity < 5) table-danger @elseif($p->quantity < 10) table-warning @endif">
                                    <td class="pl-3 py-2">
                                        <input type="checkbox" name="product_ids[]"
                                               value="{{ $p->id }}" class="low-stock-check"
                                               @if($isDoubleWarning) title="⚠️ SP này bán chậm + sắp hết hạn — cân nhắc trước khi đặt thêm" @endif>
                                    </td>
                                    <td class="py-2">
                                        @if($p->image)
                                            <img src="{{ $p->image }}" style="width:32px;height:32px;object-fit:cover;" class="border rounded">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2 font-weight-bold">
                                        {{ $p->title }}
                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                            @if($p->quantity == 0)
                                                <span class="badge badge-danger rounded-0 text-white" style="font-size:0.6rem;">HẾT HÀNG</span>
                                            @elseif($p->quantity < 5)
                                                <span class="badge badge-warning rounded-0 text-white" style="font-size:0.6rem;">SẮP HẾT</span>
                                            @endif
                                            @if($isSlow)
                                                <span class="badge rounded-0 text-dark" style="font-size:0.6rem; background:#ffc107;">🐢 Bán chậm ({{ $speedInfo['ratio'] ?? 0 }}%)</span>
                                            @elseif($isFast)
                                                <span class="badge rounded-0 text-white" style="font-size:0.6rem; background:#28a745;">🔥 Bán nhanh ({{ $speedInfo['ratio'] ?? 0 }}%)</span>
                                            @elseif($isWatching)
                                                <span class="badge rounded-0 text-dark" style="font-size:0.6rem; background:#e2e3e5;">⏳ Đang theo dõi (còn {{ $speedInfo['days_left'] }} ngày)</span>
                                            @endif
                                            @if($isExpiring)
                                                <span class="badge rounded-0 text-white" style="font-size:0.6rem; background:#dc3545;">⏰ HSD còn {{ $pDaysLeft }} ngày</span>
                                            @endif
                                            @if($isDoubleWarning)
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center py-2 font-weight-bold
                                        {{ $p->quantity == 0 ? 'text-danger' : ($p->quantity < 5 ? 'text-warning' : 'text-dark') }}">
                                        {{ $p->quantity }}
                                    </td>
                                    <td class="text-center py-2" style="font-size:0.8rem;">
                                        @if($pExpiry)
                                            <span class="fw-bold {{ $pDaysLeft <= 30 ? 'text-danger' : ($pDaysLeft <= 90 ? 'text-warning' : 'text-muted') }}">
                                                {{ \Carbon\Carbon::parse($pExpiry['date'])->format('d/m/Y') }}
                                            </span>
                                            <br><small class="text-muted">còn {{ $pDaysLeft }} ngày</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        <input type="number" name="qty_suggest[{{ $p->id }}]"
                                               value="{{ $isDoubleWarning ? 0 : 10 }}" min="0"
                                               class="form-control form-control-sm rounded-0 text-center {{ $isDoubleWarning ? 'bg-light text-muted' : '' }}">
                                        @if($isDoubleWarning)
                                            <small class="text-danger d-block text-center mt-1" style="font-size:0.6rem;">Xem lại!</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    @endif
                </div>

                @if(true)
                <div class="modal-footer border-top py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-0" data-dismiss="modal">
                        Đóng
                    </button>
                    <button type="submit" class="btn btn-dark btn-sm rounded-0">
                        <i class="fa-solid fa-bullhorn mr-1"></i> Đăng yêu cầu cho NSX xem
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

@section('script')
<script src="{{ asset('js/admin/adminProduct_search.js') }}"></script>
<script src="{{ asset('js/admin/product-list.js') }}"></script>
@endsection