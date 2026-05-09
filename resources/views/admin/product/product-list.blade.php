@extends('layout/admin')
@section('body')
<div class="card-footer small text-mutted">
    <h3>product</h3>
    <a href="{{ route('admin.product.create') }}" class="btn btn-warning">Add</a>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Title</th>
                <th scope="col">Decription</th>
                <th scope="col">Category</th>
                <th scope="col">Image</th>
                <th scope="col">Price</th>
                <th scope="col">Concentration</th>
                <th scope="col">Stock</th>
                <th scope="col">Volume</th>
                <th scope="col">Status</th>
                <th scope="col">Edit</th>
                <th scope="col">Delete</th>

            </tr>
        </thead>
        <tbody>
            @forelse($products as $object)
            <tr>
                <th scope="row">{{ $object->id }}</th>

                <td>{{$object->title}}</td>
                <td>{{$object->decription}}</td>
                <td>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark" viewBox="0 0 16 16">
                        <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z" />
                    </svg>
                    {{$object->category->name}}
                </td>

                <td><img src="{{ $object->image }}" width="150" alt=""></td>
                <td>{{$object->price}}</td>

                <td>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark" viewBox="0 0 16 16">
                        <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z" />
                    </svg>
                    {{ $object->concentration?->concentration ?? 'Trống' }}
                </td>

                <td>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark" viewBox="0 0 16 16">
                        <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z" />
                    </svg>
                    {{ $object->volume?->name ?? 'Trống' }}
                </td>
                <td>
                    @if($object->status==1)
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="green" class="bi bi-check-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                        <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05" />
                    </svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                        <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05" />
                    </svg>
                    @endif
                </td>

                <td><a href=" {{ route('admin.product.edit',['product' =>$object->id]) }}  "><i class="fa-solid fa-pen-to-square text-warning"></i></a></td>
                <td><a href="{{route('admin.product.destroy',['product'=>$object->id])}}" title="Delete {{$object->name}}" onclick="event.preventDefault();window.confirm('Bạn đã chắc chắn xóa '+ '{{$object->name}}' +' chưa?') ?document.getElementById('product-delete-{{ $object->id }}').submit() :0;" class="btn btn-danger"><i class="far fa-trash-alt"></i>
                        <form action="{{ route('admin.product.destroy', ['product' => $object->id]) }}" method="post" id="product-delete-{{ $object->id }}">
                            {{ csrf_field() }}
                            {{ method_field('delete') }}
                        </form>
                    </a></td>
            </tr>
            @empty
            <tr>
                <td>khong tim thay</td>
            </tr>
            @endforelse

        </tbody>
    </table>
</div>
@endsection