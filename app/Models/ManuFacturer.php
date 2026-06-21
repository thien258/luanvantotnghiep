<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ManuFacturer — Nhà Sản Xuất (NSX).
 *
 * Lưu thông tin liên hệ của NSX/nhà cung cấp.
 * Quan hệ many-to-many với Product thông qua bảng trung gian manufacturers_product.
 *
 * Bảng trung gian manufacturers_product:
 *   - Ghi nhận NSX nào cung cấp sản phẩm nào (danh bạ cố định)
 *   - Được tự động sync khi admin tạo PurchaseOrder
 *   - Dùng để gợi ý SP khi NSX tạo báo giá mới
 *
 * Bảng: manufacturers
 *
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property string|null $address
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManuFacturer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManuFacturer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManuFacturer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManuFacturer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManuFacturer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManuFacturer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManuFacturer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManuFacturer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManuFacturer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ManuFacturer extends Model
{
    protected $table = 'manufacturers';

    protected $fillable = [
        'name',    // Tên NSX / nhà cung cấp
        'phone',   // Số điện thoại liên hệ
        'address', // Địa chỉ nhà máy / kho
    ];

    /**
     * Danh sách sản phẩm NSX này có thể cung cấp (danh bạ).
     * Many-to-many qua bảng manufacturers_product.
     *
     * Dùng syncWithoutDetaching() để thêm SP mới vào danh bạ
     * mà không xóa quan hệ cũ khi admin tạo đơn đặt hàng mới.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'manufacturers_product', 'manufacturer_id', 'product_id');
    }
}
