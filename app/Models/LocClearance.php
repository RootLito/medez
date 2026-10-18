<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocClearance extends Model
{
    use HasFactory;

    protected $table = 'loc_clearances';

    protected $guarded = [];

    protected $casts = [
        'is_walk_in' => 'boolean',
        'has_written_notice' => 'boolean',
        'has_related_action' => 'boolean',
        'is_tenanted' => 'boolean',
        'date_issued' => 'date',
        'date_of_receipt' => 'date',
        'notice_date' => 'date',
        'action_date_filed' => 'date',
        'amount_paid' => 'decimal:2',
        'lot_area_sqm' => 'decimal:2',
        'building_area_sqm' => 'decimal:2',
        'perimeter_m' => 'decimal:2',
        'project_cost' => 'decimal:2',
        'requirements' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function scopeOnline($query)
    {
        return $query->where('is_walk_in', false);
    }

    public function scopeWalkIn($query)
    {
        return $query->where('is_walk_in', true);
    }
}