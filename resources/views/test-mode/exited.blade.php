@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning">Sortie du mode test</div>
                <div class="card-body text-center">
                    <h3>Mode test désactivé</h3>
                    <p class="mb-4">Toutes les données de test ont été supprimées (sessions, cookies, localStorage).</p>
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Redirection en cours...</span>
                    </div>
                    <p>Redirection vers la page d'accueil en cours...</p>
                </div>
            </div>
        </div>
    </div>
</div>

{!! $clearScript !!}
@endsection
