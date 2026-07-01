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
 *   price    : giá bán (VNĐ), có thể bị giảm bởi Festival
 *   volume   : dung tích (VD: 100ml)
 *
 * Quan hệ:
 *   belongsTo  Category, Brand, Concentration
 *   hasMany    Comment
 *   belongsToMany Festival (qua bảng festival_product)
 *   belongsToMany ManuFacturer (qua bảng manufacturers_product — danh bạ NSX)
 *
 * Tự động hóa:
 *   - Khi quantity về 0 → status tự chuyển sang 0 (ẩn) qua hook booted()
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

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        "id",
        "title",           // Tên sản phẩm
        'image',           // Đường dẫn ảnh sản phẩm
        'decription',      // Mô tả (tên cột typo trong DB gốc — giữ nguyên để khớp schema)
        'price',           // Giá bán (VNĐ)
        'quantity',        // Số lượng tồn kho
        'volume',          // Dung tích (VD: 100ml, 50ml)
        'status',          // 1 = đang bán, 0 = tạm ẩn / hết hàng
        'idConcentration', // FK → concentrations (nồng độ: EDP, EDT, ...)
        'idBrand',         // FK → brands (thương hiệu)
        'idCategory',      // FK → categories (danh mục: Nam, Nữ, Unisex, ...)
    ];

    // =========================================================================
    // QUAN HỆ (Relationships)
    // =========================================================================

    /** Danh mục sản phẩm (Nam / Nữ / Unisex ...). */
    public function category()
    {
        return $this->belongsTo(Category::class, 'idCategory', 'id');
    }

    /** Nồng độ nước hoa (EDP, EDT, EDP, Parfum ...). */
    public function concentration()
    {
        return $this->belongsTo(Concentration::class, 'idConcentration', 'id');
    }

    /** Thương hiệu sản phẩm (Chanel, Dior, ...). */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'idBrand', 'id');
    }

    /**
     * Các Festival đang áp dụng giảm giá cho sản phẩm này.
     * Quan hệ nhiều-nhiều qua bảng trung gian festival_product.
     * Cột pivot: idProduct, idFestival.
     */
    public function festivals()
    {
        return $this->belongsToMany(Festival::class, 'festival_product', 'idProduct', 'idFestival');
    }

    /** Danh sách bình luận / đánh giá của khách về sản phẩm này. */
    public function comment()
    {
        return $this->hasMany(Comment::class, 'idProduct', 'id');
    }

    /**
     * Danh sách nhà sản xuất (NSX) có thể cung cấp sản phẩm này.
     * Quan hệ nhiều-nhiều qua bảng manufacturers_product.
     * Được đồng bộ (sync) tự động khi admin tạo PurchaseOrder.
     */
    public function manufacturers()
    {
        return $this->belongsToMany(ManuFacturer::class, 'manufacturers_product', 'product_id', 'manufacturer_id');
    }

    // =========================================================================
    // BOOTED — Hook vòng đời model (tự động kích hoạt khi lưu)
    // =========================================================================

    /**
     * Đăng ký hook 'saving': chạy trước khi INSERT hoặc UPDATE.
     *
     * Logic: Khi tồn kho (quantity) giảm về 0 hoặc âm →
     *   - Ép quantity = 0 (tránh lưu giá trị âm vào DB)
     *   - Tự động ẩn sản phẩm (status = 0)
     *
     * Lưu ý: Khi nhập hàng lại, admin phải bật status thủ công — hệ thống KHÔNG
     * tự động bật lại để tránh hiển thị sản phẩm khi chưa được kiểm tra.
     */
    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            // isDirty('quantity') → chỉ xử lý khi cột quantity thực sự thay đổi
            if ($product->isDirty('quantity') && $product->quantity <= 0) {
                $product->quantity = 0;  // đảm bảo không lưu số âm
                $product->status   = 0;  // ẩn sản phẩm khỏi giao diện mua hàng
            }
        });
    }

    // =========================================================================
    // METHODS — Tính giá sau giảm từ Festival
    // =========================================================================

    /**
     * Tính giá sau khi áp dụng Festival (nếu có).
     *
     * Quy tắc hiển thị sản phẩm trong festival:
     *   - Nếu 1 SP có 2 festival đang active → chỉ hiện SP ở festival giảm GIÁ CAO HƠN.
     *   - Festival giảm giá thấp hơn sẽ ẩn SP đó đi (tránh khách thấy giá cao hơn).
     *   - Khi festival giảm giá cao hơn hết hạn → SP tự động hiện lại ở festival còn lại.
     *
     * @param Festival|null $festival
     *   - Truyền vào object Festival cụ thể: chỉ áp discount của festival đó.
     *     Dùng ở trang chi tiết festival: /festival/{id}
     *   - null: tìm và áp discount CAO NHẤT từ tất cả festival đang active.
     *     Dùng ở trang chủ / danh sách sản phẩm chung.
     *
     * @return float|int Giá sau giảm, hoặc giá gốc nếu không có festival nào áp dụng.
     */
    public function getDiscountedPrice(?Festival $festival = null)
    {
        $today = Carbon::today()->toDateString(); // Lấy ngày hôm nay để so sánh thời gian festival

        if ($festival !== null) {
            /*
             * Chế độ festival cụ thể:
             * Kiểm tra festival được truyền vào có:
             *   - status == 1 (đang kích hoạt)
             *   - start_date <= hôm nay (đã bắt đầu)
             *   - end_date >= hôm nay (chưa kết thúc)
             * Nếu thỏa → lấy discount, ngược lại discount = 0.
             */
            $maxDiscount = ($festival->status == 1
                && $festival->start_date->toDateString() <= $today
                && $festival->end_date->toDateString() >= $today)
                ? $festival->discount
                : 0;
        } else {
            /*
             * Chế độ tự động: query tất cả festival active của SP này,
             * lọc theo thời gian hợp lệ, lấy mức discount CAO NHẤT.
             * ?? 0 → mặc định 0 nếu không có festival nào phù hợp.
             */
            $maxDiscount = $this->festivals()
                ->where('status', 1)
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->max('discount') ?? 0;
        }

        // Nếu có giảm giá: tính giá = giá gốc × (1 - discount%)
        if ($maxDiscount > 0) {
            return $this->price * (1 - ($maxDiscount / 100));
        }

        // Không có festival → trả về giá gốc
        return $this->price;
    }
}
