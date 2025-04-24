<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Upload;

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
        'image_url' => 'string',
    ];

    /**
     * Obtenir les distributions de ce prix.
     */
    public function prizeDistributions(): HasMany
    {
        return $this->hasMany(PrizeDistribution::class);
    }

    /**
     * Obtenir les participations associées à ce prix.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }
}
