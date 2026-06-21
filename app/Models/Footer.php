<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $header
 * @property string $textheader
 * @property string $header2
 * @property string $address
 * @property int $phone
 * @property string $email
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer whereHeader($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer whereHeader2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer whereTextheader($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Footer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Footer extends Model
{
    //
    protected $table ='footer';
    public $timestamps = false; 
    protected $fillable = ['header','textheader','header2','address','phone','email'];
}
