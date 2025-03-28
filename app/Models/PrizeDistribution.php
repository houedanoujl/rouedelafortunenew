<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrizeDistribution extends Model
{
    use HasFactory;

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
}
