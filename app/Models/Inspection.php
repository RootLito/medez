<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inspection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inspections';

    protected $fillable = [
        'inspection_no',
        'application_id',
        'inspector_id',
        'inspection_type',
        'scheduled_date',
        'scheduled_time',
        'status',
        'compliance_status',
        'findings',
        'recommendations',
        'remarks',
        'inspected_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'inspected_at'   => 'datetime',
    ];

    /**
     * Clearance application being inspected
     */
    public function locClearance(): BelongsTo
    {
        return $this->belongsTo(LocClearance::class, 'application_id');
    }

    /**
     * Assigned Zoning Inspector
     */
    public function inspector(): BelongsTo
    {
        $adminModel = config('auth.providers.admins.model') 
            ?? (class_exists(Admin::class) ? Admin::class : User::class);

        return $this->belongsTo($adminModel, 'inspector_id');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->whereIn('status', ['scheduled', 'rescheduled', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}