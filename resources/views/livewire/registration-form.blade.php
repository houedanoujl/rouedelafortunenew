<div style="text-align: center; font-weight: normal;">
<!-- CSS pour le popup de vérification d'âge -->
<style>
    /* Overlay qui couvre tout l'écran */
    .age-verification-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    /* Le popup lui-même */
    .age-verification-popup {
        background-color: white;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        animation: popup-fade-in 0.5s ease-out;
    }
    
    @keyframes popup-fade-in {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }
    
    .age-verification-popup h2 {
        color: var(--secondary-color);
        margin-bottom: 20px;
        font-size: 1.8rem;
    }
    
    .age-verification-popup p {
        margin-bottom: 30px;
        font-size: 1.2rem;
    }
    
    .age-verification-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
    }
    
    .age-verification-buttons button {
        padding: 10px 30px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1.1rem;
        font-weight: bold;
        transition: all 0.2s;
    }
    
    .btn-age-yes {
        background-color: var(--apple-green);
        color: white;
    }
    
    .btn-age-no {
        background-color: var(--persian-red);
        color: white;
    }
    
    .btn-age-yes:hover {
        background-color: var(--sea-green);
        transform: translateY(-2px);
    }
    
    .btn-age-no:hover {
        background-color: #b02a1d;
        transform: translateY(-2px);
    }
    
    .hidden {
        display: none !important;
    }
</style>

<!-- Les popups sont déplacés en dehors du conteneur principal pour un affichage correct -->

<style>
    /* Styles pour améliorer la lisibilité */
    .form-group {
        margin-bottom: 1.5rem;
        text-align: left;
    }
    .form-group label {
        font-size: 1.3rem;
        margin-bottom: 0.7rem;
        display: block;
    }
    .form-control {
        padding: 0.8rem 1rem;
        font-size: 1.2rem;
        line-height: 1.5;
        height: auto;
        border: 1px solid #cccccc;
        border-radius: 6px;
        background-color: white;
    }
    .form-check-label {
        font-size: 1.1rem;
        line-height: 1.5;
        padding-left: 0.5rem;
    }
    .text-danger {
        font-size: 1.1rem;
        margin-top: 0.4rem;
        display: block;
    }
    .alert {
        font-size: 1.2rem;
        line-height: 1.6;
        padding: 1.2rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .alert h4 {
        font-size: 1.4rem;
        margin-bottom: 1rem;
    }
    .alert p {
        margin-bottom: 0.8rem;
    }
    .btn {
        font-size: 1.2rem;
        padding: 0.8rem 1.5rem;
    }
</style>
    <div class="card" style="border: 1px solid #e0e0e0; border-radius: 4px; box-shadow: none;">
        <div class="card-header" style="background-color: var(--honolulu-blue); color: white;">
            <h2>📝 Inscription 🎟️</h2>
        </div>
        <div class="card-body">
            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('info'))
                <div class="alert alert-info">
                    {{ session('info') }}
                </div>
            @endif

            @if ($isExistingParticipant)
                <div class="alert alert-info">
                    <h5><i class="fas fa-user-check"></i> Participant reconnu !</h5>
                    <p>Bienvenue à nouveau ! Vous pouvez maintenant participer à ce nouveau concours.</p>
                    @if ($previousContestsCount > 0)
                        <p><small>Vous avez déjà participé à {{ $previousContestsCount }} concours précédemment.</small></p>
                    @endif
                </div>
            @endif

            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($isBlocked)
                <div class="alert alert-warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> 🚫 Limite de participations atteinte 🚫</h4>
                    <p>😥 Vous avez déjà participé récemment et avez atteint le nombre maximum de tentatives autorisées.</p>
                    <p>📅 Pas d'inquiétude ! Vous pourrez retenter votre chance à partir du: <span style="color: var(--primary-red);">{{ $limitedUntil }}</span></p>
                    <p>🔔 Nous vous attendons avec impatience pour votre prochaine tentative ! 🍀</p>
                </div>
            @elseif ($alreadyParticipated && $existingEntry)
                <div class="alert alert-info">
                    <h4><i class="fas fa-info-circle"></i> 📝 Vous avez déjà participé 🎟️</h4>
                    <p>📱 Nous avons détecté que vous avez déjà participé à ce concours avec ce numéro de téléphone ou cette adresse email.</p>
                    <p>🎲 Vous pouvez consulter votre participation existante ci-dessous :</p>
                    <div class="mt-3">
                        <a href="{{ route('result.show', ['entry' => $existingEntry->id]) }}" class="btn btn-primary">
                            🏆 Voir ma participation 🔎
                        </a>
                    </div>
                </div>
            @else
                <form wire:submit.prevent="register" style="text-align: center;">
                    <!-- Champ caché pour l'ID du concours - utilisé par le système de limitation de participation -->
                    <input type="hidden" name="contestId" value="{{ $contestId }}" id="contestId">
                    <div class="form-group">
                        <label for="firstName">{{ __('registration.fields.firstName.label') }}</label>
                        <input type="text" class="form-control" id="firstName" wire:model="firstName" required>
                        @error('firstName') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="lastName">{{ __('registration.fields.lastName.label') }}</label>
                        <input type="text" class="form-control" id="lastName" wire:model="lastName" required>
                        @error('lastName') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="phone">{{ __('registration.fields.phone.label') }}</label>
                        <input type="tel" class="form-control {{ $isExistingParticipant ? 'bg-light' : '' }}" id="phone" wire:model="phone" {{ $isExistingParticipant ? 'readonly' : '' }} required>
                        @if (!$isExistingParticipant)
                            <small class="form-text text-muted">Si vous avez déjà participé, saisissez votre numéro pour retrouver vos informations.</small>
                        @endif
                        @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="email">{{ __('registration.fields.email.label') }}</label>
                        <input type="email" class="form-control" id="email" wire:model="email">
                        @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Case à cocher pour le consentement individuel -->
                    <div class="form-group mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="consentCheckbox" wire:model="consentement" required>
                            <label class="form-check-label" for="consentCheckbox">
                                J'accepte le <a href="#" data-bs-toggle="modal" data-bs-target="#consentModal" style="color: red;">recueil de consentement individuel</a>
                            </label>
                        </div>
                        @error('consentement') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Case à cocher pour le règlement de la tombola -->
                    <div class="form-group mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="reglementCheckbox" wire:model="reglement" required>
                            <label class="form-check-label" for="reglementCheckbox">
                                J'ai lu et j'accepte le <a href="#" data-bs-toggle="modal" data-bs-target="#reglementModal" style="color: red;">règlement de la tombola</a>
                            </label>
                        </div>
                        @error('reglement') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-block" style="background-color: var(--school-bus-yellow); border: none; border-radius: 4px; color: var(--dark-gray); font-weight: normal;" wire:loading.attr="disabled">
                            <span wire:loading wire:target="register">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                            @if ($isExistingParticipant)
                                Participer au concours 🎲
                            @else
                                S'inscrire 🎲
                            @endif
                        </button>
                        <p class="mt-2 text-muted"></p>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Modal pour le consentement individuel -->
    <div class="modal fade" id="consentModal" tabindex="-1" aria-labelledby="consentModalLabel" aria-hidden="true" style="z-index: 1060;" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="consentModalLabel">{{ $modalContents['consent']['title'] ?? 'Fiche de recueil de consentement' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align: center; font-weight: normal;">
                    @if(!empty($modalContents['consent']['content']))
                        @foreach($modalContents['consent']['content'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    @else
                        <p>Contenu du consentement non disponible.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $modalContents['consent']['buttonText'] ?? 'Fermer' }}</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal pour le règlement de la tombola -->
    <div class="modal fade" id="reglementModal" tabindex="-1" aria-labelledby="reglementModalLabel" aria-hidden="true" style="z-index: 1060;" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reglementModalLabel">{{ $modalContents['rules']['title'] ?? 'Règlement de la tombola' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; text-align: center; font-weight: normal;">
                    @if(!empty($modalContents['rules']['content']))
                        @foreach($modalContents['rules']['content'] as $item)
                            @if(isset($item['subtitle']))
                                <h6 class="mt-4 mb-2">{{ $item['subtitle'] }}</h6>
                            @endif
                            @if(isset($item['paragraph']))
                                <p>{{ $item['paragraph'] }}</p>
                            @endif
                        @endforeach
                    @else
                        <p>Contenu du règlement non disponible.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $modalContents['rules']['buttonText'] ?? 'Fermer' }}</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer avec le nom du concours -->
    @if($contestName)
    <div class="contest-footer" style="margin-top: 20px; text-align: center; padding: 15px; background-color: rgba(255, 255, 255, 0.8); border-radius: 4px;">
        <p style="margin-bottom: 0; font-size: 14px; color: #666;">
            <i class="bi bi-calendar-event"></i> Vous participez au concours: <strong style="color: var(--primary-red);">{{ $contestName }}</strong>
        </p>
    </div>
    @endif
</div>

<!-- Modal d'avertissement pour navigation privée / cookies désactivés -->
<div id="privacyWarningOverlay" class="age-verification-overlay hidden">
    <div class="age-verification-popup">
        <h2><i class="bi bi-shield-exclamation"></i> Cookies désactivés</h2>
        <p>Il semble que vous naviguiez en mode privé ou que les cookies soient désactivés sur votre appareil.</p>
        <p>Pour participer à notre concours, veuillez :</p>
        <ul style="text-align: left; margin: 20px auto; max-width: 80%;">
            <li>Utiliser le mode de navigation normal</li>
            <li>Activer les cookies dans les paramètres de votre navigateur</li>
            <li>Désactiver le mode "Prévention du suivi intelligent" (utilisateurs iOS)</li>
        </ul>
        <div class="age-verification-buttons">
            <button class="btn-age-yes" onclick="window.location.reload()">J'ai activé les cookies</button>
        </div>
    </div>
</div>

<!-- Popup de vérification d'âge - caché par défaut -->
<div id="ageVerificationOverlay" class="age-verification-overlay hidden">
    <div class="age-verification-popup">
        <h2>Vérification de l'âge</h2>
        <p>Êtes vous agé d'au moins 18 ans ?</p>
        <div class="age-verification-buttons">
            <button class="btn-age-yes" onclick="verifyAge(true)">Oui</button>
            <button class="btn-age-no" onclick="verifyAge(false)">Non</button>
        </div>
    </div>
</div>

<!-- Script de vérification d'âge, participations et détection du mode privé -->
<script>
    // Détection de la navigation privée et des cookies désactivés
    (function detectPrivateMode() {
        // Variables pour indiquer l'état de détection
        let isPrivate = false;
        let isCookieEnabled = navigator.cookieEnabled;

        // Si les cookies sont désactivés, bloquer immédiatement
        if (!isCookieEnabled) {
            showPrivacyWarning();
            return;
        }

        // Test de stockage pour détecter le mode privé
        try {
            // Safari, Firefox, Chrome et autres navigateurs based sur WebKit et Gecko
            const testKey = 'test_private_mode';
            // Détection basée sur localStorage
            localStorage.setItem(testKey, '1');
            localStorage.removeItem(testKey);

            // Détection spécifique pour les appareils iOS
            if (navigator.userAgent.includes('iPhone') || navigator.userAgent.includes('iPad')) {
                // Test supplémentaire pour iOS
                // Certains appareils iOS en mode ITP peuvent stocker des cookies mais les suppriment rapidement
                const iOSTestCookie = '_ios_cookie_test';
                document.cookie = `${iOSTestCookie}=1; path=/;`;
                setTimeout(() => {
                    if (!document.cookie.includes(iOSTestCookie)) {
                        console.log('Détection de \'Intelligent Tracking Prevention\' sur iOS');
                        showPrivacyWarning();
                    }
                }, 100);
            }
            
            // Vérification de la taille limitée du localStorage (Safari en mode privé)
            const storageSize = 5 * 1024 * 1024; // 5MB
            const testData = '0'.repeat(storageSize);
            try {
                localStorage.setItem('test_storage_limit', testData);
                localStorage.removeItem('test_storage_limit');
            } catch (e) {
                isPrivate = true;
            }
        } catch (e) {
            // Exception lors de l'accès au localStorage (navigateur en mode privé)
            isPrivate = true;
        }

        // Si en mode privé, afficher l'avertissement
        if (isPrivate) {
            showPrivacyWarning();
        }
    })();

    // Fonction pour afficher l'avertissement de navigation privée
    function showPrivacyWarning() {
        // Masquer le formulaire
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.style.display = 'none';
        });

        // Afficher l'avertissement
        document.getElementById('privacyWarningOverlay').classList.remove('hidden');
    }
    
    // Script pour vérifier l'âge avant le chargement complet de la page
    (function() {
        // Vérifier si la vérification d'âge est déjà stockée dans localStorage
        try {
            const ageVerified = localStorage.getItem('age_verified');
            
            // Si l'âge n'a pas encore été vérifié, afficher le popup
            if (ageVerified !== 'true') {
                // Attendre un court instant pour permettre le rendu de la page
                setTimeout(function() {
                    document.getElementById('ageVerificationOverlay').classList.remove('hidden');
                }, 100);
            }
        } catch (e) {
            // En cas d'erreur d'accès à localStorage, afficher quand même la vérification d'âge
            setTimeout(function() {
                document.getElementById('ageVerificationOverlay').classList.remove('hidden');
            }, 100);
        }
    })();
    
    // Au chargement de la page, vérifier si l'utilisateur a déjà participé
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier si l'utilisateur a déjà participé à ce concours (vérification côté client)
        try {
            checkForExistingParticipation();
        } catch (e) {
            console.log('Erreur lors de la vérification de participation:', e);
        }
    });
    
@script
    // Écouter les événements envoyés par Livewire (syntaxe de Livewire v3)
    $wire.on('setup-participation-check', (params) => {
        let contestId = params.contestId;
        checkForExistingParticipation(contestId);
    });
    
    // Écouter l'événement pour stocker la participation
    $wire.on('store-participation', (params) => {
        let key = params.key;
        let value = params.value;
        let contestId = params.contestId;
        
        // Stocker dans localStorage
        localStorage.setItem(key, value);
        console.log(`Participation au concours ${contestId} enregistrée dans localStorage`);
    });
    
    // Écouter l'événement de redirection en cas de participation existante
    $wire.on('redirect-already-played', (params) => {
        let url = params.url;
        console.log(`Redirection vers: ${url}`);
        window.location.href = url;
    });
@endscript
    
    /**
     * Vérifie si l'utilisateur a déjà participé au concours spécifié
     */
    function checkForExistingParticipation(contestId = null) {
        try {
            // Si aucun ID de concours n'est fourni, essayer de le récupérer depuis le formulaire
            if (!contestId) {
                const contestIdInput = document.getElementById('contestId');
                if (contestIdInput) {
                    contestId = contestIdInput.value;
                }
            }
            
            if (!contestId) return; // Ne rien faire si aucun concours n'est spécifié
            
            // Clé spécifique au concours
            const key = `contest_played_${contestId}`;
            
            // Vérifier dans localStorage
            const hasPlayed = localStorage.getItem(key);
            
            if (hasPlayed) {
                console.log(`Participation détectée dans localStorage pour le concours ${contestId}`);
                
                // Redirect avec les paramètres appropriés
                const redirectUrl = `/home?already_played=true&contest_id=${contestId}`;
                
                // Ajouter un petit délai pour permettre à Livewire de s'initialiser
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 300);
            }
        } catch (e) {
            console.error('Erreur lors de la vérification de participation:', e);
            // Si une erreur se produit lors de l'accès au localStorage, cela peut indiquer le mode privé
            showPrivacyWarning();
        }
    }
    
    // Fonction pour vérifier l'âge
    function verifyAge(isAdult) {
        if (isAdult) {
            // Si l'utilisateur a plus de 18 ans, sauvegarder dans localStorage et cacher le popup
            localStorage.setItem('age_verified', 'true');
            document.getElementById('ageVerificationOverlay').classList.add('hidden');
        } else {
            // Si l'utilisateur a moins de 18 ans, rediriger vers Google
            window.location.href = 'https://www.google.com';
        }
    }
</script>
