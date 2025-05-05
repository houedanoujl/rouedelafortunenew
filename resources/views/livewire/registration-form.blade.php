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

    /* Styles pour le popup de vérification d'âge */
    .age-verification-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        font-family: 'EB Garamond', serif;
    }
    
    .age-verification-overlay.hidden {
        display: none !important;
    }
    
    .age-verification-popup {
        background-color: white;
        padding: 2rem;
        border-radius: 10px;
        text-align: center;
        max-width: 90%;
        width: 400px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
    }
    
    .age-verification-popup h2 {
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
        color: var(--honolulu-blue);
        font-family: 'EB Garamond', serif;
        font-weight: 600;
    }
    
    .age-verification-popup p {
        font-size: 1.4rem;
        margin-bottom: 2rem;
        color: #333;
        font-family: 'EB Garamond', serif;
    }
    
    .age-verification-buttons {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
    }
    
    .age-verification-buttons button {
        padding: 0.8rem 2.5rem;
        border: none;
        border-radius: 5px;
        font-size: 1.2rem;
        cursor: pointer;
        font-family: 'EB Garamond', serif;
        font-weight: 600;
        transition: transform 0.1s, background-color 0.2s;
    }
    
    .age-verification-buttons button:hover {
        transform: translateY(-2px);
    }
    
    .btn-age-yes {
        background-color: #28a745;
        color: white;
    }
    
    .btn-age-yes:hover {
        background-color: #218838;
    }
    
    .btn-age-no {
        background-color: #dc3545;
        color: white;
    }
    
    .btn-age-no:hover {
        background-color: #c82333;
    }
</style>

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
                            <input type="tel" class="form-control" id="phone" wire:model.lazy="phone" required maxlength="10" pattern="[0-9]{10}" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                            @if (!$isExistingParticipant)
                                <small class="form-text text-muted">Saisissez un numéro à 10 chiffres sans espaces ni indicatif. Ex: 0701234567</small>
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
                                J'accepte le <a href="#isidor" data-bs-toggle="modal" data-bs-target="#consentModal" style="color: red;" onclick="setTimeout(function() { window.scrollTo({top: document.getElementById('isidor').getBoundingClientRect().top + window.pageYOffset, behavior: 'smooth'}); }, 400);">recueil de consentement individuel</a> <span style="color: red;">*</span>
                            </label>
                        </div>
                        @error('consentement') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <!-- Case à cocher pour le règlement du jeu -->
                    <div class="form-group mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="reglementCheckbox" wire:model="reglement" required>
                            <label class="form-check-label" for="reglementCheckbox">
                                J'ai lu et j'accepte le <a href="#" data-bs-toggle="modal" data-bs-target="#reglementModal" style="color: red;">règlement du jeu</a>
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
        <div class="modal-dialog modal-lg modal-dialog-centered" style="margin: 10px auto; max-width: 95%;">
            <div class="modal-content" style="max-height: 90vh; margin: auto; position: relative; top: 0;">
                <div class="modal-header">
                    <h5 class="modal-title" id="consentModalLabel">{{ $modalContents['consent']['title'] ?? 'Fiche de recueil de consentement' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto; padding: 15px; text-align: center; font-weight: normal;">
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

    <!-- UNIQUE ID pour éviter les conflits -->
    <div id="uniqueAgePopup_2023152755" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; overflow:auto; justify-content:center; align-items:center;">
        <div style="background:white; width:90%; max-width:400px; padding:30px; border-radius:10px; text-align:center; position:relative;">
            <h2 style="color:#0079B2; font-size:24px; margin-bottom:15px;">Vérification de l'âge</h2>
            <p style="font-size:18px; margin-bottom:20px;">Êtes vous agé(e) d'au moins 18 ans ?</p>
            <div style="display:flex; gap:15px; justify-content:center;">
                <button onclick="ageVerifier2023152755.yes()" style="background:#0079B2; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-size:16px;">Oui</button>
                <button onclick="ageVerifier2023152755.no()" style="background:#D03A2C; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-size:16px;">Non</button>
            </div>
        </div>
    </div>

    <!-- Script minimaliste sans conflits potentiels -->
    <script>
    // Objet namespace unique pour éviter les conflits
    var ageVerifier2023152755 = {
        // Référence au popup
        popupId: 'uniqueAgePopup_2023152755',
        
        // Montrer le popup
        show: function() {
            var popup = document.getElementById(this.popupId);
            if (popup) popup.style.display = 'flex';
        },
        
        // Cacher le popup
        hide: function() {
            var popup = document.getElementById(this.popupId);
            if (popup) popup.style.display = 'none';
            
            // Libérer les ressources et supprimer le popup du DOM complètement
            setTimeout(function() {
                var popupElement = document.getElementById('uniqueAgePopup_2023152755');
                if (popupElement && popupElement.parentNode) {
                    popupElement.parentNode.removeChild(popupElement);
                }
            }, 100);
        },
        
        // Action "Oui"
        yes: function() {
            this.hide();
            window.sessionStorage.setItem('age_verified_2023152755', true);
        },
        
        // Action "Non"
        no: function() {
            window.location.href = 'https://www.google.com';
        },
        
        // Initialiser le popup (une seule fois par page)
        init: function() {
            // Vérifier si nous avons déjà affiché le popup dans cette session
            if (window.sessionStorage && !window.sessionStorage.getItem('age_verified_2023152755')) {
                this.show();
                
                // S'assurer que tous les liens et boutons fonctionnent correctement
                var allButtons = document.querySelectorAll('button');
                for (var i = 0; i < allButtons.length; i++) {
                    if (allButtons[i].id !== 'uniqueAgePopup_2023152755') {
                        allButtons[i].addEventListener('click', function(e) {
                            // Si le popup est visible, masquer le popup et permettre l'événement
                            var popup = document.getElementById('uniqueAgePopup_2023152755');
                            if (popup && popup.style.display === 'flex') {
                                ageVerifier2023152755.hide();
                            }
                        });
                    }
                }
            }
        }
    };
    
    // Exécuté une seule fois au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        ageVerifier2023152755.init();
    });
    </script>

    <!-- Script pour afficher les lots disponibles dans la console -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Récupérer les lots disponibles via une requête AJAX
        fetch('/api/prizes/available?contest_id={{ $contestId }}')
            .then(response => response.json())
            .then(data => {
                console.log('=== LOTS DISPONIBLES ===');
                console.log(data);
                console.table(data.prizes);
                console.log('=== RÉCAPITULATIF ===');
                console.log(`Total: ${data.total} lots disponibles`);
                console.log(`Concours actif: ${data.contest_name}`);
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des lots:', error);
            });
    });
    </script>
</div>
