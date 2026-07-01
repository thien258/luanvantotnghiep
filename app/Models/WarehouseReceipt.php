<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * WarehouseReceipt — Phiếu nhập kho.
 *
 * Được tạo khi admin phê duyệt file Excel nhập kho (WarehouseController::importApprove).
 * Mỗi phiếu ghi nhận 1 lần nhập kho từ 1 nhà cung cấp.
 *
 * receipt_code: mã phiếu sinh tự động theo định dạng PN + timestamp
 *               VD: PN2606171530 (PN + ngày 26/06 + giờ 17:30)
 *
 * Sau khi phiếu được tạo, hệ thống tự sinh các WarehouseStockLog tương ứng
 * cho từng dòng sản phẩm trong phiếu và cộng vào tồn kho (Product.quantity).
 *
 * Bảng: warehouse_receipts
 *
 * @property int $id
 * @property string $receipt_code
 * @property string|null $supplier
 * @property string|null $note
 * @property int $total_items
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseStockLog> $stockLogs
 * @property-read int|null $stock_logs_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseReceipt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseReceipt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseReceipt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseReceipt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseReceipt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseReceipt whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseReceipt whereReceiptCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseReceipt whereSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseReceipt whereTotalItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseReceipt whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WarehouseReceipt extends Model
{
    protected $table = "warehouse_receipts";

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        'receipt_code', // Mã phiếu nhập kho (tự sinh, dạng PNddmmHHii)
        'supplier',     // Tên nhà cung cấp (lưu dạng text, chưa liên kết FK)
        'note',         // Ghi chú thêm cho phiếu nhập
        'total_items',  // Tổng số dòng sản phẩm trong phiếu
    ];

    /**
     * Quan hệ: 1 phiếu nhập kho sinh ra nhiều bản ghi log tồn kho.
     * Mỗi WarehouseStockLog tương ứng với 1 sản phẩm và số lượng nhập cụ thể.
     */
    public function stockLogs()
    {
        return $this->hasMany(WarehouseStockLog::class, 'receipt_id', 'id');
    }
}
