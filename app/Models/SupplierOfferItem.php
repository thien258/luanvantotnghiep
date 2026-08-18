<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SupplierOfferItem — Dòng sản phẩm trong phiếu báo giá NSX.
 *
 * Mỗi dòng là 1 SP mà NSX đang chào giá.
 *
 * Lưu ý quan trọng:
 *   - KHÔNG có cột quantity: NSX chỉ chào giá, KHÔNG quyết định số lượng.
 *     Số lượng mua do admin tự điền khi tạo PurchaseOrderItem.
 *   - product_id nullable: SP có thể chưa tồn tại trong hệ thống khi NSX báo giá.
 *   - product_name luôn có: phòng trường hợp product bị xóa sau này,
 *     tên SP vẫn còn trong lịch sử báo giá.
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

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        "offer_id",
        "product_id",
        "product_name",
        "unit_price",
        "note",
        "image",
        "volume",
        "concentration_text",
        "category_text",
        "brand_text",
    ];

    /**
     * Phiếu báo giá chứa dòng sản phẩm này.
     * Dùng để truy ngược về thông tin NSX, trạng thái báo giá.
     */
    public function offer()
    {
        return $this->belongsTo(SupplierOffer::class, "offer_id", "id");
    }

    /**
     * Sản phẩm tương ứng trong hệ thống (nullable).
     * Dùng để hiển thị ảnh, dung tích, danh mục, thương hiệu
     * trong bảng báo giá cho admin tiện so sánh.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, "product_id", "id");
    }
}
