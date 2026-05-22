@extends('layout/admin')
@section('body')
<div class="card-footer small text-mutted">
    <h3>Home</h3>
    <a href="{{ route('admin.home.create') }}" class="btn btn-warning mb-3">Add</a>

    <table class="table text-center align-middle table-hover">
        <thead class="table-light">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">image</th>
                <th scope="col">Description</th>
   
                <th scope="col">Option</th>
            </tr>
        </thead>
        <tbody>
            @forelse($home as $object)
            <tr>
                <th scope="row">{{ $object->id }}</th>
                <td>{{$object->name}}</td>
                <td>
                    <img src="{{ $object->image }}" alt="{{ $object->name }}" width="80" class="rounded shadow-sm">
                </td>
                <td>{{ $object->descrip }}</td>
               
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $object->id }}" data-toggle="dropdown" aria-expanded="false">
                            ...
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownMenu{{ $object->id }}">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.home.edit',['home' =>$object->id]) }}">
                                    <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Chỉnh sửa
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(confirm('Bạn có chắc chắn muốn xóa thương hiệu: {{ $object->name }}?')) { document.getElementById('home-delete-{{ $object->id }}').submit(); }">
                                    <i class="far fa-trash-alt me-2"></i> Xóa
                                </a>
                                <form action="{{ route('admin.home.destroy', ['home' => $object->id]) }}" method="post" id="home-delete-{{ $object->id }}" class="d-none">
                                    {{ csrf_field() }}
                                    {{ method_field('delete') }}
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-muted py-4">Không tìm thấy thương hiệu nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection