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
                    <a class="nav-link active rounded-0 border-0 fw-bold small text-uppercase px-3 py-3 text-danger"
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
            </ul>
        </div>

        <div class="card-body tab-content" id="warehouseTabContent">

            {{-- TAB 1: CẢNH BÁO BÁN CHẬM --}}
            <div class="tab-pane fade show active" id="content-bancham">
                <div class="alert alert-warning rounded-0 border-0 small mb-3">
                    <i class="fa fa-lightbulb me-1"></i> <strong>Gợi ý hệ thống:</strong>
                    Danh sách sản phẩm nhập kho từ <strong>7 ngày trở lên</strong> mà tỷ lệ bán chưa đạt <strong>30%</strong>.
                    Hệ thống đề xuất lập tức bật <strong>Flash Sale</strong> hoặc <strong>Giảm giá</strong> để giải phóng kho.
                </div>
                <div class="table-responsive">
                    <table class="table align-middle small text-start">
                        <thead class="table-light text-uppercase" style="font-size:0.75rem;">
                            <tr>
                                <th>Sản phẩm ứ đọng</th>
                                <th class="text-center">Lượng nhập</th>
                                <th class="text-center">Đã tiêu thụ</th>
                                <th class="text-center">Tỉ lệ bán</th>
                                <th class="text-center">Đề xuất</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slowProducts as $sp)
                            <tr>
                                <td><span class="fw-bold text-dark">{{ $sp->title }}</span></td>
                                <td class="text-center fw-bold text-secondary">{{ $sp->total_import }}</td>
                                <td class="text-center fw-bold text-danger">{{ $sp->total_sold }}</td>
                                <td class="text-center">
                                    <span class="badge bg-danger rounded-0" style="font-size:0.65rem;">{{ $sp->sale_rate }}%</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-0 px-2 py-1" style="font-size:0.65rem;">
                                        <i class="fa fa-bolt me-1"></i> CẦN GIẢM GIÁ SALE
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Không có sản phẩm bán chậm (tất cả SP nhập từ 7 ngày trước đã bán > 30%).</td>
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
                                    <span class="badge bg-success rounded-0" style="font-size:0.65rem;">Nhập kho</span>
                                    @else
                                    <span class="badge bg-primary rounded-0" style="font-size:0.65rem;">Xuất bán</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-success">+{{ $log->quantity }}</td>
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

        </div>
    </div>
</div>
@endsection
