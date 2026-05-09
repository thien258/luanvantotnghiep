@extends('layout.admin')

@section('body')
<div class="container">
    <div class="row">

        <form action="{{route('admin.volume.store')  }}" method="POST">
            @csrf()
            <div class="mb-3">
                <label for="name" class="form-label font-weight-bold">Dung tích (Volume)</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Ví dụ: 30ml, 50ml, 75ml..." required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label font-weight-bold">Status (Trạng thái)</label>
                <select name="status" id="status" class="form-control">
                    <option value="1">ON</option>
                    <option value="0">Off</option>
                </select>
            </div>


            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{ route('admin.volume.index') }} " class='btn btn-secondary'>BACK</a>
        </form>

    </div>
</div>

@endsection