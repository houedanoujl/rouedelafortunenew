<!-- Popup simple de vérification d'âge - plein écran -->
<style>
    /* Style pour le popup de vérification d'âge */
    .age-verify-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.75);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        font-family: 'EB Garamond', serif;
    }
    
    .age-verify-popup {
        background-color: white;
        max-width: 400px;
        width: 90%;
        padding: 2rem;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
    }
    
    .age-verify-popup h2 {
        color: var(--honolulu-blue, #0079B2);
        font-size: 1.8rem;
        margin-bottom: 1rem;
        font-family: 'EB Garamond', serif;
    }
    
    .age-verify-popup p {
        font-size: 1.4rem;
        margin-bottom: 1.5rem;
        color: #333;
        font-family: 'EB Garamond', serif;
    }
    
    .age-verify-buttons {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }
    
    .age-verify-buttons button {
        padding: 0.7rem 2rem;
        border: none;
        border-radius: 5px;
        font-size: 1.2rem;
        cursor: pointer;
        font-family: 'EB Garamond', serif;
        transition: transform 0.1s, box-shadow 0.2s;
    }
    
    .age-verify-buttons button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .btn-age-yes {
        background-color: var(--honolulu-blue, #0079B2);
        color: white;
    }
    
    .btn-age-no {
        background-color: var(--persian-red, #d13239);
        color: white;
    }
    
    .hidden {
        display: none !important;
    }
</style>

<div id="ageVerificationOverlay" class="age-verify-overlay" style="display: none;">
    <div class="age-verify-popup">
        <h2>Vérification de l'âge</h2>
        <p>Êtes-vous âgé(e) d'au moins 18 ans ?</p>
        <div class="age-verify-buttons">
            <button class="btn-age-yes" onclick="ageCheck.verify(true)">Oui</button>
            <button class="btn-age-no" onclick="ageCheck.verify(false)">Non</button>
        </div>
    </div>
</div>

<script>
// Objet simple pour gérer la vérification d'âge
// Utilise un namespace pour éviter les conflits
var ageCheck = {
    // Méthode pour vérifier si le popup a déjà été affiché
    alreadyVerified: function() {
        return sessionStorage.getItem('ageVerified') === 'true';
    },
    
    // Afficher le popup
    showPopup: function() {
        document.getElementById('ageVerificationOverlay').style.display = 'flex';
    },
    
    // Cacher le popup
    hidePopup: function() {
        document.getElementById('ageVerificationOverlay').style.display = 'none';
    },
    
    // Méthode appelée lors du clic sur Oui/Non
    verify: function(isAdult) {
        if (isAdult) {
            this.hidePopup();
            sessionStorage.setItem('ageVerified', 'true');
        } else {
            window.location.href = 'https://www.google.com';
        }
    },
    
    // Méthode d'initialisation
    init: function() {
        // N'afficher que si pas encore vérifié
        if (!this.alreadyVerified()) {
            // Attendre que le DOM soit chargé
            document.addEventListener('DOMContentLoaded', function() {
                ageCheck.showPopup();
            });
        }
    }
};

// Initialiser le popup
ageCheck.init();
</script>
