@extends('layout/admin')
@section('body')

<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h5 class="font-weight-bold text-dark mb-0">
        <i class="fa-solid fa-bullhorn mr-2 text-muted"></i>
        Yêu cầu: <span class="text-primary">{{ $procRequest->request_code }}</span>
    </h5>
    <a href="{{ route('admin.procurement.index') }}" class="btn btn-outline-secondary btn-sm rounded-0">
        <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-0">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
@endif

{{-- Thông tin yêu cầu --}}
<div class="card shadow-none border rounded-0 mb-3">
    <div class="card-body py-3">
        <div class="row small">
            <div class="col-md-2">
                <span class="text-muted">Trạng thái:</span><br>
                @if($procRequest->status === 'open')
                    <span class="badge badge-success rounded-0 px-2 py-1 text-white">Đang mở — NSX có thể chào giá</span>
                @else
                    <span class="badge badge-secondary rounded-0 px-2 py-1 text-white">Đã đóng</span>
                @endif
            </div>
            <div class="col-md-2">
                <span class="text-muted">Hạn chót:</span><br>
                <strong>{{ $procRequest->deadline ? \Carbon\Carbon::parse($procRequest->deadline)->format('d/m/Y') : '—' }}</strong>
            </div>
            <div class="col-md-2">
                <span class="text-muted">Ngày tạo:</span><br>
                <strong>{{ $procRequest->created_at->format('d/m/Y H:i') }}</strong>
            </div>
            <div class="col-md-2">
                <span class="text-muted">Người tạo:</span><br>
                <strong>{{ $procRequest->creator?->name ?? '—' }}</strong>
            </div>
            <div class="col-md-4">
                <span class="text-muted">Ghi chú:</span><br>
                <span>{{ $procRequest->note ?: '—' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Nút đóng yêu cầu: chỉ admin --}}
@if($procRequest->status === 'open' && auth()->user()->isAdmin())
<form action="{{ route('admin.procurement.close', $procRequest->id) }}" method="POST" class="mb-3"
      onsubmit="return confirm('Đóng yêu cầu này? NSX sẽ không upload báo giá được nữa.')">
    @csrf
    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-0">
        <i class="fa-solid fa-lock mr-1"></i> Đóng yêu cầu
    </button>
</form>
@endif

{{-- Form upload file báo giá: chỉ manufacturer --}}
@if($procRequest->status === 'open' && auth()->user()->role === 'manufacturer')
<div class="card shadow-none border rounded-0 mb-4">
    <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
        <span class="small font-weight-bold text-uppercase text-muted">
            <i class="fa-solid fa-file-arrow-up mr-1"></i> Upload file báo giá từ NSX
        </span>
        <a href="{{ route('admin.procurement.export-template', $procRequest->id) }}"
           class="btn btn-sm btn-outline-primary rounded-0 px-2 py-1" style="font-size:0.75rem;">
            <i class="fa-solid fa-download mr-1"></i> Tải file mẫu Excel
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.procurement.upload-offer', $procRequest->id) }}" method="POST"
              enctype="multipart/form-data">
            @csrf
            <div class="form-row align-items-end">
                <div class="form-group col-md-4 mb-2">
                    <label class="small font-weight-bold">Nhà sản xuất <span class="text-danger">*</span></label>
                    @php $myManufacturer = auth()->user()->manufacturer; @endphp
                    <input type="hidden" name="manufacturer_id" value="{{ $myManufacturer?->id }}">
                    <input type="text" class="form-control form-control-sm rounded-0 bg-light"
                           value="{{ $myManufacturer?->name ?? '—' }}" readonly>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="small font-weight-bold">
                        File Excel / CSV <span class="text-danger">*</span>
                        <span class="text-muted font-weight-normal">(cột: title | price | note)</span>
                    </label>
                    <input type="file" name="file" class="form-control form-control-sm rounded-0"
                           accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="form-group col-md-3 mb-2">
                    <label class="small font-weight-bold">Ghi chú</label>
                    <input type="text" name="note" class="form-control form-control-sm rounded-0"
                           placeholder="VD: Giao trong 5 ngày...">
                </div>
                <div class="form-group col-md-1 mb-2">
                    <button type="submit" class="btn btn-dark btn-sm rounded-0 w-100">
                        <i class="fa-solid fa-upload"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Danh sách SP cần nhập --}}
<div class="card shadow-none border rounded-0 mb-4">
    <div class="card-header bg-white py-2 border-bottom">
        <span class="small font-weight-bold text-uppercase text-muted">
            <i class="fa-solid fa-list mr-1"></i> Danh sách sản phẩm cần nhập
        </span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 small table-hover">
            <thead class="table-light">
                <tr>
                    <th class="pl-4 py-2" style="width:6%">Ảnh</th>
                    <th class="py-2">Tên sản phẩm</th>
                    <th class="py-2">Brand</th>
                    <th class="py-2">Danh mục</th>
                    <th class="py-2">Nồng độ</th>
                    <th class="text-center py-2">Tốc độ bán</th>
                    <th class="text-center py-2">Cần nhập</th>
                    <th class="py-2">Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                @foreach($procRequest->items as $item)
                <tr>
                    <td class="pl-4 py-2">
                        @if($item->product?->image)
                            <img src="{{ $item->product->image }}" style="width:36px;height:36px;object-fit:cover;" class="border rounded">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="py-2 font-weight-bold">{{ $item->product_name }}</td>
                    <td class="py-2 text-muted">{{ $item->product?->brand?->name ?? '—' }}</td>
                    <td class="py-2 text-muted">{{ $item->product?->category?->name ?? '—' }}</td>
                    <td class="py-2 text-muted">{{ $item->product?->concentration?->concentration ?? '—' }}</td>
                    <td class="text-center py-2">
                        @php $sale = $saleStatusMap[$item->product_id] ?? null; @endphp
                        @if(($sale['status'] ?? '') === 'fast')
                            <span class="badge badge-danger rounded-0 px-2 py-1" title="Tỷ lệ bán: {{ $sale['ratio'] }}%">🔥 Bán nhanh</span>
                        @elseif(($sale['status'] ?? '') === 'slow')
                            <span class="badge badge-warning rounded-0 px-2 py-1 text-dark" title="Tỷ lệ bán: {{ $sale['ratio'] }}%">🐢 Bán chậm</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center py-2 font-weight-bold text-primary">{{ $item->qty_needed }}</td>

                    <td class="py-2 text-muted small">{{ $item->note ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Danh sách báo giá NSX đã gửi --}}
<div class="card shadow-none border rounded-0 mb-3">
    <div class="card-header bg-white py-2 border-bottom">
        <span class="small font-weight-bold text-uppercase text-muted">
            <i class="fa-solid fa-file-invoice mr-1"></i>
            Báo giá nhận được từ NSX
            <span class="badge badge-primary rounded-0 ml-1">{{ $procRequest->offers->count() }}</span>
        </span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0 small">
            <thead class="table-light">
                <tr>
                    <th class="pl-4 py-2">Nhà sản xuất</th>
                    <th class="py-2">Mã báo giá</th>
                    <th class="text-center py-2">Số SP chào</th>
                    <th class="text-center py-2">Trạng thái</th>
                    <th class="text-center py-2">Ngày gửi</th>
                    <th class="text-center py-2">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($procRequest->offers as $offer)
                <tr>
                    <td class="pl-4 py-2 font-weight-bold">{{ $offer->manufacturer?->name ?? '—' }}</td>
                    <td class="py-2 text-muted">{{ $offer->offer_code }}</td>
                    <td class="text-center py-2">{{ $offer->items->count() }} SP</td>
                    <td class="text-center py-2">
                        <span class="badge
                            {{ $offer->status === 'submitted' ? 'badge-primary' : ($offer->status === 'accepted' ? 'badge-success' : 'badge-secondary') }}
                            rounded-0 text-white" style="font-size:0.65rem;">
                            {{ ['submitted'=>'Chờ duyệt','accepted'=>'Đã đặt hàng','rejected'=>'Từ chối','draft'=>'Nháp'][$offer->status] ?? $offer->status }}
                        </span>
                    </td>
                    <td class="text-center py-2 text-muted">{{ $offer->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center py-2">
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.supplier-offers.show', $offer->id) }}"
                           class="btn btn-outline-dark btn-sm rounded-0 px-2 py-1" style="font-size:0.75rem;">
                            <i class="fa-solid fa-eye mr-1"></i> Xem & Đặt hàng
                        </a>
                        @else
                        <a href="{{ route('admin.supplier-offers.show', $offer->id) }}"
                           class="btn btn-outline-secondary btn-sm rounded-0 px-2 py-1" style="font-size:0.75rem;">
                            <i class="fa-solid fa-eye mr-1"></i> Xem
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4 small">
                        <i class="fa-solid fa-clock mr-1"></i>
                        Chưa có NSX nào gửi báo giá. Upload file ở trên để thêm.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
