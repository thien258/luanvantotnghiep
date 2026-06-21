<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $file_path
 * @property string $original_name
 * @property string|null $supplier
 * @property string|null $note
 * @property int|null $uploaded_by
 * @property string $status
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $reviewer
 * @property-read \App\Models\User|null $uploader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseImport whereUploadedBy($value)
 * @mixin \Eloquent
 */
class WarehouseImport extends Model
{
    protected $table = 'warehouse_imports';
    protected $fillable = [
        'file_path', 'original_name', 'supplier', 'note',
        'uploaded_by', 'status', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
