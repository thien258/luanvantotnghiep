<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concentration extends Model
{
    //
      protected $table ='concentrations';
    public $timestamps = false;

     protected $fillable = ['id','concentration','status'];
     public function product(){
        return $this->hasMany('App\Models\Product','idProduct','id');
    }
}
