<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ProcurementRequest — Yêu cầu nhập hàng do admin tạo.
 *
 * Luồng sử dụng:
 *   Admin tạo yêu cầu → ghi danh sách SP cần nhập (ProcurementRequestItem)
 *   → NSX nhận yêu cầu → gửi báo giá (SupplierOffer) đính kèm request_id này
 *   → Admin xét duyệt báo giá → tạo PurchaseOrder
 *
 * Trạng thái (status) thường dùng:
 *   pending  — chờ NSX báo giá
 *   offered  — đã có ít nhất 1 báo giá
 *   closed   — đã chọn xong và tạo PurchaseOrder
 *
 * Bảng: procurement_requests
 */
class ProcurementRequest extends Model
{
    protected $table = 'procurement_requests';

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        'request_code', // Mã yêu cầu tự sinh (VD: REQ-20240601-001)
        'status',       // Trạng thái của yêu cầu
        'note',         // Ghi chú nội bộ
        'deadline',     // Hạn chót cần nhập hàng
        'created_by',   // FK → users (admin nào tạo yêu cầu)
    ];

    /**
     * Danh sách sản phẩm cần nhập trong yêu cầu này.
     * 1 yêu cầu có nhiều dòng sản phẩm (ProcurementRequestItem).
     */
    public function items()
    {
        return $this->hasMany(ProcurementRequestItem::class, 'request_id', 'id');
    }

    /**
     * Các báo giá NSX gửi đáp lại yêu cầu này.
     * 1 yêu cầu có thể nhận nhiều báo giá từ nhiều NSX khác nhau.
     */
    public function offers()
    {
        return $this->hasMany(SupplierOffer::class, 'request_id', 'id');
    }

    /**
     * Admin đã tạo yêu cầu này.
     * Dùng để hiển thị tên người tạo trong danh sách yêu cầu.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
