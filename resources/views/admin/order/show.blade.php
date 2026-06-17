@extends('layout/admin')
@section('body')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4 shadow-sm">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center border-bottom pb-3 mb-4 gap-2">
            <div>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary rounded-0">
                    <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>
            <div class="text-sm-end">
                <h5 class="m-0 text-dark fw-bold">CHI TIẾT ĐƠN HÀNG #DH{{ $order->id }}</h5>
                <small class="text-muted">Mã vận đơn: <span class="font-monospace fw-bold text-dark">{{ $order->tracking_code }}</span></small>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success rounded-0 mb-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger rounded-0 mb-3">{{ session('error') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border border-light-subtle rounded-0 p-4 bg-white h-100 shadow-sm">
                    <h6 class="text-uppercase fw-bold text-muted border-bottom pb-2 mb-3" style="font-size: 0.8rem; letter-spacing: 1px;">
                        <i class="fa fa-truck me-2"></i>Thông tin giao nhận
                    </h6>
                    <div class="text-dark small lh-base mb-4">
                        <p class="mb-2"><strong>Người nhận:</strong> {{ $order->fullname }}</p>
                        <p class="mb-2"><strong>Số điện thoại:</strong> {{ $order->phone }}</p>
                        <p class="mb-2"><strong>Địa chỉ giao:</strong> {{ $order->address }}</p>
                        <p class="mb-2"><strong>Hình thức:</strong> <span class="text-uppercase badge bg-dark text-white rounded-0 px-2 py-1">{{ $order->payment_method }}</span></p>
                        <p class="mb-2"><strong>Ghi chú:</strong> <span class="text-secondary fst-italic">{{ $order->note ?? 'Không có ghi chú' }}</span></p>
                        <p class="mb-2"><strong>Trạng thái hiện tại:</strong>
                            @if($order->status == 1)
                            <span class="badge bg-warning text-dark rounded-0 px-2 py-1">Đang Lấy Hàng</span>
                            @elseif($order->status == 3)
                            <span class="badge bg-primary text-white rounded-0 px-2 py-1">Đang Giao Hàng</span>
                            @elseif($order->status == 4)
                            <span class="badge bg-success text-white rounded-0 px-2 py-1">Hoàn Tất</span>
                            @elseif($order->status == 5)
                            <span class="badge bg-secondary text-white rounded-0 px-2 py-1">Hoàn Hàng</span>
                            @else
                            <span class="badge bg-danger text-white rounded-0 px-2 py-1">Không xác định</span>
                            @endif
                        </p>
                    </div>

                    <h6 class="text-uppercase fw-bold text-muted border-bottom pb-2 mb-3" style="font-size: 0.8rem; letter-spacing: 1px;">
                        <i class="fa fa-cogs me-2"></i>Hành động vận hành
                    </h6>

                    {{-- Nút Xuất kho: Chỉ hiện khi trạng thái là 1 (Kho lấy hàng) --}}
                    @if($order->status == 1)
                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="mb-2">
                        @csrf
                        <input type="hidden" name="status" value="3">
                        <input type="hidden" name="action_type" value="export_warehouse">
                        <button type="submit" class="btn btn-dark w-100 py-2.5 rounded-0 fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;" onclick="return confirm('Hệ thống sẽ tiến hành trừ kho biến thể nước hoa tương ứng. Xác nhận xuất kho?')">
                            <i class="fa fa-box-open me-2"></i> Xác nhận xuất kho & Giao hàng
                        </button>
                    </form>
                    @else
                    <div class="alert alert-secondary py-2 small text-center rounded-0 mb-3">
                        Đơn hàng đã qua bước xử lý xuất kho hoặc đã hủy.
                    </div>
                    @endif

                    {{-- Nút In mã QR dán thùng hàng --}}
                    <button type="button"
                            id="btn-print-label"
                            class="btn btn-outline-dark w-100 py-2 rounded-0 text-uppercase fw-semibold"
                            onclick="printShippingLabel()"
                            style="font-size: 0.8rem; letter-spacing: 1px;"
                            data-confirm-url="{{ route('order.confirm-delivery', $order->tracking_code) }}"
                            data-tracking-code="{{ $order->tracking_code }}"
                            data-order-id="{{ $order->id }}"
                            data-fullname="{{ $order->fullname }}"
                            data-phone="{{ $order->phone }}"
                            data-address="{{ $order->address }}"
                            data-cod="{{ $order->payment_method === 'COD' ? number_format($order->total_price).'đ' : '0đ (Đã chuyển khoản)' }}">
                        <i class="fa fa-print me-2"></i> In mã QR dán thùng hàng
                    </button>

                    {{-- QR preview để quét thử trực tiếp --}}
                    <div class="text-center mt-3 p-3 border">
                        <div class="text-muted mb-2" style="font-size:0.7rem; letter-spacing:1px; text-transform:uppercase;">Quét để xác nhận nhận hàng</div>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode(route('order.confirm-delivery', $order->tracking_code)) }}&t={{ time() }}"
                             style="width:160px;height:160px;" alt="QR">
                        <div class="mt-1 text-muted" style="font-size:0.65rem; word-break:break-all;">
                            {{ route('order.confirm-delivery', $order->tracking_code) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border border-light-subtle rounded-0 p-4 bg-white h-100 shadow-sm">
                    <h6 class="text-uppercase fw-bold text-muted border-bottom pb-2 mb-3" style="font-size: 0.8rem; letter-spacing: 1px;">
                        <i class="fa fa-list me-2"></i>Danh sách sản phẩm xuất xưởng
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle text-dark small">
                            <thead>
                                <tr class="table-light">
                                    <th>Sản phẩm</th>
                                    <th class="text-center" style="width: 60px;">SL</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orderdetail as $detail)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 py-1">
                                            @if(!empty($detail->product?->image))
                                            <img src="{{ $detail->product->image }}" style="width: 45px; height: 45px; object-fit: cover;" class="border" alt="">
                                            @else
                                            <div class="border d-flex align-items-center justify-content-center text-muted bg-light" style="width: 45px; height: 45px; font-size: 0.6rem;">No Img</div>
                                            @endif
                                            <div>
                                                <span class="d-block fw-bold">{{ $detail->product?->title ?? 'Sản phẩm đã xóa' }}</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    Dung tích: {{ $detail->product?->volume ?? 'Mặc định' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold">{{ $detail->quantity }}</td>
                                    <td class="text-end">{{ number_format($detail->price) }}đ</td>
                                    <td class="text-end fw-bold">{{ number_format($detail->price * $detail->quantity) }}đ</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Không tìm thấy chi tiết sản phẩm của đơn hàng này.</td>
                                </tr>
                                @endforelse
                                <tr class="border-top border-dark">
                                    <td colspan="3" class="text-end fw-bold pt-3">Tổng tiền đơn:</td>
                                    <td class="text-end text-danger fw-bold h5 pt-3 mb-0">{{ number_format($order->total_price) }}đ</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="shipping-label-print-zone" style="display:none;">
    <div class="label-inner">
        <h2>ATELIER SCENT</h2>
        <p class="label-sub">TEM VẬN CHUYỂN ĐƠN HÀNG</p>
        <div class="qr-wrap">
            <img id="qr-label-img"
                 src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('order.confirm-delivery', $order->tracking_code)) }}"
                 width="200" height="200" alt="QR">
            <div class="tracking">{{ $order->tracking_code }}</div>
        </div>
        <div class="label-info">
            <p><strong>Mã đơn:</strong> #DH{{ $order->id }}</p>
            <p><strong>Khách hàng:</strong> {{ $order->fullname }}</p>
            <p><strong>SĐT:</strong> {{ $order->phone }}</p>
            <p><strong>Địa chỉ:</strong> {{ $order->address }}</p>
            <p><strong>Thu hộ:</strong> {{ $order->payment_method === 'COD' ? number_format($order->total_price).'đ' : '0đ   (Đã chuyển khoản)' }}</p>
        </div>
    </div>
</div>

<script src="{{ asset('js/admin/order-show.js') }}"></script>
@endsection