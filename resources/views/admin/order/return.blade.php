@extends('layout/admin')
@section('body')

<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary rounded-0">
                <i class="fa fa-arrow-left me-1"></i> Quay lại
            </a>
            <div class="text-center">
                <h5 class="m-0 fw-bold text-dark">XỬ LÝ HOÀN HÀNG</h5>
                <small class="text-muted">#DH{{ $order->id }} &nbsp;·&nbsp; {{ $order->created_at->format('d/m/Y H:i') }}</small>
            </div>
            <span class="badge bg-primary text-white px-3 py-2" style="font-size:0.8rem;">Đang giao hàng</span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
        @endif

        <div class="row g-4">

                {{-- CỘT TRÁI: HÓA ĐƠN --}}
                <div class="col-lg-6">

                    {{-- Thông tin khách --}}
                    <div class="card rounded-0 border bg-white mb-3">
                        <div class="card-header bg-white border-bottom py-2 px-3">
                            <span class="fw-bold text-uppercase text-muted" style="font-size:0.75rem; letter-spacing:1px;">
                                <i class="fa fa-user me-2"></i>Thông tin giao nhận
                            </span>
                        </div>
                        <div class="card-body px-3 py-3 small">
                            <div class="row g-2">
                                <div class="col-4 text-muted">Khách hàng:</div>
                                <div class="col-8 fw-bold">{{ $order->fullname }}</div>
                                <div class="col-4 text-muted">SĐT:</div>
                                <div class="col-8">{{ $order->phone }}</div>
                                <div class="col-4 text-muted">Địa chỉ:</div>
                                <div class="col-8">{{ $order->address }}</div>
                                <div class="col-4 text-muted">Hình thức:</div>
                                <div class="col-8">
                                    <span class="badge bg-dark text-white">{{ $order->payment_method }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Chi tiết sản phẩm --}}
                    <div class="card rounded-0 border bg-white">
                        <div class="card-header bg-white border-bottom py-2 px-3">
                            <span class="fw-bold text-uppercase text-muted" style="font-size:0.75rem; letter-spacing:1px;">
                                <i class="fa fa-box me-2"></i>Sản phẩm hoàn về
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Sản phẩm</th>
                                        <th class="text-center">SL</th>
                                        <th class="text-end pe-3">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orderdetail as $detail)
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <div class="d-flex align-items-center gap-2">
                                                @if($detail->product?->image)
                                                    <img src="{{ $detail->product->image }}" style="width:40px;height:40px;object-fit:cover;" class="border">
                                                @else
                                                    <div class="border bg-light d-flex align-items-center justify-content-center text-muted" style="width:40px;height:40px;font-size:0.55rem;">No Img</div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ $detail->product?->title ?? $detail->name }}</div>
                                                    <div class="text-muted" style="font-size:0.72rem;">{{ number_format($detail->price) }}đ/cái</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold">{{ $detail->quantity }}</td>
                                        <td class="text-end pe-3 fw-bold text-danger">{{ number_format($detail->price * $detail->quantity) }}đ</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-top">
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold ps-3 py-2">Tổng đơn:</td>
                                        <td class="text-end pe-3 fw-bold text-danger" style="font-size:1rem;">{{ number_format($order->total_price) }}đ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>

                {{-- CỘT PHẢI: XỬ LÝ --}}
                <div class="col-lg-6">
                    <div class="card rounded-0 border bg-white h-100">
                        <div class="card-header bg-white border-bottom py-2 px-3">
                            <span class="fw-bold text-uppercase text-muted" style="font-size:0.75rem; letter-spacing:1px;">
                                <i class="fa fa-clipboard-check me-2"></i>Xử lý hoàn hàng
                            </span>
                        </div>
                        <div class="card-body p-4">

                            {{-- Thông báo chính sách --}}
                            <div class="alert rounded-0 mb-4" style="background:#fff3cd; border:1px solid #ffc107;">
                                <i class="fa fa-info-circle text-warning me-2"></i>
                                <strong>Tất cả hàng hoàn</strong> sẽ được chuyển sang
                                <strong>danh sách chờ trả nhà sản xuất</strong> — không nhập lại tồn kho.
                            </div>
                                
                                <div class="col-8">{{ $order->note }}</div>

                            {{-- Hoàn tiền — chỉ Bank Transfer --}}
                            @if($order->payment_method === 'BANK TRANSFER')
                            <div id="refund-section" class="alert rounded-0 mb-4" style="background:#fff8e1; border:1px solid #ffe082; display:none;">
                                <div class="fw-bold mb-1" style="font-size:0.85rem;">
                                    <i class="fa fa-money-bill-wave text-warning me-2"></i>Hoàn tiền cho khách
                                </div>
                                <div class="text-muted small mb-3">Số tiền cần hoàn: <strong class="text-danger">{{ number_format($order->total_price) }}đ</strong></div>
                                <button type="button" class="btn btn-warning rounded-0 fw-bold text-dark w-100"
                                        style="font-size:0.8rem; letter-spacing:1px;"
                                        onclick="if(confirm('Xác nhận đã hoàn {{ number_format($order->total_price) }}đ cho {{ $order->fullname }}?')) alert('Đã ghi nhận. Vui lòng thực hiện chuyển khoản thủ công.')">
                                    <i class="fa fa-hand-holding-dollar me-2"></i>Xác nhận đã hoàn tiền
                                </button>
                            </div>

                            @else
                            <div class="alert alert-light border rounded-0 mb-4 small text-muted">
                                <i class="fa fa-info-circle me-2"></i>
                                Đơn COD — khách chưa trả tiền, không cần hoàn tiền.
                            </div>
                            @endif

                            {{-- Form xác nhận xử lý hoàn hàng (processReturn) --}}
                            <form action="{{ route('admin.orders.processReturn', $order->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="condition" value="damaged">
                                <button type="submit" class="btn btn-dark rounded-0 w-100 py-2 text-uppercase fw-bold mb-2"
                                        style="font-size:0.8rem; letter-spacing:1.5px;"
                                        onclick="return confirm('Xác nhận chuyển đơn sang danh sách chờ trả nhà sản xuất?')">
                                    <i class="fa fa-check me-2"></i>Xác nhận xử lý hoàn hàng
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Từ chối hoàn hàng --}}
                <div class="col-12 mt-3">
                    <div class="card rounded-0 border border-danger bg-white">
                        <div class="card-header bg-white border-bottom border-danger py-2 px-3">
                            <span class="fw-bold text-uppercase text-danger" style="font-size:0.75rem; letter-spacing:1px;">
                                <i class="fa fa-ban me-2"></i>Từ chối yêu cầu hoàn hàng
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert rounded-0 mb-3" style="background:#fdecea; border:1px solid #f44336; font-size:0.85rem;">
                                <i class="fa fa-info-circle text-danger me-2"></i>
                                Từ chối sẽ chuyển đơn về trạng thái <strong>Hoàn tất</strong>. Phía khách hàng sẽ thấy đơn bình thường.
                            </div>
                            <form action="{{ route('admin.orders.rejectReturn', $order->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label text-uppercase small fw-bold text-secondary" style="font-size:0.72rem; letter-spacing:0.5px;">
                                        Lý do từ chối (tuỳ chọn)
                                    </label>
                                    <input type="text" name="reject_reason" class="form-control rounded-0"
                                           placeholder="VD: Sản phẩm đã qua sử dụng, không đủ điều kiện hoàn..."
                                           style="font-size:0.85rem;">
                                </div>
                                <button type="submit" class="btn btn-outline-danger rounded-0 w-100 py-2 text-uppercase fw-bold"
                                        style="font-size:0.8rem; letter-spacing:1.5px;"
                                        onclick="return confirm('Xác nhận từ chối yêu cầu hoàn hàng của khách?')">
                                    <i class="fa fa-ban me-2"></i>Từ chối hoàn hàng
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

    </div>
</div>

@endsection

@section('script')
<script src="{{ asset('js/admin/order-return.js') }}"></script>
@endsection
