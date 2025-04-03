<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Roue de la Fortune') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS and Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        :root {
            /* Palette de couleurs originale avec rouge vif */
            --red-cmyk: #e3201cff;
            --golden-brown: #965d0bff;
            --field-drab: #544719ff;
            --light-coral: #eb8885ff;
            --cordovan: #8c4948ff;
            --black-bean: #4c1711ff;
            --lavender-blush: #f6e7e4ff;
            --primary-color: var(--red-cmyk);
        }
        
        body {
            background-color: var(--lavender-blush);
            color: var(--field-drab);
            font-family: 'EB Garamond', serif;
            letter-spacing: 0.02em;
            line-height: 1.8;
            /* Motif matelassé de type capitonné de luxe */
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiB2aWV3Qm94PSIwIDAgMjAwIDIwMCI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNmNmU3ZTQiIC8+PHBhdGggZD0iTTAgMCBRIDEwMCA1MCwgMjAwIDAgUyAxNTAgMTAwLCAyMDAgMjAwIFEgMTAwIDE1MCwgMCAxNDAgWiIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZWI4ODg1IiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLW9wYWNpdHk9IjAuMiIgLz48cGF0aCBkPSJNMCAxMDAgUSA1MCA1MCwgMTAwIDEwMCBTIDE1MCAxNTAsIDIwMCA1MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZWI4ODg1IiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLW9wYWNpdHk9IjAuMiIgLz48Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSIxMCIgZmlsbD0iI2ViODg4NTE1IiBzdHJva2U9IiNlYjg4ODUiIHN0cm9rZS13aWR0aD0iMC41IiBzdHJva2Utb3BhY2l0eT0iMC4zIiAvPjxjaXJjbGUgY3g9IjE1MCIgY3k9IjUwIiByPSIxMCIgZmlsbD0iI2ViODg4NTE1IiBzdHJva2U9IiNlYjg4ODUiIHN0cm9rZS13aWR0aD0iMC41IiBzdHJva2Utb3BhY2l0eT0iMC4zIiAvPjxjaXJjbGUgY3g9IjUwIiBjeT0iMTUwIiByPSIxMCIgZmlsbD0iI2ViODg4NTE1IiBzdHJva2U9IiNlYjg4ODUiIHN0cm9rZS13aWR0aD0iMC41IiBzdHJva2Utb3BhY2l0eT0iMC4zIiAvPjxjaXJjbGUgY3g9IjE1MCIgY3k9IjE1MCIgcj0iMTAiIGZpbGw9IiNlYjg4ODUxNSIgc3Ryb2tlPSIjZWI4ODg1IiBzdHJva2Utd2lkdGg9IjAuNSIgc3Ryb2tlLW9wYWNpdHk9IjAuMyIgLz48Y2lyY2xlIGN4PSIxMDAiIGN5PSIxMDAiIHI9IjEwIiBmaWxsPSIjZWI4ODg1MTUiIHN0cm9rZT0iI2ViODg4NSIgc3Ryb2tlLXdpZHRoPSIwLjUiIHN0cm9rZS1vcGFjaXR5PSIwLjMiIC8+PHBhdGggZD0iTTAgMCBRIDUwIDI1LCAxMDAgMCBTIDE1MCAyNSwgMjAwIDAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2ViODg4NSIgc3Ryb2tlLXdpZHRoPSIxIiBzdHJva2Utb3BhY2l0eT0iMC4yIiAvPjxwYXRoIGQ9Ik0wIDUwIFEgNTAgNzUsIDEwMCA1MCBTIDE1MCA3NSwgMjAwIDUwIiBmaWxsPSJub25lIiBzdHJva2U9IiNlYjg4ODUiIHN0cm9rZS13aWR0aD0iMSIgc3Ryb2tlLW9wYWNpdHk9IjAuMiIgLz48cGF0aCBkPSJNMCAxMDAgUSA1MCAxMjUsIDEwMCAxMDAgUyAxNTAgMTI1LCAyMDAgMTAwIiBmaWxsPSJub25lIiBzdHJva2U9IiNlYjg4ODUiIHN0cm9rZS13aWR0aD0iMSIgc3Ryb2tlLW9wYWNpdHk9IjAuMiIgLz48cGF0aCBkPSJNMCAxNTAgUSA1MCAxNzUsIDEwMCAxNTAgUyAxNTAgMTc1LCAyMDAgMTUwIiBmaWxsPSJub25lIiBzdHJva2U9IiNlYjg4ODUiIHN0cm9rZS13aWR0aD0iMSIgc3Ryb2tlLW9wYWNpdHk9IjAuMiIgLz48cGF0aCBkPSJNMCAwIFEgMjUgNTAsIDAgMTAwIFMgMjUgMTUwLCAwIDIwMCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZWI4ODg1IiBzdHJva2Utd2lkdGg9IjEiIHN0cm9rZS1vcGFjaXR5PSIwLjIiIC8+PHBhdGggZD0iTTUwIDAgUSA3NSA1MCwgNTAgMTAwIFMgNzUgMTUwLCA1MCAyMDAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2ViODg4NSIgc3Ryb2tlLXdpZHRoPSIxIiBzdHJva2Utb3BhY2l0eT0iMC4yIiAvPjxwYXRoIGQ9Ik0xMDAgMCBRIDEyNSA1MCwgMTAwIDEwMCBTIDEyNSAxNTAsIDEwMCAyMDAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2ViODg4NSIgc3Ryb2tlLXdpZHRoPSIxIiBzdHJva2Utb3BhY2l0eT0iMC4yIiAvPjxwYXRoIGQ9Ik0xNTAgMCBRIDE3NSA1MCwgMTUwIDEwMCBTIDE3NSAxNTAsIDE1MCAyMDAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2ViODg4NSIgc3Ryb2tlLXdpZHRoPSIxIiBzdHJva2Utb3BhY2l0eT0iMC4yIiAvPjwvc3ZnPg==');
            background-size: 200px 200px;
            position: relative;
        }
        
        /* Effet matelassé en 3D - capitons et coutures */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiB2aWV3Qm94PSIwIDAgMjAwIDIwMCI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9Im5vbmUiIC8+PGNpcmNsZSBjeD0iNTAiIGN5PSI1MCIgcj0iNSIgZmlsbD0iI2U2MjAxYyIgZmlsbC1vcGFjaXR5PSIwLjA4IiBzdHJva2U9IiNlNjIwMWMiIHN0cm9rZS13aWR0aD0iMC41IiBzdHJva2Utb3BhY2l0eT0iMC4yIi8+PGNpcmNsZSBjeD0iMTUwIiBjeT0iNTAiIHI9IjUiIGZpbGw9IiNlNjIwMWMiIGZpbGwtb3BhY2l0eT0iMC4wOCIgc3Ryb2tlPSIjZTYyMDFjIiBzdHJva2Utd2lkdGg9IjAuNSIgc3Ryb2tlLW9wYWNpdHk9IjAuMiIvPjxjaXJjbGUgY3g9IjUwIiBjeT0iMTUwIiByPSI1IiBmaWxsPSIjZTYyMDFjIiBmaWxsLW9wYWNpdHk9IjAuMDgiIHN0cm9rZT0iI2U2MjAxYyIgc3Ryb2tlLXdpZHRoPSIwLjUiIHN0cm9rZS1vcGFjaXR5PSIwLjIiLz48Y2lyY2xlIGN4PSIxNTAiIGN5PSIxNTAiIHI9IjUiIGZpbGw9IiNlNjIwMWMiIGZpbGwtb3BhY2l0eT0iMC4wOCIgc3Ryb2tlPSIjZTYyMDFjIiBzdHJva2Utd2lkdGg9IjAuNSIgc3Ryb2tlLW9wYWNpdHk9IjAuMiIvPjxjaXJjbGUgY3g9IjEwMCIgY3k9IjEwMCIgcj0iNSIgZmlsbD0iI2U2MjAxYyIgZmlsbC1vcGFjaXR5PSIwLjA4IiBzdHJva2U9IiNlNjIwMWMiIHN0cm9rZS13aWR0aD0iMC41IiBzdHJva2Utb3BhY2l0eT0iMC4yIi8+PHBhdGggZD0iTTAgMCBDIDI1IDEwLCA3NSA0MCwgMTAwIDUwIEMgMTI1IDQwLCAxNzUgMTAsIDIwMCAwIiBmaWxsPSJub25lIiBzdHJva2U9IiM5NjVkMGIiIHN0cm9rZS13aWR0aD0iMC43IiBzdHJva2Utb3BhY2l0eT0iMC4xNSIvPjxwYXRoIGQ9Ik0wIDEwMCBDIDI1IDExMCwgNzUgMTQwLCAxMDAgMTUwIEMgMTI1IDE0MCwgMTc1IDExMCwgMjAwIDEwMCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjOTY1ZDBiIiBzdHJva2Utd2lkdGg9IjAuNyIgc3Ryb2tlLW9wYWNpdHk9IjAuMTUiLz48cGF0aCBkPSJNMCAwIEMgMTAgMjUsIDQwIDc1LCA1MCAxMDAgQyA0MCAxMjUsIDEwIDE3NSwgMCAyMDAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzk2NWQwYiIgc3Ryb2tlLXdpZHRoPSIwLjciIHN0cm9rZS1vcGFjaXR5PSIwLjE1Ii8+PHBhdGggZD0iTTEwMCAwIEMgMTEwIDI1LCAxNDAgNzUsIDE1MCAxMDAgQyAxNDAgMTI1LCAxMTAgMTc1LCAxMDAgMjAwIiBmaWxsPSJub25lIiBzdHJva2U9IiM5NjVkMGIiIHN0cm9rZS13aWR0aD0iMC43IiBzdHJva2Utb3BhY2l0eT0iMC4xNSIvPjwvc3ZnPg==');
            background-size: 200px 200px;
            pointer-events: none;
            z-index: 0;
            opacity: 0.9;
            filter: drop-shadow(0 0 2px rgba(0, 0, 0, 0.2));
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'EB Garamond', serif;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: var(--field-drab);
        }
        
        h1 {
            font-size: 2.5rem;
            text-shadow: 1px 1px 2px rgba(150, 93, 11, 0.2);
            position: relative;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--red-cmyk), var(--golden-brown));
        }
        
        h2 {
            font-size: 2rem;
            color: white;
        }
        
        /* Style des boutons festif */
        .btn-primary {
            background: linear-gradient(145deg, var(--red-cmyk), #ff3d39) !important;
            border: none;
            font-family: 'EB Garamond', serif;
            font-size: 1.1rem;
            letter-spacing: 0.05em;
            padding: 0.5rem 1.2rem;
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
            border-radius: 0.5rem;
            text-transform: uppercase;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            transform-style: preserve-3d;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(145deg, #ff3d39, var(--red-cmyk)) !important;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -100%;
            width: 150%;
            height: 200%;
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(30deg);
            transition: all 0.8s ease;
        }
        
        .btn-primary:hover::after {
            left: 100%;
        }
        
        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(145deg, var(--golden-brown), var(--field-drab));
            border: none;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary:hover, .btn-secondary:focus {
            background: linear-gradient(145deg, var(--field-drab), var(--golden-brown));
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        
        /* Style navbar festif */
        .navbar {
            background-color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            padding: 0.6rem 0;
            position: relative;
            border-bottom: 3px solid var(--red-cmyk);
        }
        
        .navbar::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, transparent, var(--golden-brown), transparent);
            opacity: 0.6;
        }
        
        .nav-link {
            color: var(--field-drab);
            font-size: 1.1rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--red-cmyk);
            transform: translateY(-2px);
        }
        
        /* Style des cartes festif */
        .card {
            border: none;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border-radius: 0.8rem;
            overflow: hidden;
            position: relative;
            padding: 0;
            margin-bottom: 1.5rem;
            background-color: white;
            transform-style: preserve-3d;
            transition: all 0.4s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--red-cmyk), var(--golden-brown), var(--red-cmyk));
            z-index: 1;
        }
        
        .card-header {
            background-color: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid rgba(150, 93, 11, 0.2);
            color: var(--field-drab);
            padding: 1rem 1.25rem;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .card-header.bg-primary {
            background: linear-gradient(145deg, var(--red-cmyk), #ff3d39) !important;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .card-body {
            padding: 1.25rem;
            /* background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0ibm9uZSIvPjxwYXRoIGQ9Ik0wLDAgTDUwLDI1IEwwLDUwIEwwLDAiIGZpbGw9IiNlYjg4ODUyMCIvPjxwYXRoIGQ9Ik0xMDAsMCBMMTUwLDI1IEwxMDAsNTAgTDEwMCwwIiBmaWxsPSIjZWI4ODg1MjAiLz48cGF0aCBkPSJNNTAsNTAgTDEwMCw3NSBMNTAsIDEwMCBMNTAsNTAiIGZpbGw9IiNlYjg4ODUyMCIvPjxwYXRoIGQ9Ik0xNTAsNTAgTDIwMCw3NSBMMTUwLDEwMCBMMTUwLDUwIiBmaWxsPSIjZWI4ODg1MjAiLz48cGF0aCBkPSJNMCwxMDAgTDUwLDEyNSBMMCwxNTAgTDAsMTAwIiBmaWxsPSIjZWI4ODg1MjAiLz48cGF0aCBkPSJNMTAwLDEwMCBMMTUwLDEyNSBMMTAwLDE1MCBMMTAwLDEwMCIgZmlsbD0iI2ViODg4NTIwIi8+PHBhdGggZD0iTTUwLDE1MCBMMTAwLDE3NSBMNTAsMjAwIEw1MCwxNTAiIGZpbGw9IiNlYjg4ODUyMCIvPjxwYXRoIGQ9Ik0xNTAsMTUwIEwyMDAsMTc1IEwxNTAsMjAwIEwxNTAsMTUwIiBmaWxsPSIjZWI4ODg1MjAiLz48L3N2Zz4='); */
            background-size: 200px;
            background-opacity: 0.05;
        }
        
        .card-footer {
            padding: 1rem 1.25rem;
            background-color: rgba(255, 255, 255, 0.9);
            border-top: 1px solid rgba(150, 93, 11, 0.2);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen">
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @livewireScripts
    @stack('scripts')
</body>
</html>
