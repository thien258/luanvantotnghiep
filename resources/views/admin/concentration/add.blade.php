@extends('layout.admin')

@section('body')
<div class="container">
    <div class="row">

        <form action="{{route('admin.concentration.store')  }}" method="POST">
            @csrf()
            <div class="mb-3">
                <label for="concentration" class="form-label">Concentration</label>
                <input type="text" class="form-control" id="concentration" name="concentration" aria-describedby="emailHelp">
            </div>
          
            <div class="mb-3">
                <label for="status" name='status'class="form-label">status</label>
                <select name="status" class='form-control'>
                    <option value="1">ON</option>
                    <option value="0">Off</option>
                </select>
            </div>


            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{ route('admin.concentration.index') }} " class='btn btn-secondary'>BACK</a>
        </form>

    </div>
</div>

@endsection