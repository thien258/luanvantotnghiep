<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PurchaseOrderItem — Dòng sản phẩm trong đơn đặt hàng.
 *
 * Đây là nơi lưu số lượng admin quyết định mua (quantity).
 * unit_price lấy từ SupplierOfferItem (giá NSX chào).
 *
 * product_id nullable — SP có thể chưa tồn tại trong hệ thống.
 * product_name luôn có để tránh mất dữ liệu nếu SP bị xóa sau này.
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

    protected $fillable = [
        'purchase_order_id', // FK → purchase_orders
        'product_id',        // FK → products (nullable)
        'product_name',      // Tên SP (dự phòng)
        'quantity',          // Số lượng admin muốn đặt mua
        'unit_price',        // Giá NSX chào (lấy từ SupplierOfferItem)
    ];

    /**
     * Sản phẩm tương ứng trong hệ thống.
     * Dùng để lấy thông tin khi xuất CSV nhập kho.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Đơn đặt hàng chứa dòng này.
     */
    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }
}
