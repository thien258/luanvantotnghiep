@extends('layout/admin')
@section('body')
<div class="container-fluid">
    <h3>Chọn sản phẩm cho: {{ $festival->name }}</h3>
    
    <div class="mb-4">
        <input type="text" id="searchInput" class="form-control" data-festival-id="{{ $festival->id }}" 
               placeholder="Gõ tên sản phẩm để tìm kiếm...">
    </div>

    <form action="{{ route('admin.festival.updateProducts', $festival->id) }}" method="POST">
        @csrf
        <table class="table table-bordered">
            <thead>
                <tr><th>Chọn</th><th>Tên sản phẩm</th><th>Hình ảnh</th><th>Giá</th></tr>
            </thead>
            <tbody id="product-tbody">
                @foreach($products as $product)
                <tr>
                    <td><input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                        {{ $festival->products->contains($product->id) ? 'checked' : '' }}></td>
                    <td>{{ $product->title }}</td>
                    <td><img src="{{ $product->image }}" style="width: 50px;"></td>
                    <td>{{ number_format($product->price) }}đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-success">Lưu danh sách</button>
    </form>
</div>
@endsection

@section('script')
    <script src="{{ asset('js/selectProductFestival.js') }}"></script>
@endsection