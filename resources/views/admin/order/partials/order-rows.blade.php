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
            <a class="btn btn-sm btn-info text-white" href="{{ route('admin.orders.show', $order->id) }}">
                Chi tiết
            </a>
            <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="m-0">
                @csrf
                <select name="status" class="form-select form-select-sm text-dark" style="font-size:0.85rem;" onchange="this.form.submit()">
                    <option value="1" {{ $order->status == 1 ? 'selected' : '' }}>Đang lấy hàng</option>
                    <option value="3" {{ $order->status == 3 ? 'selected' : '' }}>Đang giao hàng</option>
                    <option value="4" {{ $order->status == 4 ? 'selected' : '' }}>Hoàn tất</option>
                    <option value="5" {{ $order->status == 5 ? 'selected' : '' }}>Hoàn hàng</option>
                </select>
            </form>
            @if($order->status == 5)
            <a href="{{ route('admin.orders.return', $order->id) }}"
               class="btn btn-sm btn-warning rounded-0 text-dark fw-bold"
               style="font-size:0.75rem; white-space:nowrap;">
                <i class="fa fa-rotate-left me-1"></i>Xử lý hoàn
            </a>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-4 text-muted">Không tìm thấy đơn hàng nào.</td>
</tr>
@endforelse
