<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $discount
 * @property int $status
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static Builder<static>|Festival active()
 * @method static \Database\Factories\FestivalFactory factory($count = null, $state = [])
 * @method static Builder<static>|Festival newModelQuery()
 * @method static Builder<static>|Festival newQuery()
 * @method static Builder<static>|Festival query()
 * @method static Builder<static>|Festival whereCreatedAt($value)
 * @method static Builder<static>|Festival whereDiscount($value)
 * @method static Builder<static>|Festival whereEndDate($value)
 * @method static Builder<static>|Festival whereId($value)
 * @method static Builder<static>|Festival whereName($value)
 * @method static Builder<static>|Festival whereStartDate($value)
 * @method static Builder<static>|Festival whereStatus($value)
 * @method static Builder<static>|Festival whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'integer',
        'discount' => 'integer',
    ];

    public function products()
    {
        return $this->belongsToMany('App\Models\Product', 'festival_product', 'idFestival', 'idProduct');
    }

    public function scopeActive(Builder $query): Builder
    {
        $today = Carbon::today()->toDateString();

        return $query
            ->where('status', 1)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);
    }

    public function isActive(): bool
    {
        if ((int) $this->status !== 1) {
            return false;
        }

        $today = Carbon::today();

        return $this->start_date->lte($today)
            && $this->end_date->gte($today);
    }

    public static function expireOutdated(): int
    {
        return static::query()
            ->where('status', 1)
            ->whereDate('end_date', '<', Carbon::today())
            ->update(['status' => 0]);
    }
}
