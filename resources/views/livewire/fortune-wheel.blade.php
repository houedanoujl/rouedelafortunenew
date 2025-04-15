<div>
    @if($showWheel)
    <div class="fortune-wheel-container">
        <div class="wheel-container">
            <!-- Les bibliothèques dans le bon ordre -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.3/TweenMax.min.js"></script>
            <script src="{{ asset('js/Winwheel.min.js') }}"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/canvas-confetti/1.6.0/confetti.browser.min.js"></script>
            
            <!-- Pointeur amélioré avec une meilleure position -->
            <div class="wheel-pointer">
                <svg width="30" height="40" viewBox="0 0 30 40">
                    <polygon points="15,40 0,10 30,10" fill="#D03A2C" stroke="none" />
                    <circle cx="15" cy="20" r="6" fill="white" stroke="none" />
                </svg>
            </div>
            
            <div class="wheel-outer">
                <canvas id="canvas" width="500" height="500">
                    Canvas not supported, please use another browser.
                </canvas>
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
        let theWheel;
        let isInitialized = false;
        
        // Créer la roue avec ses segments
        function createWheel() {
            if (isInitialized) return;
            
            // Segments de la roue (5 gagnants, 5 perdants)
            const segments = [];
            for (let i = 0; i < 10; i++) {
                const isWinning = i % 2 === 0;
                segments.push({
                    'id': 'secteur-' + i,
                    'text': isWinning ? 'GAGNÉ' : 'PERDU',
                    'fillStyle': isWinning ? '#F7DB15' : '#D03A2C',
                    'textFillStyle': '#000000', // Texte en noir
                    'textFontSize': 16,
                    'textFontFamily': 'Arial',
                    'textFontWeight': 'bold',
                    'strokeStyle': '#FFFFFF',
                    'textOrientation': 'horizontal', // Texte horizontal
                    'textDirection': 'normal', // Direction normale
                    'textMargin': 30, // Augmenter la marge pour meilleur positionnement
                    'textAlignment': 'center', // Alignement au centre
                    'rotateAngle': i % 2 === 0 ? 90 : 270, // Rotation à 90 degrés
                    'class': isWinning ? 'secteur-gagne' : 'secteur-perdu'
                });
            }
            
            // Configuration de la roue
            theWheel = new Winwheel({
                'canvasId': 'canvas',
                'numSegments': 10,
                'segments': segments,
                'outerRadius': 212,
                'centerX': 250,
                'centerY': 250,
                'textAlignment': 'center',
                'lineWidth': 1,
                'drawText': true,
                'centerImage': '/assets/images/rlogo.svg', // Logo au centre
                'imageOverlay': true,
                'animation': {
                    'type': 'spinToStop',
                    'duration': 10,
                    'spins': 8,
                    'callbackFinished': 'spinComplete()'
                }
            });
            
            // Charger l'image du centre
            let loadedImg = new Image();
            loadedImg.src = '/assets/images/rlogo.svg';
            loadedImg.onload = function() {
                theWheel.wheelImage = loadedImg;
                theWheel.draw();
            };
            
            isInitialized = true;
            console.log('Roue initialisée avec succès');
        }
        
        // Initialiser la roue dès le chargement
        window.addEventListener('DOMContentLoaded', createWheel);
        
        // Callback de fin de rotation
        window.spinComplete = function() {
            const winningSegment = theWheel.getIndicatedSegment();
            console.log('Roue arrêtée sur:', winningSegment.text);
            
            const isWinning = winningSegment.class === 'secteur-gagne';
            if (isWinning) {
                document.getElementById('winSound').play();
                launchConfetti();
            } else {
                document.getElementById('loseSound').play();
            }
            
            // Redirection après la fin de l'animation
            setTimeout(() => {
                window.location.href = "{{ route('spin.result', ['entry' => $entry->id]) }}";
            }, 3000);
        };
        
        // Écouter les événements Livewire
        document.addEventListener('livewire:initialized', () => {
            // Réagir à l'événement de rotation de Livewire
            @this.on('startSpinWithSound', (data) => {
                if (!isInitialized) {
                    createWheel();
                }
                
                document.getElementById('spinSound').play();
                
                // Calculer l'angle et démarrer l'animation
                const finalAngle = data.angle;
                const stopPosition = finalAngle;
                
                console.log('Démarrage de la rotation - Angle:', finalAngle);
                
                // Mettre à jour l'animation et démarrer
                theWheel.animation.stopAngle = stopPosition;
                theWheel.startAnimation();
            });
        });
        
        // Fonction pour les confettis
        function launchConfetti() {
            var count = 200;
            var defaults = {
                origin: { y: 0.7 }
            };

            function fire(particleRatio, opts) {
                confetti(Object.assign({}, defaults, opts, {
                    particleCount: Math.floor(count * particleRatio)
                }));
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
                scalar: 0.8,
            });
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

        .wheel-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
            padding: 1.5rem;
        }

        .wheel-outer {
            position: relative;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 50%;
            border: 1px solid rgba(224, 224, 224, 0.3);
        }

        #canvas {
            width: 100%;
            height: auto;
            display: block;
            max-width: 500px;
            margin: 0 auto;
        }

        .wheel-pointer {
            position: absolute;
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }

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

        .spin-button:hover {
            background-color: #e6cc00;
            opacity: 0.9;
        }

        .spin-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>
</div>
