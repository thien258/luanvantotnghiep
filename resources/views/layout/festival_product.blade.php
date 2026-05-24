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
                <div class="d-flex justify-content-center gap-2 fw-bold fs-4 text-danger" id="festival-countdown">
                    <span>00</span>:<span class="text-dark">00</span>:<span class="text-dark">00</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const endDate = new Date("{{ $festival->end_date }} 23:59:59").getTime();
        const timer = setInterval(function() {
            const now = new Date().getTime();
            const distance = endDate - now;
            if (distance < 0) {
                clearInterval(timer);
                document.getElementById("festival-countdown").innerHTML = "HẾT HẠN";
                return;
            }
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)) + (days * 24);
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById("festival-countdown").innerHTML = 
                (hours < 10 ? "0" + hours : hours) + ":" + 
                (minutes < 10 ? "0" + minutes : minutes) + ":" + 
                (seconds < 10 ? "0" + seconds : seconds);
        }, 1000);
    });
</script>
@endsection

@section('product_grid_title')
    <span class="text-danger fw-bold small"><i class="fa-solid fa-fire me-1"></i> Đang áp dụng giảm giá Lễ hội</span>
@endsection