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
                resultText.innerHTML = 'Perdu. Merci de revenir la semaine prochaine pour tenter à nouveau votre chance.';
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

            // Envoi d'un log (optionnel, ici on n'envoie PAS de log d'historique, mais on montre l'exemple)
            /*
            fetch('/api/spin/log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    wheel_type: 'no-stock',
                    // ... autres infos si besoin
                })
            });
            */
        }

        // Fonction pour faire tourner la roue avec un angle précis reçu du backend
        function spinNoStockWheelWithAngle(targetAngle) {
            if (nostockIsSpinning) return;
            nostockIsSpinning = true;
            const resultMessage = document.getElementById('nostockResultMessage');
            if (resultMessage) resultMessage.style.display = 'none';
            const spinBtn = document.getElementById('spinNoStockBtn');
            if (spinBtn) {
                spinBtn.disabled = true;
                spinBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> En cours...';
            }
            if (nostockSpinningSound) {
                nostockSpinningSound.currentTime = 0;
                nostockSpinningSound.play();
            }
            // PATCH: Remise à zéro de la roue avant chaque spin
            nostockWheel.stopAnimation(false);
            nostockWheel.rotationAngle = 0;
            nostockWheel.draw();
            // PATCH: Nombre de tours fixe et angle exact
            nostockWheel.animation = {
                'type': 'spinToStop',
                'duration': 10,
                'spins': 8,
                'stopAngle': targetAngle,
                'callbackFinished': finishedNoStockSpinning,
                'callbackSound': playNoStockTickSound,
                'soundTrigger': 'pin'
            };
            nostockWheel.startAnimation();
            setNostockCookie('nostock_wheel_played', 'true', 7);
            if (typeof(Storage) !== "undefined") {
                sessionStorage.setItem('nostock_wheel_played', 'true');
            }
        }

        // Lors du chargement, demander l'angle exact au backend
        document.addEventListener('DOMContentLoaded', function() {
            // SUPPRESSION de tout spin automatique résiduel ici
            fetch('/wheel/api/no-stock-wheel-angle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ wheel_type: 'no-stock' })
            })
            .then(response => response.json())
            .then(data => {
                if (data && typeof data.target_angle === 'number') {
                    // Ne lance le spin qu'ICI, jamais ailleurs
                    spinNoStockWheelWithAngle(data.target_angle);
                }
            });
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
