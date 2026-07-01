<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Category — Danh mục phân loại sản phẩm.
 *
 * Dùng để phân nhóm sản phẩm theo đối tượng sử dụng.
 * Ví dụ: Nam, Nữ, Unisex, ...
 *
 * status: 1 = hiện, 0 = ẩn (danh mục ẩn không hiển thị trên menu mua hàng).
 *
 * Bảng: categories
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $product
 * @property-read int|null $product_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model
{
    protected $table = "categories";

    // Các cột được phép gán hàng loạt
    protected $fillable = [
        "id",
        "name",   // Tên danh mục (VD: Nam, Nữ, Unisex)
        'status', // 1 = hiện thị, 0 = ẩn
    ];

    /**
     * Quan hệ: 1 danh mục có nhiều sản phẩm.
     * Dùng cột idCategory làm khóa ngoại ở bảng products.
     */
    public function product()
    {
        return $this->hasMany('App\Models\Product', 'idCategory', 'id');
    }
}
