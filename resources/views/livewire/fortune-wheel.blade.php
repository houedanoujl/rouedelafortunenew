<div>
    <div class="fortune-wheel-container">
        <div class="wheel-wrapper">
            <svg class="wheel" id="wheel" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
                <!-- Sections de la roue -->
                @if (count($prizes) > 0)
                    @foreach ($prizes as $index => $prize)
                        @php
                            $angle = 360 / count($prizes);
                            $startAngle = $index * $angle;
                            $endAngle = ($index + 1) * $angle;
                            
                            // Convertir en coordonnées pour le chemin SVG
                            $centerX = 250;
                            $centerY = 250;
                            $radius = 250;
                            
                            // Calculer les points du chemin pour ce secteur
                            $x1 = $centerX + $radius * cos(deg2rad($startAngle));
                            $y1 = $centerY + $radius * sin(deg2rad($startAngle));
                            $x2 = $centerX + $radius * cos(deg2rad($endAngle));
                            $y2 = $centerY + $radius * sin(deg2rad($endAngle));
                            
                            // Déterminer la couleur (jaune pour gagnant, rouge pour perdant)
                            $color = isset($prize['is_winning']) && $prize['is_winning'] ? '#FFCC00' : '#F44336';
                            
                            // Calculer position du texte
                            $textAngle = $startAngle + ($angle / 2);
                            $textRadius = $radius * 0.6;
                            $textX = $centerX + $textRadius * cos(deg2rad($textAngle));
                            $textY = $centerY + $textRadius * sin(deg2rad($textAngle));
                            
                            // Orienter le texte
                            $textRotation = $textAngle + 90; // Ajusté pour meilleure lisibilité
                            
                            // Texte à afficher (simplement "Gagné" pour les secteurs gagnants)
                            $displayText = isset($prize['is_winning']) && $prize['is_winning'] ? 'Gagné' : 'Pas de chance';
                        @endphp
                        
                        <path 
                            d="M {{ $centerX }},{{ $centerY }} L {{ $x1 }},{{ $y1 }} A {{ $radius }},{{ $radius }} 0 0,1 {{ $x2 }},{{ $y2 }} Z" 
                            fill="{{ $color }}"
                            stroke="#333"
                            stroke-width="1"
                        />
                        
                        <text 
                            x="{{ $textX }}" 
                            y="{{ $textY }}" 
                            text-anchor="middle" 
                            fill="white" 
                            font-weight="bold"
                            font-size="14"
                            transform="rotate({{ $textRotation }}, {{ $textX }}, {{ $textY }})"
                            class="prize-name"
                        >{{ $displayText }}</text>
                    @endforeach
                @else
                    <circle cx="250" cy="250" r="250" fill="#ccc" />
                    <text x="250" y="250" text-anchor="middle" fill="white" font-weight="bold">Pas de prix</text>
                @endif
                
                <!-- Centre de la roue -->
                <circle cx="250" cy="250" r="40" fill="#333" stroke="#fff" stroke-width="5" />
            </svg>
            
            <!-- Marqueur / Flèche -->
            <div class="wheel-marker"></div>
        </div>
    
        <div class="wheel-controls">
            @if ($result)
                <div class="result-container">
                    <div class="result {{ $result['status'] }}">
                        <h3>{{ $result['message'] }}</h3>
                        
                        @if ($qrCodeUrl)
                            <div class="qr-code-container">
                                <p>Scannez ce code QR pour découvrir votre résultat</p>
                                <div id="qrcode-livewire" class="qr-code"></div>
                                <p class="qr-code-text">Code: {{ $qrCodeUrl }}</p>
                                <div class="qr-actions mt-3">
                                    <button id="capture-qr" class="btn btn-success">
                                        <i class="fas fa-camera"></i> Capturer le QR code
                                    </button>
                                    <a id="download-qr" class="btn btn-primary ml-2" download="qrcode_{{ $qrCodeUrl }}.jpg" href="#">
                                        <i class="fas fa-download"></i> Télécharger en JPG
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <button class="btn btn-primary mt-4" onclick="location.reload();">
                    Retour à l'accueil
                </button>
            @else
                <button 
                    class="btn btn-primary spin-button" 
                    onclick="startSpin(); return false;" 
                    {{ $spinning || count($prizes) === 0 ? 'disabled' : '' }}>
                    {{ $spinning ? 'La roue tourne...' : 'Tourner la roue' }}
                </button>
            @endif
        </div>
    </div>
    
    <!-- Ajout du token CSRF pour les requêtes AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Modal pour afficher le résultat -->
    <div class="result-modal" id="resultModal" style="display: none;">
        <div class="result-modal-content">
            <span class="result-modal-close">&times;</span>
            <div class="result-modal-body">
                <h2 id="result-title"></h2>
                <p id="result-message"></p>
                <button id="result-continue" class="btn btn-primary">Continuer</button>
            </div>
        </div>
    </div>
    
    <!-- Ajout des éléments audio -->
    <audio id="wheel-spinning-sound" preload="auto">
        <source src="{{ asset('sounds/wheel-spinning.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="win-sound" preload="auto">
        <source src="{{ asset('sounds/win.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="lose-sound" preload="auto">
        <source src="{{ asset('sounds/lose.mp3') }}" type="audio/mpeg">
    </audio>
    
    <script>
        // Animation de la roue et appel AJAX vers le contrôleur
        function startSpin() {
            console.log('Début de l\'animation');
            
            // Désactiver le bouton
            const button = document.querySelector('.spin-button');
            if (button) button.disabled = true;
            
            // Jouer le son de la roue qui tourne
            const spinningSound = document.getElementById('wheel-spinning-sound');
            if (spinningSound) {
                spinningSound.currentTime = 0;
                spinningSound.play().catch(e => console.log('Erreur lecture audio:', e));
            }
            
            // Animer la roue
            const wheel = document.getElementById('wheel');
            if (!wheel) {
                console.error('Roue introuvable!');
                return false;
            }
            
            // Réinitialiser
            wheel.style.transition = 'none';
            wheel.style.transform = 'rotate(0deg)';
            void wheel.offsetWidth;
            
            // Animation
            const degrees = 1800 + Math.floor(Math.random() * 1800);
            console.log('Rotation de ' + degrees + ' degrés');
            wheel.style.transition = 'transform 6s cubic-bezier(0.2, 0.8, 0.2, 1)';
            wheel.style.transform = 'rotate(' + degrees + 'deg)';
            
            // Faire une requête AJAX pour obtenir le résultat après l'animation
            setTimeout(function() {
                // Créer un token CSRF pour la sécurité Laravel
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                console.log('Token CSRF:', csrfToken);
                
                const entryId = '{{ $entry->id ?? "" }}';
                console.log('ID Entrée:', entryId);
                
                // Encodage des données en format formulaire (au lieu de JSON)
                const formData = new FormData();
                formData.append('entry_id', entryId);
                formData.append('_token', csrfToken);
                
                // Faire la requête
                fetch('/wheel/spin', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Statut de la réponse:', response.status);
                    console.log('Headers:', [...response.headers.entries()]);
                    return response.json();
                })
                .then(data => {
                    console.log('Résultat complet reçu:', data);
                    if (data.success) {
                        // Afficher le popup avec le résultat
                        const resultModal = document.getElementById('resultModal');
                        const resultTitle = document.getElementById('result-title');
                        const resultMessage = document.getElementById('result-message');
                        const resultContinue = document.getElementById('result-continue');
                        
                        if (data.result && data.result.status) {
                            // Jouer le son approprié
                            const soundElement = document.getElementById(data.result.status === 'win' ? 'win-sound' : 'lose-sound');
                            if (soundElement) {
                                soundElement.currentTime = 0;
                                soundElement.play().catch(e => console.log('Erreur lecture audio:', e));
                            }
                            
                            // Configurer le modal
                            resultTitle.textContent = data.result.status === 'win' ? 'Félicitations !' : 'Pas de chance...';
                            resultMessage.textContent = data.result.status === 'win' ? 
                                'Vous avez gagné ! Consultez votre QR code pour récupérer votre lot.' : 
                                'Vous n\'avez pas gagné cette fois-ci. Vous pourrez réessayer ultérieurement.';
                            
                            // Afficher le modal
                            resultModal.style.display = 'flex';
                            
                            // Configurer le bouton continuer
                            resultContinue.onclick = function() {
                                resultModal.style.display = 'none';
                                location.reload(); // Recharger la page pour afficher le résultat complet
                            }
                        } else {
                            // Si on n'a pas de résultat, rechargement direct
                            console.log('Succès, rechargement de la page...');
                            location.reload();
                        }
                    } else {
                        alert('Une erreur est survenue: ' + (data.message || 'Erreur inconnue'));
                        // Réactiver le bouton
                        if (button) button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Erreur complète:', error);
                    alert('Une erreur est survenue lors de la communication avec le serveur. Veuillez réessayer.');
                    // Réactiver le bouton
                    if (button) button.disabled = false;
                });
            }, 6000); // Attendre la fin de l'animation
            
            return false;
        }
        
        // Initialiser le QR code avec qrcode-generator
        document.addEventListener('DOMContentLoaded', function() {
            const qrcodeContainer = document.getElementById('qrcode-livewire');
            if (qrcodeContainer) {
                // Vider le conteneur au cas où
                qrcodeContainer.innerHTML = '';
                
                // Vérifier si la bibliothèque est chargée
                if (typeof qrcode === 'function') {
                    // Créer le QR code
                    const qr = qrcode(0, 'L');
                    qr.addData('{{ url('/qr/' . $qrCodeUrl) }}');
                    qr.make();
                    
                    // Ajouter l'image au conteneur avec une taille plus grande
                    const qrImage = qr.createImgTag(10);
                    qrcodeContainer.innerHTML = qrImage;
                    console.log('QR Code créé pour URL:', '{{ url('/qr/' . $qrCodeUrl) }}');
                    
                    // Configurer le bouton de capture et téléchargement
                    setupQrCodeCapture();
                } else {
                    // Charger la bibliothèque si elle n'est pas déjà chargée
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js';
                    script.onload = function() {
                        const qr = qrcode(0, 'L');
                        qr.addData('{{ url('/qr/' . $qrCodeUrl) }}');
                        qr.make();
                        const qrImage = qr.createImgTag(10);
                        qrcodeContainer.innerHTML = qrImage;
                        console.log('QR Code créé pour URL:', '{{ url('/qr/' . $qrCodeUrl) }}');
                        
                        // Configurer le bouton de capture et téléchargement
                        setupQrCodeCapture();
                    };
                    document.head.appendChild(script);
                }
            }
            
            // Fermeture du modal de résultat
            const closeModal = document.querySelector('.result-modal-close');
            if (closeModal) {
                closeModal.onclick = function() {
                    document.getElementById('resultModal').style.display = 'none';
                }
            }
            
            // Fermer le modal si on clique en dehors
            window.onclick = function(event) {
                const modal = document.getElementById('resultModal');
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        });
        
        // Fonction pour configurer la capture et le téléchargement du QR code
        function setupQrCodeCapture() {
            // Ajouter html2canvas pour la capture
            if (typeof html2canvas !== 'function') {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
                document.head.appendChild(script);
            }
            
            // Configurer le bouton de capture
            const captureButton = document.getElementById('capture-qr');
            const downloadLink = document.getElementById('download-qr');
            
            if (captureButton && downloadLink) {
                captureButton.addEventListener('click', function() {
                    const qrContainer = document.getElementById('qrcode-livewire');
                    if (!qrContainer) return;
                    
                    // S'assurer que html2canvas est chargé
                    if (typeof html2canvas === 'function') {
                        html2canvas(qrContainer).then(canvas => {
                            // Convertir en JPG
                            const imgData = canvas.toDataURL('image/jpeg', 0.8);
                            
                            // Mettre à jour le lien de téléchargement
                            downloadLink.href = imgData;
                            
                            // Déclencher automatiquement le téléchargement
                            downloadLink.click();
                        });
                    } else {
                        alert('Veuillez patienter, chargement des ressources nécessaires...');
                        setTimeout(() => {
                            captureButton.click();
                        }, 1000);
                    }
                });
            }
        }
    </script>
    
    <style>
        .fortune-wheel-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            max-width: 100%;
            overflow: hidden;
        }
        
        .wheel-wrapper {
            position: relative;
            width: 80vw;
            max-width: 500px;
            height: auto;
            aspect-ratio: 1/1;
            margin: 0 auto 2rem;
        }
        
        .wheel {
            width: 100%;
            height: 100%;
            transform-origin: center;
            transition: transform 6s cubic-bezier(0.2, 0.8, 0.2, 1);
            border-radius: 50%;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        
        .prize-name {
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }
        
        .wheel-marker {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 20px solid transparent;
            border-right: 20px solid transparent;
            border-top: 40px solid #e91e63;
            z-index: 20;
            filter: drop-shadow(0px 3px 3px rgba(0, 0, 0, 0.3));
        }
        
        .result-container {
            margin-top: 1rem;
            text-align: center;
            width: 100%;
            max-width: 400px;
        }
        
        .result {
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .result.win {
            background-color: #4CAF50;
            color: white;
        }
        
        .result.lose {
            background-color: #F44336;
            color: white;
        }
        
        .result.error {
            background-color: #FF9800;
            color: white;
        }
        
        .qr-code-container {
            margin-top: 1rem;
        }
        
        .qr-code {
            max-width: 200px;
            margin: 0 auto;
        }
        
        .qr-code-text {
            font-size: 1.2rem;
            margin-top: 0.5rem;
        }
        
        .spin-button {
            font-size: 1.2rem;
            padding: 0.8rem 1.5rem;
            background-color: #2196F3;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .spin-button:hover:not(:disabled) {
            background-color: #0b7dda;
        }
        
        .spin-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        /* Styles pour le modal de résultat */
        .result-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            align-items: center;
            justify-content: center;
        }
        
        .result-modal-content {
            position: relative;
            background-color: #fff;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.5s;
        }
        
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-30px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .result-modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        
        .result-modal-body {
            text-align: center;
            padding: 20px 0;
        }
        
        .result-modal-body h2 {
            color: #2196F3;
            margin-bottom: 15px;
        }
        
        .qr-actions {
            display: flex;
            justify-content: center;
            gap: 8px;
        }
        
        /* Pour les boutons alignés */
        .ml-2 {
            margin-left: 8px;
        }
        
        .mt-3 {
            margin-top: 12px;
        }
    </style>
</div>
