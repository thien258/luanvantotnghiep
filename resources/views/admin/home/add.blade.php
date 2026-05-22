@extends('layout.admin')

@section('body')
<div class="container">
    <div class="row">

        <form action="{{route('admin.home.store')  }}" method="POST">
            @csrf()
            <div class="mb-3">
                <label for="email" class="form-label">Home</label>
                <input type="text" class="form-control" id="name" name="name" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">image</label>
                <input type="text" class="form-control" id="image" name="image" aria-describedby="emailHelp">
            </div>
              <div class="mb-3">
                <label for="email" class="form-label">Description</label>
                <input type="text" class="form-control" id="descrip" name="descrip" aria-describedby="emailHelp">
            </div>
          
           


            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{ route('admin.home.index') }} " class='btn btn-secondary'>BACK</a>
        </form>

    </div>
</div>

@endsection