// Ajout d'un correctif pour le problème de format de date
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour corriger le format des inputs datetime-local
    function fixDateTimeInputs() {
        // Sélectionner tous les inputs de type datetime-local
        const dateTimeInputs = document.querySelectorAll('input[type="datetime-local"]');
        
        dateTimeInputs.forEach(input => {
            // Obtenir la valeur actuelle
            const value = input.value;
            
            // Si la valeur contient un fuseau horaire, le supprimer
            if (value && value.includes('+')) {
                // Extraire la partie de la date sans le fuseau horaire
                const fixedValue = value.split('+')[0];
                
                // Définir la nouvelle valeur
                input.value = fixedValue;
                
                // Logger la correction pour débogage
                console.log('Format de date corrigé :', value, ' -> ', fixedValue);
            }
        });
    }
    
    // Exécuter la correction immédiatement
    fixDateTimeInputs();
    
    // Observer les changements DOM pour corriger les inputs ajoutés dynamiquement
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                fixDateTimeInputs();
            }
        });
    });
    
    // Observer les changements dans tout le document
    observer.observe(document.body, { childList: true, subtree: true });
    
    // Écouter les événements Livewire
    document.addEventListener('livewire:load', function() {
        if (window.Livewire) {
            // Correction après les mises à jour Livewire
            window.Livewire.hook('message.processed', () => {
                fixDateTimeInputs();
            });
        }
    });
});
