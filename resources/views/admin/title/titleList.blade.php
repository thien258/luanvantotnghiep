@extends('layout/admin')
@section('body')
<div class="card-footer small text-mutted">
    <h3>Title</h3>
    <a href="{{ route('admin.title.create') }}" class="btn btn-warning">Add</a>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">title</th>
                <th scope="col">image</th>
                <th scope="col">button</th>
                <th scope="col">descrip</th>
                
                <th scope="col">edit</th>
                <th scope="col">Delete</th>
                
            </tr>
        </thead>
        <tbody>
            @forelse($title as $object)
            <tr>
                <th scope="row">{{ $object->idTitle }}</th>
                <td>{{$object->title}}</td>
                <td><img src="{{ $object->image }}"width="150" alt=""></td>
                <td>{{$object->button}}</td>
                <td>{{$object->descrip}}</td>
               
                <td><a href=" {{ route('admin.title.edit',['title'=>$object->idTitle]) }}  "><i class="fa-solid fa-pen-to-square text-warning"></i></a></td>
                <td><a href="{{route('admin.title.destroy',['title'=>$object->idTitle])}}" title="Delete {{$object->title}}" onclick="event.preventDefault();window.confirm('Bạn đã chắc chắn xóa '+ '{{$object->title}}' +' chưa?') ?document.getElementById('title-delete-{{ $object->idTitle }}').submit() :0;" class="btn btn-danger"><i class="far fa-trash-alt"></i>
                        <form action="{{ route('admin.title.destroy', ['title' => $object->idTitle]) }}" method="post" id="title-delete-{{ $object->idTitle }}">
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