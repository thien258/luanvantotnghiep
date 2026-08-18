<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng #DH{{ $order->id }}</title>
</head>

<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;font-size:14px;color:#333;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    {{-- ===== HEADER ===== --}}
                    <tr>
                        <td style="background:#1a1a1a;text-align:center;padding:32px 40px;">
                            <div style="font-size:26px;font-weight:700;letter-spacing:6px;color:#e8c97a;">AROMA</div>
                            <div style="font-size:12px;color:#aaa;margin-top:4px;letter-spacing:1px;">Nước hoa cao cấp</div>
                        </td>
                    </tr>

                    {{-- ===== BODY ===== --}}
                    <tr>
                        <td style="padding:36px 40px;">

                            <p style="font-size:17px;font-weight:700;margin:0 0 8px;">Xin chào {{ $order->fullname }}!</p>
                            <p style="color:#666;margin:0 0 28px;">
                                Cảm ơn bạn đã tin tưởng mua sắm tại <strong>AROMA</strong>.
                                Đơn hàng của bạn đã được xác nhận và đang được xử lý.
                            </p>

                            {{-- Thông tin đơn --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f9f9;border:1px solid #e8e8e8;border-radius:6px;margin-bottom:28px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin:0 0 12px;font-weight:700;">Thông tin đơn hàng</p>
                                        <table width="100%" cellpadding="4" cellspacing="0">
                                            <tr>
                                                <td style="color:#888;width:45%;">Mã đơn hàng</td>
                                                <td style="font-weight:700;text-align:right;">#DH{{ $order->id }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color:#888;">Ngày đặt</td>
                                                <td style="font-weight:700;text-align:right;">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color:#888;">Người nhận</td>
                                                <td style="font-weight:700;text-align:right;">{{ $order->fullname }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color:#888;">Số điện thoại</td>
                                                <td style="font-weight:700;text-align:right;">{{ $order->phone }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color:#888;">Địa chỉ giao hàng</td>
                                                <td style="font-weight:700;text-align:right;">{{ $order->address }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color:#888;">Thanh toán</td>
                                                <td style="text-align:right;">
                                                    @if($order->payment_method === 'COD')
                                                    <span style="background:#fff3e0;color:#e65100;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">COD - Nhận hàng trả tiền</span>
                                                    @else
                                                    <span style="background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">✓ Đã chuyển khoản</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Bảng sản phẩm --}}
                            <p style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;font-weight:700;margin:0 0 10px;">Chi tiết sản phẩm</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:28px;">
                                <thead>
                                    <tr style="background:#1a1a1a;">
                                        <th style="color:#e8c97a;padding:10px 14px;font-size:12px;text-align:left;font-weight:600;">Sản phẩm</th>
                                        <th style="color:#e8c97a;padding:10px 14px;font-size:12px;text-align:center;font-weight:600;width:50px;">SL</th>
                                        <th style="color:#e8c97a;padding:10px 14px;font-size:12px;text-align:right;font-weight:600;width:120px;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->details as $detail)
                                    <tr style="border-bottom:1px solid #f0f0f0;">
                                        {{-- Tên sản phẩm --}}
                                        <td style="padding:12px 14px;">
                                            <div style="font-weight:700;color:#222;font-size:14px;">{{ $detail->name }}</div>
                                        </td>
                                        {{-- Số lượng --}}
                                        <td style="padding:12px 14px;text-align:center;color:#555;">x{{ $detail->quantity }}</td>
                                        {{-- Thành tiền --}}
                                        <td style="padding:12px 14px;text-align:right;font-weight:700;">
                                            {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}₫
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background:#f9f9f9;border-top:2px solid #1a1a1a;">
                                        <td colspan="2" style="padding:14px;font-weight:700;font-size:15px;">Tổng cộng</td>
                                        <td style="padding:14px;text-align:right;font-weight:700;font-size:18px;color:#c0392b;">
                                            {{ number_format($order->total_price, 0, ',', '.') }}₫
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            {{-- Ghi chú --}}
                            @if($order->note)
                            <div style="background:#fffbf0;border-left:4px solid #e8c97a;padding:12px 16px;border-radius:0 4px 4px 0;margin-bottom:24px;font-size:13px;color:#666;">
                                <strong>Ghi chú:</strong> {{ $order->note }}
                            </div>
                            @endif

                            {{-- COD: banner + QR --}}
                            @if($order->payment_method === 'COD')
                            <div style="background:#fffbf0;border:1px solid #f0c040;border-left:4px solid #f0a000;border-radius:6px;padding:20px;margin-bottom:24px;">
                                <p style="font-size:15px;font-weight:700;color:#b06000;margin:0 0 8px;">⚠️ Đơn COD được xử lý sau đơn chuyển khoản</p>
                                <p style="font-size:13px;color:#666;margin:0 0 16px;">
                                    Các đơn đã thanh toán trước sẽ được ưu tiên đóng gói và giao hàng sớm hơn.
                                </p>
                                @if(!empty($payosUrl))
                                <div style="text-align:center;">
                                    <a href="{{ $payosUrl }}"
                                        style="display:inline-block;background:#e8a000;color:#fff;text-decoration:none;padding:13px 32px;border-radius:4px;font-size:15px;font-weight:700;">
                                        💳 Thanh toán ngay
                                    </a>
                                </div>
                                <p style="font-size:11px;color:#e65100;margin:12px 0 0;text-align:center;">
                                    ⚠️ Link thanh toán chỉ dùng <strong>1 lần</strong>. Nếu đã bấm nhưng chưa thanh toán hoặc bấm Hủy, link sẽ không còn hiệu lực.<br>
                                    Để thanh toán lại, vui lòng đăng nhập → <strong>Lịch sử đơn hàng</strong> → chọn đơn này → bấm <strong>"Thanh toán online"</strong>.
                                </p>
                                @else
                                <p style="font-size:12px;color:#999;margin:0;">
                                    ⚠️ Không thể tạo link thanh toán tự động. Vui lòng liên hệ shop.
                                </p>
                                @endif
                            </div>
                            @endif

                            <p style="text-align:center;font-size:13px;color:#999;margin:0;">
                                Đơn hàng sẽ được giao trong <strong>3–5 ngày làm việc</strong>.
                            </p>

                        </td>
                    </tr>

                    {{-- ===== FOOTER ===== --}}
                    <tr>
                        <td style="background:#f5f5f5;text-align:center;padding:24px 40px;border-top:1px solid #eee;">
                            <div style="font-size:14px;font-weight:700;letter-spacing:3px;color:#555;margin-bottom:6px;">AROMA</div>
                            <div style="font-size:12px;color:#aaa;">Email này được gửi tự động, vui lòng không trả lời.</div>
                            <div style="font-size:12px;color:#aaa;margin-top:2px;">© {{ date('Y') }} AROMA – Tất cả quyền được bảo lưu.</div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>