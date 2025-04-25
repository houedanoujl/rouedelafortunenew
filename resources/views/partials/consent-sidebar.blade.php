<!-- Sidebar pour le consentement individuel -->
<style>
#consentSidebar {
    height: 80vh !important;
    max-height: 80vh !important;
    position: absolute !important;
    left: 0;
    right: 0;
    margin: 0 10px;
}
</style>
<div class="sidebar-overlay" id="consentOverlay"></div>
<div class="sidebar" id="consentSidebar">
    <div class="sidebar-header">
        <h5 class="sidebar-title">Fiche de recueil de consentement</h5>
        <button type="button" class="sidebar-close">&times;</button>
    </div>
    <div class="sidebar-body">
        <p>Dans le cadre uniquement de la communication des résultats du jeu dénommé « Promo 70 ans de la marque DINOR  », la société SANIA  est amenée à collecter des informations personnelles vous concernant à savoir vos nom, prénom(s), numéro de téléphone, adresse e-mail et image.</p>
        <p>Les destinataires de vos données sont les services concernés de la société SANIA </p>
        <p>Conformément à la loi N° 2013-450 du 19 juin 2013 portant sur la protection des données à caractère personnel, vous bénéficiez d'un droit à l'information, d'accès, de rectification, d'opposition, d'effacement, et d'oubli numérique relativement aux informations qui vous concernent.</p>
        <p>Pour exercer ces droits et obtenir communication desdites informations, vous pouvez contacter ………en écrivant à l'adresse suivante : KOUAKOUJP@sifca-ci.com </p>
        <p>En signant ce présent document, je consens de façon expresse et éclairée au traitement de mes données par les services concernés.</p>
        <p>Ces données pourront être utilisées à des fins de prises de contacts et d'études de satisfaction, sans contrepartie financière, uniquement dans le cadre de la communication des résultats du jeu promotionnelle …... </p>
        
        <div class="d-flex justify-content-between mt-4">
            <div><strong>J'accepte</strong></div>
            <div><strong>Je refuse</strong></div>
        </div>
        
        <div class="mt-4">
            <p><strong>Noms & Prénoms</strong> ____________________________________________________</p>
            <p><strong>Date</strong> ________________________________________________________________</p>
            <p><strong>Signature</strong>:</p>
        </div>
        
        <div class="text-center mt-4">
            <button type="button" class="btn btn-secondary" onclick="closeConsentSidebar()">Fermer</button>
        </div>
    </div>
</div>

<!-- Polyfill pour le smooth scroll sur iOS/Safari -->
<script src="https://cdn.jsdelivr.net/npm/smoothscroll-polyfill@0.4.4/dist/smoothscroll.min.js"></script>
<script>
  if (window.smoothscroll) {
    window.smoothscroll.polyfill();
  }
</script>

<!-- Script pour la sidebar -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Éléments DOM
        const sidebar = document.getElementById('consentSidebar');
        const overlay = document.getElementById('consentOverlay');
        const closeButton = sidebar.querySelector('.sidebar-close');

        // Centrage dynamique et hauteur 80vh sur tous les écrans
        function centerConsentSidebar() {
            if (sidebar) {
                var topPx = (window.innerHeight - sidebar.offsetHeight) / 2;
                if (topPx < window.innerHeight * 0.10) topPx = window.innerHeight * 0.10;
                sidebar.style.top = topPx + 'px';
                sidebar.style.height = '80vh';
                sidebar.style.maxHeight = '80vh';
                sidebar.style.position = 'absolute';
                sidebar.style.left = '0';
                sidebar.style.right = '0';
                sidebar.style.margin = '0 auto';
                console.log('[DEBUG] Consent sidebar centered at', topPx + 'px, height 80vh');
            }
        }

        // Smooth scroll sur #isidor à l'ouverture (iOS compatible)
        function scrollToIsidor() {
            var isidor = document.getElementById('isidor');
            if (isidor) {
                var originalOverflow = document.body.style.overflow;
                document.body.style.overflow = '';
                setTimeout(function() {
                    if (isidor.scrollIntoView) {
                        isidor.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else {
                        window.scrollTo(0, isidor.offsetTop);
                    }
                    document.body.style.overflow = originalOverflow;
                    console.log('[DEBUG] Smooth scroll to #isidor (iOS compatible)');
                }, 150);
            }
        }

        // Fonction pour ouvrir la sidebar
        window.openConsentSidebar = function() {
            overlay.classList.add('active');
            sidebar.classList.add('active');
            document.body.style.overflow = 'hidden';
            centerConsentSidebar();
            scrollToIsidor();
        };

        // Fonction pour fermer la sidebar
        window.closeConsentSidebar = function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            setTimeout(function() {
                overlay.classList.remove('active');
            }, 300);
            document.body.style.overflow = '';
        };

        // Écouteurs d'événements
        closeButton.addEventListener('click', closeConsentSidebar);
        overlay.addEventListener('click', closeConsentSidebar);

        // Remapper les anciens déclencheurs de modal vers la sidebar
        document.querySelectorAll('a[href="#consentModal"], [data-bs-toggle="modal"][data-bs-target="#consentModal"]').forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                openConsentSidebar();
            });
            trigger.removeAttribute('data-bs-toggle');
            trigger.removeAttribute('data-bs-target');
        });
    });
</script>
