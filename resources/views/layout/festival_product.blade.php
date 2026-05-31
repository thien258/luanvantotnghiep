@extends('show-product')

@section('product_header_zone')
<div class="festival-banner p-5 rounded text-white position-relative shadow-sm"
     style="background: linear-gradient(135deg, #ff416c, #ff4b2b); border-left: 5px solid #ffca28; text-align: left;">
    <div class="row align-items-center">
        <div class="col-md-8">
            <span class="badge bg-warning text-dark px-3 py-1 fw-bold text-uppercase mb-2">Sự kiện giới hạn</span>
            <h1 class="display-4 fw-bold mb-2">{{ $festival->name }}</h1>
            <p class="lead mb-0">Áp dụng trợ giá đặc biệt toàn sàn lên đến <strong class="text-warning fs-3">-{{ $festival->discount }}%</strong>.</p>
        </div>
        <div class="col-md-4 text-center mt-3 mt-md-0">
            <div class="p-3 bg-white text-dark rounded border">
                <p class="small fw-bold text-uppercase text-muted mb-1">Kết thúc sau</p>
                <div class="d-flex justify-content-center gap-1 fw-bold text-danger"
                    id="festival-countdown"
                    data-end-date="{{ $festival->end_date->format('Y-m-d') }}T23:59:59">
                    <span class="countdown-days">00</span><span class="text-dark">d</span>
                    <span class="countdown-hours">00</span><span class="text-dark">:</span>
                    <span class="countdown-minutes">00</span><span class="text-dark">:</span>
                    <span class="countdown-seconds">00</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('product_grid_title')
    <span class="text-danger fw-bold small"><i class="fa-solid fa-fire me-1"></i> Đang áp dụng giảm giá Lễ hội</span>
@endsection

@section('script')
@parent
<script src="{{ asset('js/festival.js') }}"></script>
@endsection
