<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "products";
    protected $fillable = ["id", "title", 'image', 'decription', 'status', 'idConcentration', 'idBrand', 'idCategory'];
    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'idCategory', 'id');
    }
    public function concentration()
    {
        return $this->belongsTo('App\Models\Concentration', 'idConcentration', 'id');
    }
    public function brand()
    {
        return $this->belongsTo('App\Models\Brand', 'idBrand', 'id');
    }
    public function variants()
    {
        return $this->hasMany('App\Models\ProductVariant', 'idProduct', 'id');
    }

  
    public function comment()
    {
        return $this->hasMany('App\Models\Comment', 'idProduct', 'id');
    }

    

}
