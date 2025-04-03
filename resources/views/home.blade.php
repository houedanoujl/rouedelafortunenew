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
            
            @if ($hasPlayedThisWeek)
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                    <strong>Participation limitée !</strong> Vous avez déjà participé cette semaine.
                    @if($daysRemaining > 0)
                        <p class="mt-2 mb-0">Vous pourrez rejouer dans <strong>{{ $daysRemaining }}</strong> jour(s).</p>
                    @endif
                </div>
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
        <div class="text-center mb-5">
            <h2>Comment ça marche ?</h2>
        </div>
        
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
    .hero-section {
        background-color: #f8f9fa;
        padding: 3rem 0;
        border-radius: 10px;
        margin-bottom: 2rem;
    }
    
    .contest-info {
        max-width: 600px;
        margin: 0 auto;
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .features-section .card {
        transition: transform 0.3s;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .features-section .card:hover {
        transform: translateY(-10px);
    }
    
    .step-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        height: 100%;
    }
    
    .step-number {
        display: inline-block;
        width: 40px;
        height: 40px;
        line-height: 40px;
        background-color: #007bff;
        color: white;
        border-radius: 50%;
        font-weight: bold;
        margin-bottom: 1rem;
    }
    .card{
    background: rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid !important;
        border-image: linear-gradient(45deg, #ffd700, #ffa500) 1 !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;

  }
</style>
@endsection
