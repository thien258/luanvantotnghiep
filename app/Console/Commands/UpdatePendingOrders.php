<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

/**
 * Command để cập nhật các đơn hàng bị stuck ở status=0
 * 
 * Sử dụng: php artisan orders:update-pending
 * 
 * Command này sẽ tìm tất cả đơn hàng BANK TRANSFER có status=0
 * và cập nhật thành status=1 (đã thanh toán)
 * 
 * Chỉ dùng khi webhook PayOS chưa được config hoặc bị lỗi.
 */
class UpdatePendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật các đơn hàng BANK TRANSFER bị stuck ở status=0 thành status=1';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang tìm các đơn hàng bị stuck...');

        $stuckOrders = Order::where('status', 0)
            ->where('payment_method', 'BANK TRANSFER')
            ->get();

        if ($stuckOrders->isEmpty()) {
            $this->info('✅ Không có đơn hàng bị stuck.');
            return 0;
        }

        $this->info("Tìm thấy {$stuckOrders->count()} đơn hàng bị stuck:");

        foreach ($stuckOrders as $order) {
            $this->line("  - Order #{$order->id} | User: {$order->idUser} | Tổng: {$order->total_price} VND");
        }

        if ($this->confirm('Bạn có muốn cập nhật tất cả các đơn này thành status=1 (Đã thanh toán)?')) {
            $updated = Order::where('status', 0)
                ->where('payment_method', 'BANK TRANSFER')
                ->update(['status' => 1]);

            $this->info("✅ Đã cập nhật {$updated} đơn hàng thành công!");
        } else {
            $this->info('❌ Đã hủy.');
        }

        return 0;
    }
}
