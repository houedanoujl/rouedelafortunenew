<div class="registration-form">
    <div class="card">
        <div class="card-header">
            <h2>Inscription</h2>
        </div>
        <div class="card-body">
            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if ($isBlocked)
                <div class="alert alert-warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> Participation limitée</h4>
                    <p>Vous avez déjà participé et vous n'avez pas gagné de prix.</p>
                    <p>Vous pourrez rejouer à partir du: <strong>{{ $limitedUntil }}</strong></p>
                </div>
            @elseif ($alreadyParticipated && $existingEntry)
                <div class="alert alert-info">
                    <h4><i class="fas fa-info-circle"></i> Vous avez déjà participé</h4>
                    <p>Vous avez déjà participé à ce concours avec ce numéro de téléphone ou cette adresse email.</p>
                    <p>Vous ne pouvez participer qu'une seule fois par concours.</p>
                    <div class="mt-3">
                        <a href="{{ route('wheel.show', ['entry' => $existingEntry->id]) }}" class="btn btn-primary">
                            Voir ma participation
                        </a>
                    </div>
                </div>
            @else
                <form wire:submit.prevent="register">
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
                        <input type="tel" class="form-control" id="phone" wire:model="phone" required>
                        @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">{{ __('registration.fields.email.label') }}</label>
                        <input type="email" class="form-control" id="email" wire:model="email">
                        @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled">
                            <span wire:loading wire:target="register">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                            {{ __('registration.submit') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
