<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prize extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'value',
        'image_url',
        'stock',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'decimal:2',
        'stock' => 'integer',
    ];

    /**
     * Obtenir les distributions de prix associées au prix.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(PrizeDistribution::class);
    }

    /**
     * Obtenir les participations associées au prix.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }
}
