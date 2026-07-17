@extends('layout/admin')
@section('body')
<div class="card-footer small text-mutted">
    <h3>Brand</h3>
    <a href="{{ route('admin.brand.create') }}" class="btn btn-warning mb-3">Add</a>

    <table class="table text-center align-middle table-hover">
        <thead class="table-light">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">image</th>
                <th scope="col">Description</th>
                <th scope="col">Status</th>
                <th scope="col">Option</th>
            </tr>
        </thead>
        <tbody>
            @forelse($brands as $object)
            <tr>
                <th scope="row">{{ $object->id }}</th>
                <td>{{$object->name}}</td>
                <td>
                    <img src="{{ $object->image }}" alt="{{ $object->name }}" width="80" class="rounded shadow-sm">
                </td>
                <td>{{ $object->descrip }}</td>
                <td>
                    @if($object->status==1)
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="green" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                    </svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="gray" class="bi bi-x-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                    </svg>
                    @endif
                </td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $object->id }}" data-toggle="dropdown" aria-expanded="false">
                            ...
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownMenu{{ $object->id }}">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.brand.edit',['brand' =>$object->id]) }}">
                                    <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Chỉnh sửa
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            {{-- <li>
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(confirm('Bạn có chắc chắn muốn xóa thương hiệu: {{ $object->name }}?')) { document.getElementById('brand-delete-{{ $object->id }}').submit(); }">
                                    <i class="far fa-trash-alt me-2"></i> Xóa
                                </a>
                                <form action="{{ route('admin.brand.destroy', ['brand' => $object->id]) }}" method="post" id="brand-delete-{{ $object->id }}" class="d-none">
                                    {{ csrf_field() }}
                                    {{ method_field('delete') }}
                                </form>
                            </li> --}}
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