@extends('layout.admin')

@section('body')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-dark font-weight-normal mb-0">Lịch sử hoạt động Root</h4>
    <span class="text-muted small">Chỉ ghi lại thao tác của tài khoản có quyền Root</span>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('admin.activity-log.index') }}" class="mb-4">
    <div class="row">
        <div class="col-md-3">
            <input type="date" name="date" value="{{ request('date') }}"
                   class="form-control rounded-0 border"
                   placeholder="Lọc theo ngày">
        </div>
        <div class="col-md-4">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-control rounded-0 border"
                   placeholder="Tìm theo tên hoặc email...">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark rounded-0 w-100">Lọc</button>
        </div>
        @if(request('date') || request('search'))
        <div class="col-md-2">
            <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline-secondary rounded-0 w-100">Xóa lọc</a>
        </div>
        @endif
    </div>
</form>

{{-- Bảng log --}}
<div class="card border-0 shadow-sm rounded-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 text-uppercase small text-muted py-3 pl-4" style="width:170px">Thời gian</th>
                    <th class="border-0 text-uppercase small text-muted py-3">Tên</th>
                    <th class="border-0 text-uppercase small text-muted py-3">Email</th>
                    <th class="border-0 text-uppercase small text-muted py-3">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="pl-4 text-muted small align-middle">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="align-middle font-weight-bold text-dark small">{{ $log->user_name }}</td>
                    <td class="align-middle text-muted small">{{ $log->user_email }}</td>
                    <td class="align-middle text-dark small">
                        @php
                            // Tô màu badge theo loại hành động
                            $action = $log->action;
                            $badgeClass = 'secondary';
                            if (str_contains($action, 'Xóa')) $badgeClass = 'danger';
                            elseif (str_contains($action, 'Tạo') || str_contains($action, 'Upload')) $badgeClass = 'success';
                            elseif (str_contains($action, 'Cập nhật') || str_contains($action, 'Duyệt') || str_contains($action, 'Xác nhận')) $badgeClass = 'warning';
                            elseif (str_contains($action, 'Từ chối') || str_contains($action, 'Đóng')) $badgeClass = 'dark';
                            elseif (str_contains($action, 'Xem')) $badgeClass = 'light';
                        @endphp
                        <span class="badge badge-{{ $badgeClass }} mr-2 rounded-0" style="font-size:0.65rem;">
                            @if(str_contains($action, 'Xóa')) XÓA
                            @elseif(str_contains($action, 'Tạo')) TẠO
                            @elseif(str_contains($action, 'Upload')) UPLOAD
                            @elseif(str_contains($action, 'Cập nhật')) SỬA
                            @elseif(str_contains($action, 'Duyệt')) DUYỆT
                            @elseif(str_contains($action, 'Từ chối')) TỪ CHỐI
                            @elseif(str_contains($action, 'Xem')) XEM
                            @else ACTION
                            @endif
                        </span>
                        {{ $action }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                        Chưa có hoạt động nào được ghi lại.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Phân trang --}}
@if($logs->hasPages())
<div class="mt-4 d-flex justify-content-center">
    {{ $logs->links() }}
</div>
@endif

<div class="mt-2 text-muted small text-right">
    Tổng: {{ $logs->total() }} bản ghi
</div>
@endsection
