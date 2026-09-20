<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocClearance extends Model
{
    use HasFactory;

    protected $table = 'loc_clearances';

    /**
     * Allow mass assignment for all attributes.
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_written_notice' => 'boolean',
        'has_related_action' => 'boolean',
        'is_tenanted' => 'boolean',
        'notice_date' => 'date',
        'action_date_filed' => 'date',
    ];

    /**
     * Relationship: Clearance belongs to a Client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
