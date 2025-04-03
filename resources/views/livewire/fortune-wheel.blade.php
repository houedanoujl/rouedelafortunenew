<div>
    @if($showWheel)
    <div class="fortune-wheel-container">
        <div class="wheel-container">
            <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            
            <!-- Pointeur amélioré avec une meilleure position -->
            <div class="wheel-pointer">
                <svg width="30" height="40" viewBox="0 0 30 40">
                    <filter id="shadow" x="-50%" y="-50%" width="200%" height="200%">
                        <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#000000" flood-opacity="0.5" />
                    </filter>
                    <polygon points="15,40 0,10 30,10" fill="#e3201c" stroke="#4c1711" stroke-width="1" filter="url(#shadow)" />
                    <circle cx="15" cy="20" r="6" fill="white" stroke="#4c1711" stroke-width="1" />
                </svg>
            </div>
            
            <div class="wheel-outer">
                <div id="wheel" class="wheel">
                    <svg viewBox="0 0 500 500" preserveAspectRatio="xMidYMid meet">
                        <defs>
                            <!-- Gradients utilisant la palette demandée -->
                            <linearGradient id="winGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#e3201c;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#8c4948;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="loseGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#965d0b;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#544719;stop-opacity:1" />
                            </linearGradient>
                            <!-- Motif matelassé amélioré -->
                            <pattern id="quilted" patternUnits="userSpaceOnUse" width="40" height="40" patternTransform="rotate(45)">
                                <rect width="40" height="40" fill="none" stroke="#ffffff" stroke-width="2" stroke-opacity="0.25"/>
                                <circle cx="20" cy="20" r="4" fill="#ffffff" fill-opacity="0.2"/>
                                <circle cx="5" cy="5" r="2" fill="#ffffff" fill-opacity="0.1"/>
                                <circle cx="35" cy="35" r="2" fill="#ffffff" fill-opacity="0.1"/>
                            </pattern>
                            <!-- Ombres prononcées -->
                            <filter id="wheelShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="#000000" flood-opacity="0.7" />
                            </filter>
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
                                
                                <!-- Secteur avec ombres noires -->
                                <path d="M {{ $centerX }} {{ $centerY }} L {{ $x1 }} {{ $y1 }} A {{ $radius }} {{ $radius }} 0 0 1 {{ $x2 }} {{ $y2 }} Z" 
                                      fill="url(#{{ $isWinning ? 'winGradient' : 'loseGradient' }})"
                                      stroke="#000"
                                      stroke-width="1"
                                      filter="url(#wheelShadow)" />
                                
                                <!-- Motif matelassé amélioré -->
                                <path d="M {{ $centerX }} {{ $centerY }} L {{ $x1 }} {{ $y1 }} A {{ $radius }} {{ $radius }} 0 0 1 {{ $x2 }} {{ $y2 }} Z" 
                                      fill="url(#quilted)"
                                      stroke="none" />
                                
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
                            
                            <!-- Centre de la roue avec ombres noires -->
                            <filter id="centerShadow" x="-50%" y="-50%" width="200%" height="200%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#000000" flood-opacity="0.6" />
                            </filter>
                            <circle cx="{{ $centerX }}" cy="{{ $centerY }}" r="50" fill="linear-gradient(145deg, var(--red-cmyk), var(--cordovan))" filter="url(#centerShadow)"/>
                            <circle cx="{{ $centerX }}" cy="{{ $centerY }}" r="45" fill="url(#quilted)" stroke="#000000" stroke-width="1" stroke-opacity="0.5"/>
                            <text x="{{ $centerX }}" y="{{ $centerY }}" text-anchor="middle" dominant-baseline="middle" fill="#ffffff" font-size="16px" font-family="'EB Garamond', serif" font-weight="bold" text-shadow="0 2px 4px rgba(0,0,0,0.5)">FORTUNE</text>
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
                colors: ['#e3201c', '#ff3d39', '#eb8885', '#ffffff', '#ff6b67'],
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
        /* Variables correspondant à la palette demandée */
        :root {
            --red-cmyk: #e3201cff;
            --golden-brown: #965d0bff;
            --field-drab: #544719ff;
            --light-coral: #eb8885ff;
            --cordovan: #8c4948ff;
            --black-bean: #4c1711ff;
            --lavender-blush: #f6e7e4ff;
        }

        .fortune-wheel-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem 1rem;
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 0.8rem;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
            border: 2px solid var(--red-cmyk);
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
            height: 5px;
            background: linear-gradient(90deg, var(--red-cmyk), var(--golden-brown), var(--red-cmyk));
            z-index: 2;
        }
        
        .fortune-wheel-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1MCIgaGVpZ2h0PSI1MCI+PHJlY3Qgd2lkdGg9IjUwIiBoZWlnaHQ9IjUwIiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTAsMCBMMjUsMjUgTDAsMjUgTDAsMMCIgZmlsbD0icmdiYSgyMjcsIDMyLCAyOCwgMC4wMykiLz48cGF0aCBkPSJNNTAsMCBMNTAsMjUgTDI1LDI1IEw1MCwwIiBmaWxsPSJyZ2JhKDIyNywgMzIsIDI4LCAwLjAzKSIvPjxwYXRoIGQ9Ik0wLDI1IEwwLDUwIEwyNSwyNSBMMCwyNSIgZmlsbD0icmdiYSgyMjcsIDMyLCAyOCwgMC4wMykiLz48cGF0aCBkPSJNNTAsMjUgTDI1LDI1IEw1MCw1MCBMNTAsMjUiIGZpbGw9InJnYmEoMjI3LCAzMiwgMjgsIDAuMDMpIi8+PC9zdmc+');
            opacity: 1;
            pointer-events: none;
            z-index: 0;
        }

        .wheel-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
            padding: 1.5rem;
        }
        
        .wheel-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSJub25lIi8+PHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIiBmaWxsPSJyZ2JhKDIyNywgMzIsIDI4LCAwLjAzKSIvPjxyZWN0IHg9IjIwIiB5PSIyMCIgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIiBmaWxsPSJyZ2JhKDIyNywgMzIsIDI4LCAwLjAzKSIvPjwvc3ZnPg==');
            background-size: 40px 40px;
            opacity: 0.6;
            z-index: -1;
            border-radius: 0.5rem;
        }

        .wheel-outer {
            position: relative;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), inset 0 0 15px rgba(0, 0, 0, 0.3);
            border: 3px solid var(--red-cmyk);
        }

        .wheel {
            width: 100%;
            height: auto;
            display: block;
            animation: wheelBreathing 5s infinite ease-in-out;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.6));
        }
        
        @keyframes wheelBreathing {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.01); }
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
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.7));
            animation: pointerBounce 1.5s infinite ease-in-out;
        }
        
        @keyframes pointerBounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-8px); }
        }

        .spin-button {
            margin-top: 30px;
            padding: 15px 40px;
            font-size: 1.3rem;
            font-family: 'EB Garamond', serif;
            background: linear-gradient(145deg, var(--red-cmyk), var(--cordovan));
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 600;
            animation: pulse 2s infinite;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 0, 0, 0.5); }
            70% { box-shadow: 0 0 0 10px rgba(0, 0, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 0, 0, 0); }
        }

        .spin-button:hover {
            background: linear-gradient(145deg, var(--golden-brown), var(--field-drab));
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6);
            animation: none;
        }
        
        .spin-button::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -100%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(30deg);
            transition: all 0.8s ease;
        }
        
        .spin-button:hover::after {
            left: 100%;
        }

        .spin-button:disabled {
            background: var(--light-coral);
            cursor: not-allowed;
            opacity: 0.7;
            transform: none;
            box-shadow: 0 2px 5px rgba(76, 23, 17, 0.2);
        }
    </style>
</div>
