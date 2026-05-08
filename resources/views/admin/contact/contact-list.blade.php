@extends('layout.admin')
@section('body')
<table class="table">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Message</th>
            <th scope="col">Delete</th>

        </tr>
    </thead>
    <tbody>
        @forelse($contacts as $contact)
        <tr>
            <td>{{ $contact->id }}</td>
            <td>{{ $contact->name }}</td>
            <td>{{ $contact->email }}</td>
            <td>{{ $contact->message }}</td>


            <td><a href="{{route('admin.contacts.destroy',['contact'=>$contact->id])}}" title="Delete {{$contact->name}}" onclick="event.preventDefault();window.confirm('Bạn đã chắc chắn xóa '+ '{{$contact->name}}' +' chưa?') ?document.getElementById('contact-delete-{{ $contact->id }}').submit() :0;" class="btn btn-danger"><i class="far fa-trash-alt"></i>
                    <form action="{{ route('admin.contacts.destroy', ['contact' => $contact->id]) }}" method="post" id="contact-delete-{{ $contact->id }}">
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
@endsection