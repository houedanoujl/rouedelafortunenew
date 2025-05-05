<!-- Ce fichier a été désactivé pour éviter les conflits avec le popup principal -->
<!--
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
        background: #fff;
        border-radius: 16px;
        padding: 36px 40px 30px 40px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        max-width: 460px;
        margin: auto;
        text-align: center;
        font-family: 'Montserrat', Arial, sans-serif;
    }

    .age-verification-popup h2 {
        color: #d9534f;
        font-size: 2rem;
        margin-bottom: 18px;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .age-verification-popup p {
        font-size: 1.15rem;
        color: #444;
        margin-bottom: 26px;
    }

    .age-verification-checkboxes {
        margin-bottom: 30px;
    }

    .verification-checkbox-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .verification-checkbox-item input[type="checkbox"] {
        accent-color: #28a745;
        margin-top: 2px;
    }

    .verification-checkbox-item label {
        font-size: 1.09rem;
        color: #222;
        line-height: 1.5;
        cursor: pointer;
    }

    .age-verification-buttons {
        display: flex;
        justify-content: center;
        gap: 18px;
        margin-top: 10px;
    }

    .btn-age-yes {
        background: #28a745;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 11px 36px;
        font-size: 1.12rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(40,167,69,0.11);
        transition: background 0.2s;
    }

    .btn-age-yes:disabled {
        background: #a5d6b2;
        cursor: not-allowed;
    }

    .btn-age-no {
        background: #d9534f;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 11px 36px;
        font-size: 1.12rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(217,83,79,0.11);
        transition: background 0.2s;
    }

    .btn-age-no:hover {
        background: #b52d29;
    }

    .hidden {
        display: none !important;
    }
</style>

<div id="ageVerificationOverlay" class="age-verification-overlay hidden">
    <div class="age-verification-popup">
        <h2>Conditions de participation</h2>
        <p>Veuillez confirmer les conditions suivantes pour participer :</p>
        
        <div class="age-verification-checkboxes" style="text-align: left; margin-bottom: 25px;">
            <div class="verification-checkbox-item" style="margin-bottom: 15px;">
                <input type="checkbox" id="ageCheckbox" style="margin-right: 10px; transform: scale(1.3);">
                <label for="ageCheckbox" style="font-size: 1.1rem;">J'ai au moins 18 ans</label>
            </div>
            
            <div class="verification-checkbox-item">
                <input type="checkbox" id="employeeCheckbox" style="margin-right: 10px; transform: scale(1.3);">
                <label for="employeeCheckbox" style="font-size: 1.1rem;">Je ne suis pas employé(e) ni membre de la famille de SIFCA, SANIA ou Big Five</label>
            </div>
        </div>
        
        <div class="age-verification-buttons">
            <button id="verificationSubmitBtn" class="btn-age-yes" disabled onclick="submitVerification()">Confirmer</button>
            <button class="btn-age-no" onclick="verifyAge(false)">Annuler</button>
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
        
        // Ajouter les listeners pour les cases à cocher
        const ageCheckbox = document.getElementById('ageCheckbox');
        const employeeCheckbox = document.getElementById('employeeCheckbox');
        
        if (ageCheckbox && employeeCheckbox) {
            ageCheckbox.addEventListener('change', checkVerificationStatus);
            employeeCheckbox.addEventListener('change', checkVerificationStatus);
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

    // Fonction pour gérer les cases à cocher et activer/désactiver le bouton
    function checkVerificationStatus() {
        const ageChecked = document.getElementById('ageCheckbox').checked;
        const employeeChecked = document.getElementById('employeeCheckbox').checked;
        const submitButton = document.getElementById('verificationSubmitBtn');
        
        // Activer le bouton seulement si les deux cases sont cochées
        submitButton.disabled = !(ageChecked && employeeChecked);
    }

    // Fonction pour soumettre la vérification
    function submitVerification() {
        verifyAge(true);
    }
</script>
-->
