@extends('layout/admin')
@section('body')
<div class="container-fluid px-4 py-3">
    <h4 class="mb-4 text-dark"><i class="fa fa-warehouse me-2"></i>Quản Lý Kho & Đối Soát Bán Chậm</h4>

    @if(session('success'))
    <div class="alert alert-success rounded-0 border-0 mb-3 small">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger rounded-0 border-0 mb-3 small">{{ session('error') }}</div>
    @endif

    <div class="card rounded-0 border shadow-sm">
        <div class="card-header bg-white p-0">
            <ul class="nav nav-tabs rounded-0 border-0" id="warehouseTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active rounded-0 border-0 fw-bold small text-uppercase px-3 py-3"
                        style="color:#b45309;"
                        id="bancham-tab" data-toggle="tab" href="#content-bancham" role="tab">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Cảnh báo Sale (Bán chậm)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-0 border-0 fw-bold small text-uppercase px-3 py-3 text-secondary"
                        id="biendong-tab" data-toggle="tab" href="#content-biendong" role="tab">
                        <i class="fa-solid fa-history me-1"></i> Biến động kho
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-0 border-0 fw-bold small text-uppercase px-3 py-3 text-secondary"
                        id="lichsu-tab" data-toggle="tab" href="#content-lichsu" role="tab">
                        <i class="fa-solid fa-list-alt me-1"></i> Lịch sử nhập kho
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-0 border-0 fw-bold small text-uppercase px-3 py-3 text-warning"
                        id="hsd-tab" data-toggle="tab" href="#content-hsd" role="tab">
                        <i class="fa-solid fa-clock me-1"></i> Sắp hết hạn
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body tab-content" id="warehouseTabContent">

            {{-- TAB 1: CẢNH BÁO BÁN CHẬM --}}
            <div class="tab-pane fade show active" id="content-bancham">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="alert alert-warning rounded-0 border-0 small mb-0 flex-grow-1 me-3">
                        <i class="fa fa-lightbulb me-1"></i> <strong>Gợi ý hệ thống:</strong>
                        Sản phẩm bán được <strong>≤ 30%</strong> số lượng nhập sau <strong>30 ngày</strong> kể từ lần nhập gần nhất.
                        Hệ thống đề xuất lập tức bật <strong>Flash Sale</strong> hoặc <strong>Giảm giá</strong> để giải phóng kho.
                    </div>
                    <button type="button" class="btn btn-success btn-sm rounded-0 text-nowrap"
                        data-toggle="modal" data-target="#modalAttachFestivalSlow">
                        <i class="fa fa-tag me-1"></i> Phân vào Festival
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle small text-start">
                        <thead class="table-light text-uppercase" style="font-size:0.75rem;">
                            <tr>
                                <th>Sản phẩm ứ đọng</th>
                                <th class="text-center">Lượng nhập</th>
                                <th class="text-center">Đã tiêu thụ</th>
                                <th class="text-center">Tỉ lệ bán</th>
                                <th class="text-center">Ngày nhập</th>
                                <th class="text-center">Festival</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($slowProducts as $sp)
                                            <tr>
                                                <td><span class="fw-bold text-dark">{{ $sp->title }}</span></td>
                                                <td class="text-center fw-bold" style="color:#6b7280;">{{ $sp->total_import }}</td>
                                                <td class="text-center fw-bold" style="color:#92400e;">{{ $sp->total_sold }}</td>
                                                <td class="text-center">
                                                    @php $rate = $sp->sale_rate; @endphp
                                                    @if($rate >= 20)
                                                        <span class="rounded-0 px-2 py-1" style="font-size:0.65rem;font-weight:600;background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;">{{ $rate }}%</span>
                                                    @elseif($rate >= 10)
                                                        <span class="rounded-0 px-2 py-1" style="font-size:0.65rem;font-weight:600;background:#fef3c7;color:#92400e;border:1px solid #fcd34d;">{{ $rate }}%</span>
                                                    @else
                                                        <span class="rounded-0 px-2 py-1" style="font-size:0.65rem;font-weight:600;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;">{{ $rate }}%</span>
                                                    @endif
                                                </td>
                                                <td class="text-center" style="font-size:0.75rem;">
                                                    @if($sp->last_import_at)
                                                        {{ $sp->last_import_at->format('d/m/Y') }}
                                                        <br><span class="text-muted">{{ $sp->days_since }} ngày trước</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($sp->festivals->isNotEmpty())
                                                        @foreach($sp->festivals as $fes)
                                                            <span class="badge bg-success text-white rounded-0 mb-1" style="font-size:0.65rem;">
                                                                🎉 {{ $fes->name }} @if($fes->discount)(-{{ $fes->discount }}%)@endif
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span class="rounded-0 px-2 py-1 small" style="font-size:0.65rem;font-weight:600;background:#fef3c7;color:#92400e;border:1px solid #fcd34d;">
                                                            ⚡ Chưa có Festival
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Không có sản phẩm bán chậm (tất cả SP nhập đã bán > 30% sau 30 ngày).</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 2: BIẾN ĐỘNG KHO --}}
            <div class="tab-pane fade" id="content-biendong">
                <div class="table-responsive">
                    <table class="table align-middle small text-start">
                        <thead class="table-light text-uppercase" style="font-size:0.75rem;">
                            <tr>
                                <th>Thời gian</th>
                                <th>Sản phẩm</th>
                                <th class="text-center">Hành động</th>
                                <th class="text-center">S.Lượng</th>
                                <th class="text-center">Stock sau</th>
                                <th>Lý do</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockLogs as $log)
                            <tr>
                                <td style="font-size:0.75rem;">{{ $log->created_at->format('d/m H:i') }}</td>
                                <td><span class="fw-bold text-dark">{{ $log->product->title ?? 'Đã xóa' }}</span></td>
                                <td class="text-center">
                                    @if($log->type == 'import')
                                    <span class="rounded-0 px-2 py-1" style="font-size:0.65rem;font-weight:600;background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;">↑ Nhập kho</span>
                                    @else
                                    <span class="rounded-0 px-2 py-1" style="font-size:0.65rem;font-weight:600;background:#e0f2fe;color:#0369a1;border:1px solid #7dd3fc;">↓ Xuất bán</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold" style="color:#059669;">+{{ $log->quantity }}</td>
                                <td class="text-center fw-bold text-dark bg-light">{{ $log->stock_after }}</td>
                                <td><span class="text-muted" style="font-size:0.8rem;">{{ $log->reason }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Chưa có bản ghi biến động kho nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 3: LỊCH SỬ NHẬP KHO --}}
            <div class="tab-pane fade" id="content-lichsu">
                <div class="table-responsive">
                    <table class="table align-middle small text-start">
                        <thead class="table-light text-uppercase" style="font-size:0.75rem;">
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Nhà cung cấp</th>
                                <th class="text-center">Số mặt hàng</th>
                                <th>Ghi chú</th>
                                <th>Ngày giờ nhập</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receipt as $rc)
                            <tr>
                                <td><span class="fw-bold text-primary">#{{ $rc->receipt_code }}</span></td>
                                <td>{{ $rc->supplier ?? '---' }}</td>
                                <td class="text-center fw-bold">{{ $rc->total_items }}</td>
                                <td><span class="text-muted" style="font-size:0.8rem;">{{ $rc->note ?? '---' }}</span></td>
                                <td>{{ $rc->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Chưa thực hiện đợt nhập kho nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 4: SẮP HẾT HẠN --}}
            <div class="tab-pane fade" id="content-hsd">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="alert alert-warning rounded-0 border-0 small mb-0 flex-grow-1 me-3">
                        <i class="fa fa-clock me-1"></i> <strong>Cảnh báo HSD:</strong>
                        Danh sách lô hàng còn tồn kho và sẽ hết hạn trong vòng <strong>2 năm</strong>.
                        🔴 <strong>Trong năm nay</strong> (≤ 365 ngày) — cần xử lý ngay.
                        🟡 <strong>Năm sau</strong> (366–730 ngày) — theo dõi, lên kế hoạch sale sớm.
                    </div>
                    <button type="button" class="btn btn-success btn-sm rounded-0 text-nowrap"
                        data-toggle="modal" data-target="#modalAttachFestival">
                        <i class="fa fa-tag me-1"></i> Phân vào Festival
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle small text-start">
                        <thead class="table-light text-uppercase" style="font-size:0.75rem;">
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-center">Còn lại</th>
                                <th class="text-center">Ngày HSD</th>
                                <th class="text-center">Số ngày còn lại</th>
                                <th class="text-center">Mức độ</th>
                                <th class="text-center">Festival</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiring as $item)
                            <tr class="{{ $item->days_left <= 30 ? 'table-danger' : ($item->days_left <= 365 ? 'table-danger' : 'table-warning') }}">
                                <td>
                                    <span class="fw-bold text-dark">{{ $item->product->title }}</span>
                                   
                                </td>
                                <td class="text-center fw-bold">{{ $item->qty_left }} chai</td>
                                <td class="text-center {{ $item->days_left <= 30 ? 'fw-bold text-danger' : '' }}">{{ \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') }}</td>
                                <td class="text-center fw-bold
                                    {{ $item->days_left <= 30 ? 'text-danger' : ($item->days_left <= 365 ? 'text-danger' : 'text-warning') }}">
                                    {{ $item->days_left }} ngày
                                </td>
                                <td class="text-center">
                                    @if($item->days_left <= 30)
                                        <span class="badge  rounded-0" style="font-size:0.65rem;">🔴 HẾT HẠN TRONG THÁNG NÀY</span>
                                    @elseif($item->days_left <= 365)
                                        <span class="badge rounded-0" style="font-size:0.65rem;">🔴 TRONG NĂM NAY</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-0" style="font-size:0.65rem;">🟡 THEO DÕI (NĂM SAU)</span>
                                    @endif
                                </td>
                                {{-- Cột Festival: hiện badge nếu SP đã được thêm vào festival nào --}}
                                <td class="text-center">
                                    @forelse($item->product->festivals as $fes)
                                    <span class="badge bg-info text-dark rounded-0 mb-1" style="font-size:0.65rem;">
                                        {{ $fes->name }}
                                    </span>
                                    @empty
                                    <span class="text-muted" style="font-size:0.75rem;">—</span>
                                    @endforelse
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Không có lô hàng nào sắp hết hạn.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
{{-- MODAL 2-BƯỚC: Phân SP bán chậm vào Festival --}}
<div class="modal fade" id="modalAttachFestivalSlow" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title mb-0"><i class="fa fa-tag me-2"></i>Phân sản phẩm bán chậm vào Festival</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                {{-- BƯỚC 1: Chọn festival --}}
                <div id="slow-step1">
                    <p class="fw-bold small text-uppercase text-muted mb-3">Bước 1 — Chọn chương trình khuyến mãi</p>
                    @forelse($festivals as $fes)
                    <div class="border rounded-0 p-3 mb-2 d-flex justify-content-between align-items-center festival-card"
                         style="cursor:pointer;"
                         data-id="{{ $fes->id }}"
                         data-name="{{ $fes->name }}"
                         data-discount="{{ $fes->discount }}"
                         data-target="slow">
                        <div>
                            <span class="fw-bold">{{ $fes->name }}</span>
                            <span class="badge bg-danger ms-2">-{{ $fes->discount }}%</span>
                            <br>
                            <small class="text-muted">{{ $fes->start_date->format('d/m/Y') }} → {{ $fes->end_date->format('d/m/Y') }}</small>
                        </div>
                        <i class="fa fa-chevron-right text-muted"></i>
                    </div>
                    @empty
                    <div class="alert alert-warning rounded-0 small">Không có festival đang active.</div>
                    @endforelse
                </div>

                {{-- BƯỚC 2: Chọn SP --}}
                <div id="slow-step2" style="display:none;">
                    <div class="d-flex align-items-center mb-3 gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-0" onclick="slowBackToStep1()">
                            <i class="fa fa-arrow-left me-1"></i> Quay lại
                        </button>
                        <span class="fw-bold small">Festival đã chọn: <span id="slow-fes-name" class="text-success"></span></span>
                    </div>
                    <form action="{{ route('admin.product.warehouse.attach-festival') }}" method="POST" id="slowFestivalForm">
                        @csrf
                        <input type="hidden" name="festival_id" id="slow-festival-id-input">
                        <p class="fw-bold small text-uppercase text-muted mb-2">Bước 2 — Chọn sản phẩm cần thêm vào</p>
                        <div style="max-height:350px; overflow-y:auto;">
                            <table class="table table-sm table-bordered small mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">✓</th>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Tỉ lệ bán</th>
                                        <th class="text-center">Festival hiện tại</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($slowProducts as $sp)
                                    @php
                                        $spFestivalIds = $sp->festivals->pluck('id')->toArray();
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="product_ids[]" value="{{ $sp->id }}"
                                                   class="slow-product-cb"
                                                   data-festival-ids="{{ implode(',', $spFestivalIds) }}">
                                        </td>
                                        <td class="fw-bold">{{ $sp->title }}</td>
                                        <td class="text-center">
                                            <span class="rounded-0 px-2 py-1" style="font-size:0.65rem;font-weight:600;background:#fef3c7;color:#92400e;border:1px solid #fcd34d;">{{ $sp->sale_rate }}%</span>
                                        </td>
                                        <td class="text-center">
                                            @if($sp->festivals->isNotEmpty())
                                                @foreach($sp->festivals as $fes)
                                                    <span class="badge bg-success rounded-0 mb-1" style="font-size:0.6rem;">
                                                        🎉 {{ $fes->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted" style="font-size:0.75rem;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary btn-sm rounded-0" data-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-success btn-sm rounded-0">
                                <i class="fa fa-check me-1"></i> Xác nhận thêm vào Festival
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
{{-- MODAL 2-BƯỚC: Phân SP sắp hết hạn vào Festival --}}
<div class="modal fade" id="modalAttachFestival" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content rounded-0">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title mb-0"><i class="fa fa-tag me-2"></i>Phân sản phẩm vào Festival</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                {{-- BƯỚC 1: Chọn festival --}}
                <div id="exp-step1">
                    <p class="fw-bold small text-uppercase text-muted mb-3">Bước 1 — Chọn chương trình khuyến mãi</p>
                    @forelse($festivals as $fes)
                    <div class="border rounded-0 p-3 mb-2 d-flex justify-content-between align-items-center festival-card"
                         style="cursor:pointer;"
                         data-id="{{ $fes->id }}"
                         data-name="{{ $fes->name }}"
                         data-discount="{{ $fes->discount }}"
                         data-target="exp">
                        <div>
                            <span class="fw-bold">{{ $fes->name }}</span>
                            <span class="badge bg-danger ms-2">-{{ $fes->discount }}%</span>
                            <br>
                            <small class="text-muted">{{ $fes->start_date->format('d/m/Y') }} → {{ $fes->end_date->format('d/m/Y') }}</small>
                        </div>
                        <i class="fa fa-chevron-right text-muted"></i>
                    </div>
                    @empty
                    <div class="alert alert-warning rounded-0 small">Không có festival đang active.</div>
                    @endforelse
                </div>

                {{-- BƯỚC 2: Chọn SP --}}
                <div id="exp-step2" style="display:none;">
                    <div class="d-flex align-items-center mb-3 gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-0" onclick="expBackToStep1()">
                            <i class="fa fa-arrow-left me-1"></i> Quay lại
                        </button>
                        <span class="fw-bold small">Festival đã chọn: <span id="exp-fes-name" class="text-success"></span></span>
                    </div>
                    <form action="{{ route('admin.product.warehouse.attach-festival') }}" method="POST" id="expFestivalForm">
                        @csrf
                        <input type="hidden" name="festival_id" id="exp-festival-id-input">
                        <p class="fw-bold small text-uppercase text-muted mb-2">Bước 2 — Chọn sản phẩm cần thêm vào</p>
                        <div style="max-height:350px; overflow-y:auto;">
                            <table class="table table-sm table-bordered small mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">✓</th>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Còn lại</th>
                                        <th class="text-center">HSD</th>
                                        <th class="text-center">Festival hiện tại</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expiring as $item)
                                    @php
                                        $itemFestivalIds = $item->product->festivals->pluck('id')->toArray();
                                    @endphp
                                    <tr class="{{ $item->days_left <= 365 ? 'table-danger' : 'table-warning' }}">
                                        <td class="text-center">
                                            <input type="checkbox" name="product_ids[]" value="{{ $item->product->id }}"
                                                   class="exp-product-cb"
                                                   data-festival-ids="{{ implode(',', $itemFestivalIds) }}">
                                        </td>
                                        <td class="fw-bold">{{ $item->product->title }}</td>
                                        <td class="text-center">{{ $item->qty_left }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            @if($item->product->festivals->isNotEmpty())
                                                @foreach($item->product->festivals as $fes)
                                                    <span class="badge bg-success rounded-0 mb-1" style="font-size:0.6rem;">
                                                        🎉 {{ $fes->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted" style="font-size:0.75rem;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary btn-sm rounded-0" data-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-success btn-sm rounded-0">
                                <i class="fa fa-check me-1"></i> Xác nhận thêm vào Festival
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Xử lý click vào card festival (cả 2 modal)
    document.querySelectorAll('.festival-card').forEach(function (card) {
        card.addEventListener('click', function () {
            var id       = this.dataset.id;
            var name     = this.dataset.name;
            var discount = this.dataset.discount;
            var target   = this.dataset.target; // 'slow' hoặc 'exp'

            if (target === 'slow') {
                document.getElementById('slow-festival-id-input').value = id;
                document.getElementById('slow-fes-name').textContent = name + ' (-' + discount + '%)';
                document.getElementById('slow-step1').style.display = 'none';
                document.getElementById('slow-step2').style.display = 'block';
                // Auto-check SP đã thuộc festival này
                document.querySelectorAll('.slow-product-cb').forEach(function (cb) {
                    var ids = cb.dataset.festivalIds ? cb.dataset.festivalIds.split(',').map(Number) : [];
                    cb.checked = ids.includes(Number(id));
                });
            } else {
                document.getElementById('exp-festival-id-input').value = id;
                document.getElementById('exp-fes-name').textContent = name + ' (-' + discount + '%)';
                document.getElementById('exp-step1').style.display = 'none';
                document.getElementById('exp-step2').style.display = 'block';
                // Auto-check SP đã thuộc festival này
                document.querySelectorAll('.exp-product-cb').forEach(function (cb) {
                    var ids = cb.dataset.festivalIds ? cb.dataset.festivalIds.split(',').map(Number) : [];
                    cb.checked = ids.includes(Number(id));
                });
            }
        });
    });

});

function slowBackToStep1() {
    document.getElementById('slow-step1').style.display = 'block';
    document.getElementById('slow-step2').style.display = 'none';
    document.querySelectorAll('.slow-product-cb').forEach(function (cb) { cb.checked = false; });
}
function expBackToStep1() {
    document.getElementById('exp-step1').style.display = 'block';
    document.getElementById('exp-step2').style.display = 'none';
    document.querySelectorAll('.exp-product-cb').forEach(function (cb) { cb.checked = false; });
}

$('#modalAttachFestivalSlow').on('hidden.bs.modal', function () { slowBackToStep1(); });
$('#modalAttachFestival').on('hidden.bs.modal', function () { expBackToStep1(); });
</script>
@endsection