<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "products";
    
    protected $fillable = [
        "id", "title", 'image', 'decription', 'price', 'quantity', 'volume', 
        'status', 'idConcentration', 'idBrand', 'idCategory'
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
     */
    public function getDiscountedPrice()
    {
        // 1. Lấy ngày hôm nay chuẩn định dạng YYYY-MM-DD
        $today = Carbon::today()->toDateString(); 

        // 2. Tìm mức giảm giá cao nhất từ các sự kiện đang diễn ra
        $maxDiscount = $this->festivals()
            ->where('status', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->max('discount') ?? 0;

        // 3. Nếu có giảm giá, tính dựa trên giá bán gốc nằm tại bảng products
        if ($maxDiscount > 0) {
            return $this->price * (1 - ($maxDiscount / 100));
        }

        // 4. Nếu không có giảm giá, trả về giá bán gốc của sản phẩm
        return $this->price;
    }
}