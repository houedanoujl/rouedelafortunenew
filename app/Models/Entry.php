<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Entry extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'participant_id',
        'contest_id',
        'has_played',
        'has_won'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_played' => 'boolean',
        'has_won' => 'boolean'
    ];

    /**
     * Obtenir le participant associé à cette participation.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Obtenir le concours associé à cette participation.
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * Obtenir le code QR associé à cette participation.
     */
    public function qrCode(): HasOne
    {
        return $this->hasOne(QrCode::class);
    }
}
