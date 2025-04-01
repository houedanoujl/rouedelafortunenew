@php
    // Générer l'URL du QR code
    $qrCodeUrl = url('/qr/' . $getRecord()->code);
@endphp

<div class="flex items-center justify-center">
    <div class="qr-code-container w-40 h-40">
        @if($getRecord()->code)
            <div id="qrcode-view-{{ $getRecord()->id }}" class="w-full h-full"></div>
            <div class="mt-2 text-center text-xs">
                <a href="{{ $qrCodeUrl }}" target="_blank" class="text-primary-600 hover:underline">
                    {{ $getRecord()->code }}
                </a>
            </div>
            <div class="mt-2 text-center">
                <button 
                    id="download-qr-{{ $getRecord()->id }}"
                    class="px-3 py-1 bg-primary-500 text-white rounded-md text-xs hover:bg-primary-600 transition-colors"
                    onclick="downloadQRCode('qrcode-view-{{ $getRecord()->id }}', '{{ $getRecord()->code }}')"
                >
                    Télécharger en JPG
                </button>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Vérifier si la bibliothèque QR est chargée
                    if (typeof qrcode === 'function') {
                        generateQRCode();
                    } else {
                        // Charger la bibliothèque QR si nécessaire
                        var script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js';
                        script.onload = generateQRCode;
                        document.head.appendChild(script);
                    }
                    
                    // Charger html2canvas pour la fonctionnalité de téléchargement
                    if (typeof html2canvas !== 'function') {
                        var html2canvasScript = document.createElement('script');
                        html2canvasScript.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
                        document.head.appendChild(html2canvasScript);
                    }
                    
                    function generateQRCode() {
                        const qr = qrcode(0, 'L');
                        qr.addData('{{ $qrCodeUrl }}');
                        qr.make();
                        const qrContainer = document.getElementById('qrcode-view-{{ $getRecord()->id }}');
                        qrContainer.innerHTML = qr.createImgTag(8); // Plus grande taille pour la vue détaillée
                    }
                });
                
                // Fonction pour télécharger le QR code en JPG
                function downloadQRCode(containerId, qrCodeText) {
                    if (typeof html2canvas !== 'function') {
                        alert('Chargement des outils nécessaires, veuillez réessayer dans quelques secondes...');
                        return;
                    }
                    
                    const container = document.getElementById(containerId);
                    html2canvas(container).then(canvas => {
                        // Convertir en JPG
                        const imgData = canvas.toDataURL('image/jpeg', 0.8);
                        
                        // Créer un lien de téléchargement
                        const link = document.createElement('a');
                        link.href = imgData;
                        link.download = 'qrcode_' + qrCodeText + '.jpg';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    });
                }
            </script>
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                Aucun code QR disponible
            </div>
        @endif
    </div>
</div>

<style>
    /* Assurer que l'image QR s'adapte bien au conteneur */
    #qrcode-view-{{ $getRecord()->id }} img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
</style>
