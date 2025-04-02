@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h2 class="mb-0">🎉 Félicitations !</h2>
                </div>
                <div class="card-body text-center">
                    <h3 class="mb-4">Voici votre lot</h3>
                    
                    @if($prize)
                        <div class="prize-details">
                            <h4 class="mb-3">🎁 {{ $prize->name }}</h4>
                            <p class="lead">{{ $prize->description }}</p>
                            
                            @if($prize->image)
                                <div class="mt-4">
                                    <img src="{{ asset('storage/' . $prize->image) }}" 
                                         alt="{{ $prize->name }}" 
                                         class="img-fluid rounded shadow">
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="lead text-danger">Désolé, il n'y a plus de lots disponibles.</p>
                    @endif
                    
                    <div class="mt-4">
                        <p class="text-muted">
                            Participant : {{ $entry->participant->first_name }} {{ $entry->participant->last_name }}
                        </p>
                        <p class="text-muted">
                            Code : {{ $qrCode->code }}
                        </p>
                    </div>
                    
                    <a href="{{ route('home') }}" class="btn btn-primary mt-4">
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if($prize)
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const count = 200;
    const defaults = {
        origin: { y: 0.7 },
        spread: 360,
        ticks: 100,
        gravity: 0.5,
        decay: 0.94,
        startVelocity: 30
    };

    function fire(particleRatio, opts) {
        confetti({
            ...defaults,
            ...opts,
            particleCount: Math.floor(count * particleRatio)
        });
    }

    fire(0.25, {
        spread: 26,
        startVelocity: 55,
    });

    fire(0.2, {
        spread: 60,
    });

    fire(0.35, {
        spread: 100,
        decay: 0.91,
        scalar: 0.8
    });

    fire(0.1, {
        spread: 120,
        startVelocity: 25,
        decay: 0.92,
        scalar: 1.2
    });

    fire(0.1, {
        spread: 120,
        startVelocity: 45,
    });
});
</script>
@endif
@endsection
