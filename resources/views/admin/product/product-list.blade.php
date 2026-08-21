@extends('layout/admin')
@section('body')

<style>
/* ── Product List Styles ──────────────────────────── */
.product-list-header {
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    padding: 1.25rem 1.5rem 1rem;
    margin-bottom: 0;
}
.product-list-header h3 {
    font-size: 1.35rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 0.75rem;
}
.product-table-wrap {
    padding: 0 1.5rem 1.5rem;
    background: #fff;
}
/* Description cell — fixed width, 3 lines clamp */
.desc-cell {
    min-width: 220px;
    max-width: 300px;
}
.desc-text {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-size: 0.82rem;
    color: #495057;
    line-height: 1.5;
    cursor: default;
}
/* Product name cell */
.product-name-cell {
    min-width: 160px;
    max-width: 200px;
}
.product-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: #212529;
    word-break: break-word;
}
/* Image */
.product-thumb {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}
/* Table tweaks */
#product-table thead th {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
    vertical-align: middle;
    padding: 10px 12px;
}
#product-table tbody td {
    vertical-align: middle;
    padding: 10px 12px;
    font-size: 0.875rem;
}
/* Status icons */
.status-badge-on  { color: #198754; font-size: 1.1rem; }
.status-badge-off { color: #adb5bd; font-size: 1.1rem; }
/* Stock cell */
.stock-cell { min-width: 80px; }
/* Expiry sub-text */
.expiry-text { font-size: 0.65rem; display: block; margin-top: 2px; }
/* Festival badges wrap nicely */
.festival-cell { min-width: 160px; max-width: 220px; }
</style>

<div class="product-list-header">
    <h3><i class="fa-solid fa-spray-can-sparkles me-2 text-warning"></i>Quản lý Sản phẩm</h3>

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.product.create') }}" class="btn btn-warning btn-sm px-3">
                <i class="fas fa-plus me-1"></i> Thêm sản phẩm
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm px-3" data-toggle="modal" data-target="#lowStockModal">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Đăng yêu cầu nhập hàng
            </button>
        </div>

        <div class="position-relative" style="width: 300px;">
            <input type="text" id="admin-search-input" class="form-control form-control-sm shadow-none pe-4"
                   placeholder="Lọc nhanh tên sản phẩm..." autocomplete="off">
            <i class="fa-solid fa-magnifying-glass position-absolute text-muted"
               style="top:50%; right:10px; transform:translateY(-50%); pointer-events:none;"></i>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3 mb-0 py-2" role="alert">
        <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3 mb-0 py-2" role="alert">
        <i class="fa-solid fa-circle-xmark me-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif
</div>

<div class="product-table-wrap">
<div class="table-responsive">
<table class="table table-bordered table-hover align-middle mb-0" id="product-table">
    <thead>
        <tr>
            <th>#</th>
            <th class="text-center">Hình ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Mô tả</th>
            <th>Danh mục</th>
            <th>Thương hiệu</th>
            <th>Giá bán</th>
            <th>Dung tích</th>
            <th>Nồng độ</th>
            <th class="text-center">Kho</th>
            <th>Sự kiện</th>
            <th class="text-center">Trạng thái</th>
            <th class="text-center">Thao tác</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $index => $object)
        @php
            $expiry   = $expiryMap[$object->id] ?? null;
            $daysLeft = $expiry ? $expiry['days_left'] : null;
        @endphp
        <tr class="
            @if($object->quantity < 5) table-danger
            @elseif($object->quantity < 10) table-warning
            @endif
            @if($daysLeft !== null && $daysLeft <= 30) border-left-danger
            @elseif($daysLeft !== null && $daysLeft <= 90) border-left-warning
            @endif
        ">
            {{-- # --}}
            <td class="text-muted text-center" style="width:40px; font-size:0.78rem;">
                {{ $products->firstItem() + $index }}
            </td>

            {{-- Hình ảnh --}}
            <td class="text-center" style="width:80px;">
                <img src="{{ $object->image }}" alt="{{ $object->title }}" class="product-thumb">
            </td>

            {{-- Tên sản phẩm --}}
            <td class="product-name-cell">
                <span class="product-name">{{ $object->title }}</span>
            </td>

            {{-- Mô tả — truncate 3 dòng, tooltip full text --}}
            <td class="desc-cell">
                @if($object->decription)
                <span class="desc-text"
                      data-toggle="tooltip"
                      data-placement="top"
                      title="{{ $object->decription }}">
                    {{ $object->decription }}
                </span>
                @else
                <span class="text-muted small fst-italic">Chưa có mô tả</span>
                @endif
            </td>

            {{-- Danh mục --}}
            <td class="small text-center">{{ $object->category?->name ?? '—' }}</td>

            {{-- Thương hiệu --}}
            <td class="text-center">
                <span class="fw-semibold text-secondary small">
                    {{ $object->brand?->name ?? '—' }}
                </span>
            </td>

            {{-- Giá bán --}}
            <td class="text-nowrap">
                @if($object->price > 0)
                <span class="fw-bold" style="color:#dc3545;">
                    {{ number_format($object->price) }}&nbsp;đ
                </span>
                @else
                <span class="text-muted small fst-italic">Chưa có giá</span>
                @endif
            </td>

            {{-- Dung tích --}}
            <td>
                @if($object->volume)
                <span class="badge text-white" style="background:#6c757d; font-size:0.8rem; padding:4px 9px;">
                    {{ $object->volume }}
                </span>
                @else
                <span class="text-muted small">—</span>
                @endif
            </td>

            {{-- Nồng độ --}}
            <td class="text-nowrap small">
                {{ $object->concentration?->concentration ?? '—' }}
            </td>

            {{-- Kho hàng --}}
            <td class="text-center stock-cell">
                <span class="fw-bold d-block
                    @if($object->quantity < 5) text-danger
                    @elseif($object->quantity < 10) text-warning
                    @else text-dark @endif"
                    style="font-size:1rem;">
                    {{ $object->quantity }}
                </span>
                @if($expiry)
                <small class="expiry-text
                    @if($daysLeft <= 30) text-danger
                    @elseif($daysLeft <= 90) text-warning
                    @else text-muted @endif">
                    ⏰ {{ \Carbon\Carbon::parse($expiry['date'])->format('d/m/Y') }}
                </small>
                @endif
            </td>

            {{-- Sự kiện --}}
            <td class="festival-cell">
                <div class="d-flex flex-wrap gap-1">
                    @forelse($object->festivals as $festival)
                        @if($festival->status == 1)
                        <span class="badge bg-success text-white p-1" style="font-size:0.72rem;" title="Đang diễn ra">
                            <i class="fa-solid fa-gift me-1"></i>{{ $festival->name }}
                            <strong>(-{{ $festival->discount }}%)</strong>
                        </span>
                        @else
                        <span class="badge bg-secondary text-white p-1" style="font-size:0.72rem;" title="Đã tắt">
                            <i class="fa-solid fa-circle-minus me-1"></i>{{ $festival->name }}
                        </span>
                        @endif
                    @empty
                        <span class="text-muted small fst-italic">Không áp dụng</span>
                    @endforelse
                </div>
            </td>

            {{-- Trạng thái --}}
            <td class="text-center">
                @if($object->status == 1)
                    <i class="fa-solid fa-circle-check status-badge-on" title="Đang bán"></i>
                @else
                    <i class="fa-solid fa-circle-xmark status-badge-off" title="Ngừng bán"></i>
                @endif
            </td>

            {{-- Thao tác --}}
            <td class="text-center">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle shadow-none"
                            type="button"
                            id="dropdownMenu{{ $object->id }}"
                            data-toggle="dropdown"
                            aria-expanded="false">
                        Tùy chọn
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="dropdownMenu{{ $object->id }}">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.product.edit',['product' => $object->id]) }}">
                                <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Chỉnh sửa
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="#"
                               onclick="event.preventDefault(); if(confirm('Bạn có chắc chắn muốn xóa sản phẩm: {{ addslashes($object->title) }}?')) { document.getElementById('product-delete-{{ $object->id }}').submit(); }">
                                <i class="far fa-trash-alt me-2"></i> Xóa
                            </a>
                            <form action="{{ route('admin.product.destroy', ['product' => $object->id]) }}"
                                  method="post" id="product-delete-{{ $object->id }}" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="13" class="text-center py-5 text-muted">
                <i class="fa-solid fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                Không tìm thấy sản phẩm nào.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>

<div class="d-flex justify-content-center mt-4">
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
                                   min="{{ now()->format('Y-m-d') }}"
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
<script>
    // Init Bootstrap tooltips for description cells
    $(function () {
        $('[data-toggle="tooltip"]').tooltip({ html: false, trigger: 'hover' });
    });
</script>
@endsection