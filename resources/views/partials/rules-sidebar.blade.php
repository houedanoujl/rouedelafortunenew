<!-- Sidebar pour le règlement du jeu -->
<div class="sidebar-overlay" id="reglementOverlay"></div>
<div class="sidebar" id="reglementSidebar">
    <div class="sidebar-header">
        <h5 class="sidebar-title">REGLEMENT du jeu « Promo 70 ans de la marque DINOR »</h5>
        <button type="button" class="sidebar-close">&times;</button>
    </div>
    <div class="sidebar-body">
        <!-- Accordéon personnalisé -->
        <div class="custom-accordion-container">
            <!-- Article 1 -->
            <div class="custom-accordion">
                <div class="custom-accordion-header active" data-accordion="article1">
                    Article 1 : Organisation du jeu
                    <span class="custom-accordion-icon"></span>
                </div>
                <div class="custom-accordion-content active">
                    <p>SANIA Cie, Société Anonyme avec Conseil d'Administration au capital de 44.110.000.000 FCFA, immatriculé au Registre du Commerce et du Crédit Mobilier d'Abidjan sous le numéro CI-ABJ-2008-B14-3985, dont le siège social est sis à Abidjan, Zone industrielle de Vridi, rue du textile, 01 BP 2949 Abidjan 01. Abidjan.</p>
                    <p>Ci-après dénommé « l'Organisateur »</p>
                    <p>Organise un jeu dénommé « Promo 70 ans de la marque DINOR ».</p>
                    <p>Ci-après désigné « le jeu ».</p>
                </div>
            </div>
            
            <!-- Article 2 -->
            <div class="custom-accordion">
                <div class="custom-accordion-header" data-accordion="article2">
                    Article 2 : Objet du jeu
                    <span class="custom-accordion-icon"></span>
                </div>
                <div class="custom-accordion-content">
                    <p>Ce Jeu a pour objet de valoriser la marque DINOR et de fidéliser les consommateurs à travers des gains exceptionnels.</p>
                </div>
            </div>
            
            <!-- Article 3 -->
            <div class="custom-accordion">
                <div class="custom-accordion-header" data-accordion="article3">
                    Article 3 : Durée du jeu
                    <span class="custom-accordion-icon"></span>
                </div>
                <div class="custom-accordion-content">
                    <p>le jeu se déroulera du 1er mai au 31 juillet 2025 inclus.</p>
                </div>
            </div>
            
            <!-- Article 4 -->
            <div class="custom-accordion">
                <div class="custom-accordion-header" data-accordion="article4">
                    Article 4 : Cadre réglementaire et conditions de participation
                    <span class="custom-accordion-icon"></span>
                </div>
                <div class="custom-accordion-content">
                    <h5>4-1 Cadre réglementaire</h5>
                    <p>le jeu est régie par la règlementation en vigueur notamment la Loi n°2019-572 du 26 Juin 2019 portant régime juridique des jeux de hasard en Côte d'Ivoire.</p>
                    <p>La roue comporte les mentions « Gagné » et « Perdu ».</p>
                    <p>En pratique, les Participants devront :</p>
                    <ul>
                        <li>S'inscrire en ligne sur le site internet www.dinor70ans.com ;</li>
                        <li>Remplir le formulaire d'inscription en ligne ;</li>
                        <li>Tourner la Roue pour tenter de gagner un lot ;</li>
                        <li>Retirer le lot au siège social de Sania Cie, sis à Abidjan Vridi, rue des Textiles ;</li>
                    </ul>
                </div>
            </div>
            
            <!-- Article 5 -->
            <div class="custom-accordion">
                <div class="custom-accordion-header" data-accordion="article5">
                    Article 5 : Conditions de participation
                    <span class="custom-accordion-icon"></span>
                </div>
                <div class="custom-accordion-content">
                    <p>le jeu est ouverte à toute personne physique majeure résidant en Côte d'Ivoire.</p>
                </div>
            </div>
            
            <!-- Article 6 -->
            <div class="custom-accordion">
                <div class="custom-accordion-header" data-accordion="article6">
                    Article 6 : Lots à gagner
                    <span class="custom-accordion-icon"></span>
                </div>
                <div class="custom-accordion-content">
                    <p>Les lots à gagner dans le cadre de ce Jeu sont les suivants :</p>
                    <table>
                        <thead>
                            <tr>
                                <th>Lots</th>
                                <th>Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tablette</td>
                                <td>10</td>
                            </tr>
                            <tr>
                                <td>TV LED 43"</td>
                                <td>5</td>
                            </tr>
                            <tr>
                                <td>Micro-onde</td>
                                <td>5</td>
                            </tr>
                            <tr>
                                <td>Blender</td>
                                <td>10</td>
                            </tr>
                            <tr>
                                <td>Bouteilles DINOR 1L</td>
                                <td>300</td>
                            </tr>
                            <tr>
                                <td>Bouteilles DINOR 5L</td>
                                <td>100</td>
                            </tr>
                            <tr>
                                <td>Cadeaux surprises</td>
                                <td>70</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script pour la sidebar -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Éléments DOM
        const sidebar = document.getElementById('reglementSidebar');
        const overlay = document.getElementById('reglementOverlay');
        const closeButton = sidebar.querySelector('.sidebar-close');
        
        // Fonction pour ouvrir la sidebar
        window.openReglementSidebar = function() {
            overlay.classList.add('active');
            sidebar.classList.add('active');
            document.body.style.overflow = 'hidden';
        };
        
        // Fonction pour fermer la sidebar
        window.closeReglementSidebar = function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            setTimeout(function() {
                overlay.classList.remove('active');
            }, 300);
            document.body.style.overflow = '';
        };
        
        // Écouteurs d'événements
        closeButton.addEventListener('click', closeReglementSidebar);
        overlay.addEventListener('click', closeReglementSidebar);
        
        // Fonctionnement de l'accordéon
        const accordionHeaders = document.querySelectorAll('.custom-accordion-header');
        accordionHeaders.forEach(header => {
            header.addEventListener('click', function() {
                // Fermer toutes les sections
                document.querySelectorAll('.custom-accordion-header').forEach(h => {
                    if (h !== this) h.classList.remove('active');
                });
                document.querySelectorAll('.custom-accordion-content').forEach(c => {
                    if (c !== this.nextElementSibling) c.classList.remove('active');
                });
                
                // Basculer la section actuelle
                this.classList.toggle('active');
                this.nextElementSibling.classList.toggle('active');
            });
        });
        
        // Remapper les anciens déclencheurs de modal vers la sidebar
        document.querySelectorAll('a[href="#reglementModal"], [data-bs-toggle="modal"][data-bs-target="#reglementModal"]').forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                openReglementSidebar();
            });
            trigger.removeAttribute('data-bs-toggle');
            trigger.removeAttribute('data-bs-target');
        });
    });
</script>
