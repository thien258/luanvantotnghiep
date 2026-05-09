<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Title extends Model
{
    //
        protected $table ='title';
    public $timestamps = false; 
    protected $primaryKey = 'idTitle';
    protected $fillable = ['title','image','button','descrip','idHeader'];
    public function header(){
        return $this->belongsTo('App\Models\Header','idHeader','id');
    }
}
