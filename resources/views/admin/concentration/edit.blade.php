@extends('layout/admin')
@section('body')
<div style="margin-left: 280px; margin-top: 100px; padding: 20px;">

  <form action="{{route('admin.concentration.update',['concentration'=>$concentration->id])}}" method="POST">
    @csrf()
    {{method_field('put')}}
    <div class="mb-3">
      <label for="concentration" class="form-label">Concentration</label>
      <input type="text" class="form-control" value="{{$concentration->concentration}}" id="concentration" name="concentration" aria-describedby="emailHelp">
    </div>



    <select name="status"  class='form-control'>
      @if ($concentration->status ==1)
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