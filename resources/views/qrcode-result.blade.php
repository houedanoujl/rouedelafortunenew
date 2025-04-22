@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h2 class="mb-0">🎉 Félicitations !</h2>
                </div>
                <div class="card-body text-center">
                    <h3 class="mb-4">✨ Voici votre lot incroyable ✨</h3>
                    
                    @if($prize)
                        <div class="prize-details">
                            <h4 class="mb-3" style="color: var(--primary-red);">🎁 {{ $prize->name }} 🎉</h4>
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
                        <p class="lead">
                            🌟 Bravo à : <span style="font-weight: normal; color: var(--primary-red);">{{ $entry->participant->first_name }} {{ $entry->participant->last_name }}</span> 🌟
                        </p>
                        <p class="text-muted">
                            🔑 Code unique : {{ $qrCode->code }}
                        </p>
                        <p class="text-success mt-3">
                            👍 Votre lot est prêt à être récupéré !
                        </p>
                    </div>
                    
                    <a href="{{ route('home') }}" class="btn btn-primary mt-4" style="background-color: var(--primary-red); border: none;">
                        🏠 Retour à l'accueil
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
    const count = 300; // Plus de confettis
    const defaults = {
        origin: { y: 0.7 },
        spread: 360,
        ticks: 120, // Dure plus longtemps
        gravity: 0.4, // Chute plus lente
        decay: 0.92, // Décélère plus lentement
        startVelocity: 35, // Plus rapide
        colors: ['#D03A2C', '#F7DB15', '#28a745', '#0079B2', '#ff9500'] // Couleurs personnalisées
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

<style>
/* Définition des variables de couleur */
:root {
    --primary-red: #D03A2C;
    --success-green: #28a745;
    --text-highlight: #D03A2C;
}
</style>
@endif

@if(session('whatsapp_result'))
    <div class="alert alert-info text-center" id="whatsapp-alert" style="z-index:9999;position:fixed;top:20px;left:50%;transform:translateX(-50%);min-width:300px;max-width:90%;">
        {{ session('whatsapp_result') }}
    </div>
    <script>
        setTimeout(function() {
            var alert = document.getElementById('whatsapp-alert');
            if(alert) alert.style.display = 'none';
        }, 6000);
    </script>
@endif

@endsection
