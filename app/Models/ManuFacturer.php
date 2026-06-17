<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManuFacturer extends Model
{
    //
    protected $table = 'manufacturers';
    protected $fillable = [
        'name',
        'phone',
        'address',
    ];
    public function products()
    {
        return $this->belongsToMany(Product::class, 'manufacturers_product', 'manufacturer_id', 'product_id');
    }
}
