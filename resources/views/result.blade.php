@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-{{ $entry->has_won ? 'success' : 'danger' }} text-white">
                    <h2 class="mb-0">{{ $entry->has_won ? 'Félicitations !' : 'Pas de chance...' }}</h2>
                </div>
                <div class="card-body text-center">
                    <p class="lead mb-4">
                        {{ $entry->participant->first_name }} {{ $entry->participant->last_name }},
                        @if($entry->has_won)
                            vous avez gagné ! Scannez le QR code ci-dessous pour réclamer votre prix.
                            @if($qrCode)
                                <div class="qr-code-container mt-4 d-flex justify-content-center align-items-center">
                                    {!! QrCode::size(200)->generate(route('qrcode.result', ['code' => $qrCode->code])) !!}
                                </div>
                                <p class="mt-4">Code : {{ $qrCode->code }}</p>
                                
                                <div class="mt-3 d-flex justify-content-center gap-2">
                                    <a href="{{ route('qrcode.download.pdf', ['code' => $qrCode->code]) }}" class="btn btn-danger">
                                        <i class="bi bi-file-earmark-pdf"></i> Télécharger en PDF
                                    </a>
                                    <a href="{{ route('qrcode.download.png', ['code' => $qrCode->code]) }}" class="btn btn-info">
                                        <i class="bi bi-file-earmark-image"></i> Télécharger en PNG
                                    </a>
                                </div>
                                
                                <button type="button" class="btn btn-primary mt-3" id="viewPrizeBtn">
                                    Voir mon lot
                                </button>
                            @endif
                        @else
                            malheureusement vous n'avez pas gagné cette fois-ci.
                        @endif
                    </p>

                    <a href="{{ route('home') }}" class="btn btn-secondary">Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
</div>

@if($entry->has_won)
<!-- Modal -->
<div class="modal fade" id="prizeModal" tabindex="-1" aria-labelledby="prizeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prizeModalLabel">Votre lot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                @if(isset($prize) && $prize)
                    <h4>🎁 {{ $prize->name }}</h4>
                    <p class="mt-3">{{ $prize->description }}</p>
                    @if($prize->image)
                        <img src="{{ asset('storage/' . $prize->image) }}" alt="{{ $prize->name }}" class="img-fluid mt-3">
                    @endif
                @else
                    <h4>🎁 Félicitations !</h4>
                    <p class="mt-3">Vous avez remporté un lot.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endif

@if($entry->has_won)
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation manuelle du modal pour éviter les conflits
    const viewPrizeBtn = document.getElementById('viewPrizeBtn');
    
    // Vérifier si le bouton existe avant d'ajouter l'écouteur d'événement
    if (viewPrizeBtn) {
        viewPrizeBtn.addEventListener('click', function() {
            // Utiliser jQuery qui est plus stable pour manipuler les modals Bootstrap
            $('#prizeModal').modal('show');
        });
    }

    // Only launch confetti if we came from a win
    if(window.location.href.includes('spin.result')) {
        setTimeout(launchConfetti, 300);
    }
    
    function launchConfetti() {
        const count = 200;
        const defaults = {
            origin: { y: 0.7 },
            spread: 360,
            ticks: 100,
            gravity: 0,
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
    }
});
</script>
@endif
@endsection
