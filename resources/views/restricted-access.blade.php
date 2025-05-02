@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h2 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Accès non autorisé</h2>
                </div>
                <div class="card-body text-center">
                    <div class="restricted-icon mb-4">
                        <i class="fas fa-user-lock fa-5x text-danger"></i>
                    </div>
                    
                    <h3 class="mb-4">Jeu interdit aux employés de SIFCA et Big Five</h3>
                    
                    <div class="alert alert-warning">
                        <p class="lead mb-0">
                            {{ $reason ?? 'Le jeu est interdit aux employés et membres de la famille de SIFCA et Big Five pour des raisons de transparence et d\'équité.' }}
                        </p>
                    </div>
                    
                    <p class="mb-4">
                        Merci de votre compréhension. Ce jeu est exclusivement réservé aux clients et partenaires externes.
                    </p>
                    
                    <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i> Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .restricted-icon {
        margin: 2rem 0;
        color: #dc3545;
    }
    
    .card {
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,.1);
        margin-top: 2rem;
    }
    
    .card-header {
        font-weight: 600;
        padding: 1.25rem;
    }
    
    .alert-warning {
        border-left: 4px solid #dc3545;
        background-color: #fff8e1;
    }
    
    .lead {
        font-size: 1.2rem;
    }
</style>
@endsection
