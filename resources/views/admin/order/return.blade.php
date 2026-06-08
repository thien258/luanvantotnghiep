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
            <span class="badge bg-warning text-dark px-3 py-2" style="font-size:0.8rem;">Đang giao hàng</span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
        @endif

        <div class="row g-4">

            {{-- CỘT TRÁI: HÓA ĐƠN ĐẦY ĐỦ --}}
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
                            @if($order->note)
                            <div class="col-4 text-muted">Ghi chú:</div>
                            <div class="col-8 fst-italic text-secondary">{{ $order->note }}</div>
                            @endif
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

            {{-- CỘT PHẢI: XỬ LÝ HOÀN HÀNG --}}
            <div class="col-lg-6">
                <div class="card rounded-0 border bg-white h-100">
                    <div class="card-header bg-white border-bottom py-2 px-3">
                        <span class="fw-bold text-uppercase text-muted" style="font-size:0.75rem; letter-spacing:1px;">
                            <i class="fa fa-clipboard-check me-2"></i>Kiểm tra & Xử lý hoàn hàng
                        </span>
                    </div>
                    <div class="card-body p-4">

                        {{-- Nút hoàn tiền — chỉ hiện với BANK TRANSFER --}}
                        @if($order->payment_method === 'BANK TRANSFER')
                        <div id="refund-section" class="alert rounded-0 mb-4" style="background:#fff8e1; border:1px solid #ffe082; display:none;">
                            <div class="fw-bold mb-1" style="font-size:0.85rem;">
                                <i class="fa fa-money-bill-wave text-warning me-2"></i>Hoàn tiền cho khách
                            </div>
                            <div class="text-muted small mb-3">Số tiền cần hoàn: <strong class="text-danger">{{ number_format($order->total_price) }}đ</strong></div>
                            <button type="button" class="btn btn-warning rounded-0 fw-bold text-dark w-100"
                                    style="font-size:0.8rem; letter-spacing:1px;"
                                    onclick="if(confirm('Xác nhận đã hoàn tiền {{ number_format($order->total_price) }}đ cho {{ $order->fullname }}?')) alert('Đã ghi nhận. Vui lòng thực hiện chuyển khoản thủ công.')">
                                <i class="fa fa-hand-holding-dollar me-2"></i>Xác nhận đã hoàn tiền
                            </button>
                        </div>

                        {{-- Lý do hoàn hàng — chỉ cho BANK TRANSFER --}}
                        <div class="mb-4">
                            <p class="fw-bold small text-uppercase mb-2" style="letter-spacing:1px;">Lý do hoàn hàng:</p>
                            <div class="d-flex gap-3">
                                <label class="d-flex align-items-center gap-2 p-3 border flex-fill rounded-0" style="cursor:pointer;" id="label-reason-bomb">
                                    <input type="radio" name="return_reason" value="bomb"
                                           class="form-check-input flex-shrink-0"
                                           onchange="handleReasonChange(this)"
                                           style="accent-color:#000;">
                                    <div>
                                        <div class="fw-bold" style="font-size:0.85rem;">💣 Hàng bom đơn</div>
                                        <div class="text-muted" style="font-size:0.72rem;">Khách không nhận, không chuyển khoản</div>
                                    </div>
                                </label>
                                <label class="d-flex align-items-center gap-2 p-3 border flex-fill rounded-0" style="cursor:pointer;" id="label-reason-return">
                                    <input type="radio" name="return_reason" value="return"
                                           class="form-check-input flex-shrink-0"
                                           onchange="handleReasonChange(this)"
                                           style="accent-color:#000;">
                                    <div>
                                        <div class="fw-bold" style="font-size:0.85rem;">↩️ Hoàn trả</div>
                                        <div class="text-muted" style="font-size:0.72rem;">Khách đã chuyển khoản, muốn trả hàng</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @else
                        {{-- COD: không cần chọn lý do, không hoàn tiền --}}
                        <div class="alert alert-light border rounded-0 mb-4 small text-muted">
                            <i class="fa fa-info-circle me-2"></i>
                            Đơn COD — khách chưa trả tiền, <strong>không cần hoàn tiền</strong>.
                        </div>
                        @endif

                        <hr class="my-3">

                        <form action="{{ route('admin.orders.processReturn', $order->id) }}" method="POST">
                            @csrf

                            <p class="fw-bold small text-uppercase mb-3" style="letter-spacing:1px;">Tình trạng hàng hoàn về:</p>

                            {{-- Hàng OK --}}
                            <label class="d-flex align-items-start gap-3 p-3 border mb-3 rounded-0"
                                   style="cursor:pointer;" id="label-intact">
                                <input type="radio" name="condition" value="intact"
                                       class="form-check-input mt-1 flex-shrink-0"
                                       onchange="highlightOption()"
                                       style="accent-color:#000;">
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size:0.9rem;">
                                        ✅ Hàng còn nguyên vẹn (OK)
                                    </div>
                                    <div class="text-muted" style="font-size:0.78rem; line-height:1.5;">
                                        Hàng không bị hư hỏng.<br>
                                        → <strong class="text-success">Cộng lại vào tồn kho</strong> để tiếp tục bán.
                                    </div>
                                </div>
                            </label>

                            {{-- Hàng hỏng --}}
                            <label class="d-flex align-items-start gap-3 p-3 border mb-3 rounded-0"
                                   style="cursor:pointer;" id="label-damaged">
                                <input type="radio" name="condition" value="damaged"
                                       class="form-check-input mt-1 flex-shrink-0"
                                       onchange="highlightOption()"
                                       style="accent-color:#000;">
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size:0.9rem;">
                                        ❌ Hàng bị lỗi / hỏng
                                    </div>
                                    <div class="text-muted" style="font-size:0.78rem; line-height:1.5;">
                                        Hàng bị móp méo, vỡ, lỗi nhà sản xuất...<br>
                                        → <strong class="text-danger">Chuyển sang Danh sách hàng hỏng</strong>.
                                    </div>
                                </div>
                            </label>

                            @error('condition')
                                <div class="text-danger small mb-3">Vui lòng chọn tình trạng hàng.</div>
                            @enderror

                            <button type="submit" class="btn btn-dark rounded-0 w-100 py-2 text-uppercase fw-bold mt-2"
                                    style="font-size:0.8rem; letter-spacing:1.5px;">
                                <i class="fa fa-check me-2"></i>Xác nhận xử lý hoàn hàng
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@section('script')
<script src="{{ asset('js/order-return.js') }}"></script>
@endsection

@endsection
