<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    //
    
    //
       protected $table= "carts";
    protected $fillable = ['idUser','idPV','quantity'];
    public function productVariant(){
        return $this->belongsTo('App\Models\ProductVariant','idPV','id');
    }
     public function user(){
        return $this->belongsTo('App\Models\User','idUser','id');
    }
}
