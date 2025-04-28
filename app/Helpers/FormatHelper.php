<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Formate un montant en FCFA avec séparateur de milliers
     * @param float|int $amount
     * @return string
     */
    public static function fcfa($amount)
    {
        return number_format($amount, 0, ',', ' ') . ' FCFA';
    }
}
