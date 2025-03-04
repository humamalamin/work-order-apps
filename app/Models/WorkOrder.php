<?php

namespace App\Models;

use App\CreatedByTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use CreatedByTrait;
    protected $fillable = [
        'work_order_number',
        'product_name',
        'quantity',
        'deadline',
        'status',
        'assigned_operator_id',
        'created_by_id',
    ];

    public const STATUS_PENDING = 'Pending';
    public const STATUS_IN_PROGRESS = 'In Progress';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELED = 'Canceled';

    public function assignedOperator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }

    public function productionLogs(): HasMany
    {
        return $this->hasMany(ProductionLog::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Generate work_order_number sebelum data disimpan
        static::creating(function ($workOrder) {
            $workOrder->work_order_number = self::generateWorkOrderNumber();
        });
    }

    protected static function generateWorkOrderNumber(): string
    {
        // Format: WO-YYYYMMDD-XXX
        $prefix = 'WO-' . now()->format('Ymd') . '-';
        $lastWorkOrder = self::where('work_order_number', 'like', $prefix . '%')->latest()->first();

        if ($lastWorkOrder) {
            $lastNumber = (int) substr($lastWorkOrder->work_order_number, -3); // Ambil 3 digit terakhir
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT); // Increment dan pad dengan 0
        } else {
            $nextNumber = '001'; // Jika belum ada work order hari ini
        }

        return $prefix . $nextNumber;
    }

}
