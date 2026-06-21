<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order — Đơn hàng của khách hàng.
 *
 * Trạng thái (status):
 *   0 — Chờ thanh toán PayOS (Bank Transfer chưa thanh toán)
 *   1 — Đã thanh toán / COD đã đặt, chờ xử lý
 *   3 — Đã xuất kho, đang giao shipper
 *   4 — Giao hàng thành công (khách xác nhận)
 *   5 — Khách yêu cầu hoàn hàng
 *   6 — Hàng hỏng / chờ trả nhà sản xuất
 *
 * payment_method: 'COD' hoặc 'BANK TRANSFER'
 * tracking_code: mã QR để khách xác nhận nhận hàng
 *
 * Bảng: orders
 *
 * @property int $id
 * @property int $idUser
 * @property string|null $fullname
 * @property string|null $phone
 * @property string $address
 * @property string $payment_method
 * @property int $total_price
 * @property int $status
 * @property string|null $note
 * @property string|null $tracking_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderDetail> $details
 * @property-read int|null $details_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTrackingCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    protected $table = "orders";

    protected $fillable = [
        'idUser',         // FK → users (khách hàng đặt đơn)
        'fullname',       // Tên người nhận
        'phone',          // SĐT người nhận
        'address',        // Địa chỉ giao hàng
        'payment_method', // Phương thức: COD / BANK TRANSFER
        'total_price',    // Tổng tiền đơn hàng (₫)
        'status',         // Trạng thái (xem mô tả trên)
        'note',           // Ghi chú của khách
        'tracking_code',  // Mã QR xác nhận giao hàng (unique)
    ];

    /**
     * Khách hàng đặt đơn này.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'idUser', 'id');
    }

    /**
     * Danh sách sản phẩm trong đơn hàng.
     * 1 đơn có nhiều dòng OrderDetail.
     */
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'idOrder', 'id');
    }
}
