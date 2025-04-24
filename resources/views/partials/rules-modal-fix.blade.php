<!-- Modal pour le règlement de la tombola (version simplifiée) -->
<div class="modal fade" id="reglementModal" tabindex="-1" aria-labelledby="reglementModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reglementModalLabel">REGLEMENT DE LA TOMBOLA « Promo 70 ans de la marque DINOR »</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Style spécifique pour améliorer la lisibilité de l'accordéon -->
                <style>
                    .reglement-accordion .article-title {
                        cursor: pointer;
                        padding: 10px 15px;
                        background-color: #f8f9fa;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        margin-bottom: 5px;
                        font-weight: 600;
                        color: #343a40;
                        position: relative;
                        transition: all 0.3s ease;
                    }

                    .reglement-accordion .article-title:hover {
                        background-color: #e9ecef;
                    }

                    .reglement-accordion .article-title::after {
                        content: "+";
                        position: absolute;
                        right: 15px;
                        top: 50%;
                        transform: translateY(-50%);
                        font-size: 18px;
                    }

                    .reglement-accordion .article-title.active::after {
                        content: "-";
                    }

                    .reglement-accordion .article-content {
                        padding: 15px;
                        border: 1px solid #e9ecef;
                        border-top: none;
                        border-radius: 0 0 4px 4px;
                        margin-bottom: 15px;
                        display: none;
                    }

                    .reglement-accordion .article-content.show {
                        display: block;
                    }

                    .reglement-accordion .article-title.active {
                        background-color: #e2e6ea;
                        border-radius: 4px 4px 0 0;
                        margin-bottom: 0;
                    }
                </style>

                <!-- Accordéon custom des articles -->
                <div class="reglement-accordion">
                    <!-- Article 1 -->
                    <div class="article-title active" data-article="1">Article 1 : Organisation de la Tombola</div>
                    <div class="article-content show" id="content-1">
                        <p>SANIA Cie, Société Anonyme avec Conseil d'Administration au capital de 44.110.000.000 FCFA, immatriculé au Registre du Commerce et du Crédit Mobilier d'Abidjan sous le numéro CI-ABJ-2008-B14-3985, dont le siège social est sis à Abidjan, Zone industrielle de Vridi, rue du textile, 01 BP 2949 Abidjan 01, représentée par son Directeur Général, Monsieur Philippe RAYNAL ayant tous pouvoirs à l'effet des présentes ;</p>
                        <p>Ci-après dénommée « l'Organisateur »</p>
                        <p>Organise une Tombola dénommée « Promo 70 ans de la marque DINOR ».</p>
                        <p>Ci-après désignée « la Tombola ».</p>
                    </div>

                    <!-- Article 2 -->
                    <div class="article-title" data-article="2">Article 2 : Objet de la Tombola</div>
                    <div class="article-content" id="content-2">
                        <p>La présente Tombola promotionnelle a pour objectif de commémorer les 70 ans de la marque DINOR.</p>
                        <p>Ci-après « les Participants »</p>
                        <p>La participation à la Tombola implique l'acceptation sans réserve par les participants du présent règlement dans son intégralité.</p>
                        <p>Ci-après « le Règlement ».</p>
                    </div>

                    <!-- Article 3 -->
                    <div class="article-title" data-article="3">Article 3 : Date et durée</div>
                    <div class="article-content" id="content-3">
                        <p>La Tombola se déroulera du 1er mai au 30 juillet 2025 inclus.</p>
                    </div>

                    <!-- Article 4 -->
                    <div class="article-title" data-article="4">Article 4 : Cadre réglementaire et conditions de participation</div>
                    <div class="article-content" id="content-4">
                        <h5>4-1 Cadre réglementaire</h5>
                        <p>La Tombola « Promo 70 ans de la marque DINOR » se déroule conformément aux dispositions de la loi n°2020-480 du 27 Mai 2020 portant régime juridique des jeux de hasard en Côte d'Ivoire et du décret n°2023-946 du 06 décembre 2023 portant régime juridique des jeux de hasard soumis à autorisation.</p>
                        <p>Le présent règlement est authentifié par Maitre Diamilatou SIDIBE AKA-ANGHUI, Notaire, dont l'Etude est située à Abidjan Cocody, Route du Lycée Technique, villa n°4, Immeuble SIKA - Danga - 06 BP 2438 Abidjan 06. Abidjan.</p>

                        <h5>4-2 Conditions de participation</h5>
                        <p>La Tombola qui se déroulera sur toute l'étendue du territoire de la République de Côte d'Ivoire est destinée aux consommateurs majeurs des produits de la marque DINOR et/ou aux utilisateurs majeurs de l'Application « Dinor App ». Cette Tombola est interdite aux mineurs de moins de 18 ans.</p>
                        <p>Lesdits Participants pourront via le Quick Response Code (QR Code), visible sur les étiquettes des produits de la marque DINOR et dédié à la Tombola, accéder à une roue qui leur permettra de gagner des lots immédiats.</p>
                        <p>La roue comporte les mentions « Gagné » et « Perdu ».</p>
                        <p>En pratique, les Participants devront :</p>
                        <ul>
                            <li>Scanner le QR Code sur les produits DINOR achetés. Ils seront redirigés sur l'application DINOR App ou invités à la télécharger pour accéder à la landing page ;</li>
                            <li>Remplir le formulaire avec leurs informations personnelles requises ;</li>
                            <li>Tourner la Roue pour tenter de gagner un lot ;</li>
                            <li>Retirer le lot au siège social de Sania Cie, sis à Abidjan Vridi, rue des Textiles ;</li>
                        </ul>
                    </div>

                    <!-- Article 5 -->
                    <div class="article-title" data-article="5">Article 5 : Désignation des lauréats</div>
                    <div class="article-content" id="content-5">
                        <p>Est déclaré gagnant tout Participant qui, après rotation de la roue, voit cette dernière s'arrêter sur la mention "Gagné".</p>
                        <p>Le lauréat est toute personne ayant rempli les conditions de participation et ayant été reconnue gagnante conformément à l'article 4.2 des présentes.</p>
                    </div>

                    <!-- Article 6 -->
                    <div class="article-title" data-article="6">Article 6 : Lots à gagner</div>
                    <div class="article-content" id="content-6">
                        <p>Les lots à gagner dans le cadre de cette Tombola sont les suivants :</p>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>DESIGNATION</th>
                                        <th>QTE</th>
                                        <th>PU</th>
                                        <th>TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Télévision</td>
                                        <td>5</td>
                                        <td>150 000</td>
                                        <td>750 000</td>
                                    </tr>
                                    <tr>
                                        <td>Réfrigérateur</td>
                                        <td>5</td>
                                        <td>100 000</td>
                                        <td>500 000</td>
                                    </tr>
                                    <tr>
                                        <td>Bons d'achat Hyper U 50 000 F CFA</td>
                                        <td>5</td>
                                        <td>50 000</td>
                                        <td>250 000</td>
                                    </tr>
                                    <tr>
                                        <td>Friteuse air fryer</td>
                                        <td>5</td>
                                        <td>40 000</td>
                                        <td>200 000</td>
                                    </tr>
                                    <tr>
                                        <td>Micro-ondes</td>
                                        <td>10</td>
                                        <td>60 000</td>
                                        <td>600 000</td>
                                    </tr>
                                    <tr>
                                        <td>Blender</td>
                                        <td>25</td>
                                        <td>30 000</td>
                                        <td>750 000</td>
                                    </tr>
                                    <tr>
                                        <td>Ventilateur</td>
                                        <td>100</td>
                                        <td>15 000</td>
                                        <td>1 500 000</td>
                                    </tr>
                                    <tr>
                                        <td>Carton de 5L Huile Dinor</td>
                                        <td>15</td>
                                        <td>15 000</td>
                                        <td>225 000</td>
                                    </tr>
                                    <tr>
                                        <td>Sac de riz 25KG Dinor</td>
                                        <td>10</td>
                                        <td>15 000</td>
                                        <td>150 000</td>
                                    </tr>
                                    <tr>
                                        <td>Carton savon Dinor</td>
                                        <td>2000</td>
                                        <td>7 000</td>
                                        <td>14 000 000</td>
                                    </tr>
                                    <tr>
                                        <td>Carton sardine Dinor</td>
                                        <td>2000</td>
                                        <td>6 500</td>
                                        <td>13 000 000</td>
                                    </tr>
                                    <tr>
                                        <td>TOTAL</td>
                                        <td></td>
                                        <td></td>
                                        <td>31 925 000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Article 7 -->
                    <div class="article-title" data-article="7">Article 7 : Informations sur les lauréats</div>
                    <div class="article-content" id="content-7">
                        <p>Les gagnants sont déclarés après tirage au sort électronique dématérialisé acté par huissier de justice pour les lots.</p>
                        <p>Le Commissaire de Justice constate le tirage électronique et dresse un procès-verbal de tirage. Il dresse également un procès-verbal d'attribution des lots.</p>
                        <p>Un exemplaire des procès-verbaux susvisés dûment enregistrés, est transmis à l'ARJH, dans un délai de dix jours après les opérations constatées par le Commissaire de Justice cité aux alinéas précédents.</p>
                    </div>

                    <!-- Article 8 -->
                    <div class="article-title" data-article="8">Article 8 : Retrait des Lots</div>
                    <div class="article-content" id="content-8">
                        <p>Les lots sont à retirer dans un délai maximum d'un (01) mois à compter de la date de leur gain, du lundi au vendredi, de 08 heures à 12 heures au siège de la société SANIA Cie.</p>
                        <p>Passé ce délai, les lots gagnés et non réclamés seront reversés à l'ARJH, au profit d'un organisme de bienfaisance désigné par le Conseil de régulation.</p>
                        <p>La remise des lots pourra être faite au représentant du lauréat muni de la coipe de la carte nationale d'identité ou du passeport du gagnant.</p>
                    </div>

                    <!-- Article 9 -->
                    <div class="article-title" data-article="9">Article 9 : Données personnelles</div>
                    <div class="article-content" id="content-9">
                        <p>Dans le cadre uniquement de la communication des résultats de la présente loterie promotionnelle, les gagnants acceptent que leurs noms, prénoms, images et voix soient utilisés par SANIA Cie à des fins publicitaires ou promotionnelles sans contrepartie financière. Les enregistrements pouvant être diffusés en tout ou partie sur des supports tels que : la presse-magazine et/ou les réseaux sociaux.</p>
                        <p>Le consentement desdits gagnants est exprès et donné par écrit sur une fiche de recueil de consentement prévue en annexe.</p>
                        <p>Les données personnelles des participants sont collectées et traitées conformément à la règlementation en vigueur. Elles ne seront utilisées uniquement que pour les besoins du jeu et ne seront pas transmises à des tiers.</p>
                        <p>Conformément à la loi n°2013-450 relative à la protection des données à caractère personnel, les gagnants disposent d'un droit d'accès, de modification, de rectification et de suppression des données les concernant. S'ils souhaitent l'exercer, ils peuvent adresser leur demande à l'adresse mail : .</p>
                    </div>

                    <!-- Article 10 -->
                    <div class="article-title" data-article="10">Article 10 : Cas de force majeure</div>
                    <div class="article-content" id="content-10">
                        <p>La responsabilité de l'organisateur ne saurait être encourue si, pour un cas de force majeure, la Tombola devait être modifiée, écourtée ou annulée.</p>
                    </div>

                    <!-- Article 11 -->
                    <div class="article-title" data-article="11">Article 11 : Litiges</div>
                    <div class="article-content" id="content-11">
                        <p>Le présent règlement est exclusivement régi par la loi ivoirienne.</p>
                        <p>Toutes contestations qui découlent du présent jeu ou qui s'y rapportent, feront l'objet d'un règlement amiable entre les parties.</p>
                        <p>À défaut d'accord entre les parties, l'Autorité de Régulation de Jeux de Hasard (ARJH) sera compétente pour connaître du litige.</p>
                    </div>

                    <!-- Article 12 -->
                    <div class="article-title" data-article="12">Article 12 : Dépôt et consultation du Règlement</div>
                    <div class="article-content" id="content-12">
                        <p>Le règlement de la Tombola est déposé en l'Etude par Maitre Diamilatou SIDIBE AKA-ANGHUI, Notaire, dont l'Etude est située à Abidjan Cocody, Route du Lycée Technique, villa n°4, Immeuble SIKA - Danga - 06 BP 2438 Abidjan 06. Abidjan et à l'Autorité de Régulation des Jeux de hasard (ARJH).</p>
                        <p>Un Procès-verbal de jeu est dressé et transmis à l'ARJH à la fin du jeu.</p>
                    </div>

                    <!-- Article 13 -->
                    <div class="article-title" data-article="13">Article 13 : Consultation du règlement</div>
                    <div class="article-content" id="content-13">
                        <p>Une copie certifiée conforme à l'original sera remise gratuitement en mains propres à toute personne qui en fera la demande par écrit. Elle ne pourra en aucun cas être expédiée par voie postale ou autrement.</p>

                        <p>Fait à Abidjan, le</p>
                        <p>En deux (02) exemplaires originaux</p>

                        <p>Authentification Notaire<br>Directeur Général</p>
                    </div>
                </div>

                <!-- Script JS personnalisé pour l'accordéon -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Sélectionner tous les titres d'articles
                        const articleTitles = document.querySelectorAll('.reglement-accordion .article-title');

                        // Ajouter des écouteurs d'événements à chaque titre
                        articleTitles.forEach(title => {
                            title.addEventListener('click', function() {
                                // Récupérer l'id de l'article
                                const articleId = this.getAttribute('data-article');
                                const content = document.getElementById('content-' + articleId);

                                // Vérifier si cet article est déjà actif
                                const isActive = this.classList.contains('active');

                                // Fermer tous les articles
                                articleTitles.forEach(t => {
                                    t.classList.remove('active');
                                });

                                const allContents = document.querySelectorAll('.reglement-accordion .article-content');
                                allContents.forEach(c => {
                                    c.classList.remove('show');
                                });

                                // Si l'article n'était pas actif, l'ouvrir
                                if (!isActive) {
                                    this.classList.add('active');
                                    content.classList.add('show');
                                }
                            });
                        });
                    });
                </script>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
