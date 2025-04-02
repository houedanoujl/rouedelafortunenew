<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>QR Code - {{ $qrCode->code }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        .header {
            margin-bottom: 30px;
        }
        .content {
            margin-bottom: 30px;
        }
        .qrcode {
            margin: 20px auto;
            width: 300px;
            height: 300px;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #666;
        }
        h1 {
            color: #FF4081;
        }
        .participant-info {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Roue de la Fortune - Votre QR Code</h1>
            <p>Félicitations pour votre gain !</p>
        </div>

        <div class="content">
            <div class="participant-info">
                <h3>Informations</h3>
                <p><strong>Nom :</strong> {{ $entry->participant->last_name }}</p>
                <p><strong>Prénom :</strong> {{ $entry->participant->first_name }}</p>
                <p><strong>Code unique :</strong> {{ $qrCode->code }}</p>
                <p><strong>Date :</strong> {{ $entry->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <div class="qrcode">
                <img src="data:image/png;base64,{{ $qrcodeImage }}" alt="QR Code">
            </div>

            <p>Scannez ce QR code pour récupérer votre lot.</p>
        </div>

        <div class="footer">
            <p>Roue de la Fortune &copy; {{ date('Y') }}. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
