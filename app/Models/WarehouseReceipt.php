<?php

namespace App\Models;
use App\Models\WarehouseStockLog;

use Illuminate\Database\Eloquent\Model;

class WarehouseReceipt extends Model
{
    //
    protected $table = "warehouse_receipts";
    protected $fillable = ['receipt_code','supplier','note','total_items'];
    public function stockLogs(){
        return $this->hasMany(WarehouseStockLog::class,'receipt_id','id');
    }
}
