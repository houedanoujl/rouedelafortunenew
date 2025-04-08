<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\QrCode;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

class QrCodeScanner extends Component
{
    
    public $code = '';
    public $result = null;
    public $status = null;
    public $scannedQrCode = null;
    
    protected $rules = [
        'code' => 'required|string'
    ];
    
    /**
     * Process QR code scanning
     */
    public function scanQrCode()
    {
        $this->validate();
        
        // Reset previous results
        $this->reset(['result', 'status', 'scannedQrCode']);
        
        // Find QR code in the database
        $qrCode = QrCode::where('code', $this->code)->first();
        
        if (!$qrCode) {
            $this->status = 'error';
            $this->result = 'QR code invalide ou inexistant';
            session()->flash('error', 'QR code invalide ou inexistant');
            return;
        }
        
        // Check if QR code is already scanned
        if ($qrCode->scanned) {
            $this->status = 'warning';
            $this->result = 'Ce QR code a déjà été utilisé le ' . $qrCode->scanned_at->format('d/m/Y à H:i');
            $this->scannedQrCode = $qrCode;
            session()->flash('warning', 'Ce QR code a déjà été utilisé le ' . $qrCode->scanned_at->format('d/m/Y à H:i'));
            return;
        }
        
        // Update QR code status
        $qrCode->scanned = true;
        $qrCode->scanned_at = now();
        $qrCode->scanned_by = Auth::id();
        $qrCode->save();
        
        // Update Entry status
        $entry = $qrCode->entry;
        if ($entry) {
            $entry->claimed = true;
            $entry->claimed_at = now();
            $entry->save();
        }
        
        $this->status = 'success';
        $this->result = 'QR code validé avec succès';
        $this->scannedQrCode = $qrCode;
        
        session()->flash('success', 'QR code validé avec succès');
        
        // Émettre un événement pour réinitialiser le scanner après un scan réussi
        $this->dispatch('scanComplete');
    }
    
    /**
     * Reset the scanner
     */
    public function resetScanner()
    {
        $this->reset(['code', 'result', 'status', 'scannedQrCode']);
        
        // Émettre un événement pour réinitialiser le scanner de caméra
        $this->dispatch('scanComplete');
    }
    
    public function render()
    {
        return view('livewire.admin.qr-code-scanner');
    }
}
