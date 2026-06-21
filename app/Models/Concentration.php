<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $concentration
 * @property int $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $product
 * @property-read int|null $product_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concentration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concentration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concentration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concentration whereConcentration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concentration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concentration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concentration whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Concentration whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
