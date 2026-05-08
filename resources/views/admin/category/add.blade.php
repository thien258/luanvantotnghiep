@extends('layout.admin')

@section('body')
<div class="container">
    <div class="row">

        <form action="{{route('admin.category.store')  }}" method="POST">
            @csrf()
            <div class="mb-3">
                <label for="email" class="form-label">Category</label>
                <input type="text" class="form-control" id="name" name="name" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">image</label>
                <input type="text" class="form-control" id="image" name="image" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="status" name='status'class="form-label">status</label>
                <select name="status" class='form-control'>
                    <option value="1">ON</option>
                    <option value="0">Off</option>
                </select>
            </div>


            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{ route('admin.category.index') }} " class='btn btn-secondary'>BACK</a>
        </form>

    </div>
</div>

@endsection