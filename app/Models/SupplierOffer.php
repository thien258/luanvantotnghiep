<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SupplierOffer — Phiếu báo giá do Nhà Sản Xuất (NSX) gửi.
 *
 * Luồng sử dụng:
 *   NSX gửi file Excel chứa danh sách SP + giá → admin upload
 *   → Hệ thống tạo SupplierOffer + các SupplierOfferItem
 *   → Admin xem, tick SP muốn mua, điền số lượng
 *   → Tạo PurchaseOrder từ báo giá này
 *
 * Vòng đời trạng thái (status):
 *   draft     — nháp, NSX chưa gửi chính thức
 *   submitted — đã gửi, chờ admin xem xét
 *   accepted  — admin đã tạo PurchaseOrder từ báo giá này
 *   rejected  — admin từ chối toàn bộ báo giá
 *
 * request_id nullable — NSX có thể gửi báo giá tự do, không cần có yêu cầu trước.
 *
 * Bảng: supplier_offers
 *
 * @property int $id
 * @property int $manufacturer_id
 * @property int|null $request_id
 * @property string $offer_code
 * @property string|null $note
 * @property string $status
 * @property string|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierOfferItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\ManuFacturer $manufacturer
 * @property-read \App\Models\ProcurementRequest|null $procurementRequest
 * @property-read \App\Models\PurchaseOrder|null $purchaseOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer whereManufacturerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer whereOfferCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOffer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SupplierOffer extends Model
{
    protected $table = "supplier_offers";

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        "manufacturer_id", // FK → manufacturers (NSX nào gửi báo giá)
        "request_id",      // FK → procurement_requests (nullable — đáp lại yêu cầu nào)
        "offer_code",      // Mã báo giá tự sinh: OFR-YYYYMMDD-001
        "note",            // Ghi chú kèm theo báo giá
        "status",          // Trạng thái: draft / submitted / accepted / rejected
        "submitted_at",    // Thời điểm NSX chính thức gửi báo giá
    ];

    /**
     * NSX đã tạo ra báo giá này — trỏ thẳng vào User (sau khi gộp bảng manufacturers).
     */
    public function manufacturer()
    {
        return $this->belongsTo(\App\Models\User::class, 'manufacturer_id', 'id');
    }

    /**
     * Danh sách sản phẩm NSX chào giá trong phiếu này.
     * 1 báo giá có nhiều dòng sản phẩm (SupplierOfferItem).
     * Admin dựa vào đây để chọn SP và tạo PurchaseOrder.
     */
    public function items()
    {
        return $this->hasMany(SupplierOfferItem::class, 'offer_id', 'id');
    }

    /**
     * Đơn đặt hàng được sinh ra từ báo giá này (nếu admin đã duyệt).
     * hasOne vì 1 báo giá chỉ được tạo 1 PurchaseOrder duy nhất.
     * Dùng để check xem báo giá đã được duyệt chưa (purchaseOrder !== null).
     */
    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'offer_id', 'id');
    }

    /**
     * Yêu cầu nhập hàng mà NSX đang phản hồi (nullable).
     * Dùng để hiển thị context: báo giá này đáp lại yêu cầu nào của admin.
     */
    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class, 'request_id', 'id');
    }
}
