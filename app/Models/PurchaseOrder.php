<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PurchaseOrder — Đơn đặt hàng admin tạo sau khi duyệt báo giá NSX.
 *
 * Luồng:
 *   Admin xem báo giá → tick SP muốn mua + điền số lượng
 *   → Tạo PurchaseOrder + PurchaseOrderItem
 *   → Cập nhật SupplierOffer.status = 'accepted'
 *   → Sync SP vào manufacturers_product (danh bạ NSX)
 *
 * Trạng thái (status):
 *   pending    — chờ xác nhận
 *   confirmed  — đã xác nhận với NSX
 *   delivering — hàng đang giao
 *   received   — đã nhận hàng (admin bấm xác nhận)
 *   cancelled  — đã hủy
 *
 * Sau khi received → admin tải file CSV → upload vào trang Nhập Kho → cộng tồn kho.
 *
 * Bảng: purchase_orders
 *
 * @property int $id
 * @property int|null $offer_id
 * @property int $manufacturer_id
 * @property string $order_code
 * @property numeric $total_amount
 * @property string $status
 * @property string|null $expected_date
 * @property string|null $note
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\ManuFacturer $manufacturer
 * @property-read \App\Models\SupplierOffer|null $offer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereExpectedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereManufacturerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereOfferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereOrderCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PurchaseOrder extends Model
{
    protected $table = "purchase_orders";

    protected $fillable = [
        'offer_id',        // FK → supplier_offers (nullable — có thể đặt không qua báo giá)
        'manufacturer_id', // FK → manufacturers
        'order_code',      // Mã đơn tự sinh: PO-YYYYMMDD-001
        'total_amount',    // Tổng tiền = sum(quantity × unit_price)
        'status',          // Trạng thái: pending / confirmed / delivering / received / cancelled
        'expected_date',   // Ngày dự kiến nhận hàng
        'note',            // Ghi chú
        'created_by',      // FK → users (admin nào tạo đơn)
    ];

    /**
     * Nhà sản xuất của đơn đặt hàng này.
     */
    public function manufacturer()
    {
        return $this->belongsTo(ManuFacturer::class, 'manufacturer_id', 'id');
    }

    /**
     * Báo giá gốc tạo ra đơn này (nullable).
     */
    public function offer()
    {
        return $this->belongsTo(SupplierOffer::class, 'offer_id', 'id');
    }

    /**
     * Danh sách sản phẩm trong đơn đặt hàng.
     * 1 đơn có nhiều dòng PurchaseOrderItem.
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id', 'id');
    }
}
