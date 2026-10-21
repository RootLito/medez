<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentOrder extends Model
{
    use HasFactory;

    protected $table = 'payment_orders';

    protected $fillable = [
        'application_id',
        'assessment_no',
        'project_cost',
        'category_type',
        'base_clearance_fee',
        'excess_cost',
        'excess_fee',
        'total_clearance_fee',
        'processing_fee',
        'inspection_fee',
        'surcharge_fee',
        'grand_total',
        'remarks',
        'assessed_by',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'project_cost' => 'decimal:2',
        'base_clearance_fee' => 'decimal:2',
        'excess_cost' => 'decimal:2',
        'excess_fee' => 'decimal:2',
        'total_clearance_fee' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'inspection_fee' => 'decimal:2',
        'surcharge_fee' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    /**
     * Get the location clearance associated with the payment order.
     */
    public function locClearance(): BelongsTo
    {
        return $this->belongsTo(LocClearance::class, 'application_id');
    }
}
