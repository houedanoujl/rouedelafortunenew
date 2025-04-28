@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-warning text-dark">
                    <h3 class="mb-0 text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>Plus de lots disponibles
                    </h3>
                </div>
                <div class="card-body text-center p-5">
                    <img src="{{ asset('images/logo.webp') }}" alt="Stock épuisé" class="img-fluid mb-4" style="max-height: 200px;">
                    
        
                    <p class="lead mb-4">
                        Merci pour votre participation au concours "{{ $contest->name ?? 'Grand Jeu Dinor 70 ans' }}".
                        Malheureusement, tous les lots disponibles cette semaine ont déjà été attribués.
                    </p>
                    
                    <p class="mb-5">
                        Revenez bientôt pour de nouvelles opportunités de gagner des prix exceptionnels !
                    </p>
                    
                    <div class="d-grid gap-2 col-md-6 mx-auto">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                    </div>
                </div>
                <div class="card-footer text-center text-muted">
                    <small> {{ date('Y') }} Dinor 70 ans - Tous droits réservés</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
