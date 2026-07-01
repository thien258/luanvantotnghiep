<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ProcurementRequestItem — Dòng sản phẩm trong yêu cầu nhập hàng.
 *
 * Mỗi dòng mô tả 1 sản phẩm admin cần nhập: tên SP, số lượng cần, ghi chú.
 * product_id có thể NULL nếu SP chưa tồn tại trong hệ thống (mua mới hoàn toàn).
 * product_name lưu dự phòng để không mất dữ liệu nếu SP bị xóa sau này.
 *
 * Bảng: procurement_request_items
 *
 * @property int $id
 * @property int $request_id
 * @property int|null $product_id
 * @property string $product_name
 * @property int $qty_needed
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\ProcurementRequest $request
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereQtyNeeded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProcurementRequestItem extends Model
{
    protected $table = 'procurement_request_items';

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        'request_id',   // FK → procurement_requests (thuộc yêu cầu nào)
        'product_id',   // FK → products (nullable — SP có thể chưa có trong hệ thống)
        'product_name', // Tên SP admin tự nhập (dùng khi product_id = null)
        'qty_needed',   // Số lượng admin cần nhập
        'note',         // Ghi chú đặc biệt cho dòng SP này
    ];

    /**
     * Sản phẩm tương ứng trong hệ thống (nếu đã tồn tại).
     * Dùng để hiển thị ảnh, danh mục, thương hiệu trong giao diện xem yêu cầu.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Yêu cầu nhập hàng chứa dòng này.
     * Dùng để navigate ngược lên yêu cầu gốc.
     */
    public function request()
    {
        return $this->belongsTo(ProcurementRequest::class, 'request_id', 'id');
    }
}
