@extends('layouts.app')

@section('content')
<div class="admin-scan-qr-code">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Scanner un code QR</h1>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Scannez un code QR pour valider un prix</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
                    </div>
                    
                    <div class="manual-entry mt-4">
                        <h5>Ou entrez le code manuellement</h5>
                        <div class="input-group mb-3">
                            <input type="text" id="qr-code-input" class="form-control" placeholder="Entrez le code QR">
                            <button class="btn btn-primary" id="verify-code-btn">Vérifier</button>
                        </div>
                    </div>
                    
                    <div id="result-container" class="mt-4" style="display: none;">
                        <div class="alert" id="result-alert">
                            <h5 id="result-title"></h5>
                            <div id="result-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.4/html5-qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const html5QrCode = new Html5Qrcode("qr-reader");
        const qrCodeInput = document.getElementById('qr-code-input');
        const verifyCodeBtn = document.getElementById('verify-code-btn');
        const resultContainer = document.getElementById('result-container');
        const resultAlert = document.getElementById('result-alert');
        const resultTitle = document.getElementById('result-title');
        const resultContent = document.getElementById('result-content');
        
        // Configuration pour le scanner
        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            // Arrêter le scan
            html5QrCode.stop().then(() => {
                console.log('QR Code scanning stopped.');
                verifyQrCode(decodedText);
            }).catch((err) => {
                console.error('Failed to stop QR Code scanning.', err);
            });
        };
        
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        // Démarrer le scanner
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
        
        // Vérification manuelle du code
        verifyCodeBtn.addEventListener('click', function() {
            const code = qrCodeInput.value.trim();
            if (code) {
                verifyQrCode(code);
            }
        });
        
        // Fonction pour vérifier le code QR
        function verifyQrCode(code) {
            // Envoyer une requête AJAX pour vérifier le code
            fetch('{{ route("admin.verify-qr-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ code: code })
            })
            .then(response => response.json())
            .then(data => {
                resultContainer.style.display = 'block';
                
                if (data.status === 'success') {
                    resultAlert.className = 'alert alert-success';
                    resultTitle.textContent = 'Code QR valide';
                    
                    let content = `
                        <p><strong>Participant:</strong> ${data.data.participant}</p>
                        <p><strong>Prix:</strong> ${data.data.prize}</p>
                        <p><strong>Date de gain:</strong> ${data.data.won_date}</p>
                        <p><strong>Statut:</strong> Prix validé avec succès</p>
                    `;
                    
                    resultContent.innerHTML = content;
                } else {
                    resultAlert.className = 'alert alert-danger';
                    resultTitle.textContent = 'Erreur';
                    resultContent.textContent = data.message;
                }
                
                // Réinitialiser le champ de saisie
                qrCodeInput.value = '';
                
                // Redémarrer le scanner après 5 secondes
                setTimeout(() => {
                    html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
                        .catch(err => console.error('Failed to restart QR Code scanning.', err));
                }, 5000);
            })
            .catch(error => {
                console.error('Error:', error);
                resultContainer.style.display = 'block';
                resultAlert.className = 'alert alert-danger';
                resultTitle.textContent = 'Erreur';
                resultContent.textContent = 'Une erreur est survenue lors de la vérification du code QR.';
            });
        }
    });
</script>
@endpush
@endsection
