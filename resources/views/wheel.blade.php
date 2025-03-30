@extends('layouts.app')

@section('content')
<div class="wheel-container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Roue de la Fortune</h2>
                </div>
                <div class="card-body text-center">
                    <p class="lead mb-4">Bonjour {{ $entry->participant->first_name }} {{ $entry->participant->last_name }}, 
                    @if(!$result)
                        tournez la roue et tentez votre chance !
                    @else
                        voici votre résultat !
                    @endif
                    </p>
                    
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
                                            
                                            // Orienter le texte à 90 degrés
                                            $textRotation = $textAngle;
                                            
                                            // Texte à afficher
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
                                        
                                        @if ($result['status'] === 'win' && $qrCodeUrl)
                                            <div class="qr-code-container">
                                                <p>Scannez ce code QR pour récupérer votre prix</p>
                                                <div id="qrcode" class="qr-code"></div>
                                                <p class="qr-code-text">Code: {{ $qrCodeUrl }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <a href="{{ route('home') }}" class="btn btn-primary mt-4">
                                    Retour à l'accueil
                                </a>
                            @else
                                <button 
                                    class="btn btn-primary spin-button" 
                                    onclick="startSpin(); return false;">
                                    Tourner la roue
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-muted">Concours: {{ $entry->contest->name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts et styles -->
<script src="https://unpkg.com/qrcodejs@1.0.0/dist/qrcode.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    // Animation de la roue et appel AJAX vers le contrôleur
    function startSpin() {
        console.log('Début de l\'animation');
        
        // Désactiver le bouton
        const button = document.querySelector('.spin-button');
        if (button) button.disabled = true;
        
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
                    // Recharger la page pour afficher le résultat
                    console.log('Succès, rechargement de la page...');
                    location.reload();
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
    
    // Initialiser le QR code si nécessaire
    document.addEventListener('DOMContentLoaded', function() {
        const qrcodeEl = document.getElementById('qrcode');
        if (qrcodeEl) {
            new QRCode(qrcodeEl, {
                text: "{{ $qrCodeUrl ?? '' }}",
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    });
</script>

<style>
    .fortune-wheel-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1rem;
        max-width: 100%;
    }
    
    .wheel-wrapper {
        position: relative;
        width: 80vw;
        max-width: 500px;
        margin: 0 auto 2rem;
    }
    
    .wheel {
        width: 100%;
        height: auto;
        transition: transform 6s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    
    .wheel-marker {
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 20px solid transparent;
        border-right: 20px solid transparent;
        border-top: 30px solid #333;
        z-index: 10;
    }
    
    .wheel-controls {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 1rem;
    }
    
    .spin-button {
        font-size: 1.2rem;
        padding: 0.5rem 2rem;
    }
    
    .result-container {
        margin: 1rem 0;
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
    }
    
    .result {
        padding: 1rem;
        border-radius: 8px;
    }
    
    .result.win {
        background-color: #FFCC00;
        color: #333;
    }
    
    .result.lose {
        background-color: #F44336;
        color: white;
    }
    
    .qr-code-container {
        margin-top: 1rem;
    }
    
    .qr-code {
        display: inline-block;
        margin: 1rem auto;
        padding: 1rem;
        background: white;
        border-radius: 8px;
    }
    
    .qr-code-text {
        margin-top: 0.5rem;
        font-family: monospace;
        font-size: 0.9rem;
    }
    
    .prize-name {
        pointer-events: none;
    }
</style>
@endsection
