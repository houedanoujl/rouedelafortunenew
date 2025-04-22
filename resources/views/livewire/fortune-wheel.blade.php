<div>
    @php
        $participantName = $entry->participant ? $entry->participant->first_name . ' ' . $entry->participant->last_name : 'Participant inconnu';
    @endphp

    <div class="container-fluid my-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 text-center">
                <!--<div class="mt-3">
                    <h2 class="fw-bold">Bienvenue {{ $participantName }}</h2>
                    <p class="lead">Tournez la roue pour tenter de gagner un prix!</p>
                </div>-->
                
                <div class="d-flex justify-content-center">
                    <div class="position-relative wheel-container">

                        <div class="wheel-and-pointer position-relative">
                            <!-- Indicateur de la roue -->
                            <div id="pointer" class="position-absolute">
                                <svg width="40" height="40" viewBox="0 0 60 60">
                                    <polygon points="30,0 5,55 55,55" fill="#333" />
                                </svg>
                            </div>
                            
                            <!-- La roue -->
                            <canvas id="wheel" width="320" height="320" class="responsive-wheel"></canvas>
                        </div>
                        
                        <!-- Bouton pour tourner -->
                        <button id="spinBtn" class="btn btn-danger btn-lg mt-4 d-block mx-auto spin-button" style="z-index: 20;" @if($spinning) disabled @endif>
                            @if($spinning)
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                En cours...
                            @else
                                Tourner la roue
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sons -->
    <audio id="wheelSound" src="{{ asset('sounds/wheel-spinning.mp3') }}" preload="auto"></audio>
    <audio id="winSound" src="{{ asset('sounds/win.mp3') }}" preload="auto"></audio>
    <audio id="loseSound" src="{{ asset('sounds/lose.mp3') }}" preload="auto"></audio>
    <audio id="tickSound" src="{{ asset('sounds/tick.mp3') }}" preload="auto"></audio>

    <!-- Scripts pour la roue -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="{{ asset('js/Winwheel.min.js') }}"></script>
    
    <script>
        // Vérifier si l'utilisateur est l'utilisateur spécial
        const isSpecialUser = {{ $entry->participant && $entry->participant->email === 'noob@saibot.com' ? 'true' : 'false' }};
        
        // Sons
        let spinningSound = document.getElementById('wheelSound');
        let winSound = document.getElementById('winSound');
        let loseSound = document.getElementById('loseSound');
        let tickSound = document.getElementById('tickSound');
        
        // Variables
        let isSpinning = false;
        let theWheel;
        
        // Initialisation de la roue
        initWheel();
        
        function initWheel() {
            // Création de la roue avec Winwheel.js
            theWheel = new Winwheel({
                'canvasId': 'wheel',
                'numSegments': 10,
                'outerRadius': 140,
                'innerRadius': 30,
                'textFontSize': 12, 
                'textFontWeight': 'bold',
                'textOrientation': 'horizontal',
                'textAlignment': 'center',
                'lineWidth': 1,
                'segments': [
                    { 'fillStyle': '#F7DB15', 'text': 'GAGNÉ' },  // 0
                    { 'fillStyle': '#D03A2C', 'text': 'PERDU' },  // 1
                    { 'fillStyle': '#F7DB15', 'text': 'GAGNÉ' },  // 2
                    { 'fillStyle': '#D03A2C', 'text': 'PERDU' },  // 3
                    { 'fillStyle': '#F7DB15', 'text': 'GAGNÉ' },  // 4
                    { 'fillStyle': '#D03A2C', 'text': 'PERDU' },  // 5
                    { 'fillStyle': '#F7DB15', 'text': 'GAGNÉ' },  // 6
                    { 'fillStyle': '#D03A2C', 'text': 'PERDU' },  // 7
                    { 'fillStyle': '#F7DB15', 'text': 'GAGNÉ' },  // 8
                    { 'fillStyle': '#D03A2C', 'text': 'PERDU' }   // 9
                ],
                'pins': {
                    'number': 20,
                    'fillStyle': 'silver',
                    'outerRadius': 5
                },
                // Activation du pointerGuide pour le développement
                'pointerGuide': {
                    'display': true,
                    'strokeStyle': 'rgba(255, 255, 255, 0.8)',
                    'lineWidth': 3,
                    'shadowBlur': 10,
                    'shadowColor': 'rgba(255, 255, 255, 0.6)',
                    'shadowOffsetX': 2,
                    'shadowOffsetY': 2
                },
                // Position du pointeur à midi - exactement 0 degrés
                'pointerAngle': 0
            });
            
            // Positionner l'indicateur pour qu'il s'arrête exactement au milieu des segments
            theWheel.pins.centerAngle = 0;
            theWheel.pins.startAngle = 0;
            theWheel.draw();
        }
        
        // Fonction de son de tick jouée à chaque fois que la roue passe sur une goupille
        function playTickSound() {
            // Jouer le son de tick quand la roue passe sur une goupille
            if (tickSound) {
                // Réinitialiser le son pour pouvoir le rejouer rapidement
                tickSound.currentTime = 0;
                tickSound.play();
            }
        }
        
        // Callback lorsque la roue s'arrête
        function finishedSpinning() {
            isSpinning = false;
            
            // Obtenez le segment final indiqué par le pointeur
            const winningSegment = theWheel.getIndicatedSegment();
            
            // Afficher les résultats
            console.log("Segment final:", winningSegment);
            console.log("Texte du segment:", winningSegment.text);
            
            // Déterminer si c'est un segment gagnant basé sur le texte réel du segment
            const isWinningSegment = winningSegment.text === 'GAGNÉ';
            
            // Mettre en avant le segment obtenu
            if (winningSegment) {
                // Sauvegarder l'ancien style
                const oldFillStyle = winningSegment.fillStyle;
                // Appliquer un effet visuel temporaire
                winningSegment.fillStyle = '#fffbe6'; // Couleur plus claire ou personnalisée
                theWheel.draw();
                setTimeout(() => {
                    winningSegment.fillStyle = oldFillStyle;
                    theWheel.draw();
                }, 1600);
            }
            
            setTimeout(() => {
                // Jouer le son correspondant
                if (isWinningSegment) {
                    console.log("Jouer le son de victoire");
                    if (winSound) {
                        winSound.currentTime = 0;
                        winSound.play();
                    }
                    // Afficher les confettis
                    launchConfetti();
                } else {
                    console.log("Jouer le son de défaite");
                    if (loseSound) {
                        loseSound.currentTime = 0;
                        loseSound.play();
                    }
                }
            }, 1000);
            
            // Envoyer le résultat réel au serveur via une requête AJAX sécurisée
            console.log('Envoi du résultat au serveur via AJAX...');
            console.log('Segment obtenu:', winningSegment.text);
            console.log('Résultat à enregistrer:', isWinningSegment ? 'win' : 'lose');
            
            fetch('{{ route('spin.record-result') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    entry_id: {{ $entry->id }},
                    displayed_result: isWinningSegment ? 'win' : 'lose',
                    segment_text: winningSegment.text
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Résultat enregistré avec succès:', data);
                console.log('ID de session PHP:', data.session_id);
                console.log('La session est stockée côté serveur et sécurisée par un cookie de session');
                
                // Redirection vers la page de résultat après un court délai
                setTimeout(() => {
                    console.log('Redirection vers la page de résultat...');
                    window.location.href = "{{ route('spin.result', ['entry' => $entry->id]) }}";
                }, 2000);
            })
            .catch(error => {
                console.error('Erreur lors de l\'enregistrement du résultat:', error);
                
                // En cas d'erreur, rediriger quand même
                setTimeout(() => {
                    window.location.href = "{{ route('spin.result', ['entry' => $entry->id]) }}";
                }, 2000);
            });
        }
        
        // Faire tourner la roue avec un résultat aléatoire mais respectant le résultat gagné/perdu
        function spinWheel(data) {
            if (isSpinning) return;
            
            isSpinning = true;
            
            // Jouer le son de rotation
            if (spinningSound) {
                spinningSound.currentTime = 0;
                spinningSound.play();
            }
            
            // Extraire les informations envoyées par le backend
            console.log('Données reçues du serveur:', data);
            const isWinning = data.isWinning === 1;
            
            // Générer un angle d'arrêt aléatoire qui respecte le résultat gagné/perdu
            let stopAngle;
            
            if (isWinning) {
                // Choisir aléatoirement un secteur gagnant (secteurs pairs: 0, 2, 4, 6, 8)
                const winningSectors = [0, 2, 4, 6, 8];
                const randomWinningSector = winningSectors[Math.floor(Math.random() * winningSectors.length)];
                
                // Calculer un angle aléatoire dans ce secteur (36 degrés par secteur)
                const sectorStart = randomWinningSector * 36;
                stopAngle = sectorStart + Math.random() * 35; // Angle aléatoire dans le secteur
                
                console.log('Secteur gagnant sélectionné:', randomWinningSector, 'Angle d\'arrêt:', stopAngle);
            } else {
                // Choisir aléatoirement un secteur perdant (secteurs impairs: 1, 3, 5, 7, 9)
                const losingSectors = [1, 3, 5, 7, 9];
                const randomLosingSector = losingSectors[Math.floor(Math.random() * losingSectors.length)];
                
                // Calculer un angle aléatoire dans ce secteur
                const sectorStart = randomLosingSector * 36;
                stopAngle = sectorStart + Math.random() * 35; // Angle aléatoire dans le secteur
                
                console.log('Secteur perdant sélectionné:', randomLosingSector, 'Angle d\'arrêt:', stopAngle);
            }
            
            // Réinitialiser la roue avant une nouvelle rotation
            theWheel.rotationAngle = 0;
            
            // Configuration de l'animation avec l'angle d'arrêt aléatoire
            theWheel.animation = {
                'type': 'spinToStop',
                'duration': 12 + Math.random() * 4, // Durée plus longue entre 12 et 16 secondes (avant : 8 sec)
                'spins': 8 + Math.random() * 6, // Nombre de tours plus important entre 8 et 14 tours (avant : 4-6)
                'stopAngle': stopAngle,
                'callbackFinished': finishedSpinning,
                'callbackSound': playTickSound,
                'soundTrigger': 'pin'
            };
            
            // Désactiver le bouton
            const spinBtn = document.getElementById('spinBtn');
            if (spinBtn) {
                spinBtn.disabled = true;
                spinBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> En cours...';
            }
            
            // Démarrer l'animation
            theWheel.startAnimation();
        }
        
        // Écouter les événements Livewire
        document.addEventListener('livewire:initialized', () => {
            @this.on('startSpinWithSound', (data) => {
                console.log('Données reçues du serveur:', data);
                
                // Vérifier que les données sont valides
                if (data) {
                    spinWheel(data);
                } else {
                    console.error("Erreur: Données invalides reçues du serveur:", data);
                }
            });
            
            @this.on('victory', () => {
                setTimeout(launchConfetti, 1000);
            });
        });
        
        // Confettis pour les gagnants
        function launchConfetti() {
            if (typeof confetti !== 'function') {
                console.warn("La fonction confetti n'est pas disponible");
                return;
            }
            
            try {
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
            } catch (e) {
                console.error("Erreur lors du lancement des confettis:", e);
            }
        }
        
        // Clic sur le bouton
        const spinBtn = document.getElementById('spinBtn');
        if (spinBtn) {
            spinBtn.addEventListener('click', function() {
                if (isSpinning) return;
                
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> En cours...';
                
                try {
                    @this.spin();
                } catch (e) {
                    console.error("Erreur lors de l'appel à spin():", e);
                    this.disabled = false;
                    this.innerText = "Tourner la roue";
                }
            });
        }
    </script>

    <!-- Confetti.js -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

    <style>
        .wheel-container {
            margin: 10px auto;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-flow: column;
            max-width: 100%;
            padding: 0 10px;
        }
        
        .welcome-text {
            width: 100%;
            max-width: 320px;
            color: #333;
        }
        
        .welcome-text h4 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .welcome-text p {
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
        }
        
        .wheel-and-pointer {
            position: relative;
            margin: 0 auto;
            width: 320px;
            max-width: 100%;
        }
        
        /* Logo au centre de la roue avec pseudo-élément */
        .wheel-and-pointer::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background-image: url('https://roue.dinorapp.com/assets/images/rlogo.svg');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            z-index: 1000;
            border-radius: 50%;
            background-color: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        }
        
        .responsive-wheel {
            max-width: 100%;
            height: auto;
        }
        
        #pointer {
            width:100%;
            transform: translateX(-50%);
            z-index: 3000;
            transform: rotateX(180deg);
            display:flex;
            align-items:center;
            justify-content:center;
        }
        
        .spin-button {
            width: 100%;
            max-width: 320px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem; /* Bouton avec texte plus petit */
            padding: 0.5rem 1rem; /* Padding réduit pour le bouton */
            z-index: 2000;
            position: relative;
        }
        
        /* Suppression du halo blanc à l'indicateur */
        .pointer-halo {
            filter: none;
        }
        
        @media (max-width: 575.98px) {
            .wheel-and-pointer {
                width: 280px;
            }
            
            canvas#wheel {
                width: 280px;
                height: 280px;
            }
            
            #pointer svg {
                width: 30px;
                height: 30px;
            }
            
            .welcome-text, .spin-button {
                max-width: 280px;
            }
        }
        
        /* Media query for very small devices */
        @media (max-width: 350px) {
            .wheel-and-pointer {
                width: 250px;
            }
            
            canvas#wheel {
                width: 250px;
                height: 250px;
            }
        }
    </style>
</div>
