@extends('layout.home')
@section('content')

<div class="container py-5">
    <div class="mb-4">
        <h4 class="font-weight-bold">Yêu cầu nhập hàng đang mở</h4>
        <p class="text-muted small">Danh sách sản phẩm Aura & Essence cần nhập — Chào giá để hợp tác</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @forelse($requests as $req)
    <div class="card border rounded-0 shadow-none mb-3">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="font-weight-bold mb-1">{{ $req->request_code }}</h6>
                    <span class="badge badge-success text-white rounded-0 px-2 py-1 small">Đang mở</span>
                    @if($req->deadline)
                        <span class="text-muted small ml-2">
                            Hạn: {{ \Carbon\Carbon::parse($req->deadline)->format('d/m/Y') }}
                        </span>
                    @endif
                    @if($req->note)
                        <p class="text-muted small mt-1 mb-0">{{ $req->note }}</p>
                    @endif
                </div>
                <a href="{{ route('procurement.show', $req->id) }}"
                   class="btn btn-dark btn-sm rounded-0 px-3">
                    <i class="fa-solid fa-tags mr-1"></i> Xem & Chào giá
                </a>
            </div>

            {{-- Danh sách SP tóm tắt --}}
            <div class="mt-3">
                <div class="row">
                    @foreach($req->items->take(4) as $item)
                    <div class="col-md-3 mb-2">
                        <div class="d-flex align-items-center border rounded-0 p-2 bg-light">
                            @if($item->product?->image)
                                <img src="{{ $item->product->image }}"
                                     style="width:36px;height:36px;object-fit:cover;" class="mr-2 border">
                            @endif
                            <div class="small">
                                <div class="font-weight-bold text-truncate" style="max-width:120px;">
                                    {{ $item->product_name }}
                                </div>
                                <div class="text-muted">Cần: {{ $item->qty_needed }} cái</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($req->items->count() > 4)
                    <div class="col-md-3 mb-2 d-flex align-items-center">
                        <span class="text-muted small">+{{ $req->items->count() - 4 }} sản phẩm khác...</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="fa-solid fa-inbox fa-2x mb-3 d-block"></i>
        Hiện không có yêu cầu nhập hàng nào đang mở.
    </div>
    @endforelse
</div>

@endsection
