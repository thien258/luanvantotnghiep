@extends('layout/admin')
@section('body')

{{-- ── THẺ TỔNG QUAN ──────────────────────────────────────────────────── --}}
<div class="row mt-4 text-left">

    {{-- Tổng doanh thu --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-none border rounded-0 border-left-success h-100" style="border-left-width:4px !important;">
            <div class="card-body py-3">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1 small">Doanh thu (đơn hoàn tất)</div>
                <div class="h5 mb-0 font-weight-bold text-dark">
                    {{ number_format($totalRevenue, 0, ',', '.') }}₫
                </div>
            </div>
        </div>
    </div>

    {{-- Tổng đơn hàng --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-none border rounded-0 border-left-primary h-100" style="border-left-width:4px !important;">
            <div class="card-body py-3">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1 small">Tổng đơn hàng</div>
                <div class="h5 mb-0 font-weight-bold text-dark">{{ number_format($totalOrders) }}</div>
            </div>
        </div>
    </div>

    {{-- Tổng người dùng --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-none border rounded-0 border-left-info h-100" style="border-left-width:4px !important;">
            <div class="card-body py-3">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1 small">Người dùng</div>
                <div class="h5 mb-0 font-weight-bold text-dark">{{ number_format($totalUsers) }}</div>
            </div>
        </div>
    </div>

    {{-- Tổng sản phẩm --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow-none border rounded-0 border-left-warning h-100" style="border-left-width:4px !important;">
            <div class="card-body py-3">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1 small">Sản phẩm đang bán</div>
                <div class="h5 mb-0 font-weight-bold text-dark">{{ number_format($totalProducts) }}</div>
            </div>
        </div>
    </div>

</div>

{{-- ── BIỂU ĐỒ DOANH THU THEO THÁNG ──────────────────────────────────── --}}
<div class="card shadow-none border rounded-0 mb-4 text-left">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-dark text-uppercase small" style="letter-spacing:1px;">
            <i class="fa-solid fa-chart-bar mr-2 text-muted"></i>
            Doanh thu theo tháng — năm {{ now()->year }} (Triệu VNĐ)
        </h6>
        @php
            $totalMonthly = array_sum($monthlyRevenue);
        @endphp
        <span class="small text-muted">
            Tổng năm: <strong>{{ number_format($totalMonthly, 1) }}M</strong>
        </span>
    </div>
    <div class="card-body">
        <div style="height:260px; position:relative;">
            {{-- data-monthly truyền mảng PHP sang JS, không dùng script inline --}}
            <canvas id="dashboardBarChart"
                data-monthly="{{ htmlspecialchars(json_encode($monthlyRevenue), ENT_QUOTES) }}">
            </canvas>
        </div>
    </div>
</div>

{{-- ── BẢNG TOP SẢN PHẨM + KHO ─────────────────────────────────────── --}}
<div class="row text-left">

    {{-- Top 5 sản phẩm bán chạy --}}
    <div class="col-lg-6 mb-4">
        <div class="card shadow-none border rounded-0 border-left-success" style="border-left-width:4px !important;">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 font-weight-bold text-success text-uppercase small" style="letter-spacing:1px;">
                    <i class="fa-solid fa-fire mr-2"></i>Top 5 sản phẩm bán chạy
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small text-dark table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="pl-4 py-2">#</th>
                            <th class="py-2">Tên sản phẩm</th>
                            <th class="text-center py-2">Đã bán</th>
                            <th class="text-center py-2">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSelling as $i => $sp)
                        <tr>
                            <td class="pl-4 py-2 text-muted">{{ $i + 1 }}</td>
                            <td class="py-2 font-weight-bold">{{ $sp->title }}</td>
                            <td class="text-center text-success font-weight-bold">{{ $sp->total_sold }}</td>
                            <td class="text-center text-muted">
                                {{ number_format($sp->total_revenue, 0, ',', '.') }}₫
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sản phẩm bán chậm --}}
    <div class="col-lg-6 mb-4">
        <div class="card shadow-none border rounded-0 border-left-danger" style="border-left-width:4px !important;">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 font-weight-bold text-danger text-uppercase small" style="letter-spacing:1px;">
                    <i class="fa-solid fa-hourglass-half mr-2"></i>Sản phẩm bán chậm (tỷ lệ bán ≤ 5%)
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small text-dark table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="pl-4 py-2">Tên sản phẩm</th>
                            <th class="text-center py-2">Đã bán</th>
                            <th class="text-center py-2">Tồn kho</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slowProducts as $sp)
                        <tr>
                            <td class="pl-4 py-2 font-weight-bold">{{ $sp->title }}</td>
                            <td class="text-center text-muted font-weight-bold">{{ $sp->total_sold }}</td>
                            <td class="text-center text-danger font-weight-bold">{{ $sp->quantity }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Không có sản phẩm bán chậm</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sản phẩm sắp hết kho --}}
    <div class="col-lg-6 mb-4">
        <div class="card shadow-none border rounded-0 border-left-warning" style="border-left-width:4px !important;">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 font-weight-bold text-warning text-uppercase small" style="letter-spacing:1px;">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i>Sản phẩm sắp hết kho (tồn &lt; 5)
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small text-dark table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="pl-4 py-2">Tên sản phẩm</th>
                            <th class="text-center py-2">Tồn kho</th>
                            <th class="text-center py-2">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $lp)
                        <tr>
                            <td class="pl-4 py-2 font-weight-bold">{{ $lp->title }}</td>
                            <td class="text-center font-weight-bold
                                {{ $lp->quantity < 3 ? 'text-danger' : 'text-warning' }}">
                                {{ $lp->quantity }}
                            </td>
                            <td class="text-center py-2">
                                @if($lp->quantity <= 2)
                                    <span class="badge badge-danger rounded-0 px-2 py-1" style="font-size:0.65rem;">
                                        CẦN NHẬP NGAY
                                    </span>
                                @else
                                    <span class="badge badge-warning text-white rounded-0 px-2 py-1" style="font-size:0.65rem;">
                                        SẮP HẾT
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Kho hàng ổn định</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ── CHART.JS ─────────────────────────────────────────────────────── --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/admin/dashboard-chart.js') }}"></script>

@endsection
