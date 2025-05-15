<!-- Ce fichier a été modifié pour un popup de vérification d'âge simple -->
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
        color: #0079B2;
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
        <h2>Vérification de l'âge</h2>
        <p>Êtes-vous âgé(e) de 18 ans ou plus ?</p>

        <div class="age-verification-buttons">
            <button onclick="verifyAge(true)" class="btn-age-yes">Oui</button>
            <button onclick="verifyAge(false)" class="btn-age-no">Non</button>
        </div>
    </div>
</div>

<script>
    // Exécuter après le chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Afficher le popup à chaque visite, sans vérifier localStorage
        document.getElementById('ageVerificationOverlay').classList.remove('hidden');
    });

    // Fonction pour vérifier l'âge
    function verifyAge(isAdult) {
        if (isAdult) {
            // Si l'utilisateur a plus de 18 ans, cacher le popup
            document.getElementById('ageVerificationOverlay').classList.add('hidden');
            // Pas de sauvegarde dans localStorage pour afficher à chaque visite
        } else {
            // Simplement fermer le popup sans redirection
            document.getElementById('ageVerificationOverlay').classList.add('hidden');
        }
    }
</script>
