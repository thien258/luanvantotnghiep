@extends('layout/admin')
@section('body')

{{-- ── THẺ TỔNG QUAN ──────────────────────────────────────────────────── --}}
<div class="row mt-4 text-left">

    {{-- Tổng doanh thu — chỉ hiển thị nếu có dữ liệu (không hiển thị với admin) --}}
    @if(!is_null($totalRevenue))
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
    @endif

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

{{-- ── BIỂU ĐỒ DOANH THU THEO THÁNG — chỉ khi có dữ liệu doanh thu ─── --}}
@if(!is_null($totalRevenue))
<div class="card shadow-none border rounded-0 mb-4 text-left">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-dark text-uppercase small" style="letter-spacing:1px;">
            <i class="fa-solid fa-chart-bar mr-2 text-muted"></i>
            Doanh thu theo tháng — năm {{ now()->year }} (VNĐ)
        </h6>
        @php $totalMonthly = array_sum($monthlyRevenue); @endphp
        <span class="small text-muted">
            Tổng năm: <strong>{{ number_format($totalMonthly, 0, ',', '.') }}₫</strong>
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
@endif

{{-- ── BẢNG TOP SẢN PHẨM + KHO ─────────────────────────────────────── --}}
<div class="row text-left">

    {{-- Top 5 sản phẩm bán chạy --}}
    <div class="col-lg-6 mb-4">
        <div class="card shadow-none border rounded-0 border-left-success" style="border-left-width:4px !important;">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 font-weight-bold text-success text-uppercase small" style="letter-spacing:1px;">
                    <i class="fa-solid fa-fire mr-2"></i>Top 5 sản phẩm bán chạy (30 ngày gần nhất)
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small text-dark table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="pl-4 py-2">#</th>
                            <th class="py-2">Tên sản phẩm</th>
                            <th class="text-center py-2">Đã bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSelling as $i => $sp)
                        <tr>
                            <td class="pl-4 py-2 text-muted">{{ $i + 1 }}</td>
                            <td class="py-2 font-weight-bold">{{ $sp->title }}</td>
                            <td class="text-center text-success font-weight-bold">{{ $sp->total_sold }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Chưa có dữ liệu trong 30 ngày</td>
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
                    <i class="fa-solid fa-hourglass-half mr-2"></i>Sản phẩm bán chậm (≤ 30% lượng nhập sau 30 ngày)
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small text-dark table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="pl-4 py-2">Tên sản phẩm</th>
                            <th class="text-center py-2">Đã bán / Đã nhập</th>
                            <th class="text-center py-2">Tỷ lệ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slowProducts as $sp)
                        <tr>
                            <td class="pl-4 py-2 font-weight-bold">{{ $sp->title }}</td>
                            <td class="text-center font-weight-bold {{ $sp->sold_30 == 0 ? 'text-danger' : 'text-warning' }}">
                                {{ $sp->sold_30 }} / {{ $sp->stock > 0 ? $sp->stock : '—' }}
                            </td>
                            <td class="text-center">
                                @php
                                    $ratio = ($sp->stock > 0) ? round($sp->sold_30 / $sp->stock * 100, 1) : 0;
                                @endphp
                                <span class="badge badge-danger">
                                    {{ $ratio }}%
                                </span>
                            </td>
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

    {{-- Sắp hết hạn (HSD) --}}
    <div class="col-lg-6 mb-4">
        <div class="card shadow-none border rounded-0" style="border-left:4px solid #dc3545 !important;">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-danger text-uppercase small" style="letter-spacing:1px;">
                    <i class="fa-solid fa-clock mr-2"></i>Lô hàng sắp hết hạn (365 ngày)
                </h6>
                <a href="{{ route('admin.product.warehouse.index') }}" class="small text-muted">Xem tất cả →</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small text-dark table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="pl-4 py-2">Tên sản phẩm</th>
                            <th class="text-center py-2">Còn lại</th>
                            <th class="text-center py-2">HSD</th>
                            <th class="text-center py-2">Ngày còn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expiringBatches as $item)
                        <tr class="{{ $item->days_left <= 30 ? 'table-danger' : ($item->days_left <= 365 ? 'table-danger' : 'table-warning') }}">
                            <td class="pl-4 py-2 font-weight-bold">
                                {{ $item->product->title }}
                                @if($item->days_left <= 30)
                                    <span class="badge badge-danger ml-1" style="font-size:0.6rem;">🔴 RẤT GẤP</span>
                                @endif
                            </td>
                            <td class="text-center font-weight-bold">{{ $item->qty_left }}</td>
                            <td class="text-center {{ $item->days_left <= 30 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                {{ \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') }}
                            </td>
                            <td class="text-center font-weight-bold {{ $item->days_left <= 30 ? 'text-danger' : 'text-warning' }}">
                                {{ $item->days_left }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Không có lô hàng sắp hết hạn</td>
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
