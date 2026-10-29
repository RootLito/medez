<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne; 
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocClearance extends Model
{
    use HasFactory;

    protected $table = 'loc_clearances';

    protected $guarded = [];

    protected $casts = [
        'is_walk_in'          => 'boolean',
        'has_written_notice'  => 'boolean',
        'has_related_action'  => 'boolean',
        'is_tenanted'         => 'boolean',
        'date_issued'         => 'date',
        'date_of_receipt'     => 'date',
        'notice_date'         => 'date',
        'action_date_filed'   => 'date',
        'amount_paid'         => 'decimal:2',
        'lot_area_sqm'        => 'decimal:2',
        'building_area_sqm'   => 'decimal:2',
        'perimeter_m'         => 'decimal:2',
        'project_cost'        => 'decimal:2',
        'requirements'        => 'array',
        'is_archived'         => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    /**
     * Relationship to the Order of Payment
     * (Use hasOne if an application only has 1 payment order, or hasMany if re-assessments happen)
     */
    public function paymentOrder(): HasOne
    {
        return $this->hasOne(PaymentOrder::class, 'application_id');
    }

    public function scopeOnline($query)
    {
        return $query->where('is_walk_in', false);
    }

    public function scopeWalkIn($query)
    {
        return $query->where('is_walk_in', true);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'application_id');
    }

    public function latestInspection(): HasOne
    {
        return $this->hasOne(Inspection::class, 'application_id')->latestOfMany();
    }
}
