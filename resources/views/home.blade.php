@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="hero-section text-center py-5">
        <h1 class="display-4 mb-4">Bienvenue à la Roue de la Fortune</h1>
        <p class="lead mb-4">Tentez votre chance et gagnez des prix exceptionnels !</p>
        
        @if ($contest)
            <div class="contest-info mb-4">
                <h2>{{ $contest->name }}</h2>
                <p>{{ $contest->description }}</p>
                <p>
                    <strong>Période :</strong> 
                    {{ $contest->start_date->format('d/m/Y') }} - {{ $contest->end_date->format('d/m/Y') }}
                </p>
            </div>
            
            @if ($hasParticipated)
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                    <strong>Participation limitée !</strong> Vous avez déjà participé à ce concours.
                    <p class="mt-2 mb-0">
                        Une seule participation par concours est autorisée.
                        @if($contest_end_date)
                            <br>Ce concours se termine le <strong>{{ $contest_end_date }}</strong>.
                        @endif
                    </p>
                </div>
                
                <!-- Bouton pour voir le dernier résultat (à implémenter dans une future mise à jour) -->
                <button class="btn btn-outline-primary" id="check-last-participation" data-contest="{{ $contest_id }}">
                    Voir mon dernier résultat
                </button>
            @else
                <a href="{{ route('register', ['contestId' => $contest->id]) }}" class="btn btn-primary btn-lg">
                    Participer maintenant
                </a>
            @endif
        @else
            <div class="alert alert-info">
                Aucun concours n'est actuellement actif. Revenez bientôt !
            </div>
        @endif
    </div>
    
    <div class="features-section py-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-gift fa-3x mb-3 text-primary"></i>
                        <h3>Des prix incroyables</h3>
                        <p>Gagnez des produits, des bons d'achat et des services exclusifs.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-sync-alt fa-3x mb-3 text-primary"></i>
                        <h3>Tournez la roue</h3>
                        <p>Une expérience interactive et amusante pour découvrir votre prix.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-qrcode fa-3x mb-3 text-primary"></i>
                        <h3>Récupération facile</h3>
                        <p>Utilisez votre code QR pour récupérer votre prix instantanément.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="how-it-works-section py-5 bg-light">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="step-card text-center">
                    <div class="step-number">1</div>
                    <h4>Inscription</h4>
                    <p>Remplissez le formulaire d'inscription avec vos coordonnées.</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-card text-center">
                    <div class="step-number">2</div>
                    <h4>Tournez la roue</h4>
                    <p>Lancez la roue et découvrez votre prix.</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-card text-center">
                    <div class="step-number">3</div>
                    <h4>Recevez votre QR code</h4>
                    <p>Un code QR unique est généré pour votre prix.</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-card text-center">
                    <div class="step-number">4</div>
                    <h4>Récupérez votre prix</h4>
                    <p>Présentez votre QR code pour récupérer votre gain.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --honolulu-blue: #0079B2ff;
        --apple-green: #86B942ff;
        --school-bus-yellow: #F7DB15ff;
        --persian-red: #D03A2Cff;
        --sea-green: #049055ff;
        --light-gray: #f5f5f5;
        --dark-gray: #333333;
    }
    
    .hero-section {
        background-color: var(--light-gray);
        padding: 3rem 0;
        border-radius: 4px;
        margin-bottom: 2rem;
    }
    
    .contest-info {
        max-width: 600px;
        margin: 0 auto;
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 4px;
        border: 1px solid #e0e0e0;
    }
    
    .features-section .card {
        transition: all 0.2s;
        border-radius: 4px;
        border: 1px solid #e0e0e0;
    }
    
    .features-section .card:hover {
        background-color: var(--light-gray);
    }
    
    .step-card {
        background: white;
        padding: 1.5rem;
        border-radius: 4px;
        border: 1px solid #e0e0e0;
        height: 100%;
    }
    
    .step-number {
        display: inline-block;
        width: 40px;
        height: 40px;
        line-height: 40px;
        background-color: var(--honolulu-blue);
        color: white;
        border-radius: 50%;
        font-weight: bold;
        margin-bottom: 1rem;
    }
    
    .card {
        background-color: #fff !important;
        border: 1px solid #e0e0e0 !important;
    }
    
    .text-primary {
        color: var(--honolulu-blue) !important;
    }
    
    .btn-primary {
        background-color: var(--honolulu-blue);
        border: none;
    }
    
    .btn-primary:hover {
        background-color: #006699;
    }
    
    .bg-light {
        background-color: var(--light-gray) !important;
    }
</style>
@endsection
