<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function product(){
        return $this->belongsTo('App\Models\Product','idProduct','id');
    }

    public function volume(){
        return $this->belongsTo('App\Models\Volume','idVolume','id');
    }

    public function cart(){
        return $this->hasMany('App\Models\Cart','idVariant','id');
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
}