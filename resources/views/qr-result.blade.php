@extends('layouts.app')

@section('content')
<div class="result-container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Résultat du Tirage</h2>
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
                    
                    <!-- Boutons du lien QR code déplacés ici pour être toujours visibles -->
                    <div class="mt-4 link-button-container">
                        <a href="{{ url('/qr/'.$code) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-link mr-2"></i> Ouvrir le lien du QR code
                        </a>
                        <button type="button" class="btn btn-outline-secondary ml-2" onclick="copyQrLink()">
                            <i class="fas fa-copy mr-2"></i> Copier le lien
                        </button>
                    </div>
                    
                    <!-- Boutons d'impression et de capture d'écran -->
                    <div class="mt-3 print-buttons">
                        <button type="button" class="btn btn-success" onclick="printResult()">
                            <i class="fas fa-print mr-2"></i> Imprimer en PDF
                        </button>
                        <button type="button" class="btn btn-info ml-2" onclick="captureResult()">
                            <i class="fas fa-camera mr-2"></i> Capturer en image
                        </button>
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

function copyQrLink() {
    const link = "{{ url('/qr/'.$code) }}";
    navigator.clipboard.writeText(link)
        .then(() => {
            // Afficher une notification de succès
            const button = document.querySelector('.link-button-container .btn-outline-secondary');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check mr-2"></i> Lien copié!';
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        })
        .catch(err => {
            console.error('Erreur lors de la copie du lien:', err);
        });
}

// Fonction pour imprimer la page en PDF
function printResult() {
    // Ajouter une classe temporaire pour le style d'impression
    document.body.classList.add('printing');
    
    // Utiliser l'API d'impression du navigateur
    window.print();
    
    // Retirer la classe après l'impression
    setTimeout(function() {
        document.body.classList.remove('printing');
    }, 1000);
}

// Fonction pour capturer la page en image
function captureResult() {
    // Charger html2canvas si ce n'est pas déjà fait
    if (typeof html2canvas !== 'function') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
        script.onload = function() {
            performCapture();
        };
        document.head.appendChild(script);
    } else {
        performCapture();
    }
}

// Fonction pour effectuer la capture d'écran
function performCapture() {
    // Montrer un message de chargement
    const captureBtn = document.querySelector('.print-buttons .btn-info');
    const originalText = captureBtn.innerHTML;
    captureBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Capture en cours...';
    captureBtn.disabled = true;
    
    // Capturer la carte de résultat
    const card = document.querySelector('.card');
    
    html2canvas(card, {
        allowTaint: true,
        useCORS: true,
        scale: 2, // Meilleure qualité
        backgroundColor: null
    }).then(canvas => {
        // Convertir en JPG
        const imgData = canvas.toDataURL('image/jpeg', 0.9);
        
        // Créer un lien de téléchargement
        const link = document.createElement('a');
        link.href = imgData;
        link.download = 'resultat-tirage-' + "{{ $code }}" + '.jpg';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Restaurer le bouton
        captureBtn.innerHTML = originalText;
        captureBtn.disabled = false;
    }).catch(error => {
        console.error('Erreur lors de la capture:', error);
        captureBtn.innerHTML = originalText;
        captureBtn.disabled = false;
        alert('Erreur lors de la capture de l\'image. Veuillez réessayer.');
    });
}
</script>

<style>
.result-container {
    padding: 30px 0;
}

/* Styles pour l'impression */
@media print {
    body * {
        visibility: hidden;
    }
    .card, .card * {
        visibility: visible;
    }
    .print-buttons, .link-button-container {
        display: none !important;
    }
    .card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
    }
}

/* Style pour le mode impression */
body.printing .navbar,
body.printing footer,
body.printing .print-buttons,
body.printing .link-button-container {
    display: none !important;
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

.link-button-container {
    margin-bottom: 20px;
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

.qr-code-container.mt-4 {
    margin:0 auto !important;
    display:flex !important;
    justify-content:center !important;
    align-items:center !important;
}
.qr-code-container.mt-4 img {
    width: 200px;
    height: 200px;
}
button.btn.btn-primary.mt-3{
    background:#dc3545 !important;
}
</style>
@endsection
