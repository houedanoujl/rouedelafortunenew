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
        'image',
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
        'image' => 'string',
    ];

    /**
     * Accesseur pour obtenir l'URL dynamique de l'image du prix
     */
    public function getImageUrlAttribute($value)
    {
        if (empty($value)) {
            return asset('img/prize_placeholder.jpg');
        }
        // Si l'URL commence par http:// ou https://, c'est déjà une URL complète
        if (preg_match('#^https?://#', $value)) {
            return $value;
        }
        // Si l'URL commence par /, c'est un chemin relatif à la racine du site
        if (strpos($value, '/') === 0) {
            return url($value);
        }
        // Sinon, on suppose que c'est dans assets/prizes
        return asset('assets/prizes/' . $value);
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
