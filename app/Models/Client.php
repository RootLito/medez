<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Client extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'clients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'birthdate',
        'address',
        'username',
        'password',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship: One client has many locational clearances.
     */
    public function locClearances(): HasMany
    {
        return $this->hasMany(LocClearance::class, 'client_id');
    }
}
