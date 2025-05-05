<script>
if (!window.agePopupScriptLoaded) {
    window.agePopupScriptLoaded = true;
    var popupDisplayCount = 0;
    function logPopupDisplay(reason) {
        popupDisplayCount++;
        console.log(`[POPUP LOG #${popupDisplayCount}] Popup affiché - Raison: ${reason}`);
    }
    function checkPopupState() {
        const popup = document.getElementById('ageVerificationOverlay');
        if (!popup) return;
        console.log(`[POPUP CHECK] État actuel: display=${popup.style.display}, sessionStorage.ageVerifiedThisSession=${sessionStorage.getItem('ageVerifiedThisSession')}`);
    }
    function handleAgeVerification(isAdult) {
        console.log(`[POPUP EVENT] Bouton cliqué: ${isAdult ? 'Oui' : 'Non'}`);
        const popup = document.getElementById('ageVerificationOverlay');
        if (!popup) return;
        if (isAdult) {
            popup.style.display = 'none';
            sessionStorage.setItem('ageVerifiedThisSession', 'true');
            console.log('[POPUP HIDE] Popup caché après clic sur Oui, sessionStorage.ageVerifiedThisSession=true');
        } else {
            alert("Vous devez avoir au moins 18 ans pour participer.");
        }
    }
    document.addEventListener('DOMContentLoaded', function () {
        const popup = document.getElementById('ageVerificationOverlay');
        if (!popup) return;
        console.log('[POPUP INIT] DOMContentLoaded déclenché');
        if (!sessionStorage.getItem('ageVerifiedThisSession')) {
            popup.style.display = 'flex';
            logPopupDisplay('DOMContentLoaded initial');
        } else {
            popup.style.display = 'none';
            console.log('[POPUP INIT] Popup non affiché car sessionStorage.ageVerifiedThisSession=true');
        }
    });
    document.addEventListener('livewire:load', function () {
        window.livewire.hook('message.sent', (message, component) => {
            checkPopupState();
        });
        window.livewire.hook('message.received', (message, component) => {
            checkPopupState();
        });
        window.livewire.hook('message.processed', (message, component) => {
            const popup = document.getElementById('ageVerificationOverlay');
            if (!popup) return;
            if (sessionStorage.getItem('ageVerifiedThisSession') === 'true' && popup.style.display === 'flex') {
                popup.style.display = 'none';
                console.log('[POPUP LIVEWIRE] Popup détecté visible alors qu\'il devrait être caché (sessionStorage)');
            } else if (sessionStorage.getItem('ageVerifiedThisSession') === 'true') {
                console.log('[POPUP LIVEWIRE] Popup correctement caché après traitement (sessionStorage)');
            } else if (popup.style.display === 'flex') {
                console.log('[POPUP LIVEWIRE] Popup visible, mais n\'a pas encore été acquitté');
            }
        });
    });
    const observer = new MutationObserver(function (mutationsList, observer) {
        let changeCount = 0;
        mutationsList.forEach(function (mutation) {
            changeCount++;
            console.log(`[POPUP MUTATION] Changement détecté dans le DOM ${changeCount}`);
            if (sessionStorage.getItem('ageVerifiedThisSession') === 'true') {
                const popup = document.getElementById('ageVerificationOverlay');
                if (popup && popup.style.display === 'flex') {
                    popup.style.display = 'none';
                    console.log('[POPUP MUTATION] Popup caché par MutationObserver (sessionStorage)');
                }
            }
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['style', 'class']
        });
        console.log('[POPUP MUTATION] Observateur de mutations démarré');
    });
    document.addEventListener('input', function(e) {
        if (e.target.id === 'phone' || e.target.name === 'phone') {
            console.log('[POPUP INPUT] Saisie détectée dans le champ téléphone');
            const popup = document.getElementById('ageVerificationOverlay');
            if (popup && sessionStorage.getItem('ageVerifiedThisSession') === 'true' && popup.style.display === 'flex') {
                popup.style.display = 'none';
                console.log('[POPUP INPUT] Popup réapparu après saisie téléphone, masquage forcé');
            }
        }
    });
    window.handleAgeVerification = handleAgeVerification;
}
</script>
