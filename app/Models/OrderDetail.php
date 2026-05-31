<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    //
    protected $table = "order_details";
    protected $fillable = ['idOrder', 'idProduct', 'name', 'quantity', 'price'];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
