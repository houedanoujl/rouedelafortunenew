@extends('layouts.app')

@section('content')
<div class="rules-container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Règlement du jeu</h2>
                </div>
                <div class="card-body">
                    <div class="rules-content">
                        <h3>Article 1 : Organisation</h3>
                        <p>La société Roue de la Fortune, immatriculée au registre du commerce et des sociétés sous le numéro 123 456 789, dont le siège social est situé au 123 Avenue des Jeux, 75000 Paris, organise un jeu concours avec obligation d'achat intitulé "La Roue de la Fortune".</p>
                        
                        <h3>Article 2 : Participants</h3>
                        <p>Ce jeu est ouvert à toute personne physique majeure résidant en France métropolitaine. Sont exclus du jeu les membres du personnel de la société organisatrice et toute personne ayant directement ou indirectement participé à la conception, à la réalisation ou à la gestion du jeu, ainsi que leur conjoint et les membres de leur famille.</p>
                        
                        <h3>Article 3 : Modalités de participation</h3>
                        <p>Pour participer au jeu, il suffit de :</p>
                        <ol>
                            <li>Se rendre sur le site www.rouedelafortune.com</li>
                            <li>Remplir le formulaire d'inscription avec ses coordonnées complètes</li>
                            <li>Valider sa participation en tournant la roue virtuelle</li>
                        </ol>
                        <p>La participation est limitée à une fois par jour et par personne (même nom, même adresse email, même numéro de téléphone).</p>
                        
                        <h3>Article 4 : Désignation des gagnants</h3>
                        <p>Les gagnants sont désignés par le résultat de la roue virtuelle. Le résultat est immédiatement communiqué au participant à l'issue de sa participation.</p>
                        
                        <h3>Article 5 : Dotations</h3>
                        <p>Les lots à gagner sont divers et variés, allant de produits physiques à des bons d'achat ou des services. La valeur des lots est comprise entre 5€ et 500€. La liste complète des lots est disponible sur demande auprès de l'organisateur.</p>
                        
                        <h3>Article 6 : Remise des lots</h3>
                        <p>Les gagnants recevront un code QR unique à présenter dans l'un des points de collecte partenaires pour récupérer leur lot. La liste des points de collecte est disponible sur le site internet. Les lots doivent être réclamés dans un délai de 30 jours à compter de la date de gain. Passé ce délai, les lots non réclamés seront remis en jeu.</p>
                        
                        <h3>Article 7 : Données personnelles</h3>
                        <p>Les informations collectées lors de l'inscription sont destinées à la société organisatrice pour les besoins du jeu. Conformément à la loi "Informatique et Libertés" du 6 janvier 1978 modifiée et au Règlement Général sur la Protection des Données (RGPD), les participants disposent d'un droit d'accès, de rectification et de suppression des données les concernant en écrivant à l'adresse de la société organisatrice.</p>
                        
                        <h3>Article 8 : Litiges</h3>
                        <p>Le présent règlement est soumis à la loi française. Tout litige relatif à l'application ou à l'interprétation du présent règlement sera soumis à la compétence des tribunaux français.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rules-content {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .rules-content h3 {
        color: #007bff;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .rules-content p, .rules-content ol {
        margin-bottom: 1rem;
    }
</style>
@endsection
