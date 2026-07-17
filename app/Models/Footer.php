<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * @property int $id
 * @property string $header
 * @property string $textheader
 * @property string $header2
 * @property string $address
 * @property int $phone
 * @property string $email
 * @property int|null $created_by
 * @property string|null $created_at
 * @property string|null $updated_at
 * @mixin \Eloquent
 */
class Footer extends Model
{
    protected $table      = 'footer';
    public    $timestamps = false;
    protected $fillable   = ['header', 'textheader', 'header2', 'address', 'phone', 'email', 'created_by'];

    /**
     * Admin đã tạo/cập nhật thông tin footer này.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
