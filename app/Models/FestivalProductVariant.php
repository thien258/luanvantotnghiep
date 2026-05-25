<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FestivalProductVariant extends Model
{
    //
    protected $table = 'festival_product_variant';
    protected $fillable = [
        'festival_id',
        'product_variant_id',
        'discount',
    ];
    public function festival()
    {
        return $this->belongsTo('App\Models\Festival', 'festival_id', 'id');
    }
    public function productVariant()
    {
        return $this->belongsTo('App\Models\ProductVariant', 'product_variant_id', 'id');
    }       
}
