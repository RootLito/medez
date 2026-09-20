<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'title',
        'zone_type',
        'latitude',
        'longitude',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}