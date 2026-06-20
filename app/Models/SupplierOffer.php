<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SupplierOffer — Phiếu báo giá từ Nhà Sản Xuất (NSX).
 *
 * Luồng: NSX gửi file Excel → admin upload → tạo SupplierOffer + SupplierOfferItem
 *        → admin xem, tick SP muốn mua, điền số lượng → tạo PurchaseOrder
 *
 * Trạng thái (status):
 *   draft     — nháp, chưa gửi
 *   submitted — đã gửi, chờ admin xem
 *   accepted  — admin đã tạo đơn đặt hàng từ báo giá này
 *   rejected  — admin từ chối
 *
 * Bảng: supplier_offers
 */
class SupplierOffer extends Model
{
    protected $table = "supplier_offers";

    // Các cột được phép gán hàng loạt (mass assignment)
    protected $fillable = [
        "manufacturer_id", // FK → manufacturers (NSX nào gửi báo giá)
        "offer_code",      // Mã báo giá tự sinh: OFR-YYYYMMDD-001
        "note",            // Ghi chú kèm theo
        "status",          // Trạng thái: draft / submitted / accepted / rejected
        "submitted_at"     // Thời điểm NSX gửi báo giá
    ];

    /**
     * NSX tạo ra báo giá này.
     * SupplierOffer thuộc về 1 ManuFacturer.
     */
    public function manufacturer()
    {
        return $this->belongsTo(ManuFacturer::class, "manufacturer_id", "id");
    }

    /**
     * Danh sách sản phẩm NSX chào trong báo giá này.
     * 1 báo giá có nhiều dòng sản phẩm (SupplierOfferItem).
     */
    public function items()
    {
        return $this->hasMany(SupplierOfferItem::class, 'offer_id', 'id');
    }

    /**
     * Đơn đặt hàng được tạo ra từ báo giá này (nếu có).
     * 1 báo giá chỉ tạo được 1 PurchaseOrder (hasOne).
     */
    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'offer_id', 'id');
    }
    // Yêu cầu thu mua mà NSX đang trả lời (nullable)
    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class, 'request_id', 'id');
    }
}
