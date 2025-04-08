<?php

// Définir les chemins pour les pages à modifier
$editPages = [
    'Admin/Resources/ContestResource/Pages/EditContest.php',
    'Admin/Resources/ParticipantResource/Pages/EditParticipant.php',
    'Admin/Resources/PrizeResource/Pages/EditPrize.php',
    'Admin/Resources/QrCodeResource/Pages/EditQrCode.php'
];

// Méthode de redirection à ajouter
$redirectMethod = <<<'PHP'

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Assure la redirection même si getRedirectUrl n'est pas appelé
    protected function afterSave(): void
    {
        parent::afterSave(); 
        // Rediriger vers la liste après la sauvegarde
        $this->redirect($this->getResource()::getUrl('index'));
    }
PHP;

// Parcourir chaque fichier d'édition
foreach ($editPages as $pagePath) {
    $fullPath = __DIR__ . '/app/Filament/' . $pagePath;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        // Vérifiez si les méthodes existent déjà
        if (strpos($content, 'getRedirectUrl') === false && strpos($content, 'afterSave') === false) {
            // Trouver la fin de la classe pour ajouter les méthodes
            $lastBrace = strrpos($content, '}');
            if ($lastBrace !== false) {
                $newContent = substr($content, 0, $lastBrace) . $redirectMethod . "\n}\n";
                file_put_contents($fullPath, $newContent);
                echo "✅ Mise à jour de $pagePath\n";
            } else {
                echo "⚠️ Impossible de trouver la fin de la classe dans $pagePath\n";
            }
        } else {
            echo "ℹ️ $pagePath contient déjà des méthodes de redirection\n";
        }
    } else {
        echo "❌ Le fichier $fullPath n'existe pas\n";
    }
}

// Faire la même chose pour les ressources dans le namespace Resources
$editPages = [
    'Resources/ContestResource/Pages/EditContest.php',
    'Resources/EntryResource/Pages/EditEntry.php',
    'Resources/ParticipantResource/Pages/EditParticipant.php',
    'Resources/PrizeDistributionResource/Pages/EditPrizeDistribution.php',
    'Resources/PrizeResource/Pages/EditPrize.php',
    'Resources/QrCodeResource/Pages/EditQrCode.php'
];

foreach ($editPages as $pagePath) {
    $fullPath = __DIR__ . '/app/Filament/' . $pagePath;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        // Vérifiez si les méthodes existent déjà
        if (strpos($content, 'getRedirectUrl') === false && strpos($content, 'afterSave') === false) {
            // Trouver la fin de la classe pour ajouter les méthodes
            $lastBrace = strrpos($content, '}');
            if ($lastBrace !== false) {
                $newContent = substr($content, 0, $lastBrace) . $redirectMethod . "\n}\n";
                file_put_contents($fullPath, $newContent);
                echo "✅ Mise à jour de $pagePath\n";
            } else {
                echo "⚠️ Impossible de trouver la fin de la classe dans $pagePath\n";
            }
        } else {
            echo "ℹ️ $pagePath contient déjà des méthodes de redirection\n";
        }
    } else {
        echo "❌ Le fichier $fullPath n'existe pas\n";
    }
}

echo "Terminé!\n";
