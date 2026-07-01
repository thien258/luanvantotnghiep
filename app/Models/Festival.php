<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Festival — Model đại diện cho chương trình khuyến mãi / sự kiện.
 *
 * Mỗi festival có mức giảm giá (discount %), trạng thái bật/tắt,
 * ngày bắt đầu và ngày kết thúc.
 * Quan hệ many-to-many với Product qua bảng trung gian festival_product.
 *
 * @property int $id
 * @property string $name
 * @property int $discount
 * @property int $status
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static Builder<static>|Festival active()
 * @method static \Database\Factories\FestivalFactory factory($count = null, $state = [])
 * @method static Builder<static>|Festival newModelQuery()
 * @method static Builder<static>|Festival newQuery()
 * @method static Builder<static>|Festival query()
 * @method static Builder<static>|Festival whereCreatedAt($value)
 * @method static Builder<static>|Festival whereDiscount($value)
 * @method static Builder<static>|Festival whereEndDate($value)
 * @method static Builder<static>|Festival whereId($value)
 * @method static Builder<static>|Festival whereName($value)
 * @method static Builder<static>|Festival whereStartDate($value)
 * @method static Builder<static>|Festival whereStatus($value)
 * @method static Builder<static>|Festival whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Festival extends Model
{
    use HasFactory;

    protected $table = 'festivals';

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        'name',
        'discount',   // % giảm giá áp dụng cho toàn bộ sản phẩm trong festival
        'status',     // 1 = đang hoạt động, 0 = tắt
        'start_date',
        'end_date',
    ];

    // Ép kiểu để Carbon / integer xử lý đúng khi so sánh ngày
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'status'     => 'integer',
        'discount'   => 'integer',
    ];

    /**
     * Danh sách sản phẩm thuộc chương trình này.
     * Many-to-many qua bảng trung gian festival_product.
     * Khóa: idFestival → id của festivals, idProduct → id của products.
     */
    public function products()
    {
        return $this->belongsToMany('App\Models\Product', 'festival_product', 'idFestival', 'idProduct');
    }

    /**
     * Scope lọc các festival đang hoạt động.
     * Điều kiện: status = 1 VÀ hôm nay nằm trong [start_date, end_date].
     * Dùng trong AppServiceProvider để chia sẻ $festivals cho navbar.
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = Carbon::today()->toDateString();

        return $query
            ->where('status', 1)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);
    }

    /**
     * Kiểm tra festival này có đang hoạt động không (dùng cho 1 instance cụ thể).
     * Khác scopeActive — method này gọi trên object, không phải query builder.
     */
    public function isActive(): bool
    {
        // Nếu status không phải 1 → không hoạt động
        if ((int) $this->status !== 1) {
            return false;
        }

        $today = Carbon::today();

        // Hôm nay phải nằm trong khoảng [start_date, end_date]
        return $this->start_date->lte($today)
            && $this->end_date->gte($today);
    }

    /**
     * Tự động tắt các festival đã hết hạn (end_date < hôm nay).
     * Được gọi trong AppServiceProvider::boot() mỗi khi app khởi động.
     * Trả về số bản ghi đã được cập nhật.
     */
    public static function expireOutdated(): int
    {
        return static::query()
            ->where('status', 1)
            ->whereDate('end_date', '<', Carbon::today())
            ->update(['status' => 0]);
    }
}
