<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * @property int $idTitle
 * @property string $title
 * @property string $image
 * @property string $button
 * @property string $descrip
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Title newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Title newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Title query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Title whereButton($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Title whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Title whereDescrip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Title whereIdTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Title whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Title whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Title whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Title extends Model
{
    //
        protected $table ='title';
    public $timestamps = false; 
    protected $primaryKey = 'idTitle';
    protected $fillable = ['title','image','button','descrip','idHeader','created_by'];

    public function header(){
        return $this->belongsTo('App\Models\Header','idHeader','id');
    }

    /**
     * Admin đã tạo banner/slide này.
     * nullable → trả về null nếu user đã bị xóa.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
