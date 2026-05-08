<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
       protected $table= "orders";
    protected $fillable = ["idUser","idProduct",'price','address'];
    public function product(){
        return $this->belongsTo('App\Models\Product','idProduct','id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User','idUser','id');
    }
}


