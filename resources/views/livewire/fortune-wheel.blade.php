<div class="fortune-wheel-container">
    <div class="wheel-container">
        <div class="wheel {{ $spinning ? 'spinning' : '' }}" id="wheel">
            @if (count($prizes) > 0)
                @foreach ($prizes as $index => $prize)
                    <div class="wheel-section" style="--index: {{ $index }}; --total: {{ count($prizes) }};">
                        <span class="prize-name">{{ $prize['name'] }}</span>
                    </div>
                @endforeach
            @else
                <div class="wheel-section" style="--index: 0; --total: 1;">
                    <span class="prize-name">Pas de prix disponible</span>
                </div>
            @endif
            <div class="wheel-center"></div>
        </div>
        <div class="wheel-pointer"></div>
    </div>

    <div class="wheel-controls">
        @if ($result)
            <div class="result-container">
                <div class="result {{ $result['status'] }}">
                    <h3>{{ $result['message'] }}</h3>
                    
                    @if ($result['status'] === 'win' && $qrCodeUrl)
                        <div class="qr-code-container">
                            <p>Scannez ce code QR pour récupérer votre prix</p>
                            <img src="{{ $qrCodeUrl }}" alt="QR Code" class="qr-code">
                        </div>
                    @endif
                </div>
            </div>
            
            <button class="btn btn-primary mt-4" onclick="location.reload();">
                Retour à l'accueil
            </button>
        @else
            <button class="btn btn-primary spin-button" wire:click="spin" wire:loading.attr="disabled" {{ $spinning || count($prizes) === 0 ? 'disabled' : '' }}>
                <span wire:loading wire:target="spin">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
                {{ $spinning ? 'La roue tourne...' : 'Tourner la roue' }}
            </button>
        @endif
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        // Animation de la roue
        Livewire.on('spin', () => {
            const wheel = document.getElementById('wheel');
            const randomDegrees = 1800 + Math.floor(Math.random() * 360);
            
            wheel.style.transition = 'transform 3s cubic-bezier(0.17, 0.67, 0.83, 0.67)';
            wheel.style.transform = `rotate(${randomDegrees}deg)`;
        });
    });
</script>

<style>
    .fortune-wheel-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem;
    }
    
    .wheel-container {
        position: relative;
        width: 300px;
        height: 300px;
        margin-bottom: 2rem;
    }
    
    .wheel {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #f0f0f0;
        overflow: hidden;
        transition: transform 3s ease-out;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    }
    
    .wheel-section {
        position: absolute;
        width: 50%;
        height: 50%;
        transform-origin: bottom right;
        left: 0;
        top: 0;
        border: 1px solid #333;
        clip-path: polygon(0 0, 100% 0, 0 100%);
        transform: rotate(calc(var(--index) * (360deg / var(--total))));
    }
    
    .wheel-section:nth-child(odd) {
        background: #ff9800;
    }
    
    .wheel-section:nth-child(even) {
        background: #2196f3;
    }
    
    .prize-name {
        position: absolute;
        top: 20%;
        left: 20%;
        transform: rotate(45deg);
        font-size: 12px;
        font-weight: bold;
        color: #fff;
        text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);
    }
    
    .wheel-center {
        position: absolute;
        width: 50px;
        height: 50px;
        background: #333;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10;
    }
    
    .wheel-pointer {
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 20px solid transparent;
        border-right: 20px solid transparent;
        border-top: 40px solid #e91e63;
        z-index: 5;
    }
    
    .wheel.spinning {
        animation: spin 3s cubic-bezier(0.17, 0.67, 0.83, 0.67) forwards;
    }
    
    .result-container {
        margin-top: 2rem;
        text-align: center;
    }
    
    .result {
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .result.win {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .result.lose {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .result.error {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .qr-code-container {
        margin-top: 1.5rem;
    }
    
    .qr-code {
        max-width: 200px;
        border: 1px solid #ddd;
        padding: 0.5rem;
        background: white;
    }
    
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(var(--final-rotation, 1800deg));
        }
    }
</style>
