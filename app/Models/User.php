<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User — Tài khoản người dùng trong hệ thống.
 *
 * Phân quyền qua cột 'role':
 *   'admin'    — Quản trị viên toàn hệ thống (không xem doanh thu)
 *   'director' — Giám đốc (chỉ xem doanh thu)
 *   'staff'    — Nhân viên (kho, xử lý đơn, ...)
 *   'customer' — Khách hàng thông thường
 *
 * Implement MustVerifyEmail: bắt buộc xác minh email trước khi dùng tài khoản.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $phone
 * @property string $address
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $role
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Các cột được phép gán hàng loạt (mass assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',           // Họ tên đầy đủ
        'email',          // Email đăng nhập (unique)
        'phone',          // Số điện thoại
        'password',       // Mật khẩu (được hash tự động qua cast bên dưới)
        'address',        // Địa chỉ mặc định
        'role',           // Vai trò: 'admin' | 'staff' | 'customer'
        'remember_token', // Token ghi nhớ đăng nhập
    ];

    /**
     * Các cột bị ẩn khi serialize (trả về JSON / array).
     * Đảm bảo password và remember_token không bao giờ lộ ra API response.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Định nghĩa cách cast dữ liệu khi đọc từ DB:
     *   - email_verified_at: string → Carbon datetime
     *   - password: tự động hash bằng bcrypt khi gán giá trị mới
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // =========================================================================
    // KIỂM TRA PHÂN QUYỀN (Role helpers)
    // =========================================================================

    /** Kiểm tra user có phải admin không. */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /** Kiểm tra user có phải nhân viên (staff) không. */
    public function isStaff()
    {
        return $this->role === 'staff';
    }

    /** Kiểm tra user có phải khách hàng thông thường không. */
    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    /** Kiểm tra user có phải giám đốc không. */
    public function isDirector()
    {
        return $this->role === 'director';
    }

    /**
     * Kiểm tra user có thuộc một trong các role truyền vào không.
     * Hỗ trợ nhiều role: $user->hasRole('admin', 'staff')
     *
     * @param string ...$role Danh sách role cần kiểm tra
     */
    public function hasRole(string ...$role): bool
    {
        return in_array($this->role, $role);
    }

    // =========================================================================
    // QUAN HỆ (Relationships)
    // =========================================================================

    /**
     * Quan hệ: 1 user (NSX) có thể gắn với 1 hồ sơ ManuFacturer.
     * Dùng khi tài khoản đăng nhập của nhà sản xuất cần lấy thông tin công ty.
     */
    public function manufacturer()
    {
        return $this->hasOne(ManuFacturer::class, 'user_id');
    }

    /**
     * Quan hệ: 1 user có nhiều đơn hàng.
     * Dùng cột idUser (thay vì chuẩn user_id) làm khóa ngoại ở bảng orders.
     */
    public function orders()
    {
        // 'idUser' là tên cột khóa ngoại ở bảng orders
        // 'id' là khóa chính của bảng users
        return $this->hasMany(Order::class, 'idUser', 'id');
    }
}
