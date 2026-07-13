<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RootActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
