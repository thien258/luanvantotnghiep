<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $table = "orders";
    protected $fillable = ['idUser', 'fullname', 'phone', 'address', 'payment_method', 'total_price', 'status', 'note', 'tracking_code'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'idUser', 'id');
    }
    public function details()
    {
        return $this->hasMany('App\Models\OrderDetail', 'idOrder', 'id');
    }
}
