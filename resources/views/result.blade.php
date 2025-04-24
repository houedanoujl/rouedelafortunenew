@extends('layouts.app')

@section('content')
<?php
// Récupérer le résultat depuis la session (beaucoup plus sécurisé qu'un paramètre URL)
$sessionKey = 'spin_result_' . $entry->id;
$displayedResult = session($sessionKey, null);
$actualResultIsWin = $entry->has_won;

// Si le résultat en session est présent, l'utiliser; sinon utiliser le résultat de la base de données
$showWinResult = $displayedResult === 'win' ? true : ($displayedResult === 'lose' ? false : $actualResultIsWin);

// Si nous avons utilisé la session, nous pouvons la supprimer pour éviter toute confusion future
if ($displayedResult !== null) {
    session()->forget($sessionKey);
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-{{ $showWinResult ? 'success' : 'danger' }} text-white">
                    <h2 class="mb-0">{{ $showWinResult ? '🎉 Félicitations ! 🎉' : '😔 Pas de chance... 😔' }}</h2>
                </div>
                <div class="card-body text-center">
                    <p class="lead mb-4">
                        <span style="font-weight: normal; color: var(--primary-red);">{{ $entry->participant->first_name }} {{ $entry->participant->last_name }}</span>,
                        @if($showWinResult)
                            vous avez gagné ! 🎁 <br>
                            Conservez les fichiers téléchargés depuis cette page précieusement, ils feront office de justificatifs lors du retrait de votre lot.
                            @if($qrCode)
                                <div class="qr-code-container mt-4 d-flex justify-content-center align-items-center">
                                    <a href="javascript:void(0)" class="qr-code-link" title="Cliquez pour voir votre lot" aria-label="Voir les détails de votre lot gagné" role="button">
                                        {!! QrCode::size(200)->generate(route('qrcode.result', ['code' => $qrCode->code])) !!}
                                        <span class="sr-only">Cliquez sur le QR code pour voir votre lot</span>
                                    </a>
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
                                    👀 Voir mon lot 🔥
                                </button>
                            @endif
                        @else
                            malheureusement vous n'avez pas gagné cette fois-ci 😢 <br>
                            Mais ne vous découragez pas ! 💪 <br>
                            Revenez tenter votre chance la semaine prochaine<br>
                            pour de nouveaux lots incroyables ✨🎁
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('whatsapp_result'))
    <div class="alert alert-info text-center" id="whatsapp-alert" style="z-index:9999;position:fixed;top:20px;left:50%;transform:translateX(-50%);min-width:300px;max-width:90%;">
        {{ session('whatsapp_result') }}
    </div>
    <script>
        console.log('WhatsApp: {{ session('whatsapp_result') }}');
        setTimeout(function() {
            var alert = document.getElementById('whatsapp-alert');
            if(alert) alert.style.display = 'none';
        }, 6000);
    </script>
@endif

@if($showWinResult)
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
                    @if($prize->image_url)
                        <div class="prize-image-container mt-3">
                            <img src="{{ $prize->image_url }}" alt="{{ $prize->name }}" class="img-fluid prize-image">
                        </div>
                    @else
                        <div class="no-image-container mt-3">
                            <div class="no-image-placeholder">
                                <i class="bi bi-gift-fill" style="font-size: 3rem;"></i>
                                <p>Image non disponible</p>
                            </div>
                        </div>
                    @endif
                @else
                    <h4>🎁 Félicitations !</h4>
                    <p class="mt-3">Vous avez remporté un lot.</p>
                    <img src="{{ asset('img/prize_placeholder.jpg') }}" alt="Lot mystère" class="img-fluid mt-3">
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endif

@if($showWinResult)
<style>
    /* Définition des variables de couleur */
    :root {
        --primary-red: #D03A2C;
        --success-green: #28a745;
        --text-highlight: #D03A2C;
    }
    
    /* Styles pour améliorer la lisibilité */
    .card {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.1);
        background-color: rgba(255, 255, 255, 0.95);
    }
    
    .card-header {
        padding: 1.5rem;
        font-size: 1.5rem;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .lead {
        font-size: 1.4rem;
        line-height: 2;
        margin-bottom: 1.5rem;
    }
    
    p {
        font-size: 1.2rem;
        line-height: 1.8;
        margin-bottom: 1rem;
    }
    
    .qr-code-container {
        margin: 1.5rem auto;
        padding: 1.5rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        max-width: 250px;
    }
    
    .btn {
        font-size: 1.2rem;
        padding: 0.8rem 1.5rem;
        margin-top: 1rem;
    }
    
    /* Style pour le texte accessible uniquement aux lecteurs d'écran */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    
    /* Styles pour rendre le QR code clairement cliquable */
    .qr-code-link {
        display: inline-block;
        position: relative;
        border-radius: 8px;
        padding: 6px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .qr-code-link:hover {
        transform: scale(1.03);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    .qr-code-link:after {
        content: '🖱️ Cliquez pour voir votre super lot 🎁';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translate(-50%, 100%);
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.85rem;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        white-space: nowrap;
        z-index: 5;
    }
    
    .qr-code-link:hover:after {
        opacity: 1;
    }
    
    /* Styles pour l'image du prix */
    .prize-image-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 1rem auto;
        max-width: 300px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .prize-image {
        max-height: 250px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }
    
    .prize-image:hover {
        transform: scale(1.05);
    }
    
    .no-image-placeholder {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 200px;
        height: 200px;
        background-color: #f8f9fa;
        border-radius: 8px;
        color: #6c757d;
        margin: 0 auto;
    }

    /* Transition pour la modal */
    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out;
        transform: scale(0.8);
    }
    
    .modal.show .modal-dialog {
        transform: scale(1);
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    // Récupérer les paramètres d'URL pour la navigation
    var urlParams = new URLSearchParams(window.location.search);
    var entryId = '{{ $entry->id }}'; // Utiliser l'ID d'entrée directement du serveur
    var localStorageKey = 'contest_played_{{ $entry->contest_id }}';
    
    // Vérifier si nous sommes en mode test (avec le flag dans sessionStorage)
    // et ne sauvegarder dans localStorage que si ce n'est pas le cas
    if (!sessionStorage.getItem('prevent_localstorage_recreation')) {
        localStorage.setItem(localStorageKey, 'played');
        console.log('Participation enregistrée dans localStorage:', localStorageKey);
    } else {
        console.log('Mode test détecté, localStorage non créé');
    }
    
    // Initialisation manuelle du modal pour éviter les conflits
    const viewPrizeBtn = document.getElementById('viewPrizeBtn');
    const qrCodeLink = document.querySelector('.qr-code-link');
    
    // Fonction pour afficher la modal
    const showPrizeModal = function() {
        // Utiliser jQuery qui est plus stable pour manipuler les modals Bootstrap
        $('#prizeModal').modal('show');
    };
    
    // Ajouter l'écouteur d'événement au bouton
    if (viewPrizeBtn) {
        viewPrizeBtn.addEventListener('click', showPrizeModal);
    }
    
    // Ajouter l'écouteur d'événement au QR code
    if (qrCodeLink) {
        qrCodeLink.addEventListener('click', showPrizeModal);
        // Ajouter un style de curseur pour indiquer que c'est cliquable
        qrCodeLink.style.cursor = 'pointer';
    }

    // Only launch confetti if we came from a win
    if(window.location.href.includes('spin.result')) {
        setTimeout(launchConfetti, 300);
    }
    
    function launchConfetti() {
        const count = 300; // Plus de confettis
        const defaults = {
            origin: { y: 0.7 },
            spread: 360,
            ticks: 120, // Dure plus longtemps
            gravity: 0,
            decay: 0.92, // Chute plus lente
            startVelocity: 35, // Vitesse initiale plus élevée
            colors: ['#D03A2C', '#F7DB15', '#28a745', '#0079B2', '#ff9500']
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
