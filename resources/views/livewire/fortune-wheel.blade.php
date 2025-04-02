<div>
    @if($showWheel)
    <div class="fortune-wheel-container">
        <div class="wheel-container">
            <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            
            <!-- Pointeur amélioré avec une meilleure position -->
            <div class="wheel-pointer">
                <svg width="30" height="40" viewBox="0 0 30 40">
                    <polygon points="15,40 0,10 30,10" fill="#FF5722" stroke="#000" stroke-width="1" />
                </svg>
            </div>
            
            <div class="wheel-outer">
                <div id="wheel" class="wheel">
                    <svg viewBox="0 0 500 500" preserveAspectRatio="xMidYMid meet">
                        <defs>
                            <linearGradient id="winGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#FFD700;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#FFA500;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="loseGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#f44336;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#e53935;stop-opacity:1" />
                            </linearGradient>
                        </defs>

                        <g id="wheel-inner">
                            @php
                                $sectors = 20;
                                $sectorAngle = 360 / $sectors;
                                $centerX = 250;
                                $centerY = 250;
                                $radius = 200;
                                $textRadius = 160;
                                $labelRadius = 145;
                            @endphp
                            
                            @for ($i = 0; $i < $sectors; $i++)
                                @php
                                    $startAngle = $i * $sectorAngle;
                                    $endAngle = ($i + 1) * $sectorAngle;
                                    $isWinning = $i % 2 === 0;
                                    
                                    // Convertir les angles en radians pour les calculs
                                    $startRad = deg2rad($startAngle);
                                    $endRad = deg2rad($endAngle);
                                    $midRad = deg2rad($startAngle + $sectorAngle / 2);
                                    
                                    // Calculer les points pour le tracé du secteur
                                    $x1 = $centerX + cos($startRad) * $radius;
                                    $y1 = $centerY + sin($startRad) * $radius;
                                    $x2 = $centerX + cos($endRad) * $radius;
                                    $y2 = $centerY + sin($endRad) * $radius;
                                    
                                    // Position du texte et des labels
                                    $textX = $centerX + cos($midRad) * $textRadius;
                                    $textY = $centerY + sin($midRad) * $textRadius;
                                    $labelX = $centerX + cos($midRad) * $labelRadius;
                                    $labelY = $centerY + sin($midRad) * $labelRadius;
                                @endphp
                                
                                <!-- Secteur -->
                                <path d="M {{ $centerX }} {{ $centerY }} L {{ $x1 }} {{ $y1 }} A {{ $radius }} {{ $radius }} 0 0 1 {{ $x2 }} {{ $y2 }} Z" 
                                      fill="url(#{{ $isWinning ? 'winGradient' : 'loseGradient' }})"
                                      stroke="#fff"
                                      stroke-width="1" />
                                
                                <!-- Emoji (🎁 ou ❌) -->
                                <text x="{{ $textX }}" y="{{ $textY }}"
                                      text-anchor="middle"
                                      dominant-baseline="middle"
                                      fill="#ffffff"
                                      font-size="24px"
                                      transform="rotate({{ $startAngle + $sectorAngle / 2 }}, {{ $textX }}, {{ $textY }})">
                                      {{ $isWinning ? '🎁' : '❌' }}
                                </text>
                                
                                <!-- Texte (GAGNÉ ou PERDU) -->
                                <text x="{{ $labelX }}" y="{{ $labelY }}"
                                      text-anchor="middle"
                                      dominant-baseline="middle"
                                      fill="#ffffff"
                                      font-size="14px"
                                      font-weight="bold"
                                      transform="rotate({{ $startAngle + $sectorAngle / 2 }}, {{ $labelX }}, {{ $labelY }})">
                                      {{ $isWinning ? 'GAGNÉ' : 'PERDU' }}
                                </text>
                            @endfor
                            
                            <!-- Centre de la roue -->
                            <circle cx="{{ $centerX }}" cy="{{ $centerY }}" r="50" fill="#333333"/>
                        </g>
                    </svg>
                </div>
            </div>
            
            <button class="spin-button" wire:click="spin" {{ $spinning ? 'disabled' : '' }}>
                {{ $spinning ? 'La roue tourne...' : 'Tourner la roue' }}
            </button>
        </div>
    </div>
    @endif

    <!-- Sons -->
    <audio id="spinSound" preload="auto">
        <source src="{{ asset('sounds/wheel-spin.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="winSound" preload="auto">
        <source src="{{ asset('sounds/win.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="loseSound" preload="auto">
        <source src="{{ asset('sounds/lose.mp3') }}" type="audio/mpeg">
    </audio>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const wheel = document.getElementById('wheel-inner');
            const spinSound = document.getElementById('spinSound');
            const winSound = document.getElementById('winSound');
            const loseSound = document.getElementById('loseSound');
            
            console.log('Wheel component initialized');
            
            @this.on('startSpinWithSound', (data) => {
                // S'assurer que l'angle est bien un nombre
                const finalAngle = parseInt(data.angle, 10) || 0;
                console.log('Spin triggered with angle:', finalAngle);
                
                // Jouer le son de rotation
                spinSound.currentTime = 0;
                spinSound.play();
                
                // Calculer la rotation totale (15 tours complets + angle final)
                const totalRotation = 5400 + finalAngle; // 15 tours = 5400 degrés
                console.log('Total rotation:', totalRotation);
                
                // Réinitialiser la rotation de la roue
                $(wheel).css({
                    'transition': 'none',
                    'transform': 'rotate(0deg)'
                });
                
                // Forcer un reflow
                wheel.offsetWidth;
                
                // Animer la roue avec jQuery
                setTimeout(() => {
                    $(wheel).css({
                        'transition': 'transform 13s cubic-bezier(0.17, 0.67, 0.12, 0.99)', // 13 secondes
                        'transform': 'rotate(' + totalRotation + 'deg)'
                    });
                }, 10);
                
                // Attendre la fin de l'animation
                setTimeout(() => {
                    // Arrêter le son de rotation
                    spinSound.pause();
                    spinSound.currentTime = 0;
                    
                    // Jouer le son approprié (victoire ou défaite)
                    const isWinning = finalAngle % 36 === 0;
                    console.log('Is winning:', isWinning);
                    
                    if (isWinning) {
                        winSound.play();
                    } else {
                        loseSound.play();
                    }
                }, 13200); // 13 secondes + 200ms de marge
            });
            
            @this.on('victory', () => {
                setTimeout(launchConfetti, 13200); // 13 secondes + 200ms de marge
            });
        });
        
        function launchConfetti() {
            const count = 200;
            const defaults = {
                origin: { y: 0.7 },
                spread: 360,
                ticks: 100,
                gravity: 0,
                decay: 0.94,
                startVelocity: 30,
                colors: ['#FFD700', '#FFA500', '#FF4081', '#E91E63'],
                shapes: ['circle', 'square'],
                scalar: 1.5,
            };

            function fire(particleRatio, opts) {
                confetti({
                    ...defaults,
                    ...opts,
                    particleCount: Math.floor(count * particleRatio),
                });
            }

            fire(0.25, { spread: 26, startVelocity: 55 });
            fire(0.2, { spread: 60 });
            fire(0.35, { spread: 100, decay: 0.91, scalar: 0.8 });
            fire(0.1, { spread: 120, startVelocity: 25, decay: 0.92, scalar: 1.2 });
            fire(0.1, { spread: 120, startVelocity: 45 });
        }
    </script>

    <style>
        .fortune-wheel-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .wheel-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
        }

        .wheel-outer {
            position: relative;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;
        }

        .wheel {
            width: 100%;
            height: auto;
            display: block;
        }

        .wheel svg {
            width: 100%;
            height: auto;
            display: block;
        }

        #wheel-inner {
            transform-origin: center center;
        }

        .wheel-pointer {
            position: absolute;
  top: 18px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
  filter: drop-shadow(0 0 3px rgba(0, 0, 0, 0.5));
        }

        .spin-button {
            margin-top: 20px;
            padding: 15px 30px;
            font-size: 18px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .spin-button:hover {
            background-color: #45a049;
        }

        .spin-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
</div>
