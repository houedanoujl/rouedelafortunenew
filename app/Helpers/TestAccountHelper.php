<?php

namespace App\Helpers;

class TestAccountHelper
{
    /**
     * Liste des domaines email autorisés pour le mode test
     */
    private static $allowedTestDomains = [
        'sifca.ci',
        'bigfiveabidjan.com',
        'bigfivesoutions.com' // Note: orthographe telle que spécifiée dans la demande
    ];

    /**
     * Adresse email spéciale pour le compte de test
     */
    private static $specialTestEmail = 'noob@saibot.com';

    /**
     * Vérifie si une adresse email correspond à un compte de test
     *
     * @param string|null $email
     * @return bool
     */
    public static function isTestAccount(?string $email): bool
    {
        if (!$email) {
            return false;
        }

        // Vérifier le compte de test spécial
        if ($email === self::$specialTestEmail) {
            return true;
        }

        // Vérifier les domaines autorisés
        $domain = substr(strrchr($email, "@"), 1);
        return in_array($domain, self::$allowedTestDomains);
    }

    /**
     * Obtenir l'entreprise associée au domaine de l'email
     *
     * @param string|null $email
     * @return string|null
     */
    public static function getCompanyName(?string $email): ?string
    {
        if (!$email) {
            return null;
        }

        if ($email === self::$specialTestEmail) {
            return 'Compte Test';
        }

        $domain = substr(strrchr($email, "@"), 1);
        
        if ($domain === 'sifca.ci') {
            return 'Sania';
        } elseif (in_array($domain, ['bigfiveabidjan.com', 'bigfivesoutions.com'])) {
            return 'Big Five';
        }

        return null;
    }
}
