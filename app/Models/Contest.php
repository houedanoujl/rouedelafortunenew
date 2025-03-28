<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contest extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'description',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Obtenir les distributions de prix associées au concours.
     */
    public function prizeDistributions(): HasMany
    {
        return $this->hasMany(PrizeDistribution::class);
    }

    /**
     * Obtenir les participations associées au concours.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }
}
