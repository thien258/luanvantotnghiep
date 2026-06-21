<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $idUser
 * @property string $name
 * @property string $phone
 * @property string $address
 * @property int $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddress whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserAddress extends Model
{
    protected $table = 'user_addresses';

    protected $fillable = [
        'idUser',
        'name',
        'phone',
        'address',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'idUser', 'id');
    }
}
