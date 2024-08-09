<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Martyr extends Model
{
    use HasFactory;
    protected $fillable = [
        'unique_slug',
        'email',
        'name',
        'birth_date',
        'death_date',
        'location',
        'contributions',
        'death_reason',
        'profile_picture',
        'is_active',
        'candles',
    ];

    protected static function boot()
    {
        parent::boot();

        // Use the 'creating' event to generate and set the unique slug
        static::creating(function ($martyr) {
            $key = $martyr->name . ' rose ' . $martyr->birth_date->format('Y-m-d'). ' died ' . $martyr->death_date->format('Y-m-d') . ' reason ' . $martyr->death_reason  ;
            $slug = Str::slug($key);
            $uniqueSlug = $slug;

            // Check for uniqueness and append a number if needed
            $counter = 1;
            while (static::where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = $slug . '-' . $counter;
                $counter++;
            }

            $martyr->slug = $uniqueSlug;
        });
    }

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
