@extends('layout/admin')
@section('body')
<div style="margin-left: 280px; margin-top: 100px; padding: 20px;">

  {{-- Hiển thị lỗi validation --}}
  @if($errors->any())
  <div class="alert alert-danger rounded-0 mb-4">
      <ul class="mb-0 ps-3 small">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
      </ul>
  </div>
  @endif

  <form action="{{route('admin.festival.update',['festival'=>$festival->id])}}" method="POST">
    @csrf()
    {{method_field('put')}}
    <div class="mb-3">
      <label for="name" class="form-label">Name</label>
      <input type="text" class="form-control" value="{{$festival->name}}" id="name" name="name" aria-describedby="emailHelp">
    </div>
    <div class="mb-3">
      <label for="discount" class="form-label">Giảm giá (%)</label>
      <input type="number" class="form-control" value="{{$festival->discount}}" id="discount" name="discount" aria-describedby="emailHelp">
    </div>
    <div class="mb-3">
      <label for="start_date" class="form-label">Thời gian bắt đầu</label>
      <input type="date" class="form-control" value="{{ $festival->start_date->format('Y-m-d') }}" id="start_date" name="start_date" aria-describedby="emailHelp">  
    </div>
    <div class="mb-3">
      <label for="end_date" class="form-label">Thời gian kết thúc</label>
      <input type="date" class="form-control" value="{{ $festival->end_date->format('Y-m-d') }}" id="end_date" name="end_date" aria-describedby="emailHelp">
    </div>

    <select name="status"  class='form-control'>
      @if ($festival->status ==1)
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