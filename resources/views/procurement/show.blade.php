@extends('layout.home')
@section('content')

<div class="container py-5">
    <div class="mb-4 d-flex justify-content-between align-items-start">
        <div>
            <h4 class="font-weight-bold mb-1">{{ $procRequest->request_code }}</h4>
            <span class="badge badge-success text-white rounded-0 px-2 py-1 small">Đang mở</span>
            @if($procRequest->deadline)
                <span class="text-muted small ml-2">
                    Hạn chào giá: {{ \Carbon\Carbon::parse($procRequest->deadline)->format('d/m/Y') }}
                </span>
            @endif
        </div>
        <a href="{{ route('procurement.index') }}" class="btn btn-outline-secondary btn-sm rounded-0">
            <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-0">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-0 small">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('procurement.offer', $procRequest->id) }}" method="POST">
        @csrf

        {{-- Chọn NSX --}}
        <div class="card border rounded-0 shadow-none mb-3">
            <div class="card-header bg-white py-2 border-bottom">
                <span class="small font-weight-bold text-uppercase text-muted">Thông tin nhà sản xuất</span>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-5 mb-2">
                        <label class="small font-weight-bold">Nhà sản xuất / Nhà cung cấp <span class="text-danger">*</span></label>
                        <select name="manufacturer_id" class="form-control form-control-sm rounded-0" required>
                            <option value="">— Chọn NSX —</option>
                            @foreach($manufacturers as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-7 mb-2">
                        <label class="small font-weight-bold">Ghi chú chung</label>
                        <input type="text" name="note" class="form-control form-control-sm rounded-0"
                               placeholder="VD: Hàng có sẵn trong kho, giao trong 3-5 ngày...">
                    </div>
                </div>
            </div>
        </div>

        {{-- Bảng sản phẩm + nhập giá --}}
        <div class="card border rounded-0 shadow-none mb-3">
            <div class="card-header bg-white py-2 border-bottom">
                <span class="small font-weight-bold text-uppercase text-muted">Chào giá từng sản phẩm</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="pl-4 py-2" style="width:7%">Ảnh</th>
                            <th class="py-2">Tên sản phẩm</th>
                            <th class="py-2">Brand</th>
                            <th class="py-2">Nồng độ</th>
                            <th class="text-center py-2" style="width:10%">Cần nhập</th>
                            <th class="text-center py-2" style="width:15%">Giá chào (₫) <span class="text-danger">*</span></th>
                            <th class="py-2" style="width:15%">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($procRequest->items as $i => $item)
                        <tr>
                            {{-- Hidden fields --}}
                            <input type="hidden" name="items[{{ $i }}][product_id]"   value="{{ $item->product_id }}">
                            <input type="hidden" name="items[{{ $i }}][product_name]" value="{{ $item->product_name }}">

                            <td class="pl-4 py-2">
                                @if($item->product?->image)
                                    <img src="{{ $item->product->image }}"
                                         style="width:36px;height:36px;object-fit:cover;" class="border rounded">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-2 font-weight-bold">{{ $item->product_name }}</td>
                            <td class="py-2 text-muted">{{ $item->product?->brand?->name ?? '—' }}</td>
                            <td class="py-2 text-muted">{{ $item->product?->concentration?->concentration ?? '—' }}</td>
                            <td class="text-center py-2 font-weight-bold text-primary">{{ $item->qty_needed }}</td>
                            <td class="py-2">
                                <input type="number" name="items[{{ $i }}][unit_price]"
                                       class="form-control form-control-sm rounded-0 text-center"
                                       placeholder="VD: 2500000" min="1" required>
                            </td>
                            <td class="py-2">
                                <input type="text" name="items[{{ $i }}][note]"
                                       class="form-control form-control-sm rounded-0"
                                       placeholder="Ghi chú...">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Sau khi gửi, admin sẽ xem xét báo giá và liên hệ với bạn.
            </small>
            <button type="submit" class="btn btn-dark rounded-0 px-4">
                <i class="fa-solid fa-paper-plane mr-1"></i> Gửi báo giá
            </button>
        </div>
    </form>
</div>

@endsection
