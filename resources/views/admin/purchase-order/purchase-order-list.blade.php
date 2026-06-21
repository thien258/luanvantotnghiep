@extends('layout/admin')
@section('body')

<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h5 class="font-weight-bold text-dark mb-0">
        <i class="fa-solid fa-cart-flatbed mr-2 text-muted"></i>Danh sách đơn đặt hàng
    </h5>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-0">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
@endif

<div class="card shadow-none border rounded-0">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0 small">
            <thead class="table-light">
                <tr>
                    <th class="pl-4 py-2">Mã đơn</th>
                    <th class="py-2">Nhà sản xuất</th>
                    <th class="text-center py-2">Tổng tiền</th>
                    <th class="text-center py-2">Trạng thái</th>
                    <th class="text-center py-2">Ngày tạo</th>
                    <th class="text-center py-2">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="pl-4 py-2 font-weight-bold">{{ $order->order_code }}</td>
                    <td class="py-2">{{ $order->manufacturer->name ?? '—' }}</td>
                    <td class="text-center py-2 text-success font-weight-bold">
                        {{ number_format($order->total_amount, 0, ',', '.') }}₫
                    </td>
                    <td class="text-center py-2">
                        @php
                            $badge = [
                                'pending'    => 'badge-warning',
                                'confirmed'  => 'badge-primary',
                                'delivering' => 'badge-info',
                                'received'   => 'badge-success',
                                'cancelled'  => 'badge-danger',
                            ][$order->status] ?? 'badge-secondary';
                            $label = [
                                'pending'    => 'Chờ xác nhận',
                                'confirmed'  => 'Đã xác nhận',
                                'delivering' => 'Đang giao',
                                'received'   => 'Đã nhận',
                                'cancelled'  => 'Đã hủy',
                            ][$order->status] ?? $order->status;
                        @endphp
                        <span class="badge {{ $badge }} rounded-0 px-2 py-1 text-white" style="font-size:0.7rem;">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="text-center py-2 text-muted">{{ $order->created_at->format('d/m/Y') }}</td>
                    <td class="text-center py-2">
                        <a href="{{ route('admin.purchase-orders.show', $order->id) }}"
                           class="btn btn-outline-dark btn-sm rounded-0 px-2 py-1" style="font-size:0.75rem;">
                            Xem
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Chưa có đơn đặt hàng nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $orders->links() }}
</div>

@endsection
