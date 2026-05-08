<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table="products";
      protected $fillable=["id","title",'image','decription','price','status','concentration','stock','volume','idCategory'];
    public function category(){
        return $this->belongsTo('App\Models\Category','idCategory','id');
    }
  public function comment(){
return $this->hasMany('App\Models\Comment','idProduct','id');
    }
      public function love(){
return $this->hasMany('App\Models\Love','idProduct','id');
    }
}
