<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
   protected $table= "categories";
    protected $fillable = ["id","name",'status'];
        public function product(){
return $this->hasMany('App\Models\Product','idProduct','id');
    }
}
