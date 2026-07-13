@extends('layout/admin')
@section('body')
<h2>Danh sách người dùng</h2>
<table class="table">
    <thead>
       <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Address</th>
        <th>Role</th>
        <th>Thao tác</th>
        <th>Xóa</th>
    </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->phone }}</td>
            <td>{{ $user->address }}</td>
            <td>{{ $user->role }}</td>

<td>
  @if($user->id === Auth::id())
    <span class="badge badge-secondary px-2 py-1">Tài khoản của bạn</span>
  @else
  <form action="{{ route('admin.user.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')
    <select name="role" class="form-control form-control-sm d-inline-block" style="width:auto;"
        onchange="this.form.submit()">
      <option value="customer"      {{ $user->role === 'customer'     ? 'selected' : '' }}>Customer</option>
      <option value="warehouse"     {{ $user->role === 'warehouse'    ? 'selected' : '' }}>Nhân viên kho</option>
      <option value="manufacturer"  {{ $user->role === 'manufacturer' ? 'selected' : '' }}>Nhà sản xuất</option>
      <option value="director"      {{ $user->role === 'director'     ? 'selected' : '' }}>Giám đốc</option>
      <option value="admin"         {{ $user->role === 'admin'        ? 'selected' : '' }}>Admin</option>
      <option value="root"          {{ $user->role === 'root'         ? 'selected' : '' }}>Root</option>
    </select>
  </form>
  @endif
</td>
<td>
    <a href="{{ route('admin.user.destroy', $user->id) }}"
       title="Xóa {{ $user->name }}"
       onclick="event.preventDefault();
                if (confirm('Bạn có chắc chắn muốn xóa không?')) {
                    document.getElementById('delete-form-{{ $user->id }}').submit();
                }"
       class="btn btn-danger btn-sm">
        <i class="far fa-trash-alt"></i>
    </a>

    <form id="delete-form-{{ $user->id }}"
          action="{{ route('admin.user.destroy', $user->id) }}"
          method="POST"
          style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</td>

        </tr>
        @empty
        <tr>
            <td>khong tim thay</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-center mt-3">
    {{ $users->links() }}
</div>
@endsection