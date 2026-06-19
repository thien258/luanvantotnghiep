<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * WarehouseReceipt — Phiếu nhập kho.
 *
 * Được tạo khi admin duyệt file nhập kho (WarehouseController::importApprove).
 * Mỗi phiếu ghi nhận 1 lần nhập kho từ 1 nhà cung cấp.
 * receipt_code sinh tự động: PN + timestamp (VD: PN2606171530).
 *
 * Bảng: warehouse_receipts
 */
class WarehouseReceipt extends Model
{
    protected $table = "warehouse_receipts";

    protected $fillable = [
        'receipt_code', // Mã phiếu nhập kho tự sinh
        'supplier',     // Tên nhà cung cấp (string, chưa FK)
        'note',         // Ghi chú phiếu
        'total_items',  // Số dòng sản phẩm trong phiếu
    ];

    /**
     * Danh sách log tồn kho thuộc phiếu này.
     * Mỗi log là 1 sản phẩm được nhập với số lượng cụ thể.
     */
    public function stockLogs()
    {
        return $this->hasMany(WarehouseStockLog::class, 'receipt_id', 'id');
    }
}
