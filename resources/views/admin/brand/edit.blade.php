@extends('layout/admin')
@section('body')
<div style="margin-left: 280px; margin-top: 100px; padding: 20px;">

  <form action="{{route('admin.brand.update',['brand'=>$brand->id])}}" method="POST">
    @csrf()
    {{method_field('put')}}
    <div class="mb-3">
      <label for="email" class="form-label">Brand</label>
      <input type="text" class="form-control" value="{{$brand->name}}" id="name" name="name" aria-describedby="emailHelp">
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Image</label>
      <input type="text" class="form-control" value="{{$brand->image}}" id="image" name="image" aria-describedby="emailHelp">
    </div>


    <select name="status"  class='form-control'>
      @if ($brand->status ==1)
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