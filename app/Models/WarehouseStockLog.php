<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * WarehouseStockLog — Log biến động tồn kho.
 *
 * Ghi lại mỗi lần tồn kho của 1 sản phẩm thay đổi.
 * type = 'import': nhập kho (cộng tồn kho)
 * type = 'export': xuất kho (trừ tồn kho — hiện chưa dùng, dự phòng)
 *
 * stock_after: tồn kho sau khi thực hiện thao tác
 * reason: ghi chú lý do (VD: "Nhập kho từ phiếu PN...", "Tạo mới từ phiếu...")
 *
 * Bảng: warehouse_stock_logs
 */
class WarehouseStockLog extends Model
{
    protected $table = "warehouse_stock_logs";

    protected $fillable = [
        'receipt_id',  // FK → warehouse_receipts (phiếu nhập kho nào)
        'product_id',  // FK → products (sản phẩm nào)
        'type',        // 'import' hoặc 'export'
        'quantity',    // Số lượng thay đổi
        'stock_after', // Tồn kho sau thay đổi (snapshot)
        'reason',      // Lý do / ghi chú
    ];

    /**
     * Sản phẩm liên quan đến log này.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Phiếu nhập kho tạo ra log này.
     */
    public function warehouse()
    {
        return $this->belongsTo(WarehouseReceipt::class, 'receipt_id', 'id');
    }
}
