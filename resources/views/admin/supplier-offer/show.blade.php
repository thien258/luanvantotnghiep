@extends('layout/admin')
@section('body')

<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h5 class="font-weight-bold text-dark mb-0">
        <i class="fa-solid fa-file-invoice mr-2 text-muted"></i>
        Chi tiết báo giá: <span class="text-primary">{{ $offer->offer_code }}</span>
    </h5>
    <a href="{{ route('admin.supplier-offers.index') }}" class="btn btn-outline-secondary btn-sm rounded-0">
        <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-0">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
@endif

{{-- Thông tin báo giá --}}
<div class="card shadow-none border rounded-0 mb-3">
    <div class="card-body py-3">
        <div class="row small">
            <div class="col-md-3">
                <span class="text-muted">Nhà sản xuất:</span><br>
                <strong>{{ $offer->manufacturer->name ?? '—' }}</strong>
            </div>
            <div class="col-md-2">
                <span class="text-muted">Trạng thái:</span><br>
                @php
                    $badge = ['submitted'=>'badge-primary','accepted'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-secondary'][$offer->status] ?? 'badge-secondary';
                    $label = ['submitted'=>'Chờ duyệt','accepted'=>'Đã đặt hàng','rejected'=>'Từ chối','draft'=>'Nháp'][$offer->status] ?? $offer->status;
                @endphp
                <span class="badge {{ $badge }} rounded-0 px-2 py-1">{{ $label }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Ngày tạo:</span><br>
                <strong>{{ $offer->created_at->format('d/m/Y H:i') }}</strong>
            </div>
            <div class="col-md-4">
                <span class="text-muted">Ghi chú:</span><br>
                <span>{{ $offer->note ?: '—' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Nếu đã có PO thì hiện link --}}
@if($offer->purchaseOrder)
    <div class="alert alert-success rounded-0 small">
        Báo giá này đã được đặt hàng — Mã đơn:
        <a href="{{ route('admin.purchase-orders.show', $offer->purchaseOrder->id) }}" class="font-weight-bold">
            {{ $offer->purchaseOrder->order_code }}
        </a>
    </div>
@endif

@if($offer->status === 'submitted')
<form action="{{ route('admin.purchase-orders.store') }}" method="POST" id="orderForm">
@csrf
<input type="hidden" name="offer_id" value="{{ $offer->id }}">

<div class="card shadow-none border rounded-0 mb-3">
    <div class="card-header bg-white py-2 border-bottom">
        <span class="small font-weight-bold text-uppercase text-muted">Sản phẩm NSX chào giá</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 small table-hover">
            <thead class="table-light">
                <tr>
                    <th class="pl-3 py-2" style="width:4%">Chọn</th>
                    <th class="py-2" style="width:5%">Ảnh</th>
                    <th class="py-2">Tên sản phẩm</th>
                    <th class="text-center py-2">Giá chào</th>
                    <th class="text-center py-2">Dung tích</th>
                    <th class="py-2">Nồng độ</th>
                    <th class="py-2">Category</th>
                    <th class="py-2">Brand</th>
                    <th class="text-center py-2" style="width:12%">Số lượng đặt</th>
                </tr>
            </thead>
            <tbody>
                @foreach($offer->items as $item)
                @php $p = $item->product; @endphp
                <tr>
                    <td class="pl-3 py-2">
                        <input type="checkbox" class="item-check" data-index="{{ $loop->index }}">
                        <input type="hidden" name="items[{{ $loop->index }}][offer_item_id]"
                               value="{{ $item->id }}" class="field-offer-item-id" disabled>
                        <input type="hidden" name="items[{{ $loop->index }}][product_id]"
                               value="{{ $item->product_id }}" class="field-product-id" disabled>
                        <input type="hidden" name="items[{{ $loop->index }}][product_name]"
                               value="{{ $item->product_name }}" class="field-product-name" disabled>
                        <input type="hidden" name="items[{{ $loop->index }}][unit_price]"
                               value="{{ $item->unit_price }}" class="field-unit-price" disabled>
                    </td>
                    <td class="py-2 text-center">
                        @if($p?->image)
                            <img src="{{ $p->image }}" style="width:36px;height:36px;object-fit:cover;" class="border rounded">
                        @else
                            <span class="text-muted" style="font-size:0.7rem;">—</span>
                        @endif
                    </td>
                    <td class="py-2 font-weight-bold">{{ $item->product_name }}</td>
                    <td class="text-center py-2 text-success font-weight-bold">
                        {{ number_format($item->unit_price, 0, ',', '.') }}₫
                    </td>
                    <td class="text-center py-2 text-muted">{{ $p?->volume ?? '—' }}</td>
                    <td class="py-2 text-muted">{{ $p?->concentration?->concentration ?? '—' }}</td>
                    <td class="py-2 text-muted">{{ $p?->category?->name ?? '—' }}</td>
                    <td class="py-2 text-muted">{{ $p?->brand?->name ?? '—' }}</td>
                    <td class="text-center py-2">
                        <input type="number" name="items[{{ $loop->index }}][quantity]"
                               class="form-control form-control-sm rounded-0 text-center qty-input"
                               min="1" value="1" style="width:80px; margin:0 auto;" disabled>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>{{-- placeholder bên trái --}}</div>
    <button type="submit" class="btn btn-dark rounded-0 px-4" id="submitOrderBtn">
        <i class="fa-solid fa-cart-flatbed mr-1"></i> Đặt hàng các SP đã chọn
    </button>
</div>

</form>

{{-- Nút từ chối đặt NGOÀI form đặt hàng --}}
<form action="{{ route('admin.supplier-offers.reject', $offer->id) }}" method="POST" class="mb-4"
      onsubmit="return confirm('Xác nhận từ chối báo giá này?')">
    @csrf
    <button type="submit" class="btn btn-outline-danger rounded-0 btn-sm">
        <i class="fa-solid fa-xmark mr-1"></i> Từ chối báo giá
    </button>
</form>

@else

{{-- Hiển thị bảng chỉ đọc khi không còn status submitted --}}
<div class="card shadow-none border rounded-0 mb-3">
    <div class="card-header bg-white py-2 border-bottom">
        <span class="small font-weight-bold text-uppercase text-muted">Sản phẩm NSX chào giá</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 small table-hover">
            <thead class="table-light">
                <tr>
                    <th class="py-2" style="width:5%">Ảnh</th>
                    <th class="py-2">Tên sản phẩm</th>
                    <th class="text-center py-2">Giá chào</th>
                    <th class="text-center py-2">Dung tích</th>
                    <th class="py-2">Nồng độ</th>
                    <th class="py-2">Category</th>
                    <th class="py-2">Brand</th>
                </tr>
            </thead>
            <tbody>
                @foreach($offer->items as $item)
                @php $p = $item->product; @endphp
                <tr>
                    <td class="py-2 text-center">
                        @if($p?->image)
                            <img src="{{ $p->image }}" style="width:36px;height:36px;object-fit:cover;" class="border rounded">
                        @else
                            <span class="text-muted" style="font-size:0.7rem;">—</span>
                        @endif
                    </td>
                    <td class="py-2 font-weight-bold">{{ $item->product_name }}</td>
                    <td class="text-center py-2 text-success font-weight-bold">
                        {{ number_format($item->unit_price, 0, ',', '.') }}₫
                    </td>
                    <td class="text-center py-2 text-muted">{{ $p?->volume ?? '—' }}</td>
                    <td class="py-2 text-muted">{{ $p?->concentration?->concentration ?? '—' }}</td>
                    <td class="py-2 text-muted">{{ $p?->category?->name ?? '—' }}</td>
                    <td class="py-2 text-muted">{{ $p?->brand?->name ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endif

@endsection

@section('script')
<script>
    // Khi tick checkbox → enable tất cả hidden fields + qty của dòng đó
    document.querySelectorAll('.item-check').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const row = this.closest('tr');
            const fields = row.querySelectorAll('.field-offer-item-id, .field-product-id, .field-product-name, .field-unit-price, .qty-input');
            fields.forEach(f => f.disabled = !this.checked);
        });
    });

    // Validate trước khi submit
    const form = document.getElementById('orderForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const checked = document.querySelectorAll('.item-check:checked').length;
            if (checked === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất 1 sản phẩm để đặt hàng.');
            }
        });
    }
</script>
@endsection
