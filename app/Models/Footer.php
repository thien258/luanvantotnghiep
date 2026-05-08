<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Footer extends Model
{
    //
    protected $table ='footer';
    public $timestamps = false; 
    protected $fillable = ['header','textheader','header2','address','phone','email'];
}
