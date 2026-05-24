@extends('show-product')

@section('product_header_zone')
    <h2 class="display-5 text-dark mb-3" style="font-family: serif; font-weight: bold; letter-spacing: 1px;">{{ $brand->name }}</h2>
    <p class="text-muted"><em>"{{ $brand->descrip ?? 'Thương hiệu hương thơm xa xỉ khẳng định đẳng cấp của bạn.' }}"</em></p>
@endsection

@section('product_grid_title')
    <span class="text-dark fw-bold small"><i class="fa-solid fa-circle-check text-success me-1"></i> Nhà phân phối chính hãng {{ $brand->name }}</span>
@endsection