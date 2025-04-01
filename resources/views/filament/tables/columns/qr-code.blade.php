@php
    // Générer l'URL du QR code
    $qrCodeUrl = url('/qr/' . $getRecord()->code);
@endphp

<div class="flex items-center justify-center">
    <div class="qr-code-container w-24 h-24">
        @if($getRecord()->code)
            <div id="qrcode-{{ $getRecord()->id }}" class="w-full h-full"></div>
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
                    
                    function generateQRCode() {
                        const qr = qrcode(0, 'L');
                        qr.addData('{{ $qrCodeUrl }}');
                        qr.make();
                        const qrContainer = document.getElementById('qrcode-{{ $getRecord()->id }}');
                        qrContainer.innerHTML = qr.createImgTag(4);
                    }
                });
            </script>
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                Aucun code
            </div>
        @endif
    </div>
</div>

<style>
    /* Assurer que l'image QR s'adapte bien au conteneur */
    #qrcode-{{ $getRecord()->id }} img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
</style>
