<?php

// Créer un fichier d'utilitaire pour formater les dates sans fuseau horaire
namespace App\Utils;

class DateFormatter
{
    /**
     * Formate une date de format ISO 8601 en format compatible avec les inputs datetime-local.
     * Supprime le fuseau horaire pour éviter les erreurs dans les inputs HTML5.
     *
     * @param string|null  La date à formater
     * @return string|null La date formatée sans fuseau horaire
     */
    public static function formatForDateTimeInput(?string ): ?string
    {
        if (empty()) {
            return null;
        }
        
        try {
            // Créer un objet DateTime
             = new \DateTime();
            
            // Retourner au format yyyy-MM-ddThh:mm:ss sans fuseau horaire
            return ->format('Y-m-d\TH:i:s');
        } catch (\Exception ) {
            // En cas d'erreur, retourner null
            return null;
        }
    }
}
