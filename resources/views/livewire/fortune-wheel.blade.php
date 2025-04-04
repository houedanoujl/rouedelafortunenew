<div>
    @if($showWheel)
    <div class="fortune-wheel-container">
        <div class="wheel-container">
            <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            
            <!-- Pointeur amélioré avec une meilleure position -->
            <div class="wheel-pointer">
                <svg width="30" height="40" viewBox="0 0 30 40">
                    <polygon points="15,40 0,10 30,10" fill="#D03A2C" stroke="none" />
                    <circle cx="15" cy="20" r="6" fill="white" stroke="none" />
                </svg>
            </div>
            
            <div class="wheel-outer">
                <div id="wheel" class="wheel">
                    <svg viewBox="0 0 500 500" preserveAspectRatio="xMidYMid meet">
                        <defs>
                            <!-- Couleurs plates selon la palette demandée -->
                            <linearGradient id="winGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#F7DB15;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#F7DB15;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="loseGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#D03A2C;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#D03A2C;stop-opacity:1" />
                            </linearGradient>
                            <!-- Suppression des motifs matelassés et ombres pour un design plat -->
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
                                
                                <!-- Secteur avec design plat -->
                                <path d="M {{ $centerX }} {{ $centerY }} L {{ $x1 }} {{ $y1 }} A {{ $radius }} {{ $radius }} 0 0 1 {{ $x2 }} {{ $y2 }} Z" 
                                      fill="url(#{{ $isWinning ? 'winGradient' : 'loseGradient' }})"
                                      stroke="#ffffff"
                                      stroke-width="1" />
                                
                                <!-- Emoji (🎁 ou ❌) -->
                                <text x="{{ $textX }}" y="{{ $textY }}"
                                      text-anchor="middle"
                                      dominant-baseline="middle"
                                      fill="#ffffff"
                                      font-size="24px"
                                      transform="rotate({{ $startAngle + $sectorAngle / 2 }}, {{ $textX }}, {{ $textY }})">

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
                            
                            <!-- Centre de la roue avec le logo -->
                            <circle cx="{{ $centerX }}" cy="{{ $centerY }}" r="50" fill="white"/>
                            <image href="/assets/images/rlogo.svg" x="{{ $centerX - 40 }}" y="{{ $centerY - 40 }}" height="80" width="80" preserveAspectRatio="xMidYMid meet" />
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
                colors: ['#F7DB15', '#D03A2C', '#ffffff', '#e6cc00', '#b73224'],
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
        /* Variables correspondant à la nouvelle palette flat demandée */
        :root {
            --honolulu-blue: #0079B2ff;
            --apple-green: #86B942ff;
            --school-bus-yellow: #F7DB15ff;
            --persian-red: #D03A2Cff;
            --sea-green: #049055ff;
            --light-gray: #f5f5f5;
            --dark-gray: #333333;
        }

        .fortune-wheel-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem 1rem;
            max-width: 800px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 0.25rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            font-family: 'EB Garamond', serif;
        }
        
        .fortune-wheel-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--persian-red);
            z-index: 2;
        }
        
        /* Suppression du motif de fond pour un design plat */

        .wheel-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
            padding: 1.5rem;
        }
        
        /* Suppression du motif de fond pour un design plat */

        .wheel-outer {
            position: relative;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 50%;
            border: 1px solid rgba(224, 224, 224, 0.3);
        }

        .wheel {
            width: 100%;
            height: auto;
            display: block;
        }
        
        /* Suppression de l'animation respiratoire pour un design plat */

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
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }
        
        /* Suppression de l'animation de rebond pour un design plat */

        .spin-button {
            margin-top: 30px;
            padding: 15px 40px;
            font-size: 1.3rem;
            font-family: 'EB Garamond', serif;
            background-color: var(--school-bus-yellow);
            color: var(--dark-gray);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 600;
        }
        
        /* Suppression de l'animation pulse pour un design plat */

        .spin-button:hover {
            background-color: #e6cc00;
            opacity: 0.9;
        }
        
        /* Suppression de l'effet de brillance pour un design plat */

        .spin-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>
</div>
