@extends('layout/home')
@section('body')
<table class="table">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Image</th>
            <th scope="col">Price</th>
            <th scope="col">Order</th>
            <th scope="col">delete</th>
        </tr>
    </thead>
    <tbody>
        @forelse($loves as $love)
        @if($love->product && $love->product->status == 1)
        <tr>
            <td>{{ $love->id }}</td>

            <td>{{$love->product->title}}</td>
            <td><img src="{{$love->product->image}}" alt="" width="100px"></td>
            <td>{{$love->product->price}}</td>


            <td>
                <form action="{{ route('order.store') }}" method="post">
                    @csrf

                    <input type="hidden" name="idProduct" value="{{ $love->product->id }}">
                    <input type="hidden" name="price" value="{{ $love->product->price }}">
                    <input type="text" name="address" class="form-control mb-1" placeholder="Nhập địa chỉ" required>
                    <button type="submit" class="btn btn-success btn-sm">Order</button>
                </form>

            </td>
            <td><a href="{{route('loves.destroy',['love'=>$love->id])}}" title="Delete {{$love->product->name}}" onclick="event.preventDefault();window.confirm('Bạn đã chắc chắn xóa '+ '{{$love->product->name}}' +' chưa?') ?document.getElementById('love-delete-{{ $love->id }}').submit() :0;" class="btn btn-danger"><i class="far fa-trash-alt"></i>
                    <form action="{{ route('loves.destroy', ['love' => $love->id]) }}" method="post" id="love-delete-{{ $love->id }}">
                        {{ csrf_field() }}
                        {{ method_field('delete') }}
                    </form>
                </a></td>
        </tr>
        @endif
        @empty
        <h2>No Products Found</h2>
        @endforelse
    </tbody>
</table>
@endsection