@extends('layout/admin')
@section('body')

<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h5 class="font-weight-bold text-dark mb-0">
        <i class="fa-solid fa-cart-flatbed mr-2 text-muted"></i>
        Chi tiết đơn đặt hàng: <span class="text-primary">{{ $purchaseOrder->order_code }}</span>
    </h5>
    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-0">
        <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-0">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
@endif

{{-- Thông tin đơn --}}
<div class="card shadow-none border rounded-0 mb-3">
    <div class="card-body py-3">
        <div class="row small">
            <div class="col-md-3">
                <span class="text-muted">Nhà sản xuất:</span><br>
                <strong>{{ $purchaseOrder->manufacturer->name ?? '—' }}</strong>
            </div>
            <div class="col-md-2">
                <span class="text-muted">Trạng thái:</span><br>
                @php
                    $badge = ['pending'=>'badge-warning','confirmed'=>'badge-primary','delivering'=>'badge-info','received'=>'badge-success','cancelled'=>'badge-danger'][$purchaseOrder->status] ?? 'badge-secondary';
                    $label = ['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','delivering'=>'Đang giao','received'=>'Đã nhận','cancelled'=>'Đã hủy'][$purchaseOrder->status] ?? $purchaseOrder->status;
                @endphp
                <span class="badge {{ $badge }} rounded-0 px-2 py-1 text-white">{{ $label }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Tổng tiền:</span><br>
                <strong class="text-success">{{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}₫</strong>
            </div>
            <div class="col-md-2">
                <span class="text-muted">Ngày đặt:</span><br>
                <strong>{{ $purchaseOrder->created_at->format('d/m/Y') }}</strong>
            </div>
            <div class="col-md-2">
                <span class="text-muted">Dự kiến nhận:</span><br>
                <strong>{{ $purchaseOrder->expected_date ? \Carbon\Carbon::parse($purchaseOrder->expected_date)->format('d/m/Y') : '—' }}</strong>
            </div>
        </div>
        @if($purchaseOrder->note)
        <div class="mt-2 small text-muted">Ghi chú: {{ $purchaseOrder->note }}</div>
        @endif
    </div>
</div>

{{-- Link báo giá gốc --}}
@if($purchaseOrder->offer)
<div class="alert alert-light border rounded-0 small mb-3">
    Từ báo giá:
    <a href="{{ route('admin.supplier-offers.show', $purchaseOrder->offer->id) }}" class="font-weight-bold">
        {{ $purchaseOrder->offer->offer_code }}
    </a>
</div>
@endif

{{-- Danh sách sản phẩm --}}
<div class="card shadow-none border rounded-0 mb-3">
    <div class="card-header bg-white py-2 border-bottom">
        <span class="small font-weight-bold text-uppercase text-muted">Chi tiết sản phẩm</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 small table-hover">
            <thead class="table-light">
                <tr>
                    <th class="pl-4 py-2">#</th>
                    <th class="py-2">Tên sản phẩm</th>
                    <th class="text-center py-2">Số lượng</th>
                    <th class="text-center py-2">Đơn giá</th>
                    <th class="text-center py-2">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $i => $item)
                <tr>
                    <td class="pl-4 py-2 text-muted">{{ $i + 1 }}</td>
                    <td class="py-2 font-weight-bold">{{ $item->product_name }}</td>
                    <td class="text-center py-2">{{ number_format($item->quantity) }}</td>
                    <td class="text-center py-2">{{ number_format($item->unit_price, 0, ',', '.') }}₫</td>
                    <td class="text-center py-2 text-success font-weight-bold">
                        {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}₫
                    </td>
                </tr>
                @endforeach
                <tr class="table-light">
                    <td colspan="4" class="text-right font-weight-bold small pr-3 py-2">Tổng cộng:</td>
                    <td class="text-center font-weight-bold text-success py-2">
                        {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}₫
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Nút xuất file — luôn hiện dù status nào --}}
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="{{ route('admin.purchase-orders.export-csv', $purchaseOrder->id) }}"
       class="btn btn-outline-dark rounded-0 px-4">
        <i class="fa-solid fa-file-csv mr-1"></i> Xuất CSV nhập kho
    </a>
    <a href="{{ route('admin.purchase-orders.export-excel', $purchaseOrder->id) }}"
       class="btn btn-success rounded-0 px-4">
        <i class="fa-solid fa-file-excel mr-1"></i> Xuất Đơn Mua Hàng (Excel)
    </a>
    <small class="text-muted align-self-center">File CSV dùng để nhập kho · File Excel dùng để gửi NSX</small>
</div>

{{-- Cập nhật trạng thái bằng nút bấm --}}
@if(!in_array($purchaseOrder->status, ['received', 'cancelled']))
<div class="d-flex gap-2 flex-wrap mb-3">

    @if($purchaseOrder->status === 'pending')
        {{-- Chờ xác nhận → Xác nhận --}}
        <form action="{{ route('admin.purchase-orders.status', $purchaseOrder->id) }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="confirmed">
            <button type="submit" class="btn btn-primary rounded-0 px-4">
                <i class="fa-solid fa-circle-check mr-1"></i> Xác nhận đơn hàng
            </button>
        </form>
        <form action="{{ route('admin.purchase-orders.status', $purchaseOrder->id) }}" method="POST"
              onsubmit="return confirm('Xác nhận hủy đơn hàng này?')">
            @csrf
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" class="btn btn-outline-danger rounded-0 px-4">
                <i class="fa-solid fa-xmark mr-1"></i> Hủy đơn
            </button>
        </form>
    @endif

    @if($purchaseOrder->status === 'confirmed')
        {{-- Đã xác nhận → Đang giao --}}
        <form action="{{ route('admin.purchase-orders.status', $purchaseOrder->id) }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="delivering">
            <button type="submit" class="btn btn-info rounded-0 px-4 text-white">
                <i class="fa-solid fa-truck mr-1"></i> Bắt đầu giao hàng
            </button>
        </form>
    @endif

    @if($purchaseOrder->status === 'delivering')
        {{-- Đang giao → Xuất CSV để nhập qua trang nhập kho --}}
        <form action="{{ route('admin.purchase-orders.receive', $purchaseOrder->id) }}" method="POST"
              onsubmit="return confirm('Xác nhận đã nhận hàng? Hệ thống sẽ tải file CSV để nhập kho.')">
            @csrf
            <button type="submit" class="btn btn-success rounded-0 px-4">
                <i class="fa-solid fa-file-csv mr-1"></i> Xác nhận nhận hàng &amp; Tải file CSV nhập kho
            </button>
        </form>
    @endif

</div>
@endif

@if($purchaseOrder->status === 'received')
<div class="alert alert-success rounded-0 small">
    <i class="fa-solid fa-check-circle mr-1"></i>
    Đơn hàng đã được nhận và tồn kho đã được cập nhật.
</div>
@endif

@if($purchaseOrder->status === 'cancelled')
<div class="alert alert-danger rounded-0 small">
    <i class="fa-solid fa-xmark-circle mr-1"></i>
    Đơn hàng đã bị hủy.
</div>
@endif

@endsection
