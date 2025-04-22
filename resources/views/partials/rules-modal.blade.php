<!-- Modal pour le règlement de la tombola (version accordéon personnalisé) -->
<style>
    #reglementModal .modal-content {
        height: 80vh !important;
        max-height: 80vh !important;
        width: auto;
        margin: 0 10px;
        position: absolute;
        left: 0;
        right: 0;
    }
    /* Styles pour l'accordéon personnalisé */
    .custom-accordion {
        margin-bottom: 10px;
    }
    .custom-accordion-header {
        background-color: #f8f9fa;
        color: #212529;
        padding: 12px 15px;
        cursor: pointer;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .custom-accordion-header:hover {
        background-color: #e9ecef;
    }
    .custom-accordion-icon::after {
        content: '\002B'; /* Signe + */
        font-size: 18px;
        color: #212529;
    }
    .custom-accordion-header.active .custom-accordion-icon::after {
        content: '\2212'; /* Signe - */
    }
    .custom-accordion-content {
        padding: 15px;
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 4px 4px;
        display: none;
        color: #212529;
    }
    .custom-accordion-content.active {
        display: block;
    }
    .custom-accordion-content p {
        color: #212529;
        margin-bottom: 10px;
        line-height: 1.6;
    }
    .custom-accordion-content h5 {
        color: #212529;
        font-weight: bold;
        margin-top: 15px;
        margin-bottom: 10px;
    }
    .custom-accordion-content ul {
        color: #212529;
        margin-left: 20px;
        margin-bottom: 15px;
    }
    .custom-accordion-content li {
        color: #212529;
        margin-bottom: 5px;
    }
    .custom-accordion-content table {
        margin-top: 15px;
        color: #212529;
        width: 100%;
        border-collapse: collapse;
    }
    .custom-accordion-content th, 
    .custom-accordion-content td {
        border: 1px solid #dee2e6;
        padding: 8px;
        text-align: left;
        color: #212529;
    }
    .custom-accordion-content th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
</style>

<div class="modal fade" id="reglementModal" tabindex="-1" aria-labelledby="reglementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: #fff; color: #212529;">
            <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <h5 class="modal-title" id="reglementModalLabel" style="color: #212529; font-weight: bold;">REGLEMENT DE LA TOMBOLA « Promo 70 ans de la marque DINOR »</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 20px; background-color: #fff; color: #212529;">
                <div class="custom-accordion" id="accordionReglement">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt1" aria-expanded="true" aria-controls="collapseArt1">
                                Article 1 : Organisation de la Tombola
                            </button>
                        </h2>
                        <div id="collapseArt1" class="accordion-collapse collapse show" aria-labelledby="headingArt1" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                SANIA Cie, Société Anonyme avec Conseil d’Administration au capital de 44.110.000.000 FCFA, immatriculé au Registre du Commerce et du Crédit Mobilier d’Abidjan sous le numéro CI-ABJ-2008-B14-3985, dont le siège social est sis à Abidjan, Zone industrielle de Vridi, rue du textile, 01 BP 2949 Abidjan 01, représentée par son Directeur Général, Monsieur Philippe RAYNAL ayant tous pouvoirs à l’effet des présentes ;<br>
                                Ci-après dénommée « l’Organisateur »<br><br>
                                Organise une Tombola dénommée « Promo 70 ans de la marque DINOR ».<br><br>
                                Ci-après désignée « la Tombola ».
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt2" aria-expanded="false" aria-controls="collapseArt2">
                                Article 2 : Objet de la Tombola
                            </button>
                        </h2>
                        <div id="collapseArt2" class="accordion-collapse collapse" aria-labelledby="headingArt2" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                La présente Tombola promotionnelle a pour objectif de commémorer les 70 ans de la marque DINOR.<br><br>
                                Ci-après « les Participants »<br><br>
                                La participation à la Tombola implique l’acceptation sans réserve par les participants du présent règlement dans son intégralité.<br><br>
                                Ci-après « le Règlement ».
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt3" aria-expanded="false" aria-controls="collapseArt3">
                                Article 3 : Date et durée
                            </button>
                        </h2>
                        <div id="collapseArt3" class="accordion-collapse collapse" aria-labelledby="headingArt3" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                La Tombola se déroulera du 1er avril au 30 juin 2025 inclus.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt4" aria-expanded="false" aria-controls="collapseArt4">
                                Article 4 : Cadre réglementaire et conditions de participation
                            </button>
                        </h2>
                        <div id="collapseArt4" class="accordion-collapse collapse" aria-labelledby="headingArt4" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                <b>4-1 Cadre réglementaire</b><br>
                                La Tombola « Promo 70 ans de la marque DINOR » se déroule conformément aux dispositions de la loi n°2020-480 du 27 Mai 2020 portant régime juridique des jeux de hasard en Côte d’Ivoire et du décret n°2023-946 du 06 décembre 2023 portant régime juridique des jeux de hasard soumis à autorisation.<br><br>
                                Le présent règlement est authentifié par Maitre Diamilatou SIDIBET AKA-ANGHUI, Notaire, dont l’Etude est située à Abidjan Cocody, Route du Lycée Technique, villa n°4, Immeuble SIKA - Danga - 06 BP 2438 Abidjan 06. Abidjan.<br><br>
                                <b>4-2 Conditions de participation</b><br>
                                La Tombola qui se déroulera sur toute l’étendue du territoire de la République de Côte d’Ivoire est destinée aux consommateurs majeurs des produits de la marque DINOR et/ou aux utilisateurs majeurs de l’Application « Dinor App ». Cette Tombola est interdite aux mineurs de moins de 18 ans.<br><br>
                                Lesdits Participants pourront via le  le Quick Response Code (QR Code), visible sur les étiquettes des produits de la marque DINOR et dédié à la Tombola, accéder à une roue qui leur permettra de gagner des lots immédiats.<br><br>
                                La roue comporte les mentions « Gagné » et « Perdu ».<br><br>
                                <b>En pratique, les Participants devront :</b><br>
                                Scanner le QR Code sur les produits DINOR achetés. Ils seront redirigés sur l’application DINOR App ou invités à la télécharger pour accéder à la landing page ;<br>
                                Remplir le formulaire avec leurs informations personnelles requises ;<br>
                                Tourner la Roue pour tenter de gagner un lot ;<br>
                                Retirer le lot au siège social de Sania Cie, sis à Abidjan Vridi, rue des Textiles ;<br>
                                Tenter de rejouer une semaine après la précédente tentative.<br><br>
                                L’organisateur se réserve le droit de procéder à toute vérification relative au respect du règlement qu’il jugera utile, notamment pour écarter tout participant ayant effectué une déclaration inexacte ou mensongère ou frauduleuse.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt5">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt5" aria-expanded="false" aria-controls="collapseArt5">
                                Article 5 : Désignation des Lauréats
                            </button>
                        </h2>
                        <div id="collapseArt5" class="accordion-collapse collapse" aria-labelledby="headingArt5" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                Le lauréat est toute personne ayant rempli les conditions de participation et ayant été reconnue gagnante conformément à l’article 4.2 des présentes.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt6">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt6" aria-expanded="false" aria-controls="collapseArt6">
                                Article 6 : Lots à gagner
                            </button>
                        </h2>
                        <div id="collapseArt6" class="accordion-collapse collapse" aria-labelledby="headingArt6" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                <!-- Les détails des prix et le total ont été retirés conformément à la demande -->
                                <b>Les lots à gagner seront communiqués ultérieurement ou selon les modalités définies par l'organisateur.</b>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt7">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt7" aria-expanded="false" aria-controls="collapseArt7">
                                Article 7 : Informations sur les lauréats
                            </button>
                        </h2>
                        <div id="collapseArt7" class="accordion-collapse collapse" aria-labelledby="headingArt7" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                Les promoteurs sont tenus, à l'occasion de tirage de loterie et autres tombolas de requérir la présence d'un Commissaire de Justice.<br>
                                À l’issue de la campagne promotionnelle, des procès-verbaux seront dressés par Maitre SEKA Monney Lucien, Commissaire de Justice près la Cour d’Appel d’Abidjan et le Tribunal de Yopougon, dont l’étude est sise à Abidjan Yopougon, toits rouges, fin ruelle, Tél : 07 48 47 19 29.<br>
                                Le procès-verbal de la campagne promotionnelle comporte la liste des gagnants ainsi que la liste des lots correspondants. Cette liste est publiée dans un journal d'annonces légales sept (07) jours au plus tard après la clôture de la campagne promotionnelle.<br>
                                Le procès-verbal de paiement, établi au plus tard vingt-cinq jours après la date de publication officielle des résultats, comporte la liste des personnes gagnantes avec leur identité complète, les lots attribués, les lots non réclamés et/ou ceux gagnés par des titres de participation.<br>
                                Un exemplaire des procès-verbaux susvisés dûment enregistrés, est transmis à l’ARJH, dans un délai de dix jours après les opérations constatées par le Commissaire de Justice cité aux alinéas précédents.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt8">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt8" aria-expanded="false" aria-controls="collapseArt8">
                                Article 8 : Retrait des Lots
                            </button>
                        </h2>
                        <div id="collapseArt8" class="accordion-collapse collapse" aria-labelledby="headingArt8" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                Les lots sont à retirer dans un délai maximum d’un (01) mois à compter de la date de leur gain, du lundi au vendredi, de 08 heures à 12 heures au siège de la société SANIA Cie.<br>
                                Passé ce délai, les lots gagnés et non réclamés seront reversés à l’ARJH, au profit d’un organisme de bienfaisance désigné par le Conseil de régulation.<br><br>
                                La remise des lots pourra être faite au représentant du lauréat muni de la coipe de la carte nationale d’identité ou du passeport du gagnant.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt9">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt9" aria-expanded="false" aria-controls="collapseArt9">
                                Article 9 : Données personnelles
                            </button>
                        </h2>
                        <div id="collapseArt9" class="accordion-collapse collapse" aria-labelledby="headingArt9" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                Dans le cadre uniquement de la communication des résultats de la présente loterie promotionnelle, les gagnants acceptent que leurs noms, prénoms, images et voix soient utilisés par SANIA Cie à des fins publicitaires ou promotionnelles sans contrepartie financière. Les enregistrements pouvant être diffusés en tout ou partie sur des supports tels que : la presse-magazine et/ou les réseaux sociaux.<br>
                                Le consentement desdits gagnants est exprès et donné par écrit sur une fiche de recueil de consentement prévue en annexe.<br>
                                Les données personnelles des participants sont collectées et traitées conformément à la règlementation en vigueur. Elles ne seront utilisées uniquement que pour les besoins du jeu et ne seront pas transmises à des tiers.<br>
                                Conformément à la loi n°2013-450 relative à la protection des données à caractère personnel, les gagnants disposent d’un droit d’accès, de modification, de rectification et de suppression des données les concernant. S’ils souhaitent l’exercer, ils peuvent adresser leur demande à l’adresse mail :   .
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt10">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt10" aria-expanded="false" aria-controls="collapseArt10">
                                Article 10 : Cas de force majeure
                            </button>
                        </h2>
                        <div id="collapseArt10" class="accordion-collapse collapse" aria-labelledby="headingArt10" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                La responsabilité de l’organisateur ne saurait être encourue si, pour un cas de force majeure, la Tombola devait être modifiée, écourtée ou annulée.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt11">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt11" aria-expanded="false" aria-controls="collapseArt11">
                                Article 11 : Litiges
                            </button>
                        </h2>
                        <div id="collapseArt11" class="accordion-collapse collapse" aria-labelledby="headingArt11" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                Le présent règlement est exclusivement régi par la loi ivoirienne.<br><br>
                                Toutes contestations qui découlent du présent jeu ou qui s’y rapportent, feront l’objet d’un règlement amiable entre les parties.<br><br>
                                À défaut d’accord entre les parties, l’Autorité de Régulation de Jeux de Hasard (ARJH) sera compétente pour connaître du litige.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt12">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt12" aria-expanded="false" aria-controls="collapseArt12">
                                Article 12 : Dépôt et consultation du Règlement
                            </button>
                        </h2>
                        <div id="collapseArt12" class="accordion-collapse collapse" aria-labelledby="headingArt12" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                Le règlement de la Tombola est déposé en l’Etude par Maitre Diamilatou SIDIBE AKA-ANGHUI, Notaire, dont l’Etude est située à Abidjan Cocody, Route du Lycée Technique, villa n°4, Immeuble SIKA - Danga - 06 BP 2438 Abidjan 06. Abidjan et à l’Autorité de Régulation des Jeux de hasard (ARJH).
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingArt13">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArt13" aria-expanded="false" aria-controls="collapseArt13">
                                Article 13 : Consultation du règlement
                            </button>
                        </h2>
                        <div id="collapseArt13" class="accordion-collapse collapse" aria-labelledby="headingArt13" data-bs-parent="#accordionReglement">
                            <div class="accordion-body">
                                Une copie certifiée conforme à l’original sera remise gratuitement en mains propres à toute personne qui en fera la demande par écrit. Elle ne pourra en aucun cas être expédiée par voie postale ou autrement.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background-color: #f8f9fa; border-top: 1px solid #dee2e6;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #6c757d; color: white; border: none; padding: 0.375rem 0.75rem; border-radius: 0.25rem;">Fermer</button>
            </div>
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

<script>
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

document.addEventListener('DOMContentLoaded', function() {
    // Ajoute le scroll smooth lors de l'ouverture du modal règlement
    var reglementTriggers = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#reglementModal"], a[href="#reglementModal"]');
    reglementTriggers.forEach(function(trigger) {
        trigger.addEventListener('click', function() {
            setTimeout(scrollToIsidor, 100);
        });
    });
});

document.addEventListener('shown.bs.modal', function (event) {
    if (event.target.id === 'reglementModal') {
        var modalContent = event.target.querySelector('.modal-content');
        if (modalContent) {
            var topPx = (window.innerHeight - modalContent.offsetHeight) / 2;
            if (topPx < window.innerHeight * 0.10) topPx = window.innerHeight * 0.10;
            modalContent.style.top = topPx + 'px';
            modalContent.style.height = '80vh';
            modalContent.style.maxHeight = '80vh';
            modalContent.style.position = 'absolute';
            modalContent.style.left = '0';
            modalContent.style.right = '0';
            modalContent.style.margin = '0 auto';
            console.log('[DEBUG] Modal centered at', topPx + 'px, height 80vh');
        }
    }
});
</script>