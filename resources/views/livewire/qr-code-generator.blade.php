<div class="qr-code-generator">
    @if ($entry)
        <div class="card">
            <div class="card-header">
                <h3>Résultat pour {{ $entry->participant->full_name }}</h3>
            </div>
            <div class="card-body text-center">
                @if ($entry->result === 'win' && $qrCodeUrl)
                    <div class="qr-code-container mb-4">
                        <img src="{{ $qrCodeUrl }}" alt="QR Code" class="qr-code">
                    </div>
                    <div class="alert alert-info mb-3">
                        <b>Scannez le QR code ci-dessous pour réclamer votre prix.<br>Conservez cette page précieusement, elle fait office de justificatifs lors du retrait.</b>
                    </div>
                    <div class="prize-info mb-4">
                        <h4>Prix gagné: {{ $entry->prize->name }}</h4>
                        <p>Date de gain: {{ $entry->won_date->format('d/m/Y H:i') }}</p>
                        <p>Statut: {{ $entry->qrCode && $entry->qrCode->scanned ? 'Réclamé' : 'Non réclamé' }}</p>
                        @if ($entry->qrCode && $entry->qrCode->scanned)
                            <p>Réclamé le: {{ $entry->qrCode->scanned_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                @elseif ($entry->result === 'lose')
                    <div class="alert alert-warning">
                        <h4>Pas de chance cette fois-ci.</h4>
                        <p>Vous n'avez pas gagné à cette participation.</p>
                        <p>Merci d'avoir participé !</p>
                    </div>
                @else
                    <div class="alert alert-warning">
                        Aucun résultat n'est associé à cette participation.
                    </div>
                @endif
                
                @if ($entry->result === 'win' && !$qrCodeUrl)
                    <button class="btn btn-primary" wire:click="regenerateQrCode" wire:loading.attr="disabled">
                        <span wire:loading wire:target="regenerateQrCode">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                        Générer le code QR
                    </button>
                @endif
                
                <a href="{{ route('home') }}" class="btn btn-secondary ml-2">
                    Retour à l'accueil
                </a>
            </div>
        </div>
    @else
        <div class="alert alert-danger">
            Participation non trouvée.
        </div>
    @endif
</div>

<style>
    .qr-code-generator {
        max-width: 600px;
        margin: 0 auto;
    }
    
    .qr-code-container {
        display: flex;
        justify-content: center;
        padding: 1rem;
    }
    
    .qr-code {
        max-width: 250px;
        border: 1px solid #ddd;
        padding: 0.5rem;
        background: white;
    }
    
    .prize-info {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 5px;
    }
</style>
