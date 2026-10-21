<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'remarks',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function locClearance(): BelongsTo
    {
        return $this->belongsTo(LocClearance::class, 'application_id');
    }
}