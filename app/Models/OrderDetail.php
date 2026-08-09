<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * OrderDetail — Dòng chi tiết trong đơn hàng.
 *
 * Mỗi dòng tương ứng 1 sản phẩm khách mua trong đơn.
 *
 * Lưu ý thiết kế:
 *   - price lưu giá TẠI THỜI ĐIỂM MUA (không phụ thuộc giá hiện tại của sản phẩm).
 *   - name lưu tên sản phẩm lúc mua (dự phòng trường hợp sản phẩm bị xóa sau).
 *   Hai cột trên đảm bảo dữ liệu lịch sử đơn hàng không bị ảnh hưởng khi admin
 *   chỉnh sửa hoặc xóa sản phẩm.
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

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        'idOrder',   // FK → orders (đơn hàng chứa dòng này)
        'idProduct', // FK → products (nullable nếu sản phẩm đã bị xóa sau này)
        'name',      // Snapshot tên sản phẩm tại thời điểm mua — tránh mất data lịch sử
        'quantity',  // Số lượng mua
        'price',     // Đơn giá tại thời điểm mua (có thể đã áp giảm giá Festival)
    ];

    /**
     * Quan hệ: Dòng chi tiết thuộc về 1 sản phẩm (Product).
     * Kết quả có thể null nếu sản phẩm đã bị xóa khỏi hệ thống sau khi đặt hàng.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'idProduct', 'id');
    }

    /**
     * Quan hệ: Dòng chi tiết có thể có nhiều đánh giá (Comment).
     * Dùng để kiểm tra user đã đánh giá sản phẩm này trong đơn chưa.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'order_detail_id', 'id');
    }
}
