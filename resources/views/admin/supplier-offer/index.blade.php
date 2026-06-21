@extends('layout/admin')
@section('body')

<div class="mt-4 mb-3">
    <h5 class="font-weight-bold text-dark mb-0">
        <i class="fa-solid fa-file-invoice mr-2 text-muted"></i>Báo giá từ nhà sản xuất
    </h5>
    <small class="text-muted">NSX gửi file Excel → Admin xem + chọn sản phẩm → Đặt hàng</small>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-0">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger rounded-0 small">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

{{-- ── FORM UPLOAD FILE ──────────────────────────────────────── --}}
<div class="card shadow-none border rounded-0 mb-4">
    <div class="card-header bg-white py-2 border-bottom">
        <span class="small font-weight-bold text-uppercase text-muted">
            <i class="fa-solid fa-file-arrow-up mr-1"></i> Upload file báo giá từ NSX
        </span>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.supplier-offers.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-row align-items-end">
                <div class="form-group col-md-4 mb-2">
                    <label class="small font-weight-bold">Nhà sản xuất <span class="text-danger">*</span></label>
                    <select name="manufacturer_id" class="form-control form-control-sm rounded-0" required>
                        <option value="">— Chọn NSX —</option>
                        @foreach($manufacturers as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="small font-weight-bold">
                        File Excel / CSV <span class="text-danger">*</span>
                        <span class="text-muted font-weight-normal">(cột: product_name | unit_price | note)</span>
                    </label>
                    <input type="file" name="file" class="form-control form-control-sm rounded-0"
                           accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="form-group col-md-3 mb-2">
                    <label class="small font-weight-bold">Ghi chú</label>
                    <input type="text" name="note" class="form-control form-control-sm rounded-0"
                           placeholder="Ghi chú thêm...">
                </div>
                <div class="form-group col-md-1 mb-2">
                    <button type="submit" class="btn btn-dark btn-sm rounded-0 w-100">
                        <i class="fa-solid fa-upload mr-1"></i> Upload
                    </button>
                </div>
            </div>
        </form>
        <div class="small text-muted mt-1">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Format file: dòng đầu là tiêu đề, từ dòng 2 là dữ liệu.
            Cột 1: Tên sản phẩm &nbsp;|&nbsp; Cột 2: Giá chào (số) &nbsp;|&nbsp; Cột 3: Ghi chú (tuỳ chọn)
        </div>
    </div>
</div>

{{-- ── DANH SÁCH BÁO GIÁ ────────────────────────────────────── --}}
<div class="card shadow-none border rounded-0">
    <div class="card-header bg-white py-2 border-bottom">
        <span class="small font-weight-bold text-uppercase text-muted">Danh sách báo giá đã nhận</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0 small">
            <thead class="table-light">
                <tr>
                    <th class="pl-4 py-2">Mã báo giá</th>
                    <th class="py-2">Nhà sản xuất</th>
                    <th class="text-center py-2">Số SP</th>
                    <th class="text-center py-2">Trạng thái</th>
                    <th class="text-center py-2">Ngày nhận</th>
                    <th class="text-center py-2">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($offers as $offer)
                <tr>
                    <td class="pl-4 py-2 font-weight-bold">{{ $offer->offer_code }}</td>
                    <td class="py-2">{{ $offer->manufacturer->name ?? '—' }}</td>
                    <td class="text-center py-2">{{ $offer->items->count() }}</td>
                    <td class="text-center py-2">
                        @php
                            $badge = ['submitted'=>'badge-primary','accepted'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-secondary'][$offer->status] ?? 'badge-secondary';
                            $label = ['submitted'=>'Chờ duyệt','accepted'=>'Đã đặt hàng','rejected'=>'Từ chối','draft'=>'Nháp'][$offer->status] ?? $offer->status;
                        @endphp
                        <span class="badge {{ $badge }} rounded-0 px-2 py-1 text-white" style="font-size:0.7rem;">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="text-center py-2 text-muted">{{ $offer->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center py-2">
                        <a href="{{ route('admin.supplier-offers.show', $offer->id) }}"
                           class="btn btn-outline-dark btn-sm rounded-0 px-2 py-1" style="font-size:0.75rem;">
                            Xem & Đặt hàng
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Chưa có báo giá nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-center mt-3">
    {{ $offers->links() }}
</div>

@endsection
