<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrizeDistribution extends Model
{
    use HasFactory;
    
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($prizeDistribution) {
            // Initialiser la quantité restante avec la quantité totale si non spécifiée
            if (is_null($prizeDistribution->remaining)) {
                $prizeDistribution->remaining = $prizeDistribution->quantity;
            }
        });
    }

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'contest_id',
        'prize_id',
        'quantity',
        'start_date',
        'end_date',
        'remaining',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'quantity' => 'integer',
        'remaining' => 'integer',
    ];

    /**
     * Obtenir le concours associé à cette distribution.
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * Obtenir le prix associé à cette distribution.
     */
    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }
    
    /**
     * Décrémenter la quantité restante d'une unité
     * 
     * @return bool True si le stock a pu être décrémenté, false si le stock est déjà à 0
     */
    public function decrementRemaining(): bool
    {
        if ($this->remaining > 0) {
            $this->remaining--;
            $this->save();
            return true;
        }
        
        return false;
    }
}
