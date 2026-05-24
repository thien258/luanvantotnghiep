@extends('show-product')

@section('product_header_zone')
    <h2 class="display-5 text-dark mb-3" style="font-family: serif;">{{ $category->name }}</h2>
    <p class="text-muted">Những sản phẩm cao cấp thuộc bộ sưu tập danh mục {{ $category->name }}</p>
@endsection

@section('product_grid_title')
    <span class="text-muted small"><i class="fa-solid fa-layer-group me-1"></i> Danh mục: {{ $category->name }} ({{ $products->count() }} sản phẩm)</span>
@endsection