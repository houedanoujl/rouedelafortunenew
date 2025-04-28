<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class LogHelper
{
    /**
     * Ajoute une ligne de séparation dans le fichier de log
     *
     * @param string $type Début ou fin de session
     * @return void
     */
    public static function addSessionSeparator($type = 'START')
    {
        $message = $type === 'START' ? 'DÉBUT DE SESSION' : 'FIN DE SESSION';
        
        Log::info('================================================================');
        Log::info('================ ' . $message . ' ' . date('Y-m-d H:i:s') . ' ================');
        Log::info('================================================================');
    }
}
