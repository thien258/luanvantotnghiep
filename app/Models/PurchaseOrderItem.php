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
