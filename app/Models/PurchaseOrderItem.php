<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PurchaseOrderItem — Dòng sản phẩm trong đơn đặt hàng.
 *
 * Admin chọn SP từ báo giá NSX và điền số lượng muốn mua.
 * unit_price lấy từ SupplierOfferItem (giá NSX đã chào), KHÔNG tự nhập lại.
 *
 * Tại sao lưu cả product_id lẫn product_name?
 *   - product_id dùng để liên kết với hệ thống (xuất CSV nhập kho, sync tồn kho)
 *   - product_name lưu dự phòng: nếu SP bị xóa sau này, đơn cũ vẫn còn tên SP
 *   - product_id nullable vì SP có thể chưa tồn tại trong hệ thống
 *
 * Bảng: purchase_order_items
 *
 * @property int $id
 * @property int $purchase_order_id
 * @property int|null $product_id
 * @property string $product_name
 * @property int $quantity
 * @property numeric $unit_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PurchaseOrder $order
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PurchaseOrderItem extends Model
{
    protected $table = "purchase_order_items";

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        'purchase_order_id', // FK → purchase_orders (thuộc đơn hàng nào)
        'product_id',        // FK → products (nullable — SP có thể chưa tồn tại)
        'product_name',      // Tên SP lưu dự phòng phòng khi product bị xóa
        'quantity',          // Số lượng admin quyết định đặt mua
        'unit_price',        // Đơn giá NSX chào (lấy từ SupplierOfferItem, đơn vị: ₫)
    ];

    /**
     * Sản phẩm tương ứng trong hệ thống.
     * Dùng để lấy thông tin khi xuất CSV nhập kho hoặc hiển thị ảnh SP.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Đơn đặt hàng chứa dòng này.
     * Dùng để navigate ngược về đơn mẹ.
     */
    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }
}
