@extends('layout/admin')
@section('body')
<h2>Danh sách người dùng</h2>
<table class="table">
    <thead>
       <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
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
            <td>{{ $user->role }}</td>

<td>
  <form action="{{ route('admin.user.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <button type="submit" class="btn btn-sm btn-warning">
        {{ $user->role === 'admin' ? 'Hạ quyền' : 'Lên admin' }}
    </button>
</form>

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
@endsection