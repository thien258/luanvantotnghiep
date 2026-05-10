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
        <label for="idConcentration" class="form-label">Concentration</label>
        <select name="idConcentration" id="idConcentration" class="form-control">
            @forelse($concentrations as $concentration)
            <option value="{{ $concentration->id }}" {{ $product->idConcentration == $concentration->id ? 'selected' : '' }}>
                {{ $concentration->concentration }}
            </option>
            @empty
            <option value="">Không có concentration</option>
            @endforelse
        </select>
      </div>
      
      <div class="mb-3">
        <label for="stock" class="form-label">Stock</label>
        <input type="number" class="form-control" id="stock" name="stock" value="{{$product->stock}}">
      </div>
    
      <div class="mb-3">
        <label for="idVolume" class="form-label">Volume</label>
        <select name="idVolume" id="idVolume" class="form-control">
            @forelse($volumes as $volume)
            <option value="{{ $volume->id }}" {{ $product->idVolume == $volume->id ? 'selected' : '' }}>
                {{ $volume->name }}
            </option>
            @empty
            <option value="">Không có volume</option>
            @endforelse
        </select>
      </div> <div class="mb-3">
        <label for="price" class="form-label">price</label>
        <input type="number" class="form-control" id="price" name="price" value="{{$product->price}}">
      </div>

      <div class="mb-3">
        <label for="idCategory" class="form-label">Category</label>
        <select name="idCategory" id="idCategory" class="form-control">
            @forelse($categories as $category)
            <option value="{{ $category->id }}" {{ $product->idCategory == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
            @empty
            <option value="">Không có category</option>
            @endforelse
        </select>
      </div>

      <div class="mb-3">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-control">
            <option value="1" {{ $product->status == 1 ? 'selected' : '' }}>ON</option>
            <option value="0" {{ $product->status == 0 ? 'selected' : '' }}>Off</option>
          </select>
      </div>

      <button type="submit" class="btn btn-primary">Submit</button>
      <a href="{{ route('admin.product.index') }}" class="btn btn-secondary">BACK</a>
      
    </form>

  </div>
</div>
@endsection