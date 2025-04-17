<div style="center; font-weight: normal;">

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

<!-- Modal d'avertissement pour navigation privée / cookies désactivés -->
<div id="privacyWarningOverlay" class="age-verification-overlay hidden">
    <div class="age-verification-popup">
        <h2><i class="bi bi-shield-exclamation"></i> Navigation privée détectée</h2>
        <p>Pour des raisons de sécurité et pour garantir une expérience optimale, ce formulaire n'est pas accessible en navigation privée.</p>
        <p>Pour participer à notre concours, veuillez :</p>
        <ul style="text-align: left; margin: 20px auto; max-width: 80%;">
            <li>Utiliser le mode de navigation normal (non privé)</li>
            <li>Vous assurer que les cookies sont activés dans les paramètres de votre navigateur</li>
            <li>Désactiver le mode "Prévention du suivi intelligent" si vous utilisez un appareil iOS</li>
        </ul>
        <div class="age-verification-buttons">
            <button class="btn-age-yes" onclick="window.location.reload()">J'ai changé de mode de navigation</button>
        </div>
    </div>
</div>

    <div class="card" style="border: 1px solid #e0e0e0; min-height:100vh; border-radius: 4px; box-shadow: none;">
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
                        <label for="firstName">{{ __('registration.fields.firstName.label') }} <span style="color: red;">*</span></label>
                        <input type="text" class="form-control" id="firstName" wire:model="firstName" required>
                        @error('firstName') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="lastName">{{ __('registration.fields.lastName.label') }} <span style="color: red;">*</span></label>
                        <input type="text" class="form-control" id="lastName" wire:model="lastName" required>
                        @error('lastName') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                            <label for="phone">{{ __('registration.fields.phone.label') }} <span style="color: red;">*</span></label>
                            <input type="tel" class="form-control" id="phone" wire:model.lazy="phone" required>
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
                                J'accepte le <a href="#" data-bs-toggle="modal" data-bs-target="#consentModal" style="color: red;">recueil de consentement individuel</a> <span style="color: red;">*</span>
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

    @include('partials.rules-modal')

    <!-- Footer avec le nom du concours -->
    @if($contestName)
    <div class="contest-footer" style="margin-top: 20px; text-align: center; padding: 15px; background-color: rgba(255, 255, 255, 0.8); border-radius: 4px;">
        <p style="margin-bottom: 0; font-size: 14px; color: #666;">
            <i class="bi bi-calendar-event"></i> Vous participez au concours: <strong style="color: var(--primary-red);">{{ $contestName }}</strong>
        </p>
    </div>
    @endif
</div>

<!-- Script de vérification d'âge, participations et détection du mode privé -->
<script>
    // Détection de la navigation privée et des cookies désactivés
    function detectPrivateMode() {
        return new Promise(function(resolve) {
            const YES = true;
            const NO = false;
            const UNKNOWN = null;

            // Pour Firefox
            if (navigator.userAgent.includes('Firefox')) {
                try {
                    indexedDB.open('test').onupgradeneeded = function() {
                        resolve(NO); // Indexé DB fonctionne => pas en privé
                    };
                    setTimeout(function() {
                        resolve(YES); // Timeout => probablement en privé
                    }, 500);
                } catch (e) {
                    resolve(UNKNOWN);
                }
                return;
            }

            // Pour Safari
            if (navigator.userAgent.includes('Safari') && !navigator.userAgent.includes('Chrome')) {
                try {
                    window.openDatabase(null, null, null, null);
                    try {
                        // Tentative de stocker 100MB en localStorage (Safari en privé limite à 50MB)
                        localStorage.setItem('test', new Array(100000000).join('1'));
                        localStorage.removeItem('test');
                        resolve(NO); // Si ça fonctionne => pas en privé
                    } catch (e) {
                        resolve(YES); // Si ça échoue => probablement en privé
                    }
                } catch (e) {
                    resolve(UNKNOWN);
                }
                return;
            }

            // Pour Chrome et autres
            if ('storage' in navigator && 'estimate' in navigator.storage) {
                navigator.storage.estimate().then(function(estimate) {
                    // En navigation privée Chrome, la quota est généralement limité à 120MB
                    if (estimate.quota < 120000000) {
                        resolve(YES);
                    } else {
                        resolve(NO);
                    }
                });
                return;
            }

            // Méthode de secours - test simple
            try {
                localStorage.setItem('test_private', '1');
                localStorage.removeItem('test_private');
                if (!navigator.cookieEnabled) {
                    resolve(YES);
                } else {
                    resolve(NO);
                }
            } catch (e) {
                resolve(YES);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier le mode de navigation
        detectPrivateMode().then(function(isPrivate) {
            if (isPrivate) {
                // En navigation privée, masquer le formulaire et afficher l'avertissement
                showPrivacyWarning();

                // Loguer pour débogage
                console.log("Navigation privée détectée, accès au formulaire bloqué");
            } else {
                // En navigation normale, vérifier la participation
                try {
                    checkForExistingParticipation();
                } catch (e) {
                    console.log('Erreur lors de la vérification de participation:', e);
                }
            }
        });
    });

    // Fonction pour afficher l'avertissement de navigation privée
    function showPrivacyWarning() {
        // Masquer tout le contenu principal
        const mainContent = document.querySelector('.card');
        if (mainContent) {
            mainContent.style.display = 'none';
        }

        // Masquer les modales et autres éléments
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });

        // Afficher l'avertissement
        document.getElementById('privacyWarningOverlay').classList.remove('hidden');
    }

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
</script>

<!-- Désactivation du masque de saisie téléphonique qui causait des problèmes -->
