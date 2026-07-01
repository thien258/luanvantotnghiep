<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PurchaseOrder — Đơn đặt hàng admin tạo sau khi duyệt báo giá NSX.
 *
 * Luồng tạo đơn:
 *   Admin xem báo giá (SupplierOffer) → tick SP muốn mua + điền số lượng
 *   → Tạo PurchaseOrder + các PurchaseOrderItem
 *   → Cập nhật SupplierOffer.status = 'accepted'
 *   → Sync SP mới vào bảng manufacturers_product (danh bạ NSX)
 *
 * Vòng đời trạng thái (status):
 *   pending    — vừa tạo, chờ xác nhận với NSX
 *   confirmed  — đã xác nhận, NSX đang chuẩn bị hàng
 *   delivering — hàng đang trên đường giao
 *   received   — admin bấm xác nhận đã nhận hàng
 *   cancelled  — đơn bị hủy
 *
 * Sau khi received → admin tải file CSV xuất từ đơn → upload vào trang Nhập Kho
 * → hệ thống cộng tồn kho tương ứng.
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

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        'offer_id',        // FK → supplier_offers (nullable — có thể đặt không qua báo giá)
        'manufacturer_id', // FK → manufacturers (NSX nhận đơn)
        'order_code',      // Mã đơn tự sinh: PO-YYYYMMDD-001
        'total_amount',    // Tổng tiền = sum(quantity × unit_price) tính khi tạo đơn
        'status',          // Trạng thái: pending / confirmed / delivering / received / cancelled
        'expected_date',   // Ngày dự kiến nhận hàng (NSX cam kết)
        'note',            // Ghi chú nội bộ
        'created_by',      // FK → users (admin nào tạo đơn)
    ];

    /**
     * Nhà sản xuất nhận đơn đặt hàng này.
     * Dùng để hiển thị tên, SĐT NSX trong trang chi tiết đơn.
     */
    public function manufacturer()
    {
        return $this->belongsTo(ManuFacturer::class, 'manufacturer_id', 'id');
    }

    /**
     * Báo giá gốc dẫn đến việc tạo đơn này (nullable).
     * Dùng để truy ngược về báo giá khi admin cần kiểm tra lại giá đã chào.
     */
    public function offer()
    {
        return $this->belongsTo(SupplierOffer::class, 'offer_id', 'id');
    }

    /**
     * Danh sách các dòng sản phẩm trong đơn đặt hàng.
     * 1 đơn có nhiều dòng, mỗi dòng là 1 sản phẩm với số lượng và đơn giá.
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id', 'id');
    }
}
