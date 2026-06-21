<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $request_id
 * @property int|null $product_id
 * @property string $product_name
 * @property int $qty_needed
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\ProcurementRequest $request
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereQtyNeeded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcurementRequestItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProcurementRequestItem extends Model
{
    protected $table = 'procurement_request_items';

    protected $fillable = [
        'request_id',
        'product_id',
        'product_name',
        'qty_needed',
        'note'
    ];

    // SP trong hệ thống
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // Yêu cầu chứa dòng này
    public function request()
    {
        return $this->belongsTo(ProcurementRequest::class, 'request_id', 'id');
    }
}
