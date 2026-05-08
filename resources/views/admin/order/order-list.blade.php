@extends('layout/admin')
@section('body')
<h2>Order</h2>
<table class="table">
    <thead>
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>product</th>
            <th>price</th>
            <th>address</th>
            <th>delete</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $order)
        <tr>
            <td>{{ $order->id }}</td>
            <td>{{ $order->user->name }}</td>
            <td>{{ $order->product->title }}</td>
            <td>{{ number_format($order->product->price)   }}</td>
            <td> {{ $order->address }}</td>

 
            <td>
                <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST"
                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">
                        <i class="far fa-trash-alt"></i>
                    </button>
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