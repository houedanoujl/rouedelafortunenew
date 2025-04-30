<!-- Popup de vérification d'âge - toujours affiché -->
<style>
    /* Overlay qui couvre tout l'écran */
    .age-verification-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    /* Le popup lui-même */
    .age-verification-popup {
        background-color: white;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        animation: popup-fade-in 0.5s ease-out;
    }

    @keyframes popup-fade-in {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }

    .age-verification-popup h2 {
        color: #D03A2C;
        margin-bottom: 20px;
        font-size: 1.8rem;
    }

    .age-verification-popup p {
        margin-bottom: 30px;
        font-size: 1.2rem;
    }

    .age-verification-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .age-verification-buttons button {
        padding: 10px 30px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1.1rem;
        font-weight: bold;
        transition: all 0.2s;
    }

    .btn-age-yes {
        background-color: #4CAF50;
        color: white;
    }

    .btn-age-no {
        background-color: #D03A2C;
        color: white;
    }

    .btn-age-yes:hover {
        background-color: #3e8e41;
        transform: translateY(-2px);
    }

    .btn-age-no:hover {
        background-color: #b02a1d;
        transform: translateY(-2px);
    }

    .hidden {
        display: none !important;
    }
</style>

<div id="ageVerificationOverlay" class="age-verification-overlay hidden">
    <div class="age-verification-popup">
        <h2>Vérification de l'âge</h2>
        <p>Êtes-vous âgé(e) d'au moins 18 ans ?</p>
        <div class="age-verification-buttons">
            <button class="btn-age-yes" onclick="verifyAge(true)">Oui</button>
            <button class="btn-age-no" onclick="verifyAge(false)">Non</button>
        </div>
    </div>
</div>

<script>
    // Exécuter après le chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier si l'utilisateur a déjà participé (via cookies ou session)
        function checkParticipation() {
            // Vérifier si la page a été chargée avec des paramètres de redirection
            const urlParams = new URLSearchParams(window.location.search);
            const alreadyPlayed = urlParams.get('already_played') === 'true';
            
            // Si déjà redirigé pour participation antérieure, ne pas montrer la popup
            if (alreadyPlayed) {
                return true;
            }
            
            // Vérifier si l'âge a déjà été vérifié
            const ageVerified = localStorage.getItem('age_verified') === 'true';
            if (ageVerified) {
                return true;
            }
            
            // Vérifier dans localStorage
            const hasParticipated = localStorage.getItem('has_participated');
            
            // Vérifier dans les cookies
            const cookies = document.cookie.split(';');
            let participationCookie = false;
            
            // Parcourir tous les cookies pour chercher des traces de participation
            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i].trim();
                // Chercher des cookies liés à la participation ou session Laravel
                if (cookie.indexOf('contest_played_') === 0 || 
                    cookie.indexOf('laravel_session') === 0 ||
                    cookie.indexOf('participation_') === 0) {
                    participationCookie = true;
                    break;
                }
            }
            
            // Vérifier également dans sessionStorage
            const sessionParticipation = sessionStorage.getItem('has_participated');
            
            return hasParticipated || participationCookie || sessionParticipation;
        }
        
        // N'afficher le popup que si l'utilisateur n'a pas déjà participé
        if (!checkParticipation()) {
            document.getElementById('ageVerificationOverlay').classList.remove('hidden');
        } else {
            console.log('Participation détectée ou âge déjà vérifié, popup de vérification d\'âge non affiché');
        }
    });

    // Fonction pour vérifier l'âge
    function verifyAge(isAdult) {
        if (isAdult) {
            // Si l'utilisateur a plus de 18 ans, cacher le popup
            document.getElementById('ageVerificationOverlay').classList.add('hidden');
            // Sauvegarder dans localStorage pour ne pas redemander
            localStorage.setItem('age_verified', 'true');
            // Empêcher les redirections en boucle
            sessionStorage.setItem('popup_shown', 'true');
        } else {
            // Si l'utilisateur a moins de 18 ans, rediriger vers Google
            window.location.href = 'https://www.google.com';
        }
    }
</script>
