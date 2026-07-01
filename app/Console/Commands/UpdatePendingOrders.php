<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

/**
 * UpdatePendingOrders — Command xử lý các đơn hàng chuyển khoản bị kẹt.
 *
 * Vấn đề: Khi webhook PayOS chưa được cấu hình hoặc bị lỗi tạm thời,
 * đơn hàng thanh toán qua BANK TRANSFER sẽ ở mãi status=0 (chưa thanh toán)
 * dù khách đã chuyển tiền thành công.
 *
 * Command này cho phép admin thủ công fix bằng cách tìm và cập nhật
 * tất cả đơn BANK TRANSFER có status=0 → status=1.
 *
 * Cách dùng: php artisan orders:update-pending
 *
 * Lưu ý: Chỉ dùng khi chắc chắn khách đã thanh toán. Không nên
 * chạy định kỳ tự động vì có thể mark sai đơn chưa thanh toán thật.
 */
class UpdatePendingOrders extends Command
{
    /**
     * Tên và chữ ký của command.
     * Dùng: php artisan orders:update-pending
     *
     * @var string
     */
    protected $signature = 'orders:update-pending';

    /**
     * Mô tả ngắn hiển thị khi chạy php artisan list
     *
     * @var string
     */
    protected $description = 'Cập nhật các đơn hàng BANK TRANSFER bị stuck ở status=0 thành status=1';

    /**
     * Xử lý chính của command.
     *
     * Quy trình:
     * 1. Tìm tất cả đơn BANK TRANSFER có status=0
     * 2. Hiển thị danh sách cho admin xem
     * 3. Hỏi xác nhận trước khi cập nhật (tránh cập nhật nhầm)
     * 4. Nếu đồng ý → bulk update status=1
     */
    public function handle()
    {
        $this->info('Đang tìm các đơn hàng bị stuck...');

        // Chỉ lấy đơn BANK TRANSFER status=0, bỏ qua PayOS/COD
        $stuckOrders = Order::where('status', 0)
            ->where('payment_method', 'BANK TRANSFER')
            ->get();

        // Không có đơn nào bị kẹt → báo thành công và thoát
        if ($stuckOrders->isEmpty()) {
            $this->info('✅ Không có đơn hàng bị stuck.');
            return 0;
        }

        $this->info("Tìm thấy {$stuckOrders->count()} đơn hàng bị stuck:");

        // Hiển thị chi tiết từng đơn để admin kiểm tra trước khi quyết định
        foreach ($stuckOrders as $order) {
            $this->line("  - Order #{$order->id} | User: {$order->idUser} | Tổng: {$order->total_price} VND");
        }

        // Hỏi xác nhận để tránh cập nhật hàng loạt nhầm
        if ($this->confirm('Bạn có muốn cập nhật tất cả các đơn này thành status=1 (Đã thanh toán)?')) {
            // Dùng bulk update thay vì foreach để tối ưu hiệu năng
            $updated = Order::where('status', 0)
                ->where('payment_method', 'BANK TRANSFER')
                ->update(['status' => 1]);

            $this->info("✅ Đã cập nhật {$updated} đơn hàng thành công!");
        } else {
            // Admin chọn không → không làm gì cả
            $this->info('❌ Đã hủy.');
        }

        return 0;
    }
}
