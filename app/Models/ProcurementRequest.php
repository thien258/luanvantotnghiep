<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementRequest extends Model
{
    //
    protected $table = 'procurement_requests';

    protected $fillable = [
        'request_code',  'status', 'note', 'deadline', 'created_by'
    ];

    // 1 yêu cầu có nhiều dòng SP
    public function items()
    {
        return $this->hasMany(ProcurementRequestItem::class, 'request_id', 'id');
    }

    // 1 yêu cầu có thể nhận nhiều báo giá từ NSX
    public function offers()
    {
        return $this->hasMany(SupplierOffer::class, 'request_id', 'id');
    }

    // Admin tạo yêu cầu
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
