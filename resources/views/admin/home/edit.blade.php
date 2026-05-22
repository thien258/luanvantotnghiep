@extends('layout/admin')
@section('body')
<div style="margin-left: 280px; margin-top: 100px; padding: 20px;">

  <form action="{{route('admin.home.update',['home'=>$home->id])}}" method="POST">
    @csrf()
    {{method_field('put')}}
    <div class="mb-3">
      <label for="email" class="form-label">Home</label>
      <input type="text" class="form-control" value="{{$home->name}}" id="name" name="name" aria-describedby="emailHelp">
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Image</label>
      <input type="text" class="form-control" value="{{$home->image}}" id="image" name="image" aria-describedby="emailHelp">
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Description</label>
      <input type="text" class="form-control" value="{{$home->descrip}}" id="descrip" name="descrip" aria-describedby="emailHelp">
    </div>




    <button type="submit" class="btn btn-primary">Update</button>
  </form>
</div>
@endsection