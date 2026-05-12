@extends('layout.admin')

@section('body')
<div class="container">
    <div class="row">

        <form action="{{route('admin.product.store')  }}" method="POST">
            @csrf()
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="decription" class="form-label">Decription</label>
                <input type="text" class="form-control" id="decription" name="decription" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">image</label>
                <input type="text" class="form-control" id="image" name="image" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">price</label>
                <input type="number" class="form-control" id="price" name="price" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" aria-describedby="emailHelp">
                <div class="mb-3">
                    <label for="idConcentration" class="form-label">Concentration (Nồng độ)</label>
                    <select name="idConcentration" class="form-control">
                        @forelse($concentrations as $concentration)
                        <option value="{{ $concentration->id }}">{{ $concentration->concentration }}</option>
                        @empty
                        <option value="">Không có dữ liệu Nồng độ</option>
                        @endforelse
                    </select>
                </div>

                <div class="mb-3">
                    <label for="idVolume" class="form-label">Volume (Dung tích)</label>
                    <select name="idVolume" class="form-control">
                        @forelse($volumes as $volume)
                        <option value="{{ $volume->id }}">{{ $volume->name }}</option>
                        @empty
                        <option value="">Không có dữ liệu Dung tích</option>
                        @endforelse
                    </select>
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
                <div class="mb-3">
                    <label for="idBrand" name='idBrand' class="form-label">Brand</label>
                    <select name="idBrand" class='form-control'>
                        @forelse($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name  }}</option>
                        @empty
                        <option>Không có brand</option>
                        @endforelse
                    </select>
                </div>
                <div class="mb-3">
                    <label for="status" name='status' class="form-label">status</label>
                    <select name="status" class='form-control'>
                        <option value="1">ON</option>
                        <option value="0">Off</option>
                    </select>
                </div>




                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('admin.product.index') }} " class='btn btn-secondary'>BACK</a>
        </form>

    </div>
</div>

@endsection