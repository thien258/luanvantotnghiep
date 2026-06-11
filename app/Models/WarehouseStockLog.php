<?php

namespace App\Models;

use App\Models\WarehouseReceipt;


use Illuminate\Database\Eloquent\Model;

class WarehouseStockLog extends Model
{
    //
    protected $table = "warehouse_stock_logs";
    protected $fillable = ['receipt_id','product_id', 'type','quantity','stock_after','reason'];
    public function product(){
        return $this->belongsTo(Product::class,'product_id','id');
    }
    public function warehouse(){
        return $this->belongsTo(WarehouseReceipt::class,'receipt_id','id');
    }
}
