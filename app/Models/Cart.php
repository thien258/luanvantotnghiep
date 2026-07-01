<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cart — Giỏ hàng của khách hàng.
 *
 * Mỗi bản ghi = 1 sản phẩm trong giỏ của 1 user.
 * Nếu user thêm cùng 1 sản phẩm nhiều lần → cập nhật quantity thay vì tạo dòng mới.
 *
 * Giỏ hàng được lưu trong DB (không dùng session), nên khách đăng nhập
 * ở thiết bị khác vẫn giữ nguyên giỏ hàng.
 *
 * Bảng: carts
 *
 * @property int $id
 * @property int $quantity
 * @property int $idUser
 * @property int $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Cart extends Model
{
    protected $table = "carts";

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        'idUser',     // FK → users (chủ sở hữu giỏ hàng)
        'product_id', // FK → products (sản phẩm được thêm vào giỏ)
        'quantity',   // Số lượng sản phẩm trong giỏ
    ];

    /**
     * Quan hệ: Dòng giỏ hàng này thuộc về sản phẩm nào.
     * Dùng để lấy thông tin giá, tên, ảnh khi hiển thị trang giỏ hàng.
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    /**
     * Quan hệ: Dòng giỏ hàng này thuộc về user nào.
     * Dùng cột idUser thay vì chuẩn user_id của Laravel.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'idUser', 'id');
    }
}
