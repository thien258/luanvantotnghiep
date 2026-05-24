<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "products";
    protected $fillable = ["id", "title", 'image', 'decription', 'status', 'idConcentration', 'idBrand', 'idCategory'];
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
    public function variants()
    {
        return $this->hasMany('App\Models\ProductVariant', 'idProduct', 'id');
    }
    public function festivals()
    {
        return $this->belongsToMany('App\Models\Festival', 'festival_product', 'idProduct', 'idFestival');
    }
  
    public function comment()
    {
        return $this->hasMany('App\Models\Comment', 'idProduct', 'id');
    }

    
public function getDiscountedPrice($originalPrice)
{
    // 1. Lấy ngày hôm nay chuẩn định dạng YYYY-MM-DD (Giúp khớp với định dạng date trong MySQL)
    $today = Carbon::today()->toDateString(); 

    // 2. Lấy số phần trăm giảm giá lớn nhất của các lễ hội đang chạy và còn hạn dùng
    // ⚠️ CHÚ Ý: Đổi 'festivals' thành đúng tên hàm quan hệ Nhiều-Nhiều của ông nếu đặt khác
    $maxDiscount = $this->festivals()
        ->where('status', 1)
        ->where('start_date', '<=', $today)  // Ngày bắt đầu nhỏ hơn hoặc bằng hôm nay
        ->where('end_date', '>=', $today)    // Ngày kết thúc lớn hơn hoặc bằng hôm nay
        ->max('discount'); // Đổi 'discount' thành đúng tên cột giảm giá trong bảng festivals của ông (trong ảnh bảng của ông ghi là "giảm giá" - check lại xem trong DB đặt tên cột là gì nhé)

    if ($maxDiscount > 0) {
        return $originalPrice * (1 - ($maxDiscount / 100));
    }

  
    return $originalPrice;
}
}
