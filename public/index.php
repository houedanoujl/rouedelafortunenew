<?php

/**
 * Roue de la Fortune - Page temporaire
 */

// Afficher une page d'accueil temporaire en attendant l'installation complète de Laravel
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roue de la Fortune DINOR</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
            color: #333;
            text-align: center;
        }
        .container {
            max-width: 800px;
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #e63946;
            margin-bottom: 1rem;
        }
        p {
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .status {
            padding: 1rem;
            background-color: #f8d7da;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        .steps {
            text-align: left;
            background-color: #f1f1f1;
            padding: 1.5rem;
            border-radius: 4px;
        }
        .steps ol {
            margin-left: 1.5rem;
        }
        .steps li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Roue de la Fortune DINOR</h1>
        
        <div class="status">
            <h2>État de l'installation</h2>
            <p>L'application est en cours de configuration. Certaines étapes d'installation doivent être complétées.</p>
        </div>
        
        <div class="steps">
            <h3>Étapes pour finaliser l'installation :</h3>
            <ol>
                <li>Installer les dépendances Laravel avec Composer</li>
                <li>Configurer le fichier .env</li>
                <li>Exécuter les migrations de base de données</li>
                <li>Générer la clé d'application</li>
            </ol>
        </div>
        
        <p>Pour plus d'informations, consultez la documentation dans le fichier README.md.</p>
    </div>
</body>
</html>
