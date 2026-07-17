<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property string      $message
 * @property int|null    $user_id   ID user đã đăng nhập khi gửi liên hệ (null nếu khách vãng lai)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class Contact extends Model
{
    protected $table    = 'contacts';
    protected $fillable = ['name', 'email', 'message', 'user_id'];

    /**
     * User đã gửi liên hệ này (nullable — khách vãng lai không có tài khoản).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
