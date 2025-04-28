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
     * Décrémente le nombre de prix restants
     * - Ne décrémente que si remaining > 0
     * - Si remaining n'est pas défini, l'initialise avec quantity
     */
    public function decrementRemaining()
    {
        // Si remaining est null, l'initialiser avec quantity
        if ($this->remaining === null) {
            $this->remaining = $this->quantity;
        }
        
        // Ne décrémenter que si remaining > 0
        if ($this->remaining > 0) {
            // Décrémenter une seule unité à la fois
            $this->remaining -= 1;
            $this->save();
            
            // Log pour déboguer le problème de double décrémentation
            \Illuminate\Support\Facades\Log::info('Décrémentation du stock pour la distribution #' . $this->id, [
                'distribution_id' => $this->id,
                'prize_id' => $this->prize_id,
                'remaining_before' => $this->remaining + 1,
                'remaining_after' => $this->remaining,
                'caller' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'inconnu'
            ]);
            
            return true;
        }
        
        return false;
    }
}
