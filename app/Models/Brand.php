<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    //
    protected $table="brands";
    protected $fillable=["id","name",'image','status'];
    public function product(){
        return $this->hasMany('App\Models\Product','idBrand','id');
    }
}
