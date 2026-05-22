<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class home extends Model
{
    //

    protected $table = 'home';
    protected $fillable = ['name', 'description', 'image'];
    
}
