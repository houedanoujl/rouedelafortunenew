@extends('layouts.app')

@section('content')
<div class="result-container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Résultat de la Roue de la Fortune</h2>
                </div>
                <div class="card-body text-center">
                    <div id="loading" class="my-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Chargement...</span>
                        </div>
                        <p class="mt-3">Vérification du code...</p>
                    </div>
                    
                    <div id="result-content" class="d-none">
                        <div id="result-status" class="my-4">
                            <!-- Le statut sera affiché ici -->
                        </div>
                        
                        <div id="result-message" class="my-4">
                            <!-- Le message sera affiché ici -->
                        </div>
                        
                        <div id="prize-info" class="my-4 d-none">
                            <!-- Les informations du prix seront affichées ici pour les gagnants -->
                        </div>
                        
                        <div class="mt-5">
                            <p class="text-muted">Ce QR code a été validé et ne peut plus être utilisé.</p>
                        </div>
                    </div>
                    
                    <div id="error-message" class="alert alert-danger d-none">
                        <!-- Les messages d'erreur seront affichés ici -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qrCode = "{{ $code }}";
    const loadingElement = document.getElementById('loading');
    const resultContent = document.getElementById('result-content');
    const resultStatus = document.getElementById('result-status');
    const resultMessage = document.getElementById('result-message');
    const prizeInfo = document.getElementById('prize-info');
    const errorMessage = document.getElementById('error-message');
    
    // Fonction pour afficher une erreur
    function showError(message) {
        loadingElement.classList.add('d-none');
        errorMessage.textContent = message;
        errorMessage.classList.remove('d-none');
    }
    
    // Vérifier le QR code avec le bon chemin d'API
    fetch(`{{ route('qrcode.check', ['code' => $code]) }}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la vérification du QR code');
            }
            return response.json();
        })
        .then(data => {
            loadingElement.classList.add('d-none');
            
            if (data.success) {
                resultContent.classList.remove('d-none');
                
                // Afficher le message
                resultMessage.innerHTML = `<h3>${data.message}</h3>`;
                
                // Afficher le statut avec style approprié
                if (data.status === 'win') {
                    resultStatus.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-trophy fa-3x mb-3"></i>
                            <h2>Félicitations ${data.participant}!</h2>
                        </div>
                    `;
                    
                    // Afficher les informations du prix
                    if (data.prize) {
                        prizeInfo.classList.remove('d-none');
                        prizeInfo.innerHTML = `
                            <div class="prize-card">
                                <h4>Votre prix:</h4>
                                <p class="prize-name">${data.prize}</p>
                                <p class="instructions">Présentez cette page à l'accueil pour récupérer votre lot</p>
                            </div>
                        `;
                    }
                } else {
                    resultStatus.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-thumbs-down fa-3x mb-3"></i>
                            <h2>Dommage ${data.participant}!</h2>
                        </div>
                    `;
                }
                
                // Vibrer l'appareil si disponible (pour une meilleure expérience mobile)
                if ('vibrate' in navigator) {
                    if (data.status === 'win') {
                        // Vibration pour une victoire (modèle de vibration joyeux)
                        navigator.vibrate([100, 50, 100, 50, 200]);
                    } else {
                        // Vibration pour une défaite (une seule vibration courte)
                        navigator.vibrate(200);
                    }
                }
                
                // Émettre un son en utilisant Web Audio API au lieu de fichiers externes
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    if (data.status === 'win') {
                        // Son de victoire (notes montantes)
                        oscillator.type = 'sine';
                        oscillator.frequency.setValueAtTime(330, audioContext.currentTime);
                        oscillator.frequency.setValueAtTime(392, audioContext.currentTime + 0.2);
                        oscillator.frequency.setValueAtTime(523, audioContext.currentTime + 0.4);
                        gainNode.gain.setValueAtTime(0.5, audioContext.currentTime);
                        gainNode.gain.linearRampToValueAtTime(0, audioContext.currentTime + 0.8);
                        oscillator.start();
                        oscillator.stop(audioContext.currentTime + 0.8);
                    } else {
                        // Son de défaite (notes descendantes)
                        oscillator.type = 'triangle';
                        oscillator.frequency.setValueAtTime(300, audioContext.currentTime);
                        oscillator.frequency.setValueAtTime(250, audioContext.currentTime + 0.2);
                        oscillator.frequency.setValueAtTime(200, audioContext.currentTime + 0.4);
                        gainNode.gain.setValueAtTime(0.5, audioContext.currentTime);
                        gainNode.gain.linearRampToValueAtTime(0, audioContext.currentTime + 0.6);
                        oscillator.start();
                        oscillator.stop(audioContext.currentTime + 0.6);
                    }
                } catch (e) {
                    console.log('Web Audio API non supportée ou erreur:', e);
                }
            } else {
                showError(data.message || 'QR code invalide');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showError('Une erreur est survenue lors de la vérification du QR code.');
        });
});
</script>

<style>
.result-container {
    padding: 30px 0;
}

.prize-card {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.prize-name {
    font-size: 24px;
    font-weight: bold;
    color: #007bff;
    margin: 15px 0;
}

.instructions {
    font-style: italic;
    color: #6c757d;
}

/* Animation pour le chargement */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.spinner-border {
    animation: pulse 2s infinite;
}
</style>
@endsection
