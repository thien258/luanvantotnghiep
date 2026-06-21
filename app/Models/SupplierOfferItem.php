<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SupplierOfferItem — Dòng sản phẩm trong báo giá NSX.
 *
 * Mỗi dòng là 1 sản phẩm NSX chào giá.
 * Lưu ý: KHÔNG có cột quantity ở đây vì NSX chỉ chào giá,
 * ADMIN mới là người quyết định mua bao nhiêu (quantity nằm trong PurchaseOrderItem).
 *
 * product_id có thể NULL nếu sản phẩm chưa tồn tại trong hệ thống.
 * product_name luôn có (lưu dự phòng phòng khi product bị xóa).
 *
 * Bảng: supplier_offer_items
 *
 * @property int $id
 * @property int $offer_id
 * @property int|null $product_id
 * @property string $product_name
 * @property numeric $unit_price
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SupplierOffer $offer
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem whereOfferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierOfferItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SupplierOfferItem extends Model
{
    protected $table = "supplier_offer_items";

    protected $fillable = [
        "offer_id",      // FK → supplier_offers (thuộc báo giá nào)
        "product_id",    // FK → products (nullable — SP có thể chưa có trong hệ thống)
        "product_name",  // Tên SP NSX tự ghi (dùng khi product_id = null)
        "unit_price",    // Giá NSX chào (₫)
        "note",          // Ghi chú thêm của NSX cho sản phẩm này
    ];

    /**
     * Báo giá chứa dòng sản phẩm này.
     */
    public function offer()
    {
        return $this->belongsTo(SupplierOffer::class, "offer_id", "id");
    }

    /**
     * Sản phẩm tương ứng trong hệ thống (nullable).
     * Dùng để lấy ảnh, dung tích, category, brand để hiển thị trong bảng.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, "product_id", "id");
    }
}
