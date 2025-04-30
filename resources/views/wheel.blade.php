@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card wheel-card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">🎯 Jeu dinor 70 ans 🎉</h2>
                </div>
                <div class="card-body text-center">
                    <!-- Identifiant du concours pour le système localStorage -->
                    <input type="hidden" name="contestId" value="{{ $entry->contest_id }}" id="contestId">
                    <p class="lead mb-4">
                        Bonjour <span style="font-weight: normal; color: var(--primary-red);">{{ $entry->participant->first_name }} {{ $entry->participant->last_name }}</span>, <br>
                        @if(!$entry->has_played)
                            tournez la roue et tentez votre chance ! 🍀🎁<br>
                        @else
                            votre participation est terminée. <br>
                            @if($entry->has_won)
                                🎉 Félicitations ! 🎉<br>
                                Vous avez gagné un lot incroyable ! 🎁🎀
                            @else
                                😔 Pas de chance cette fois-ci. <br>
                                Mais ne vous découragez pas, revenez bientôt pour tenter à nouveau ! 💪
                            @endif
                        @endif
                    </p>

                    @livewire('fortune-wheel', ['entry' => $entry])
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

/* Styles améliorés pour la lisibilité */
.card-header {
    padding: 1.5rem;
    font-size: 1.4rem;
}

.lead {
    font-size: 1.4rem;
    line-height: 2;
    margin-bottom: 1.5rem;
}

p {
    font-size: 1.2rem;
    line-height: 1.8;
    margin-bottom: 0.8rem;
}

.btn {
    font-size: 1.2rem;
    padding: 0.8rem 1.5rem;
    margin-top: 1rem;
}

.wheel-card {
    margin-bottom: 2rem;
    overflow: visible !important;
    border: 1px solid rgba(0, 0, 0, 0.1);
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card-body {
    padding: 2rem;
}

@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}
</style>
@endsection
