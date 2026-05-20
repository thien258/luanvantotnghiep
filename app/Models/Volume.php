<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Volume extends Model
{
    //
    protected $table = 'volumes';
    public $timestamps = false;

    protected $fillable = ['id', 'name', 'status'];
 
    public function productVariant()
    {
        return $this->hasMany('App\Models\ProductVariant', 'idVolume', 'id');
    }
}
