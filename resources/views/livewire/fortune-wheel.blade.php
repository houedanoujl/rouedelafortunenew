<div>
    @php
        $participantName = $entry->participant ? $entry->participant->first_name . ' ' . $entry->participant->last_name : 'Participant inconnu';
    @endphp

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-10 text-center">
                <div class="mt-3">
                    <h2 class="fw-bold">Bienvenue {{ $participantName }}</h2>
                    <p class="lead">Tournez la roue pour tenter de gagner un prix!</p>
                </div>
                
                <div class="d-flex justify-content-center">
                    <div class="position-relative wheel-container">
                        <!-- Conteneur du pointeur et de la roue, alignés verticalement -->
                        <div class="wheel-and-pointer">
                            <!-- Triangle indicateur - parfaitement aligné avec le centre exact de la roue -->
                            <div id="pointer" class="position-absolute">
                                <svg width="60" height="60" viewBox="0 0 60 60">
                                    <polygon points="30,0 5,55 55,55" fill="#333" />
                                </svg>
                            </div>
                            
                            <!-- La roue -->
                            <canvas id="wheel" width="400" height="400"></canvas>
                        </div>
                        
                        <!-- Bouton pour tourner -->
                        <button id="spinBtn" class="btn btn-primary btn-lg mt-3 d-block mx-auto" style="z-index: 20;" @if($spinning) disabled @endif>
                            @if($spinning)
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                En cours...
                            @else
                                Tourner la roue!
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sons -->
    <audio id="wheelSound" src="{{ asset('sound/wheel-spinning.mp3') }}" preload="auto"></audio>
    <audio id="winSound" src="{{ asset('sound/win-sound.mp3') }}" preload="auto"></audio>
    <audio id="loseSound" src="{{ asset('sound/lose-sound.mp3') }}" preload="auto"></audio>

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
                'outerRadius': 180,
                'innerRadius': 50,
                'textFontSize': 16,
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
                    'strokeStyle': 'rgba(255, 0, 0, 0.8)',
                    'lineWidth': 3
                },
                // Position du pointeur à midi - exactement 0 degrés
                'pointerAngle': 0
            });
            
            // Ajouter un logo au centre
            loadCenterImage();
            
            // Traçons la roue immédiatement pour l'afficher correctement
            theWheel.draw();
        }
        
        // Charger le logo au centre
        function loadCenterImage() {
            let wheelImage = new Image();
            wheelImage.onload = function() {
                theWheel.wheelImage = wheelImage;
                theWheel.draw();
            };
            wheelImage.src = '/assets/images/rlogo.svg';
            wheelImage.onerror = function(e) {
                console.warn("Impossible de charger le logo:", e);
            };
        }
        
        // Son du tic-tac pendant la rotation
        function playTickSound() {
            // Le son de rotation continue est géré séparément
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
            
            // Afficher une alerte avec le résultat
            setTimeout(() => {
                // Affiché en fonction du texte du segment, pas de la couleur
                alert("Vous avez " + (isWinningSegment ? "GAGNÉ!" : "PERDU!"));
                
                // Jouer le son correspondant
                if (isWinningSegment) {
                    console.log("Jouer le son de victoire");
                    if (winSound) {
                        winSound.currentTime = 0;
                        winSound.play();
                    }
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
                'duration': 8,
                'spins': 4 + Math.random() * 2, // Nombre de tours également aléatoire
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
                    this.innerText = "Tourner la roue!";
                }
            });
        }
    </script>

    <!-- Confetti.js -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

    <style>
        .wheel-container {
            margin: 20px auto;
            position: relative;
            width: 400px;
        }
        
        .wheel-and-pointer {
            position: relative;
            width: 400px;
            height: 460px;
        }
        
        #wheel {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            display: block;
            position: absolute;
            top: 60px;
        }
        
        #pointer {
            filter: drop-shadow(0 3px 5px rgba(0, 0, 0, 0.5));
            z-index: 100;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        #spinBtn {
            transition: all 0.3s ease;
            background-color: #0079B2;
            border: none;
            padding: 12px 30px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
        }
        
        #spinBtn:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            background-color: #0091D5;
        }
        
        #spinBtn:disabled {
            background-color: #666;
            cursor: not-allowed;
        }
    </style>
</div>
