<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

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
