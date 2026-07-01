<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * WarehouseStockLog — Nhật ký biến động tồn kho.
 *
 * Ghi lại mỗi lần số lượng tồn kho của 1 sản phẩm thay đổi.
 * Mỗi bản ghi = 1 sự kiện tăng/giảm tồn kho.
 *
 * Loại biến động (type):
 *   'import' — Nhập kho: cộng thêm tồn kho (từ phiếu nhập / WarehouseReceipt)
 *   'export' — Xuất kho: trừ tồn kho (hiện chưa sử dụng, để dự phòng)
 *
 * stock_after: snapshot tồn kho SAU khi thực hiện thao tác — dùng để tra cứu
 *              lịch sử mà không cần tính lại từ đầu.
 * expiry_date: hạn sử dụng của lô hàng nhập (null với log xuất kho).
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

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        'receipt_id',  // FK → warehouse_receipts (phiếu nhập kho sinh ra log này; null nếu nhập tay)
        'product_id',  // FK → products (sản phẩm được nhập/xuất)
        'type',        // Loại thao tác: 'import' | 'export'
        'quantity',    // Số lượng thay đổi trong lần này
        'stock_after', // Tồn kho sau thao tác (snapshot — không cần tính lại)
        'reason',      // Ghi chú lý do (VD: "Nhập từ phiếu PN2506...", "Điều chỉnh thủ công")
        'expiry_date', // Hạn sử dụng của lô hàng nhập (nullable — log xuất không cần)
    ];

    /**
     * Cast expiry_date thành Carbon date khi đọc ra từ DB.
     * Cho phép dùng $log->expiry_date->format('d/m/Y') hoặc so sánh trực tiếp.
     */
    protected $casts = [
        'expiry_date' => 'date',
    ];

    /**
     * Quan hệ: Log này thuộc về sản phẩm nào.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Quan hệ: Log này thuộc về phiếu nhập kho nào.
     * Có thể null nếu log được tạo không qua phiếu nhập (VD: nhập thủ công).
     */
    public function warehouse()
    {
        return $this->belongsTo(WarehouseReceipt::class, 'receipt_id', 'id');
    }
}
