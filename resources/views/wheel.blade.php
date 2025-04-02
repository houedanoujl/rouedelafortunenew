@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card wheel-card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Roue de la Fortune</h2>
                </div>
                <div class="card-body text-center">
                    <p class="lead mb-4">
                        Bonjour {{ $entry->participant->first_name }} {{ $entry->participant->last_name }}, 
                        @if(!$entry->has_played)
                            tournez la roue et tentez votre chance !
                        @else
                            votre participation est terminée.
                            @if($entry->has_won)
                                Félicitations ! Vous avez gagné !
                            @else
                                Pas de chance cette fois-ci.
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
.wheel-card {
    margin-bottom: 2rem;
    overflow: visible !important;
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
