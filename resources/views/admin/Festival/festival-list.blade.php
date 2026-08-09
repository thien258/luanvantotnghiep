@extends('layout/admin')
@section('body')
<div class="card-footer small text-mutted">
    <h3>Festival</h3>
    <a href="{{ route('admin.festival.create') }}" class="btn btn-warning">Thêm</a>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">giảm giá</th>
                <th scope="col">Thời gian bắt đầu</th>
                <th scope="col">Thời gian kết thúc</th>
                <th scope="col">Thêm sản phẩm</th>
                <th scope="col">Trạng thái</th>
                <th scope="col">Sửa</th>
                <th scope="col">Xóa</th>

            </tr>
        </thead>
        <tbody>
            @forelse($festivals as $object)
            <tr>
                <th scope="row">{{ $object->id }}</th>
                <td>{{$object->name}}</td>
                <td>{{$object->discount}}</td>
                <td>{{$object->start_date}}</td>
                <td>{{$object->end_date}}</td>
                <td>
                    <a href="{{ route('admin.festival.selectProducts', $object->id) }}" class="btn btn-info btn-sm">
                        <i class="fa-solid fa-box-open"></i>
                    </a>
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

                <td><a href=" {{ route('admin.festival.edit',['festival'=>$object->id]) }}  "><i class="fa-solid fa-pen-to-square text-warning"></i></a></td>
                <td><a href="{{route('admin.festival.destroy',['festival'=>$object->id])}}" title="Delete {{$object->name}}" onclick="event.preventDefault();window.confirm('Bạn đã chắc chắn xóa '+ '{{$object->name}}' +' chưa?') ?document.getElementById('festival-delete-{{ $object->id }}').submit() :0;" class="btn btn-danger"><i class="far fa-trash-alt"></i>
                        <form action="{{ route('admin.festival.destroy', ['festival' => $object->id]) }}" method="post" id="festival-delete-{{ $object->id }}">
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