<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
