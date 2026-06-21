<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * OrderDetail — Dòng chi tiết trong đơn hàng.
 *
 * Mỗi dòng = 1 sản phẩm khách mua trong đơn.
 * price lưu giá tại thời điểm mua (không phụ thuộc giá hiện tại của SP).
 * name lưu tên SP tại thời điểm mua (dự phòng SP bị xóa sau).
 *
 * Bảng: order_details
 *
 * @property int $id
 * @property int $idProduct
 * @property int $idOrder
 * @property int $quantity
 * @property int $price
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereIdOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereIdProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderDetail extends Model
{
    protected $table = "order_details";

    protected $fillable = [
        'idOrder',   // FK → orders
        'idProduct', // FK → products (nullable nếu SP đã bị xóa)
        'name',      // Tên SP lúc mua (lưu snapshot tránh mất data)
        'quantity',  // Số lượng mua
        'price',     // Đơn giá lúc mua (có thể đã áp discount festival)
    ];

    /**
     * Sản phẩm tương ứng.
     * Có thể null nếu SP đã bị xóa khỏi hệ thống sau khi mua.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'idProduct', 'id');
    }
}
