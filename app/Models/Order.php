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
