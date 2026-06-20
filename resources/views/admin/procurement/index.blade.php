@extends('layout/admin')
@section('body')

<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h5 class="font-weight-bold text-dark mb-0">
        <i class="fa-solid fa-bullhorn mr-2 text-muted"></i>Yêu cầu nhập hàng công khai
    </h5>
    <small class="text-muted">Admin đăng → Tất cả NSX xem → NSX chào giá</small>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-0">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
@endif

<div class="card shadow-none border rounded-0">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0 small">
            <thead class="table-light">
                <tr>
                    <th class="pl-4 py-2">Mã yêu cầu</th>
                    <th class="text-center py-2">Số SP</th>
                    <th class="text-center py-2">Trạng thái</th>
                    <th class="text-center py-2">Hạn chót</th>
                    <th class="text-center py-2">Ngày tạo</th>
                    <th class="text-center py-2">Báo giá nhận</th>
                    <th class="text-center py-2">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td class="pl-4 py-2 font-weight-bold">{{ $req->request_code }}</td>
                    <td class="text-center py-2">{{ $req->items->count() }} SP</td>
                    <td class="text-center py-2">
                        @if($req->status === 'open')
                            <span class="badge badge-success rounded-0 px-2 py-1 text-white" style="font-size:0.7rem;">Đang mở</span>
                        @else
                            <span class="badge badge-secondary rounded-0 px-2 py-1 text-white" style="font-size:0.7rem;">Đã đóng</span>
                        @endif
                    </td>
                    <td class="text-center py-2 text-muted">
                        {{ $req->deadline ? \Carbon\Carbon::parse($req->deadline)->format('d/m/Y') : '—' }}
                    </td>
                    <td class="text-center py-2 text-muted">{{ $req->created_at->format('d/m/Y') }}</td>
                    <td class="text-center py-2">
                        <span class="font-weight-bold {{ $req->offers->count() > 0 ? 'text-success' : 'text-muted' }}">
                            {{ $req->offers->count() }} báo giá
                        </span>
                    </td>
                    <td class="text-center py-2">
                        <a href="{{ route('admin.procurement.show', $req->id) }}"
                           class="btn btn-outline-dark btn-sm rounded-0 px-2 py-1" style="font-size:0.75rem;">
                            Xem chi tiết
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Chưa có yêu cầu nào. Vào trang <a href="{{ route('admin.product.index') }}">Sản phẩm</a> để tạo.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
