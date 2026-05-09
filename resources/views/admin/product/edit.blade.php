@extends('layout.admin')

@section('body')
<div class="container">
  <div class="row">

    <form action="{{route('admin.product.update',['product'=>$product->id])}}" method="POST">
      @csrf()
      {{method_field('put')}}
      <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" value="{{$product->title}}">
      </div>
      <div class="mb-3">
        <label for="decription" class="form-label">Decription</label>
        <input type="text" class="form-control" id="decription" name="decription" value="{{$product->decription}}">
      </div>
      <div class="mb-3">
        <label for="image" class="form-label">image</label>
        <input type="text" class="form-control" id="image" name="image" value="{{$product->image}}">
      </div>
    <div class="mb-3">
                <label for="idConcentration" name='idConcentration' class="form-label">Concentration</label>
                <select name="idConcentration" class='form-control'>
                    @forelse($concentrations as $concentration)
                    <option value="{{ $concentration->id }}">{{ $concentration->concentration  }}</option>
                    @empty
                    <option>Không có concentration</option>
                    @endforelse
                </select>
            </div>
      <div class="mb-3">
        <label for="stock" class="form-label">Stock</label>
        <input type="number" class="form-control" id="stock" name="stock" value="{{$product->stock}}">
      </div>
    
             <div class="mb-3">
                <label for="idVolume" name='idVolume' class="form-label">Volume</label>
                <select name="idVolume" class='form-control'>
                    @forelse($volumes as $volume)
                    <option value="{{ $volume->id }}">{{ $volume->name  }}</option>
                    @empty
                    <option>Không có volume</option>
                    @endforelse
                </select>
      <div class="mb-3">
        <label for="price" class="form-label">price</label>
        <input type="number" class="form-control" id="price" name="price" value="{{$product->price}}">
  </div>

         <div class="mb-3">
                <label for="idCategory" name='idCategory' class="form-label">Category</label>
                <select name="idCategory" class='form-control'>
                    @forelse($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name  }}</option>
                    @empty
                    <option>Không có category</option>
                    @endforelse
                </select>
            </div>

  <select name="status" class='form-control'>
    @if ($product->status ==1)
    <option value="1" selected>ON</option>
    @else
    <option value="1">ON</option>
    @endif
    <option value="0">Off</option>
  </select>


  <button type="submit" class="btn btn-primary">Submit</button>
  <a href="{{ route('admin.product.index') }} " class='btn btn-secondary'>BACK</a>
  </form>

</div>
</div>

@endsection