<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    protected $table= 'comments';
    protected $fillable = ['idProduct','name','chat'];
     public function product(){
        return $this->belongsTo('App\Models\Product','idProduct','id');
    }
}
