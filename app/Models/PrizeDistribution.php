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
    
    /**
     * Vérifie que l'intervalle de dates ne dépasse pas 1 jour.
     *
     * @param PrizeDistribution $prizeDistribution
     * @throws \InvalidArgumentException
     */
    protected static function validateDateRange(PrizeDistribution $prizeDistribution)
    {
        if ($prizeDistribution->start_date && $prizeDistribution->end_date) {
            $startDate = $prizeDistribution->start_date;
            $endDate = $prizeDistribution->end_date;
            
            // Vérifier que la date de fin est après la date de début
            if ($endDate->lt($startDate)) {
                throw new \InvalidArgumentException('La date de fin doit être postérieure à la date de début.');
            }
            
            // Vérifier que l'intervalle ne dépasse pas 24 heures (1 jour)
            $diffInHours = $startDate->diffInHours($endDate);
            if ($diffInHours > 48) {
                throw new \InvalidArgumentException('La période de distribution ne peut pas excéder 48 heures (2 jours).');
            }
        }
    }
    
    /**
     * Vérifie que les dates sont dans les limites du concours.
     *
     * @param PrizeDistribution $prizeDistribution
     * @throws \InvalidArgumentException
     */
    protected static function validateContestDates(PrizeDistribution $prizeDistribution)
    {
        if ($prizeDistribution->start_date && $prizeDistribution->end_date && $prizeDistribution->contest_id) {
            // Récupérer le concours associé
            $contest = Contest::find($prizeDistribution->contest_id);
            
            if ($contest) {
                // Vérifier que la date de début est après ou égale à la date de début du concours
                if ($prizeDistribution->start_date->lt($contest->start_date)) {
                    throw new \InvalidArgumentException(
                        'La distribution ne peut pas commencer avant le début du concours (' . 
                        $contest->start_date->format('d/m/Y H:i:s') . ').'
                    );
                }
                
                // Vérifier que la date de fin est avant ou égale à la date de fin du concours
                if ($prizeDistribution->end_date->gt($contest->end_date)) {
                    throw new \InvalidArgumentException(
                        'La distribution ne peut pas se terminer après la fin du concours (' . 
                        $contest->end_date->format('d/m/Y H:i:s') . ').'
                    );
                }
            }
        }
    }
}
