<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Product — Sản phẩm nước hoa.
 *
 * Các cột quan trọng:
 *   status   : 1 = đang bán, 0 = tạm ẩn / hết hàng
 *   quantity : tồn kho hiện tại
 *   price    : giá bán (₫), có thể bị giảm bởi Festival
 *   volume   : dung tích (VD: 100ml)
 *
 * Quan hệ:
 *   belongsTo  Category, Brand, Concentration
 *   hasMany    Comment
 *   belongsToMany Festival (qua festival_product)
 *   belongsToMany ManuFacturer (qua manufacturers_product — danh bạ NSX)
 *
 * Tự động hóa:
 *   - Khi quantity về 0 → status tự chuyển sang 0 (off) qua booted()
 *
 * Bảng: products
 *
 * @property int $id
 * @property string $title
 * @property string $decription
 * @property string|null $volume
 * @property int $price
 * @property int $quantity
 * @property string $image
 * @property int $idCategory
 * @property int|null $idBrand
 * @property string $status
 * @property int|null $idConcentration
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Brand|null $brand
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comment
 * @property-read int|null $comment_count
 * @property-read \App\Models\Concentration|null $concentration
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Festival> $festivals
 * @property-read int|null $festivals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ManuFacturer> $manufacturers
 * @property-read int|null $manufacturers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDecription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIdBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIdCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIdConcentration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVolume($value)
 * @mixin \Eloquent
 */
class Product extends Model
{
    protected $table = "products";

    protected $fillable = [
        "id",
        "title",         // Tên sản phẩm
        'image',         // URL ảnh sản phẩm
        'decription',    // Mô tả (typo trong DB gốc, giữ nguyên)
        'price',         // Giá bán (₫)
        'quantity',      // Tồn kho
        'volume',        // Dung tích (VD: 100ml, 50ml)
        'status',        // 1 = đang bán, 0 = ẩn
        'idConcentration', // FK → concentrations
        'idBrand',         // FK → brands
        'idCategory',      // FK → categories
    ];

    /** Danh mục sản phẩm (Nam / Nữ / Unisex). */
    public function category()
    {
        return $this->belongsTo(Category::class, 'idCategory', 'id');
    }

    /** Nồng độ nước hoa (EDP, EDT, ...). */
    public function concentration()
    {
        return $this->belongsTo(Concentration::class, 'idConcentration', 'id');
    }

    /** Thương hiệu (Chanel, Dior, ...). */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'idBrand', 'id');
    }

    /**
     * Các Festival đang áp dụng giảm giá cho sản phẩm này.
     * Many-to-many qua bảng festival_product.
     */
    public function festivals()
    {
        return $this->belongsToMany(Festival::class, 'festival_product', 'idProduct', 'idFestival');
    }

    /** Bình luận của khách hàng về sản phẩm. */
    public function comment()
    {
        return $this->hasMany(Comment::class, 'idProduct', 'id');
    }

    /**
     * Danh sách NSX có thể cung cấp sản phẩm này.
     * Many-to-many qua bảng manufacturers_product (danh bạ).
     * Được sync tự động khi admin tạo PurchaseOrder.
     */
    public function manufacturers()
    {
        return $this->belongsToMany(ManuFacturer::class, 'manufacturers_product', 'product_id', 'manufacturer_id');
    }

    // =========================================================================
    // BOOTED — Hook tự động khi lưu model
    // =========================================================================

    /**
     * Khi quantity thay đổi về 0 → tự động ẩn sản phẩm (status = 0).
     * Không tự bật lại khi nhập hàng (phải bật thủ công).
     */
    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            // Chỉ can thiệp khi cột quantity vừa thay đổi
            if ($product->isDirty('quantity') && $product->quantity <= 0) {
                $product->quantity = 0;     // đảm bảo không âm
                $product->status   = 0;     // ẩn sản phẩm
            }
        });
    }

    // =========================================================================
    // METHODS — Tính giá sau giảm
    // =========================================================================

    /**
     * Tính giá đã giảm dựa trên Festival đang diễn ra.
     *
     * Logic ẩn/hiện sản phẩm theo festival:
     *   - Nếu SP có 2 festival đang active → chỉ hiện ở festival có discount cao hơn
     *   - Festival discount thấp hơn sẽ ẩn SP đó đi
     *   - Khi festival cao hơn hết hạn → SP hiện lại ở festival thấp hơn (nếu còn hạn)
     *
     * @param Festival|null $festival
     *   - Truyền vào: chỉ áp discount của festival đó (dùng ở trang /festival/{id})
     *   - null: lấy discount cao nhất từ tất cả festival active (dùng ở trang chủ)
     */
    public function getDiscountedPrice(?Festival $festival = null)
    {
        $today = Carbon::today()->toDateString();

        if ($festival !== null) {
            // Kiểm tra festival được truyền vào có đang active và đúng thời gian không
            $maxDiscount = ($festival->status == 1
                && $festival->start_date->toDateString() <= $today
                && $festival->end_date->toDateString() >= $today)
                ? $festival->discount
                : 0;
        } else {
            // Lấy mức giảm cao nhất từ tất cả festival đang active + đúng thời gian
            $maxDiscount = $this->festivals()
                ->where('status', 1)
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->max('discount') ?? 0;
        }

        // Áp dụng giảm giá nếu có
        if ($maxDiscount > 0) {
            return $this->price * (1 - ($maxDiscount / 100));
        }

        return $this->price; // không có festival → trả giá gốc
    }
}
