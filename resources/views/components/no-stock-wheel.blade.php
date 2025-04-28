<div class="text-center">
    <div class="d-flex justify-content-center">
        <div class="position-relative wheel-container fortune-wheel-container">
            <div class="wheel-and-pointer position-relative">
                <!-- Indicateur de la roue -->
                <div id="pointer-nostock" class="position-absolute">
                    <svg width="40" height="40" viewBox="0 0 60 60">
                        <polygon points="30,0 5,55 55,55" fill="#333" />
                    </svg>
                </div>
                <!-- La roue -->
                <canvas id="nostock-wheel" width="320" height="320" class="responsive-wheel"></canvas>
            </div>
            
            <!-- Bouton pour tourner -->
            <button id="spinNoStockBtn" class="btn btn-danger btn-lg mt-4 d-block mx-auto spin-button" style="z-index: 20; display: none;">
                Tourner la roue
            </button>
        </div>
    </div>
    
    <!-- Message de résultat (caché par défaut) -->
    <div id="nostockResultMessage" class="alert my-3" style="display: none; max-width: 320px; margin: 0 auto;">
        <h4 id="nostockResultTitle"></h4>
        <p id="nostockResultText"></p>
    </div>

    <!-- Sons -->
    <audio id="nostockWheelSound" src="{{ asset('sounds/wheel-spinning.mp3') }}" preload="auto"></audio>
    <audio id="nostockLoseSound" src="{{ asset('sounds/sadtrombone.swf.mp3') }}" preload="auto"></audio>
    <audio id="nostockTickSound" src="{{ asset('sounds/tick.mp3') }}" preload="auto"></audio>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="{{ asset('js/Winwheel.min.js') }}"></script>
    <script>
        // Sons
        let nostockSpinningSound = document.getElementById('nostockWheelSound');
        let nostockLoseSound = document.getElementById('nostockLoseSound');
        let nostockTickSound = document.getElementById('nostockTickSound');

        // Variables
        let nostockIsSpinning = false;
        let nostockWheel;

        // Fonction pour définir un cookie
        function setNostockCookie(name, value, days) {
            let expires = "";
            if (days) {
                let date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        // Fonction pour lire un cookie
        function getNostockCookie(name) {
            let nameEQ = name + "=";
            let ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // Initialisation de la roue
        initNoStockWheel();

        function initNoStockWheel() {
            // Création de la roue avec Winwheel.js
            nostockWheel = new Winwheel({
                'canvasId': 'nostock-wheel',
                'numSegments': 10,
                'outerRadius': 140,
                'innerRadius': 30,
                'textFontSize': 12,
                'textFontWeight': 'bold',
                'segments': [
                    {'fillStyle': '#f44336', 'text': 'PERDU'},  // 1
                    {'fillStyle': '#ffe082', 'text': 'GAGNÉ'},  // 2
                    {'fillStyle': '#f44336', 'text': 'PERDU'},  // 3
                    {'fillStyle': '#ffe082', 'text': 'GAGNÉ'},  // 4
                    {'fillStyle': '#f44336', 'text': 'PERDU'},  // 5
                    {'fillStyle': '#ffe082', 'text': 'GAGNÉ'},  // 6
                    {'fillStyle': '#f44336', 'text': 'PERDU'},  // 7 (index 6) - CETTE POSITION
                    {'fillStyle': '#ffe082', 'text': 'GAGNÉ'},  // 8
                    {'fillStyle': '#f44336', 'text': 'PERDU'},  // 9
                    {'fillStyle': '#ffe082', 'text': 'GAGNÉ'}   // 10
                ],
                'pins': {
                    'number': 20,
                    'fillStyle': 'silver',
                    'outerRadius': 5
                },
                'pointerGuide': {
                    'display': true,
                    'strokeStyle': 'rgba(255, 255, 255, 0.8)',
                    'lineWidth': 3,
                    'shadowBlur': 10,
                    'shadowColor': 'rgba(255, 255, 255, 0.6)',
                    'shadowOffsetX': 2,
                    'shadowOffsetY': 2
                },
                'pointerAngle': 0,  // Important! Le pointeur est à 0 degrés (haut/12h)
                'rotationAngle': 0  // Assurer que la roue commence à la position correcte
            });
            
            // Positionner l'indicateur pour qu'il s'arrête exactement au milieu des segments
            nostockWheel.pins.centerAngle = 0;
            nostockWheel.pins.startAngle = 0;
            nostockWheel.draw();
        }

        // Fonction de son de tick jouée à chaque fois que la roue passe sur une goupille
        function playNoStockTickSound() {
            // Jouer le son de tick quand la roue passe sur une goupille
            if (nostockTickSound) {
                nostockTickSound.currentTime = 0;
                nostockTickSound.play();
            }
        }

        // Callback lorsque la roue s'arrête
        function finishedNoStockSpinning() {
            nostockIsSpinning = false;

            // Obtenez le segment final indiqué par le pointeur
            const winningSegment = nostockWheel.getIndicatedSegment();

            // Arrêter le son de la roue qui tourne
            if (nostockSpinningSound) {
                nostockSpinningSound.pause();
                nostockSpinningSound.currentTime = 0;
            }

            // Mise en évidence du segment obtenu
            if (winningSegment) {
                // Sauvegarder l'ancien style
                const oldFillStyle = winningSegment.fillStyle;
                // Appliquer un effet visuel temporaire
                winningSegment.fillStyle = '#333';
                nostockWheel.draw();
                // Restaurer après un délai
                setTimeout(function() {
                    winningSegment.fillStyle = oldFillStyle;
                    nostockWheel.draw();
                }, 300);
            }

            // Afficher le message de résultat (toujours perdant)
            const resultMessage = document.getElementById('nostockResultMessage');
            const resultTitle = document.getElementById('nostockResultTitle');
            const resultText = document.getElementById('nostockResultText');
            
            if (resultMessage && resultTitle && resultText) {
                resultMessage.className = 'alert alert-danger my-3';
                resultTitle.innerHTML = '<i class="fas fa-times-circle"></i> Dommage !';
                resultText.innerHTML = 'Vous avez perdu cette fois-ci. La roue s\'est arrêtée sur un segment PERDU.';
                resultMessage.style.display = 'block';
            }

            // Jouer le son de défaite
            setTimeout(function() {
                if (nostockLoseSound) {
                    nostockLoseSound.currentTime = 0;
                    nostockLoseSound.play();
                }
            }, 1000);

            // Désactiver complètement la roue après le premier tour
            const wheelContainer = document.querySelector('.wheel-container');
            if (wheelContainer) {
                // Réduire l'opacité de la roue
                wheelContainer.style.opacity = '0.5';
                // Désactiver les interactions avec la roue
                wheelContainer.style.pointerEvents = 'none';
            }

            // Désactiver le bouton définitivement
            const spinBtn = document.getElementById('spinNoStockBtn');
            if (spinBtn) {
                spinBtn.disabled = true;
                spinBtn.innerHTML = 'Tour terminé';
                spinBtn.style.opacity = '0.5';
                spinBtn.style.pointerEvents = 'none';
            }
        }

        // Clic sur le bouton
        document.addEventListener('DOMContentLoaded', function() {
            // Vérifier si l'utilisateur a déjà joué (cookie)
            const hasPlayed = getNostockCookie('nostock_wheel_played');
            
            if (hasPlayed) {
                console.log('Utilisateur a déjà joué à la roue no-stock');
                // Afficher directement le message de résultat sans faire tourner la roue
                const resultMessage = document.getElementById('nostockResultMessage');
                const resultTitle = document.getElementById('nostockResultTitle');
                const resultText = document.getElementById('nostockResultText');
                
                if (resultMessage && resultTitle && resultText) {
                    resultMessage.className = 'alert alert-danger my-3';
                    resultTitle.innerHTML = '<i class="fas fa-times-circle"></i> Dommage !';
                    resultText.innerHTML = 'Vous avez perdu cette fois-ci. La roue s\'est arrêtée sur un segment PERDU.<br>Merci de revenir la semaine prochaine pour tenter à nouveau votre chance.';
                    resultMessage.style.display = 'block';
                }
                
                // Désactiver la roue
                const wheelContainer = document.querySelector('.wheel-container');
                if (wheelContainer) {
                    wheelContainer.style.opacity = '0.5';
                    wheelContainer.style.pointerEvents = 'none';
                }
                
                return; // Ne pas déclencher la rotation
            }
            
            // Si l'utilisateur n'a pas encore joué, déclencher la rotation automatiquement
            setTimeout(function() {
                spinNoStockWheel();
            }, 1000); // Attendre 1 seconde avant de lancer la roue automatiquement
            
            // Fonction pour faire tourner la roue
            function spinNoStockWheel() {
                if (nostockIsSpinning) return;
                nostockIsSpinning = true;
                
                // Masquer le message de résultat précédent
                const resultMessage = document.getElementById('nostockResultMessage');
                if (resultMessage) {
                    resultMessage.style.display = 'none';
                }
                
                // Désactiver le bouton pendant le spin
                const spinBtn = document.getElementById('spinNoStockBtn');
                if (spinBtn) {
                    spinBtn.disabled = true;
                    spinBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> En cours...';
                }
                
                // Jouer le son de la roue qui tourne
                if (nostockSpinningSound) {
                    nostockSpinningSound.currentTime = 0;
                    nostockSpinningSound.play();
                }
                
                // TECHNIQUE CORRECTE SELON LA DOCUMENTATION: Contrôler exactement où la roue s'arrête
                
                // Les segments "PERDU" sont aux positions 1, 3, 5, 7, 9 (index 0, 2, 4, 6, 8)
                const losingSectors = [0, 2, 4, 6, 8];
                
                // Choisir aléatoirement l'un des secteurs perdants
                const randomIndex = Math.floor(Math.random() * losingSectors.length);
                const targetSegment = losingSectors[randomIndex] + 1; // +1 car getRandomForSegment attend un numéro de segment (1-based)
                
                console.log('Segment cible choisi:', targetSegment);
                
                // Utiliser la méthode recommandée dans la documentation:
                // getRandomForSegment retourne un angle aléatoire à l'intérieur du segment spécifié
                // et évite les angles proches des bords du segment (problème de segments partageant des angles)
                const stopAt = nostockWheel.getRandomForSegment(targetSegment);
                
                console.log('Angle d\'arrêt choisi dans le segment:', stopAt);
                
                // Configurer l'animation avec l'angle d'arrêt calculé
                nostockWheel.animation = {
                    'type': 'spinToStop',
                    'duration': 10,
                    'spins': 8,
                    'stopAngle': stopAt,
                    'callbackFinished': finishedNoStockSpinning,
                    'callbackSound': playNoStockTickSound,
                    'soundTrigger': 'pin'
                };
                
                // Démarrer l'animation
                nostockWheel.startAnimation();
                
                // Définir le cookie pour indiquer que l'utilisateur a joué
                setNostockCookie('nostock_wheel_played', 'true', 7); // Cookie valide 7 jours (1 semaine)
                
                // Définir également une variable de session côté client
                if (typeof(Storage) !== "undefined") {
                    sessionStorage.setItem('nostock_wheel_played', 'true');
                }
                
                // Vérification supplémentaire: confirmer le segment qui sera indiqué à l'arrêt
                setTimeout(function() {
                    // Calculer quel segment sera indiqué avec cet angle d'arrêt
                    nostockWheel.computeAnimation();
                    
                    // Vérifier que nous nous arrêtons bien sur un secteur PERDU
                    const indicatedSegmentNumber = nostockWheel.getIndicatedSegmentNumber();
                    const indicatedSegment = nostockWheel.getIndicatedSegment();
                    
                    console.log('Segment qui sera indiqué (numéro):', indicatedSegmentNumber);
                    console.log('Segment qui sera indiqué (objet):', indicatedSegment);
                    console.log('Texte du segment:', indicatedSegment.text);
                    
                    // S'assurer que c'est bien un segment PERDU
                    if (indicatedSegment.text !== 'PERDU') {
                        console.error('ERREUR: L\'angle d\'arrêt ne correspond pas à un segment PERDU!');
                    }
                }, 100);
            }

            // Le bouton n'est plus nécessaire car la roue tourne automatiquement au chargement
            const spinBtn = document.getElementById('spinNoStockBtn');
            if (spinBtn) {
                spinBtn.style.display = 'none';
            }
        });
    </script>
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

        #pointer-nostock {
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

        @media (max-width: 575.98px) {
            .wheel-and-pointer {
                width: 280px;
            }

            canvas#nostock-wheel {
                width: 280px;
                height: 280px;
            }

            #pointer-nostock svg {
                width: 30px;
                height: 30px;
            }

            .spin-button {
                max-width: 280px;
            }
        }

        /* Media query for very small devices */
        @media (max-width: 350px) {
            .wheel-and-pointer {
                width: 250px;
            }

            canvas#nostock-wheel {
                width: 250px;
                height: 250px;
            }
        }
    </style>
</div>
