@extends('layout/admin')
@section('body')
<div style="margin-left: 280px; margin-top: 100px; padding: 20px;">

  <form action="{{route('admin.volume.update',['volume'=>$volume->id])}}" method="POST">
    @csrf()
    {{method_field('put')}}
    <div class="mb-3">
      <label for="name" class="form-label">Name</label>
      <input type="text" class="form-control" value="{{$volume->name}}" id="name" name="name" aria-describedby="emailHelp">
    </div>



    <select name="status"  class='form-control'>
      @if ($volume->status ==1)
      <option value="1" selected>ON</option>
      @else
      <option value="1">ON</option>
      @endif
      <option value="0">Off</option>
    </select>

    <button type="submit" class="btn btn-primary">Update</button>
  </form>
</div>
@endsection