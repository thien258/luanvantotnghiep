<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Love extends Model
{
    //
    
    //
       protected $table= "loves";
    protected $fillable = ['idUser','idProduct'];
   public function product(){
        return $this->belongsTo('App\Models\Product','idProduct','id');
    }

}
