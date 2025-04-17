<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nettoyage des cookies</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0079B2;
        }
        .spinner {
            margin: 20px auto;
            width: 50px;
            height: 50px;
            border: 5px solid rgba(0,121,178,0.2);
            border-radius: 50%;
            border-top-color: #0079B2;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .message {
            margin: 20px 0;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mode Test - Nettoyage</h1>
        
        <div class="spinner"></div>
        
        <div class="message">
            {{ $message }}
        </div>
        
        <!-- Scripts de nettoyage des cookies -->
        <script>
            console.log('=== SUPPRESSION COMPLÈTE DES COOKIES ===');
            console.log('Cookies actuels:', document.cookie);
            
            // Supprimer tous les cookies possible
            const cookiesToDelete = ['contest_played_1', '70_ans_dinor_session'];
            const paths = ['/', '/spin', '/register', '/result', '/home', ''];
            
            // Essayer différentes méthodes de suppression
            cookiesToDelete.forEach(name => {
                paths.forEach(path => {
                    document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=${path}`;
                    document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=${path}; domain=${window.location.hostname}`;
                });
            });
            
            // Nettoyer aussi localStorage
            localStorage.removeItem('contest_played_1');
            localStorage.clear();
            
            console.log('Cookies après suppression:', document.cookie);
            
            // Rediriger après 3 secondes
            setTimeout(function() {
                window.location.href = '{{ $redirect_url }}';
            }, 3000);
        </script>
    </div>
</body>
</html>
