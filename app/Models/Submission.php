<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'birth_date',
        'death_date',
        'location',
        'contributions',
        'death_reason',
        'profile_picture',
        'is_approved',
        'approved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'death_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }
}
