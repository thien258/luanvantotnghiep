<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $idProduct
 * @property string $name
 * @property string $chat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereChat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereIdProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Comment extends Model
{
    //
    protected $table= 'comments';
    protected $fillable = ['idProduct','name','chat','rating','user_id','order_detail_id'];
     public function product(){
        return $this->belongsTo('App\Models\Product','idProduct','id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }
public function orderDetail(){
        return $this->belongsTo('App\Models\OrderDetail','order_detail_id','id');
    }
    
}
