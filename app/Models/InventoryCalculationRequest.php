<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCalculationRequest extends Model
{
    protected $fillable = [
        'product_id',
        'avg_daily_usage',
        'max_daily_usage',
        'lead_time_days',
        'safety_stock',
        'rop',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
