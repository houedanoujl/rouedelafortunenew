@extends('layouts.app')

@section('content')
<div class="container already-played-page">
    <!-- Script pour stocker la clé en localStorage -->
    @if(isset($localStorageKey))
    <script>
        // Stocker l'information dans localStorage pour renforcer la limitation
        document.addEventListener('DOMContentLoaded', function() {
            localStorage.setItem('{{ $localStorageKey }}', 'played');
            console.log('Participation enregistrée dans localStorage: {{ $localStorageKey }}');
        });
    </script>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header" style="background-color: var(--primary-red); color: white;">
                    <h1 class="text-center">⏰ Limite de participation ⏰</h1>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-warning">
                        <i class="bi bi-hourglass-split fs-1 d-block mb-3"></i>
                        <h2 style="color: var(--primary-red);">{{ $message }} 👀</h2>

                        @if(isset($contest_name))
                            <p class="mt-3">🎟️ Vous avez déjà participé au concours <span style="color: var(--primary-red);">{{ $contest_name }}</span>.</p>
                            <p class="mt-2">🎁 Chaque participant ne peut tenter sa chance qu'une seule fois par semaine !</p>
                            @if(isset($contest_end_date))
                                <!--<p class="mt-3">📅 Ce concours se termine le <span style="color: var(--primary-red);">{{ $contest_end_date }}</span>.</p>-->
                                <p>✨ Un nouveau concours avec de nouveaux lots incroyables sera peut-être disponible après cette date. 🎉</p>
                                <p class="mt-3">📱 Gardez un œil sur notre application pour ne rien manquer !</p>
                            @endif
                        @endif
                    </div>

                    <div class="mt-4">
                        @if(isset($existing_entry) && $existing_entry)
                            <a href="{{ route('result.show', ['entry' => $existing_entry->id]) }}" class="btn btn-primary" style="background-color: var(--success-green); border: none;">
                                <i class="bi bi-ticket-perforated"></i> Voir ma participation 🎟️
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="hide hidden btn btn-primary" style="background-color: var(--primary-red); border: none;display:none !important;">
                                <i class="bi bi-house-door"></i> Retour à l'accueil 🏠
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Définition des variables de couleur */
:root {
    --primary-red: #D03A2C;
    --success-green: #28a745;
    --text-highlight: #D03A2C;
}
</style>
@endsection
