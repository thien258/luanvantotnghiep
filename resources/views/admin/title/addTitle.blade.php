@extends('layout.admin')

@section('body')
<div class="container">
    <div class="row">

        <form action="{{route('admin.title.store')  }}" method="POST">
            @csrf()
            <div class="mb-3">
                <label for="email" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">image</label>
                <input type="text" class="form-control" id="image" name="image" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="button" class="form-label">button</label>
                <input type="text" class="form-control" id="button" name="button" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="descrip" class="form-label">Description</label>
                <input type="text" class="form-control" id="descrip" name="descrip" aria-describedby="emailHelp">
            </div>
 

            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{ route('admin.title.index') }} " class='btn btn-secondary'>BACK</a>
        </form>

    </div>
</div>

@endsection
