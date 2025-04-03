@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="text-center">Limite de participation</h1>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-3"></i>
                        <h2>{{ $message }}</h2>
                        
                        @if($days_remaining > 0)
                            <p class="mt-4">Vous pourrez jouer à nouveau dans <strong>{{ $days_remaining }}</strong> jour(s).</p>
                        @endif
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <i class="bi bi-house-door"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
