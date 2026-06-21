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
