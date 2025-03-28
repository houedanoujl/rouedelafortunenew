<?php

namespace App\Livewire\Admin;

use App\Models\Prize;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PrizesManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $name = '';
    public $description = '';
    public $type = 'product';
    public $value = 0;
    public $stock = 0;
    public $image = null;
    
    public $editingPrizeId = null;
    public $isEditing = false;
    public $confirmingPrizeDeletion = false;
    public $prizeIdToDelete = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'required|in:product,voucher,service',
        'value' => 'nullable|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'image' => 'nullable|image|max:1024', // max 1MB
    ];

    protected $messages = [
        'name.required' => 'Le nom du prix est obligatoire.',
        'type.required' => 'Le type de prix est obligatoire.',
        'type.in' => 'Le type de prix doit être produit, bon d\'achat ou service.',
        'value.numeric' => 'La valeur doit être un nombre.',
        'value.min' => 'La valeur ne peut pas être négative.',
        'stock.required' => 'Le stock est obligatoire.',
        'stock.integer' => 'Le stock doit être un nombre entier.',
        'stock.min' => 'Le stock ne peut pas être négatif.',
        'image.image' => 'Le fichier doit être une image.',
        'image.max' => 'L\'image ne doit pas dépasser 1MB.',
    ];

    public function resetForm()
    {
        $this->reset(['name', 'description', 'type', 'value', 'stock', 'image', 'editingPrizeId', 'isEditing']);
        $this->resetValidation();
    }

    public function createPrize()
    {
        $this->validate();

        $prizeData = [
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'stock' => $this->stock,
        ];

        if ($this->image) {
            $imagePath = $this->image->store('prizes', 'public');
            $prizeData['image_url'] = $imagePath;
        }

        Prize::create($prizeData);

        $this->resetForm();
        $this->dispatch('prizeCreated');
    }

    public function editPrize($prizeId)
    {
        $this->resetForm();
        $this->editingPrizeId = $prizeId;
        $this->isEditing = true;

        $prize = Prize::findOrFail($prizeId);
        $this->name = $prize->name;
        $this->description = $prize->description;
        $this->type = $prize->type;
        $this->value = $prize->value;
        $this->stock = $prize->stock;
    }

    public function updatePrize()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:product,voucher,service',
            'value' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:1024', // max 1MB
        ]);

        $prize = Prize::findOrFail($this->editingPrizeId);

        $prizeData = [
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'stock' => $this->stock,
        ];

        if ($this->image) {
            $imagePath = $this->image->store('prizes', 'public');
            $prizeData['image_url'] = $imagePath;
        }

        $prize->update($prizeData);

        $this->resetForm();
        $this->dispatch('prizeUpdated');
    }

    public function confirmPrizeDeletion($prizeId)
    {
        $this->confirmingPrizeDeletion = true;
        $this->prizeIdToDelete = $prizeId;
    }

    public function deletePrize()
    {
        $prize = Prize::findOrFail($this->prizeIdToDelete);
        $prize->delete();

        $this->confirmingPrizeDeletion = false;
        $this->prizeIdToDelete = null;
        $this->dispatch('prizeDeleted');
    }

    public function cancelPrizeDeletion()
    {
        $this->confirmingPrizeDeletion = false;
        $this->prizeIdToDelete = null;
    }

    public function render()
    {
        $prizes = Prize::orderBy('created_at', 'desc')->paginate(10);
        return view('livewire.admin.prizes-manager', [
            'prizes' => $prizes
        ]);
    }
}
