@extends('layout/admin')
@section('body')

<div class="d-flex align-items-center mb-4">
    <div>
        <h5 class="font-weight-bold text-dark mb-1 text-uppercase" style="letter-spacing:1px;">
            Báo cáo Doanh thu — Năm {{ $year }}
        </h5>
        <p class="text-muted small mb-0">Doanh thu bán hàng, chi phí nhập hàng và lợi nhuận theo tháng</p>
    </div>
</div>

{{-- ── 3 THẺ TỔNG QUAN ──────────────────────────────────────────────────── --}}
<div class="row text-left">

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow-none border rounded-0 border-left-success h-100" style="border-left-width:4px !important;">
            <div class="card-body py-3">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1 small">
                    <i class="fa-solid fa-arrow-trend-up mr-1"></i> Tổng doanh thu (đơn hoàn tất)
                </div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                    {{ number_format($totalRevenue, 0, ',', '.') }}₫
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow-none border rounded-0 border-left-danger h-100" style="border-left-width:4px !important;">
            <div class="card-body py-3">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1 small">
                    <i class="fa-solid fa-arrow-trend-down mr-1"></i> Tổng chi phí nhập hàng
                </div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                    {{ number_format($totalImportCost, 0, ',', '.') }}₫
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow-none border rounded-0 h-100"
             style="border-left: 4px solid {{ $totalProfit >= 0 ? '#1cc88a' : '#e74a3b' }} !important;">
            <div class="card-body py-3">
                <div class="text-xs font-weight-bold text-uppercase mb-1 small"
                     style="color: {{ $totalProfit >= 0 ? '#1cc88a' : '#e74a3b' }}">
                    <i class="fa-solid fa-scale-balanced mr-1"></i> Lợi nhuận gộp (doanh thu − nhập hàng)
                </div>
                <div class="h4 mb-0 font-weight-bold {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ ($totalProfit >= 0 ? '' : '-') . number_format(abs($totalProfit), 0, ',', '.') }}₫
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── BIỂU ĐỒ SO SÁNH THEO THÁNG ─────────────────────────────────────── --}}
<div class="card shadow-none border rounded-0 mb-4 text-left">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-dark text-uppercase small" style="letter-spacing:1px;">
            <i class="fa-solid fa-chart-bar mr-2 text-muted"></i>
            Doanh thu vs Chi phí nhập hàng theo tháng (Triệu VNĐ)
        </h6>
        <span class="small text-muted">Năm {{ $year }}</span>
    </div>
    <div class="card-body">
        <div style="height:300px; position:relative;">
            <canvas id="directorBarChart"
                data-revenue="{{ htmlspecialchars(json_encode($monthlyRevenue), ENT_QUOTES) }}"
                data-cost="{{ htmlspecialchars(json_encode($monthlyImportCost), ENT_QUOTES) }}"
                data-profit="{{ htmlspecialchars(json_encode($monthlyProfit), ENT_QUOTES) }}">
            </canvas>
        </div>
    </div>
</div>

{{-- ── BẢNG CHI TIẾT THEO THÁNG ────────────────────────────────────────── --}}
<div class="card shadow-none border rounded-0 mb-4 text-left">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="m-0 font-weight-bold text-dark text-uppercase small" style="letter-spacing:1px;">
            <i class="fa-solid fa-table mr-2 text-muted"></i>
            Chi tiết từng tháng
        </h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 small text-dark table-hover">
            <thead class="table-light">
                <tr>
                    <th class="pl-4 py-2">Tháng</th>
                    <th class="text-right py-2">Doanh thu</th>
                    <th class="text-right py-2">Chi phí nhập hàng</th>
                    <th class="text-right pr-4 py-2">Lợi nhuận gộp</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $months = ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'];
                @endphp
                @foreach($months as $i => $label)
                @php
                    $rev    = $monthlyRevenue[$i]    * 1_000_000;
                    $cost   = $monthlyImportCost[$i] * 1_000_000;
                    $profit = $monthlyProfit[$i]      * 1_000_000;
                    $isEmpty = ($rev == 0 && $cost == 0);
                @endphp
                <tr class="{{ $isEmpty ? 'text-muted' : '' }}">
                    <td class="pl-4 py-2 font-weight-bold">{{ $label }}</td>
                    <td class="text-right py-2 {{ $rev > 0 ? 'text-success font-weight-bold' : '' }}">
                        {{ $rev > 0 ? number_format($rev, 0, ',', '.') . '₫' : '—' }}
                    </td>
                    <td class="text-right py-2 {{ $cost > 0 ? 'text-danger' : '' }}">
                        {{ $cost > 0 ? number_format($cost, 0, ',', '.') . '₫' : '—' }}
                    </td>
                    <td class="text-right pr-4 py-2 font-weight-bold {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                        @if(!$isEmpty)
                            {{ ($profit >= 0 ? '+' : '') . number_format($profit, 0, ',', '.') }}₫
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light font-weight-bold">
                <tr>
                    <td class="pl-4 py-2">Cả năm</td>
                    <td class="text-right py-2 text-success">{{ number_format($totalRevenue, 0, ',', '.') }}₫</td>
                    <td class="text-right py-2 text-danger">{{ number_format($totalImportCost, 0, ',', '.') }}₫</td>
                    <td class="text-right pr-4 py-2 {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ ($totalProfit >= 0 ? '+' : '') . number_format($totalProfit, 0, ',', '.') }}₫
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ── CHART.JS ─────────────────────────────────────────────────────────── --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('directorBarChart');
    if (!canvas) return;

    const revenue = JSON.parse(canvas.dataset.revenue);
    const cost    = JSON.parse(canvas.dataset.cost);
    const profit  = JSON.parse(canvas.dataset.profit);

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
            datasets: [
                {
                    label: 'Doanh thu',
                    data: revenue,
                    backgroundColor: 'rgba(28, 200, 138, 0.7)',
                    borderColor: '#1cc88a',
                    borderWidth: 1,
                    borderRadius: 2,
                },
                {
                    label: 'Chi phí nhập hàng',
                    data: cost,
                    backgroundColor: 'rgba(231, 74, 59, 0.65)',
                    borderColor: '#e74a3b',
                    borderWidth: 1,
                    borderRadius: 2,
                },
                {
                    label: 'Lợi nhuận gộp',
                    data: profit,
                    type: 'line',
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.08)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#4e73df',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 14 } },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toFixed(1)}M₫`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 10 },
                        callback: v => v + 'M'
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>

@endsection
