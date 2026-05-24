<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Festival extends Model
{
    //
    use HasFactory;
    protected $table = 'festivals';
    protected $fillable = [
        'name',
        'discount',
        'status',
        'start_date',
        'end_date',
    ];
    public function products()
    {
        return $this->belongsToMany('App\Models\Product', 'festival_product', 'idFestival', 'idProduct');
    }
}
