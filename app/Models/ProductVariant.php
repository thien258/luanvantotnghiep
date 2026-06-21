<?php

namespace App\Models;

use App\Models\Festival;
use App\Models\FestivalProductVariant;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cart> $cart
 * @property-read int|null $cart_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Festival> $festival
 * @property-read int|null $festival_count
 * @property-read mixed $final_price
 * @property-read \App\Models\Product|null $product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Festival> $specificFestivals
 * @property-read int|null $specific_festivals_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant query()
 * @mixin \Eloquent
 */
class ProductVariant extends Model
{
    protected $table = 'product_variants';
    protected $fillable = [
        'id',
        'idProduct',
        'idVolume',
        'price',
        'stock',
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'idProduct', 'id');
    }

    public function volume()
    {
        return $this->belongsTo('App\Models\Volume', 'idVolume', 'id');
    }

    public function cart()
    {
        return $this->hasMany('App\Models\Cart', 'idVariant', 'id');
    }
    public function festival()
    {
        return $this->belongsToMany('App\Models\Festival', 'festival_product_variant', 'product_variant_id', 'festival_id');
    }
    protected static function boot()
    {
        parent::boot();

        // Đã đổi tên biến thành $variant để tránh nhầm lẫn với Product cha
        static::saving(function ($variant) {
            // Nếu người dùng không nhập stock (null), hoặc nhập số <= 0
            if (is_null($variant->stock) || $variant->stock <= 0) {
                $variant->stock = 0;   // CHỈ ép số lượng tồn kho về 0.

                // ĐÃ XÓA DÒNG GÁN STATUS Ở ĐÂY LÀ HẾT LỖI 100%
            }
        });
    }
    public function getFinalPriceAttribute()
    {
        $now = now();

        // 1. Lễ hội riêng: Kiểm tra thêm điều kiện Nằm trong thời gian diễn ra
        $variantDiscount = $this->specificFestivals()
            ->where('festivals.status', 1)
            ->where('festivals.start_date', '<=', $now) // Đã bắt đầu
            ->where('festivals.end_date', '>=', $now)   // Chưa kết thúc
            ->max('festivals.discount') ?? 0;

        // 2. Lễ hội chung: Cũng kiểm tra thời gian tương tự
        $productDiscount = $this->product->festivals()
            ->where('festivals.status', 1)
            ->where('festivals.start_date', '<=', $now)
            ->where('festivals.end_date', '>=', $now)
            ->max('festivals.discount') ?? 0;

        // 3. Lấy cái cao nhất trong 2 cái (Nếu hết hạn hết thì nó tự = 0)
        $finalDiscount = max($variantDiscount, $productDiscount);

        return round($this->price * (1 - ($finalDiscount / 100)));
    }
    public function specificFestivals()
    {
        // Quan hệ Nhiều-Nhiều: 1 Dung tích có thể có nhiều Lễ hội, 1 Lễ hội có thể có nhiều dung tích
        return $this->belongsToMany(
            Festival::class,
            'festival_product_variant', // Tên bảng trung gian
            'product_variant_id',       // Khóa ngoại của model hiện tại (Variant)
            'festival_id'               // Khóa ngoại của model liên kết tới (Festival)
        );
    }
}
