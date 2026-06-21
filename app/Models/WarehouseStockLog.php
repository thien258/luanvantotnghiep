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
 *
 * @property int $id
 * @property int|null $receipt_id
 * @property int $product_id
 * @property string $type
 * @property int $quantity
 * @property int $stock_after
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\WarehouseReceipt|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog whereReceiptId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog whereStockAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockLog whereUpdatedAt($value)
 * @mixin \Eloquent
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
