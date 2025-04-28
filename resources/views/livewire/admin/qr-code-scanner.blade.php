<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-primary-600 dark:text-primary-500">Scanner un QR Code</h2>
    
    <div class="flex flex-col space-y-6">
        <!-- Messages flash -->
        @if (session()->has('success'))
            <div class="p-4 mb-4 text-sm text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/20 rounded-lg" role="alert">
                <span class="font-medium">Succès!</span> {{ session('success') }}
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="p-4 mb-4 text-sm text-red-700 dark:text-red-400 bg-red-100 dark:bg-red-900/20 rounded-lg" role="alert">
                <span class="font-medium">Erreur!</span> {{ session('error') }}
            </div>
        @endif
        
        @if (session()->has('warning'))
            <div class="p-4 mb-4 text-sm text-yellow-700 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg" role="alert">
                <span class="font-medium">Attention!</span> {{ session('warning') }}
            </div>
        @endif
        
        <!-- Options de scan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Scanner avec la caméra -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                <h3 class="text-lg font-medium mb-4 dark:text-white">Scanner avec la caméra</h3>
                
                <div id="qr-reader" class="w-full"></div>
                <div id="qr-reader-results" class="mt-2 text-sm dark:text-gray-300"></div>
            </div>
            
            <!-- Formulaire de saisie manuelle -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                <h3 class="text-lg font-medium mb-4 dark:text-white">Saisie manuelle</h3>
                
                <form wire:submit.prevent="scanQrCode" class="space-y-4">
                    <div class="mb-4">
                        <label for="code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">Code QR</label>
                        <input type="text" id="code" wire:model="code" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-200 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" placeholder="Saisissez le code QR manuellement" required>
                        @error('code') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="submit" class="text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:focus:ring-primary-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Valider le code
                        </button>
                        
                        <button type="button" wire:click="resetScanner" class="text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:focus:ring-gray-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center ml-2">
                            Réinitialiser
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Résultat du scan -->
        @if($status == 'success')
        <div class="mt-6 p-4 border rounded-lg bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
            <h3 class="text-lg font-semibold text-green-800 dark:text-green-400">{{ $result }}</h3>
            
            @if($scannedQrCode)
            <div class="mt-4 space-y-2 text-sm dark:text-gray-300">
                <p><span class="font-medium dark:text-white">Participant :</span> {{ $scannedQrCode->entry->participant->first_name }} {{ $scannedQrCode->entry->participant->last_name }}</p>
                <p><span class="font-medium dark:text-white">Email :</span> {{ $scannedQrCode->entry->participant->email }}</p>
                <p><span class="font-medium dark:text-white">Téléphone :</span> {{ $scannedQrCode->entry->participant->phone }}</p>
                <p><span class="font-medium dark:text-white">Concours :</span> {{ $scannedQrCode->entry->contest->name }}</p>
                @if($scannedQrCode->entry->prize)
                <p><span class="font-medium dark:text-white">Lot gagné :</span> {{ $scannedQrCode->entry->prize->name }}</p>
                <p><span class="font-medium dark:text-white">Valeur :</span> {{ \App\Helpers\FormatHelper::fcfa($scannedQrCode->entry->prize->value) }}</p>
                @endif
                <p><span class="font-medium dark:text-white">Statut :</span> <span class="px-2 py-1 text-xs font-semibold text-white bg-green-500 rounded-full">Réclamé</span></p>
                <p><span class="font-medium dark:text-white">Réclamé le :</span> {{ $scannedQrCode->entry->claimed_at->format('d/m/Y à H:i') }}</p>
            </div>
            @endif
        </div>
        @elseif($status == 'error')
        <div class="mt-6 p-4 border rounded-lg bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800">
            <h3 class="text-lg font-semibold text-red-800 dark:text-red-400">{{ $result }}</h3>
        </div>
        @elseif($status == 'warning')
        <div class="mt-6 p-4 border rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800">
            <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-400">{{ $result }}</h3>
            
            @if($scannedQrCode)
            <div class="mt-4 space-y-2 text-sm dark:text-gray-300">
                <p><span class="font-medium dark:text-white">Participant :</span> {{ $scannedQrCode->entry->participant->first_name }} {{ $scannedQrCode->entry->participant->last_name }}</p>
                <p><span class="font-medium dark:text-white">Email :</span> {{ $scannedQrCode->entry->participant->email }}</p>
                <p><span class="font-medium dark:text-white">Téléphone :</span> {{ $scannedQrCode->entry->participant->phone }}</p>
                <p><span class="font-medium dark:text-white">Concours :</span> {{ $scannedQrCode->entry->contest->name }}</p>
                @if($scannedQrCode->entry->prize)
                <p><span class="font-medium dark:text-white">Lot gagné :</span> {{ $scannedQrCode->entry->prize->name }}</p>
                <p><span class="font-medium dark:text-white">Valeur :</span> {{ \App\Helpers\FormatHelper::fcfa($scannedQrCode->entry->prize->value) }}</p>
                @endif
                <p><span class="font-medium dark:text-white">Statut :</span> <span class="px-2 py-1 text-xs font-semibold text-white bg-yellow-500 rounded-full">Déjà réclamé</span></p>
                <p><span class="font-medium dark:text-white">Scanné le :</span> {{ $scannedQrCode->scanned_at->format('d/m/Y à H:i') }}</p>
                @if($scannedQrCode->scanned_by)
                <p><span class="font-medium dark:text-white">Scanné par :</span> ID: {{ $scannedQrCode->scanned_by }}</p>
                @endif
            </div>
            @endif
        </div>
        @endif
    </div>
    
    <!-- Scripts pour le scanner QR -->
    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.4/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function () {
            let html5QrCode;
            let qrCodeSuccessCallback;
            let scannerActive = false;
            const resultElement = document.getElementById('qr-reader-results');
            
            function showCameraError(message) {
                const qrReader = document.getElementById('qr-reader');
                if (qrReader) {
                    qrReader.innerHTML = `
                        <div class="p-4 text-center border border-red-200 dark:border-red-800 rounded-lg bg-red-50 dark:bg-red-900/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-red-500 dark:text-red-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="text-md font-medium text-red-800 dark:text-red-400 mb-2">Accès à la caméra impossible</h3>
                            <p class="text-sm text-red-700 dark:text-red-300 mb-3">${message}</p>
                            <button type="button" id="retryCamera" class="text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:focus:ring-primary-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                Réessayer
                            </button>
                        </div>
                    `;
                    
                    // Add retry button functionality
                    document.getElementById('retryCamera').addEventListener('click', function() {
                        initQrScanner();
                    });
                }
                
                if (resultElement) {
                    resultElement.innerText = "La caméra n'est pas disponible. Veuillez utiliser la saisie manuelle.";
                }
            }
            
            function initQrScanner() {
                try {
                    const qrBoxSize = Math.floor(Math.min(window.innerWidth, 500) * 0.8);
                    
                    if (!html5QrCode) {
                        html5QrCode = new Html5Qrcode("qr-reader");
                    } else if (scannerActive) {
                        // If scanner is active, stop it first
                        try {
                            html5QrCode.stop().then(() => {
                                startScanner(qrBoxSize);
                            }).catch(error => {
                                console.warn("Could not stop scanner, probably not running:", error);
                                startScanner(qrBoxSize);
                            });
                            return;
                        } catch (error) {
                            console.warn("Error stopping scanner:", error);
                            // Continue to start scanner
                        }
                    }
                    
                    startScanner(qrBoxSize);
                } catch (error) {
                    console.error("Error initializing scanner:", error);
                    showCameraError("Une erreur s'est produite lors de l'initialisation du scanner.");
                }
            }
            
            function startScanner(qrBoxSize) {
                if (!html5QrCode) return;
                
                qrCodeSuccessCallback = (decodedText) => {
                    try {
                        // Mark scanner as not active while processing
                        scannerActive = false;
                        
                        // Stop scanning
                        html5QrCode.stop().catch(error => {
                            console.warn("Error stopping scanner after success:", error);
                        });
                        
                        // Update the input field with the scanned value
                        @this.set('code', decodedText);
                        
                        // Submit the form
                        @this.scanQrCode();
                        
                        // Show message
                        if (resultElement) {
                            resultElement.innerText = "QR Code détecté: " + decodedText;
                        }
                    } catch (error) {
                        console.error("Error processing scanned QR code:", error);
                        if (resultElement) {
                            resultElement.innerText = "Erreur lors du traitement du QR code.";
                        }
                    }
                };
                
                const config = {
                    fps: 10,
                    qrbox: qrBoxSize,
                    aspectRatio: 1.0
                };
                
                html5QrCode.start(
                    { facingMode: "environment" }, 
                    config, 
                    qrCodeSuccessCallback,
                    (errorMessage) => {
                        // Error callback - silent for normal operation
                    }
                ).then(() => {
                    scannerActive = true;
                    if (resultElement) {
                        resultElement.innerText = "Scanner actif. Placez un QR code devant la caméra.";
                    }
                }).catch((error) => {
                    scannerActive = false;
                    console.error("Error starting scanner:", error);
                    
                    let errorMessage = "Impossible d'accéder à la caméra.";
                    
                    if (error.name === "NotFoundError") {
                        errorMessage = "Aucune caméra n'a été trouvée sur cet appareil.";
                    } else if (error.name === "NotAllowedError") {
                        errorMessage = "L'accès à la caméra a été refusé. Veuillez autoriser l'accès dans les paramètres de votre navigateur.";
                    } else if (error.name === "NotReadableError") {
                        errorMessage = "La caméra est peut-être utilisée par une autre application.";
                    }
                    
                    showCameraError(errorMessage);
                });
            }
            
            // Initialize QR scanner after a short delay
            setTimeout(() => {
                initQrScanner();
            }, 500);
            
            // Reset the QR scanner when the form is reset
            Livewire.on('scanComplete', async () => {
                try {
                    if (html5QrCode && scannerActive) {
                        await html5QrCode.stop();
                        scannerActive = false;
                        setTimeout(() => {
                            initQrScanner();
                        }, 1000);
                    } else {
                        setTimeout(() => {
                            initQrScanner();
                        }, 1000);
                    }
                } catch (error) {
                    console.warn("Error during scanner reset:", error);
                    setTimeout(() => {
                        initQrScanner();
                    }, 1000);
                }
            });
            
            // Clean up when component is removed
            document.addEventListener('livewire:navigated', () => {
                try {
                    if (html5QrCode && scannerActive) {
                        html5QrCode.stop().catch(error => {
                            console.warn("Error stopping scanner during navigation:", error);
                        });
                        scannerActive = false;
                    }
                } catch (error) {
                    console.warn("Error during cleanup:", error);
                }
            });
        });
    </script>
    @endpush
</div>
