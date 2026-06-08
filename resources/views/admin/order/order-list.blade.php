@extends('layout/admin')
@section('body')

<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 class="mb-0 text-dark font-weight-bold" style="font-size: 1.3rem;">Quản Lý Đơn Hàng</h5>
            <form method="GET" action="{{ route('admin.orders.index') }}" class="d-flex align-items-center gap-2">
                <select name="status" class="form-select form-select-sm" style="width:200px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="1" {{ $status == '1' ? 'selected' : '' }}>Đang Lấy Hàng</option>
                    <option value="3" {{ $status == '3' ? 'selected' : '' }}>Đang Giao Hàng</option>
                    <option value="4" {{ $status == '4' ? 'selected' : '' }}>Hoàn Tất</option>
                    <option value="5" {{ $status == '5' ? 'selected' : '' }}>Hoàn Hàng</option>
                    <option value="6" {{ $status == '6' ? 'selected' : '' }}>Hàng Hỏng</option>
                </select>
            </form>
        </div>

        @if(session('success'))
        <div class="alert alert-success text-start py-2">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger text-start py-2">
            <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
        @endif

        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0 bg-white">
                <thead>
                    <tr class="text-dark bg-secondary-subtle" style="font-size: 0.9rem;">
                        <th style="width: 90px;">Mã Đơn</th>
                        <th>Khách Hàng</th>
                        <th>Số Điện Thoại</th>
                        <th>Tổng Tiền</th>
                        <th class="text-center">Hình Thức</th>
                        <th class="text-center" style="width: 180px;">Trạng Thái</th>
                        <th>Ngày Đặt</th>
                        <th class="text-center" style="width: 180px;">Hành Động</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.9rem;">
                    @forelse($orders as $order)
                    <tr>
                        <td><strong>#DH{{ $order->id }}</strong></td>
                        <td>{{ $order->fullname }}</td>
                        <td>{{ $order->phone }}</td>
                        <td class="text-danger fw-bold">{{ number_format($order->total_price) }}đ</td>

                        <td class="text-center">
                            @if($order->payment_method === 'BANK TRANSFER')
                            <span class="badge bg-primary text-white px-2 py-1" style="font-size:0.75rem;">
                                <i class="fa-solid fa-building-columns me-1"></i>Bank Transfer
                            </span>
                            @elseif($order->payment_method === 'COD')
                            <span class="badge bg-warning text-dark px-2 py-1" style="font-size:0.75rem;">
                                <i class="fa-solid fa-money-bill me-1"></i>COD
                            </span>
                            @else
                            <span class="badge bg-secondary text-white px-2 py-1" style="font-size:0.75rem;">{{ $order->payment_method }}</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($order->status == 1)
                            <span class="badge bg-warning text-dark w-100" style="font-size:0.8rem; padding:6px 8px;">Đang Lấy Hàng</span>
                            @elseif($order->status == 3)
                            <span class="badge bg-primary text-white w-100" style="font-size:0.8rem; padding:6px 8px;">Đang Giao Hàng</span>
                            @elseif($order->status == 4)
                            <span class="badge bg-success text-white w-100" style="font-size:0.8rem; padding:6px 8px;">Hoàn Tất</span>
                            @elseif($order->status == 5)
                            <span class="badge bg-secondary text-white w-100" style="font-size:0.8rem; padding:6px 8px;">Hoàn Hàng</span>
                            @elseif($order->status == 6)
                            <span class="badge bg-danger text-white w-100" style="font-size:0.8rem; padding:6px 8px;">Hàng Hỏng</span>
                            @else
                            <span class="badge bg-secondary text-white w-100" style="font-size:0.8rem; padding:6px 8px;">Không xác định</span>
                            @endif
                        </td>

                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>

                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a class="btn btn-sm btn-info text-white rounded-0"
                                    href="{{ route('admin.orders.show', $order->id) }}">
                                    Chi tiết
                                </a>

                                {{-- NÚT XỬ LÝ HOÀN HÀNG: Chỉ xuất hiện khi đơn hàng có trạng thái Đang Giao (status = 3) --}}
                                @if($order->status == 5 )
                                <a class="btn btn-sm btn-warning text-dark fw-bold rounded-0"
                                    href="{{ route('admin.orders.return', $order->id) }}">
                                    <i class="fa fa-rotate-left me-1"></i>Xử lý hoàn
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Hệ thống chưa ghi nhận đơn hàng nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection