<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $quantity
 * @property int $idUser
 * @property int $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Cart extends Model
{
    //

    //
    protected $table = "carts";
    protected $fillable = ['idUser', 'product_id', 'quantity'];
    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'idUser', 'id');
    }
}
