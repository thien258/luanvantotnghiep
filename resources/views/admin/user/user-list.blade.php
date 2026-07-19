@extends('layout/admin')
@section('body')

<h2>Danh sách người dùng</h2>

<form method="GET" action="{{ route('admin.user.index') }}" class="mb-3 d-flex gap-2" style="max-width:400px;">
    <input type="text" name="email" value="{{ $email ?? '' }}"
           class="form-control form-control-sm rounded-0"
           placeholder="Tìm theo email...">
    <button type="submit" class="btn btn-sm btn-dark rounded-0">
        <i class="fa fa-search"></i>
    </button>
    @if(!empty($email))
    <a href="{{ route('admin.user.index') }}" class="btn btn-sm btn-outline-secondary rounded-0">✕</a>
    @endif
</form>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@php
    $operator     = Auth::user();
    $operatorRole = $operator->role;

    // Role nào operator được phép toggle
    $allowedTargets = match ($operatorRole) {
        'director' => ['customer', 'warehouse', 'manufacturer'],
        'admin'    => ['customer', 'warehouse', 'manufacturer', 'admin'],
        'root'     => ['customer', 'warehouse', 'manufacturer', 'admin', 'director'],
        default    => [],
    };

    // Role nào operator được phép gán (dùng trong dropdown)
    $allowedRoles = match ($operatorRole) {
        'root'     => ['customer', 'warehouse', 'manufacturer', 'admin', 'director', 'root'],
        'admin'    => ['customer', 'warehouse', 'manufacturer', 'admin'],   // không được gán director, root
        'director' => ['admin'],                                         // chỉ được gán admin
        default    => [],
    };

    $canChangeRole = count($allowedRoles) > 0;
@endphp

<table class="table table-bordered table-hover small">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Email</th>
            <th>Điện thoại</th>
            <th>Địa chỉ</th>
            <th>Role</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
        @php
            $isSelf       = $user->id === $operator->id;
            $canToggle    = !$isSelf && in_array($user->role, $allowedTargets);
        @endphp
        <tr class="{{ $user->is_active ? '' : 'table-secondary text-muted' }}">
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->phone }}</td>
            <td>{{ $user->address }}</td>
            <td>{{ $user->role }}</td>

            {{-- Badge trạng thái --}}
            <td>
                @if($user->is_active)
                    <span class="badge badge-success px-2 py-1">Hoạt động</span>
                @else
                    <span class="badge badge-secondary px-2 py-1">Đã tắt</span>
                @endif
            </td>

            {{-- Thao tác --}}
            <td class="d-flex align-items-center" style="gap:6px;">

                @if($isSelf)
                    <span class="badge badge-secondary px-2 py-1">Tài khoản của bạn</span>

                @else
                    {{-- Đổi role: tuỳ quyền operator --}}
                    @if($canChangeRole)
                    <form action="{{ route('admin.user.update', $user->id) }}" method="POST" class="mb-0">
                        @csrf
                        @method('PUT')
                        <select name="role"
                                class="form-control form-control-sm d-inline-block"
                                style="width:auto;"
                                onchange="this.form.submit()">
                            @if(in_array('customer', $allowedRoles))
                            <option value="customer"     {{ $user->role === 'customer'     ? 'selected' : '' }}>Customer</option>
                            @endif
                            @if(in_array('warehouse', $allowedRoles))
                            <option value="warehouse"    {{ $user->role === 'warehouse'    ? 'selected' : '' }}>Nhân viên kho</option>
                            @endif
                            @if(in_array('manufacturer', $allowedRoles))
                            <option value="manufacturer" {{ $user->role === 'manufacturer' ? 'selected' : '' }}>Nhà sản xuất</option>
                            @endif
                            @if(in_array('admin', $allowedRoles))
                            <option value="admin"        {{ $user->role === 'admin'        ? 'selected' : '' }}>Admin</option>
                            @endif
                            @if(in_array('director', $allowedRoles))
                            <option value="director"     {{ $user->role === 'director'     ? 'selected' : '' }}>Giám đốc</option>
                            @endif
                            @if(in_array('root', $allowedRoles))
                            <option value="root"         {{ $user->role === 'root'         ? 'selected' : '' }}>Root</option>
                            @endif
                        </select>
                    </form>
                    @endif

                    {{-- Toggle tắt/mở: chỉ với role được phép --}}
                    @if($canToggle)
                    <form action="{{ route('admin.user.toggleStatus', $user->id) }}" method="POST" class="mb-0">
                        @csrf
                        @method('PATCH')
                        @if($user->is_active)
                            <button type="submit"
                                    class="btn btn-warning btn-sm"
                                    title="Tắt tài khoản {{ $user->name }}"
                                    onclick="return confirm('Tắt tài khoản {{ addslashes($user->name) }}?')">
                                <i class="fas fa-ban"></i> Tắt
                            </button>
                        @else
                            <button type="submit"
                                    class="btn btn-success btn-sm"
                                    title="Kích hoạt lại {{ $user->name }}"
                                    onclick="return confirm('Kích hoạt lại tài khoản {{ addslashes($user->name) }}?')">
                                <i class="fas fa-check-circle"></i> Mở
                            </button>
                        @endif
                    </form>
                    @endif

                    {{-- Không có quyền gì với user này --}}
                    @if(!$canChangeRole && !$canToggle)
                        <span class="text-muted small">—</span>
                    @endif
                @endif

            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center text-muted py-3">Không tìm thấy người dùng nào.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-center mt-3">
    {{ $users->links() }}
</div>

@endsection
