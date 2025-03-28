<div class="prizes-manager">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{ $isEditing ? 'Modifier le prix' : 'Ajouter un nouveau prix' }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="{{ $isEditing ? 'updatePrize' : 'createPrize' }}">
                        <div class="form-group mb-3">
                            <label for="name">Nom</label>
                            <input type="text" class="form-control" id="name" wire:model="name" required>
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="type">Type</label>
                            <select class="form-control" id="type" wire:model="type" required>
                                <option value="product">Produit</option>
                                <option value="voucher">Bon d'achat</option>
                                <option value="service">Service</option>
                            </select>
                            @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="value">Valeur (€)</label>
                            <input type="number" class="form-control" id="value" wire:model="value" min="0" step="0.01">
                            @error('value') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="stock">Stock</label>
                            <input type="number" class="form-control" id="stock" wire:model="stock" min="0" required>
                            @error('stock') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="image">Image</label>
                            <input type="file" class="form-control" id="image" wire:model="image" accept="image/*">
                            @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                            
                            @if ($image)
                                <div class="mt-2">
                                    <p>Aperçu:</p>
                                    <img src="{{ $image->temporaryUrl() }}" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            @endif
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" wire:model="description" rows="3"></textarea>
                            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading wire:target="{{ $isEditing ? 'updatePrize' : 'createPrize' }}">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                                {{ $isEditing ? 'Mettre à jour' : 'Ajouter' }}
                            </button>
                            
                            @if ($isEditing)
                                <button type="button" class="btn btn-secondary" wire:click="resetForm">
                                    Annuler
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Liste des prix</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Valeur</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($prizes as $prize)
                                    <tr>
                                        <td>
                                            @if ($prize->image_url)
                                                <img src="{{ Storage::url($prize->image_url) }}" alt="{{ $prize->name }}" class="img-thumbnail" style="max-height: 50px;">
                                            @else
                                                <span class="badge bg-secondary">Pas d'image</span>
                                            @endif
                                        </td>
                                        <td>{{ $prize->name }}</td>
                                        <td>
                                            @if ($prize->type === 'product')
                                                <span class="badge bg-success">Produit</span>
                                            @elseif ($prize->type === 'voucher')
                                                <span class="badge bg-warning">Bon d'achat</span>
                                            @elseif ($prize->type === 'service')
                                                <span class="badge bg-info">Service</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($prize->value, 2, ',', ' ') }} €</td>
                                        <td>{{ $prize->stock }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" wire:click="editPrize({{ $prize->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" wire:click="confirmPrizeDeletion({{ $prize->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun prix disponible</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $prizes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    @if ($confirmingPrizeDeletion)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmer la suppression</h5>
                        <button type="button" class="btn-close" wire:click="cancelPrizeDeletion"></button>
                    </div>
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer ce prix ? Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelPrizeDeletion">Annuler</button>
                        <button type="button" class="btn btn-danger" wire:click="deletePrize">Supprimer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
