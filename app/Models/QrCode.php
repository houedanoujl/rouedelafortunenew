<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class QrCode extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entry_id',
        'code',
        'scanned',
        'scanned_at',
        'scanned_by',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scanned' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    /**
     * Obtenir la participation associée à ce code QR.
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    /**
     * Obtenir l'utilisateur qui a scanné ce QR code.
     */
    public function scannedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
