<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\QrCode as QrCodeModel;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeGenerator extends Component
{
    public $entryId;
    public $qrCodeUrl = null;
    public $entry = null;

    public function mount($entryId)
    {
        $this->entryId = $entryId;
        $this->loadEntry();
    }

    public function loadEntry()
    {
        $this->entry = Entry::with(['participant', 'prize', 'qrCode'])->find($this->entryId);
        
        if ($this->entry && $this->entry->qrCode) {
            $this->qrCodeUrl = \Storage::url('qrcodes/' . $this->entry->qrCode->code . '.png');
        }
    }

    public function regenerateQrCode()
    {
        if (!$this->entry) {
            return;
        }

        try {
            // Générer un nouveau code QR
            $qrCodeText = "DINOR-" . $this->entry->id . "-" . time();
            $qrCodeImage = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($qrCodeText);
            
            $qrCodePath = 'qrcodes/' . $qrCodeText . '.png';
            \Storage::disk('public')->put($qrCodePath, $qrCodeImage);
            
            // Mettre à jour ou créer l'enregistrement QR code
            if ($this->entry->qrCode) {
                $this->entry->qrCode->update([
                    'code' => $qrCodeText,
                    'scanned' => false,
                    'scanned_at' => null,
                    'scanned_by' => null,
                ]);
            } else {
                QrCodeModel::create([
                    'entry_id' => $this->entry->id,
                    'code' => $qrCodeText,
                    'scanned' => false,
                ]);
            }
            
            $this->entry->qr_code = $qrCodePath;
            $this->entry->save();
            
            $this->qrCodeUrl = \Storage::url($qrCodePath);
            
            $this->dispatch('qrCodeRegenerated');
            
        } catch (\Exception $e) {
            $this->dispatch('error', ['message' => 'Erreur lors de la génération du code QR: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.qr-code-generator');
    }
}
