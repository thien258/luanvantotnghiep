<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "products";

    protected $fillable = [
        "id",
        "title",
        'image',
        'decription',
        'price',
        'quantity',
        'volume',
        'status',
        'idConcentration',
        'idBrand',
        'idCategory'
    ];

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'idCategory', 'id');
    }

    public function concentration()
    {
        return $this->belongsTo('App\Models\Concentration', 'idConcentration', 'id');
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Brand', 'idBrand', 'id');
    }

    public function festivals()
    {
        return $this->belongsToMany('App\Models\Festival', 'festival_product', 'idProduct', 'idFestival');
    }

    public function comment()
    {
        return $this->hasMany('App\Models\Comment', 'idProduct', 'id');
    }
    public function manufacturers()
    {
        return $this->belongsToMany(ManuFacturer::class, 'manufacturers_product', 'product_id', 'manufacturer_id');
    }
    /**
     * Tự động chuyển status = 0 (off) khi số lượng về 0.
     */
    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if ($product->isDirty('quantity') && $product->quantity <= 0) {
                $product->quantity = 0;
                $product->status   = 0; // Hết hàng → tắt tự động
            }
        });
    }

    /**
     * Tính toán giá đã giảm dựa trên các sự kiện (Festivals) đang diễn ra.
     *
     * @param  Festival|null  $festival  Nếu truyền vào, chỉ áp dụng discount của festival đó.
     *                                   Nếu null, lấy discount cao nhất từ tất cả festival đang active.
     */
    public function getDiscountedPrice(?Festival $festival = null)
    {
        $today = Carbon::today()->toDateString();

        if ($festival !== null) {
            // Chỉ áp dụng discount của festival được chỉ định (nếu nó đang active)
            $maxDiscount = ($festival->status == 1
                && $festival->start_date->toDateString() <= $today
                && $festival->end_date->toDateString() >= $today)
                ? $festival->discount
                : 0;
        } else {
            // Lấy mức giảm giá cao nhất từ tất cả sự kiện đang diễn ra
            $maxDiscount = $this->festivals()
                ->where('status', 1)
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->max('discount') ?? 0;
        }

        if ($maxDiscount > 0) {
            return $this->price * (1 - ($maxDiscount / 100));
        }

        return $this->price;
    }
}
