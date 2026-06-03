@extends('layout/admin')
@section('body')

<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">

        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <h5 class="m-0 fw-bold text-dark">
                <i class="fa fa-triangle-exclamation text-danger me-2"></i>Danh Sách Hàng Hỏng / Lỗi
            </h5>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary rounded-0">
                ← Quay lại đơn hàng
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-0 py-2">{{ session('success') }}</div>
        @endif

        @if($orders->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fa fa-box-open fa-2x mb-3 d-block"></i>
                Chưa có hàng hỏng nào được ghi nhận.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white mb-0" style="font-size:0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Khách Hàng</th>
                        <th>Sản Phẩm Hỏng</th>
                        <th>Tổng Tiền</th>
                        <th>Ngày Hoàn</th>
                        <th>Ghi Chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td><strong>#DH{{ $order->id }}</strong></td>
                        <td>
                            <div class="fw-bold">{{ $order->fullname }}</div>
                            <div class="text-muted" style="font-size:0.8rem;">{{ $order->phone }}</div>
                        </td>
                        <td>
                            @foreach($order->detatil as $detail)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                @if($detail->product?->image)
                                    <img src="{{ $detail->product->image }}" style="width:35px;height:35px;object-fit:cover;" class="border">
                                @endif
                                <span>{{ $detail->product?->title ?? 'Đã xóa' }}
                                    <span class="text-muted">(x{{ $detail->quantity }})</span>
                                </span>
                            </div>
                            @endforeach
                        </td>
                        <td class="text-danger fw-bold">{{ number_format($order->total_price) }}đ</td>
                        <td>{{ $order->updated_at->format('d/m/Y H:i') }}</td>
                        <td class="text-muted fst-italic" style="font-size:0.8rem;">
                            {{ $order->note ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>

@endsection
