@extends('layouts.app')

@section('content')
<div class="result-page">
    <div class="result-container {{ $isWinner ? 'winner' : 'loser' }}">
        <div class="result-content">
            @if($isWinner)
                <div class="winner-content">
                    <h1 class="result-title">🎉 Félicitations ! 🎉</h1>
                    <p class="result-message">Votre participation est terminée. Scannez le QR code pour découvrir votre lot !</p>
                    
                    <div class="qr-code-section">
                        @livewire('qr-code-display', ['code' => $qrCode->code])
                        
                        <div class="qr-actions">
                            <button class="btn btn-primary" onclick="captureQR()">
                                <i class="fas fa-camera"></i> Capturer
                            </button>
                            <button class="btn btn-success" onclick="downloadQR()">
                                <i class="fas fa-download"></i> Télécharger
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="loser-content">
                    <h1 class="result-title">😢 Pas de chance...</h1>
                    <p class="result-message">Votre participation est terminée. Vous n'avez pas gagné cette fois-ci.</p>
                    <p class="encouragement">Merci d'avoir participé !</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.result-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background-color: #f5f5f5;
}

.result-container {
    max-width: 600px;
    width: 100%;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    animation: fadeIn 0.5s ease-out;
}

.winner .result-container {
    background-color: #4CAF50;
    color: white;
}

.loser .result-container {
    background-color: #f44336;
    color: white;
}

.result-title {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: bold;
}

.result-message {
    font-size: 1.5rem;
    margin-bottom: 2rem;
}

.qr-code-section {
    background-color: white;
    padding: 2rem;
    border-radius: 0.5rem;
    margin: 2rem 0;
    color: #333;
}

.qr-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 1.5rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.btn i {
    margin-right: 0.5rem;
}

.encouragement {
    font-size: 1.2rem;
    margin-top: 1rem;
    font-style: italic;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .result-container {
        padding: 1.5rem;
    }

    .result-title {
        font-size: 2rem;
    }

    .result-message {
        font-size: 1.2rem;
    }

    .qr-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
function captureQR() {
    const qrElement = document.querySelector('.qr-code-section');
    html2canvas(qrElement).then(canvas => {
        const image = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = 'qr-code.png';
        link.href = image;
        link.click();
    });
}

function downloadQR() {
    const qrImage = document.querySelector('.qr-code-section img');
    if (qrImage) {
        const link = document.createElement('a');
        link.download = 'qr-code.png';
        link.href = qrImage.src;
        link.click();
    }
}

// Jouer un son en fonction du résultat
document.addEventListener('DOMContentLoaded', function() {
    const isWinner = {{ $isWinner ? 'true' : 'false' }};
    const sound = new Audio(isWinner ? '/sounds/win.mp3' : '/sounds/lose.mp3');
    sound.play();
});
</script>
@endsection
