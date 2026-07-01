<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Brand — Thương hiệu nước hoa.
 *
 * Lưu thông tin các thương hiệu được bán trong hệ thống.
 * Ví dụ: Chanel, Dior, Gucci, Versace, ...
 *
 * status: 1 = đang hoạt động, 0 = ẩn
 *         (thương hiệu ẩn vẫn giữ liên kết với sản phẩm nhưng không hiển thị trên menu)
 *
 * Bảng: brands
 *
 * @property int $id
 * @property string $name
 * @property string $image
 * @property string $descrip
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $product
 * @property-read int|null $product_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereDescrip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Brand extends Model
{
    protected $table = "brands";

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        "id",
        "name",    // Tên thương hiệu (VD: Chanel, Dior)
        'image',   // Đường dẫn logo / ảnh đại diện thương hiệu
        'descrip', // Mô tả ngắn về thương hiệu
        'status',  // 1 = đang hiển thị, 0 = ẩn
    ];

    /**
     * Quan hệ: 1 thương hiệu có nhiều sản phẩm.
     * Dùng cột idBrand làm khóa ngoại ở bảng products.
     */
    public function product()
    {
        return $this->hasMany('App\Models\Product', 'idBrand', 'id');
    }
}
