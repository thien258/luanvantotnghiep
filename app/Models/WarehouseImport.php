<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * WarehouseImport — Phiếu nhập kho từ file CSV/Excel.
 *
 * Luồng sử dụng:
 *   Admin nhận hàng (PurchaseOrder.status = received)
 *   → Tải file CSV từ đơn đặt hàng → Upload lên trang Nhập Kho
 *   → Hệ thống tạo bản ghi WarehouseImport (status = pending)
 *   → Admin xem lại, duyệt từng dòng SP (approved_items)
 *   → Sau khi approve → cộng tồn kho vào bảng product_variants
 *
 * Trạng thái (status):
 *   pending  — file đã upload, chờ admin xét duyệt
 *   approved — đã duyệt, tồn kho đã được cập nhật
 *   rejected — đã từ chối, tồn kho KHÔNG cập nhật
 *
 * Bảng: warehouse_imports
 *
 * @property int $id
 * @property string $file_path
 * @property string $original_name
 * @property string|null $supplier
 * @property string|null $note
 * @property int|null $uploaded_by
 * @property string $status
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $reviewer
 * @property-read \App\Models\User|null $uploader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereUploadedBy($value)
 * @mixin \Eloquent
 */
class WarehouseImport extends Model
{
    protected $table = 'warehouse_imports';

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        'file_path',      // Đường dẫn file CSV/Excel đã lưu trên server
        'original_name',  // Tên file gốc người dùng upload (để hiển thị)
        'supplier',       // Tên NSX / nhà cung cấp (nhập tay, không bắt buộc FK)
        'note',           // Ghi chú kèm theo phiếu nhập
        'uploaded_by',    // FK → users (admin nào upload file)
        'status',         // Trạng thái: pending / approved / rejected
        'approved_items', // JSON: danh sách dòng SP đã được admin tick duyệt
        'reviewed_by',    // FK → users (admin nào xét duyệt)
        'reviewed_at',    // Thời điểm xét duyệt
    ];

    // Ép kiểu: reviewed_at thành Carbon, approved_items tự động encode/decode JSON
    protected $casts = [
        'reviewed_at'    => 'datetime',
        'approved_items' => 'array', // Lưu JSON trong DB, truy cập như mảng PHP
    ];

    /**
     * Admin đã upload file nhập kho này.
     * Dùng để hiển thị "Người tạo phiếu" trong danh sách nhập kho.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Admin đã xét duyệt phiếu nhập kho này.
     * Dùng để audit: ai đã approve/reject lô hàng này.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
