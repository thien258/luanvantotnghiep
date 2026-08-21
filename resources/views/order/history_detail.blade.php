    @extends('layout/home')
    @section('body')
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">

    <div class="py-5 bg-white text-dark" style="font-family: 'Montserrat', sans-serif;">
        <div class="container" style="max-width: 900px;">

            <div class="mb-4">
                <a href="{{ route('order.history') }}" class="text-dark small text-decoration-underline text-uppercase fw-bold" style="letter-spacing:1px;">← Trở về lịch sử</a>
            </div>

            <div class="border border-dark p-5 bg-white">
                <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                    <div>
                        <h2 class="m-0" style="font-family: 'Playfair Display', serif;">Chi tiết đơn hàng #DH{{ $order->id }}</h2>
                        <p class="text-muted small m-0 mt-1">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-muted small m-0">Mã vận đơn: <span class="font-monospace text-dark fw-medium">{{ $order->tracking_code }}</span></p>
                    </div>
                    <div class="text-end">
                        <span class="text-uppercase small border border-dark px-3 py-1.5 fw-semibold">
                            @if($order->status == 0)
                                Chờ thanh toán
                            @elseif($order->status == 1)
                                Chờ xử lý
                            @elseif($order->status == 3)
                                Đang giao
                            @elseif($order->status == 4)
                                Hoàn thành
                            @elseif($order->status == 5)
                                Yêu cầu hoàn hàng
                            @elseif($order->status == 6)
                                Hàng hỏng
                            @elseif($order->status == -1)
                                Đã hủy
                            @else
                                Không xác định
                            @endif
                        </span>
                    </div>
                </div>

                {{-- TIMELINE: Lịch sử trạng thái đơn hàng --}}
                <div class="mb-5 border-bottom pb-4">
                    <p class="text-uppercase fw-bold text-muted small mb-3" style="letter-spacing:1px;">
                        &#9203; Trạng thái đơn hàng
                    </p>

                    <div class="d-flex align-items-start w-100">

                        @foreach($timelineSteps as $step)
                            {{-- Mỗi step: dot + label --}}
                            <div class="d-flex flex-column align-items-center text-center flex-shrink-0" style="min-width:64px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center border {{ $step['dotClass'] }}"
                                     style="width:34px; height:34px;">
                                    <i class="fas {{ $step['icon'] }}" style="font-size:0.8rem;"></i>
                                </div>
                                <div class="mt-2 {{ $step['textClass'] }}" style="font-size:0.68rem; line-height:1.3;">
                                    {{ $step['label'] }}<br>
                                    <span class="text-muted" style="font-size:0.6rem;">
                                        {{ $step['isDone'] ? $order->updated_at->format('d/m/Y') : '—' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Đường nối giữa các step --}}
                            @if(!$loop->last)
                                <div class="flex-grow-1 {{ $step['lineClass'] }}" style="height:2px; margin-top:17px;"></div>
                            @endif
                        @endforeach

                        @if($isReturn)
                            {{-- Đường nối tới step hoàn hàng --}}
                            <div class="flex-grow-1 bg-dark" style="height:2px; margin-top:17px;"></div>

                            {{-- Step hoàn hàng --}}
                            <div class="d-flex flex-column align-items-center text-center flex-shrink-0" style="min-width:72px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center border bg-danger text-white border-danger"
                                     style="width:34px; height:34px;">
                                    <i class="fas fa-undo" style="font-size:0.8rem;"></i>
                                </div>
                                <div class="mt-2 text-danger fw-semibold" style="font-size:0.68rem; line-height:1.3;">
                                    {{ $currentStatus == 5 ? 'Yêu cầu hoàn hàng' : 'Hàng hỏng' }}<br>
                                    <span class="text-muted" style="font-size:0.6rem;">{{ $order->updated_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
                {{-- END TIMELINE --}}

                <div class="row g-4 mb-5 small">
                    <div class="col-md-6">
                        <div class="text-uppercase fw-bold text-muted mb-2" style="font-size:0.7rem; letter-spacing:1px;">Địa chỉ nhận hàng</div>
                        <strong>{{ $order->fullname }}</strong><br>
                        SĐT: {{ $order->phone }}<br>
                        Địa chỉ: {{ $order->address }}
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-uppercase fw-bold text-muted mb-2" style="font-size:0.7rem; letter-spacing:1px;">Thông tin thanh toán</div>
                        Phương thức: <strong class="text-uppercase">{{ $order->payment_method }}</strong><br>
                        @php
                            $rawNote = $order->note ?? '';
                            // Tách phần "Admin từ chối hoàn: ..." và xóa tag [RETURN_REJECTED] và [REVIEWED]
                            $displayNote = preg_replace('/\s*\[RETURN_REJECTED\]/i', '', $rawNote);
                            $displayNote = preg_replace('/\s*\|\s*\[REVIEWED\]/i', '', $displayNote);
                            $displayNote = preg_replace('/\s*\[REVIEWED\]/i', '', $displayNote);
                            // Xuống hàng trước "Lý do hoàn:" và "Admin từ chối hoàn:"
                            $displayNote = preg_replace('/\s*\|\s*(Lý do hoàn:)/i', '<br>$1', $displayNote);
                            $displayNote = preg_replace('/\s*\|\s*(Admin từ chối hoàn:)/i', '<br>$1', $displayNote);
                        @endphp
                        @if($displayNote)
                            Ghi chú đơn: <span class="text-secondary">{!! $displayNote !!}</span>
                        @else
                            Ghi chú đơn: <span class="text-secondary">Không có ghi chú</span>
                        @endif
                    </div>
                </div>


                <div class="row text-uppercase fw-extrabold text-dark pb-2 mb-2 border-bottom d-none d-md-flex" style="font-size:0.75rem; letter-spacing:1px;">
                    <div class="col-md-5">Sản phẩm</div>
                    <div class="col-md-2 text-center">Số lượng</div>
                    <div class="col-md-3 text-end">Giá tiền</div>
                </div>  
                <div class="mb-4">
                    @php $totalQuantity = 0; @endphp

                    @foreach($orderDetails as $detail)
                    @php $totalQuantity += $detail->quantity; @endphp

                    <div class="row align-items-center py-3 border-bottom border-light g-3">
                        <div class="col-12 col-md-5">
                            <div class="d-flex align-items-center gap-3">
                                @if(!empty($detail->product?->image))
                                <img src="{{ $detail->product->image }}" class="border bg-white rounded-1" alt="{{ $detail->name }}" style="width: 60px; height: 60px; object-fit: cover; flex-shrink: 0;">
                                @else
                                <div class="border bg-white d-flex align-items-center justify-content-center text-muted text-uppercase rounded-1" style="width: 60px; height: 60px; font-size: 0.6rem; flex-shrink: 0;">No Img</div>
                                @endif

                                <div>
                                    <h6 class="fw-bold text-dark mb-0" style="font-family: 'Playfair Display', serif; font-size: 1.05rem;">
                                        {{ $detail->product?->title ?? $detail->name }}
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <div class="col-4 col-md-2 text-start text-md-center">
                            <span class="d-md-none text-muted small d-block text-uppercase" style="font-size: 0.65rem;">Số lượng:</span>
                            <span class="fw-medium">{{ $detail->quantity }}</span>
                        </div>

                        <div class="col-4 col-md-3 text-end">
                            <span class="d-md-none text-muted small d-block text-uppercase" style="font-size: 0.65rem;">Giá tiền:</span>
                            <span class="fw-bold text-dark" style="font-family: 'Playfair Display', serif;">
                                {{ number_format($detail->price * $detail->quantity) }}đ
                            </span>
                        </div>
                    </div>
                    @endforeach

                        <div class="row align-items-center py-3 border-top border-dark g-3 fw-bold">
                            <div class="col-md-5 d-none d-md-block">
                                <span class="text-uppercase" style="font-family: 'Playfair Display', serif; font-size: 0.9rem; letter-spacing: 0.5px;">Tổng cộng</span>
                            </div>

                            <div class="col-12 d-md-none">
                                <span class="text-uppercase text-muted small" style="letter-spacing:1px; font-size: 0.7rem;">Tóm tắt dòng</span>
                            </div>

                            <div class="col-4 col-md-2 text-start text-md-center">
                                <span class="d-md-none text-muted small fw-normal d-block text-uppercase" style="font-size: 0.65rem;">Tổng số lượng:</span>
                                <span class="text-danger">{{ $totalQuantity }}</span>
                            </div>

                            <div class="col-8 col-md-3 text-end">
                                <span class="d-md-none text-muted small fw-normal d-block text-uppercase" style="font-size: 0.65rem;">Tổng tiền sản phẩm:</span>
                                <span class="text-danger" style="font-family: 'Playfair Display', serif;">
                                    {{ number_format($order->total_price) }}đ
                                </span>
                            </div>
                        </div>
                </div>

                <div class="row justify-content-end small pt-2">
                    <div class="col-md-5">
                        <div class="d-flex justify-content-between mb-2 text-muted">
                            <span>Tạm tính</span>
                            <span>{{ number_format($order->total_price) }}đ</span>
                        </div>

                        <hr class="my-2 text-secondary-subtle">
                        <div class="d-flex justify-content-between align-items-baseline mt-2">
                            <span class="h5 m-0" style="font-family: 'Playfair Display', serif;">Tổng cộng</span>
                            <span class="h4 m-0 fw-bold text-danger" style="font-family: 'Playfair Display', serif;">{{ number_format($order->total_price) }}đ</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Nút thanh toán online cho đơn COD --}}
    @if($order->payment_method === 'COD' && $order->status == 1)
    <div class="container mt-3" style="max-width: 900px;">
        <div class="border border-dark p-4 bg-white d-flex align-items-center justify-content-between gap-3">
            <div>
                <p class="fw-bold mb-1">Muốn được ưu tiên xử lý?</p>
                <p class="text-muted small mb-0">Thanh toán online ngay để đơn được xử lý trước các đơn COD.</p>
            </div>
            <form action="{{ route('order.repay', $order->id) }}" method="POST" class="flex-shrink-0">
                @csrf
                <button type="submit" class="btn btn-dark rounded-0 px-4 py-2 text-uppercase fw-semibold small" style="letter-spacing:1px;">
                    💳 Thanh toán online
                </button>
            </form>
        </div>
    </div>
    @endif

    @endsection