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
     * Accesseur pour obtenir l'URL complète de l'image
     * 
     * @return string
     */
    public function getImageUrlAttribute($value)
    {
        if (empty($value)) {
            return asset('img/prize_placeholder.jpg');
        }

        // Si l'URL commence par http:// ou https://, c'est déjà une URL complète
        if (strpos($value, 'http://') === 0 || strpos($value, 'https://') === 0) {
            return $value;
        }

        // Si l'URL est un chemin relatif, construire l'URL complète
        return asset('assets/prizes/' . basename($value));
    }

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
