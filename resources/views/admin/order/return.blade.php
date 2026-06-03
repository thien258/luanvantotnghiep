@extends('layout/admin')
@section('body')

<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">

        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary rounded-0">
                <i class="fa fa-arrow-left me-1"></i> Quay lại
            </a>
            <h5 class="m-0 fw-bold text-dark">XỬ LÝ HOÀN HÀNG — #DH{{ $order->id }}</h5>
            <span class="badge bg-secondary px-3 py-2">Hoàn Hàng</span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
        @endif

        <div class="row g-4">

            {{-- THÔNG TIN ĐƠN --}}
            <div class="col-lg-5">
                <div class="card rounded-0 border p-4 bg-white h-100">
                    <h6 class="text-uppercase fw-bold text-muted border-bottom pb-2 mb-3" style="font-size:0.8rem; letter-spacing:1px;">
                        <i class="fa fa-box me-2"></i>Sản phẩm hoàn về
                    </h6>
                    @foreach($orderdetail as $detail)
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        @if($detail->product?->image)
                            <img src="{{ $detail->product->image }}" style="width:50px;height:50px;object-fit:cover;" class="border">
                        @else
                            <div class="border bg-light d-flex align-items-center justify-content-center text-muted" style="width:50px;height:50px;font-size:0.6rem;">No Img</div>
                        @endif
                        <div>
                            <div class="fw-bold small">{{ $detail->product?->title ?? 'Sản phẩm đã xóa' }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">SL: {{ $detail->quantity }} — {{ number_format($detail->price) }}đ/cái</div>
                        </div>
                    </div>
                    @endforeach
                    <div class="mt-2 small text-muted">
                        <strong>Khách hàng:</strong> {{ $order->fullname }}<br>
                        <strong>SĐT:</strong> {{ $order->phone }}<br>
                        <strong>Địa chỉ:</strong> {{ $order->address }}
                    </div>
                </div>
            </div>

            {{-- FORM CHỌN TÌNH TRẠNG --}}
            <div class="col-lg-7">
                <div class="card rounded-0 border p-4 bg-white h-100">
                    <h6 class="text-uppercase fw-bold text-muted border-bottom pb-2 mb-4" style="font-size:0.8rem; letter-spacing:1px;">
                        <i class="fa fa-clipboard-check me-2"></i>Kiểm tra tình trạng hàng hoàn
                    </h6>

                    <form action="{{ route('admin.orders.processReturn', $order->id) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            {{-- Lựa chọn nguyên vẹn --}}
                            <label class="d-flex align-items-start gap-3 p-3 border mb-3 rounded-0"
                                   style="cursor:pointer;" id="label-intact">
                                <input type="radio" name="condition" value="intact"
                                       class="form-check-input mt-1 flex-shrink-0"
                                       onchange="highlightOption()"
                                       style="accent-color:#000;">
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size:0.9rem;">
                                        ✅ Hàng còn nguyên vẹn
                                    </div>
                                    <div class="text-muted" style="font-size:0.8rem; line-height:1.5;">
                                        Khách không nhận / bom đơn nhưng hàng không bị hư hỏng.<br>
                                        Hệ thống sẽ <strong class="text-success">cộng lại số lượng vào tồn kho</strong> để tiếp tục bán.
                                    </div>
                                </div>
                            </label>

                            {{-- Lựa chọn hàng hỏng --}}
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
                                    <div class="text-muted" style="font-size:0.8rem; line-height:1.5;">
                                        Hàng bị móp méo, vỡ, hoặc lỗi nhà sản xuất.<br>
                                        Hệ thống sẽ <strong class="text-danger">KHÔNG cộng vào tồn kho</strong>, chuyển sang <strong>Danh sách hàng hỏng</strong>.
                                    </div>
                                </div>
                            </label>
                        </div>

                        @error('condition')
                            <div class="text-danger small mb-3">Vui lòng chọn tình trạng hàng.</div>
                        @enderror

                        <button type="submit" class="btn btn-dark rounded-0 w-100 py-2 text-uppercase fw-bold"
                                style="font-size:0.8rem; letter-spacing:1.5px;">
                            <i class="fa fa-check me-2"></i>Xác nhận xử lý hoàn hàng
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function highlightOption() {
    document.getElementById('label-intact').style.borderColor  = '';
    document.getElementById('label-damaged').style.borderColor = '';

    var selected = document.querySelector('input[name="condition"]:checked');
    if (selected) {
        var label = document.getElementById('label-' + selected.value);
        label.style.borderColor = '#000';
    }
}
</script>

@endsection
