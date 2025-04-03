<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roue de la Fortune - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        :root {
            --red-cmyk: #e3201cff;
            --golden-brown: #965d0bff;
            --field-drab: #544719ff;
            --light-coral: #eb8885ff;
            --cordovan: #8c4948ff;
            --black-bean: #4c1711ff;
            --lavender-blush: #f6e7e4ff;
        }
        
        body {
            background-color: var(--lavender-blush);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--field-drab);
            font-family: 'EB Garamond', serif;
            letter-spacing: 0.03em;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjZlN2U0ZmYiIC8+PGNpcmNsZSBjeD0iMCIgY3k9IjAiIHI9IjMwIiBmaWxsPSJub25lIiBzdHJva2U9IiNlYjg4ODUiIHN0cm9rZS13aWR0aD0iMSIgc3Ryb2tlLW9wYWNpdHk9IjAuMSIgLz48Y2lyY2xlIGN4PSIxMDAiIGN5PSIwIiByPSIzMCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZWI4ODg1IiBzdHJva2Utd2lkdGg9IjEiIHN0cm9rZS1vcGFjaXR5PSIwLjEiIC8+PGNpcmNsZSBjeD0iMCIgY3k9IjEwMCIgcj0iMzAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2ViODg4NSIgc3Ryb2tlLXdpZHRoPSIxIiBzdHJva2Utb3BhY2l0eT0iMC4xIiAvPjxjaXJjbGUgY3g9IjEwMCIgY3k9IjEwMCIgcj0iMzAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2ViODg4NSIgc3Ryb2tlLXdpZHRoPSIxIiBzdHJva2Utb3BhY2l0eT0iMC4xIiAvPjxjaXJjbGUgY3g9IjUwIiBjeT0iNTAiIHI9IjMwIiBmaWxsPSJub25lIiBzdHJva2U9IiNlYjg4ODUiIHN0cm9rZS13aWR0aD0iMSIgc3Ryb2tlLW9wYWNpdHk9IjAuMSIgLz48L3N2Zz4=');
            background-attachment: fixed;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0ibm9uZSIvPjxwYXRoIGQ9Ik0wLDAgTDUwLDI1IEwwLDUwIEwwLDAiIGZpbGw9IiNlYjg4ODUyMCIvPjxwYXRoIGQ9Ik0xMDAsMCBMMTUwLDI1IEwxMDAsNTAgTDEwMCwwIiBmaWxsPSIjZWI4ODg1MjAiLz48cGF0aCBkPSJNNTAsNTAgTDEwMCw3NSBMNTQ4ODAxMDAgTDUwLDUwIiBmaWxsPSIjZWI4ODg1MjAiLz48cGF0aCBkPSJNMTUwLDUwIEwyMDAsNzUgTDE1MCwxMDAgTDE1MCw1MCIgZmlsbD0iI2ViODg4NTIwIi8+PHBhdGggZD0iTTAsMTAwIEw1MCwxMjUgTDAsMTUwIEwwLDEwMCIgZmlsbD0iI2ViODg4NTIwIi8+PHBhdGggZD0iTTEwMCwxMDAgTDE1MCwxMjUgTDEwMCwxNTAgTDEwMCwxMDAiIGZpbGw9IiNlYjg4ODUyMCIvPjxwYXRoIGQ9Ik01MCwxNTAgTDEwMCwxNzUgTDUwLDIwMCBMNTAsMTUwIiBmaWxsPSIjZWI4ODg1MjAiLz48cGF0aCBkPSJNMTUwLDE1MCBMMjAwLDE3NSBMMTUMLI4MDAgTDE1MCwxNTAiIGZpbGw9IiNlYjg4ODUyMCIvPjwvc3ZnPg==');
            z-index: -1;
            opacity: 0.3;
            pointer-events: none;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px 15px;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 10px 30px rgba(227, 32, 28, 0.25);
            border: 1px solid rgba(227, 32, 28, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), #ff3d39, var(--primary-color));
        }
        
        .login-container::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0icmdiYSgxNTAsIDkzLCAxMSwgMC4wNSkiIGQ9Ik0xMiAyMS4zNWwtMS40NS0xLjMyQzUuNCAxNS4zNiAyIDEyLjI4IDIgOC41IDIgNS40MiA0LjQyIDMgNy41IDNjMS43NCAwIDMuNDEuODEgNC41IDIuMDlDMTMuMDkgMy44MSAxNC43NiAzIDE2LjUgMyAxOS41OCAzIDIyIDUuNDIgMjIgOC41YzAgMy43OC0zLjQgNi44Ni04LjU1IDExLjU0TDEyIDIxLjM1eiIvPjwvc3ZnPg==');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.1;
            pointer-events: none;
            transform: rotate(-15deg);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: var(--primary-color);
            font-weight: bold;
            text-shadow: 1px 1px 3px rgba(227, 32, 28, 0.2);
            font-family: 'EB Garamond', serif;
            font-size: 2.2rem;
            letter-spacing: 0.05em;
            position: relative;
            display: inline-block;
            margin-bottom: 0.3rem;
        }
        
        .logo h1::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
        }
        
        .logo p {
            color: #ff3d39;
            font-style: italic;
            margin-top: 3px;
            font-size: 1rem;
            font-weight: 500;
        }
        .form-label {
            color: var(--field-drab);
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.03em;
            margin-bottom: 0.4rem;
        }
        .form-control:focus {
            border-color: var(--cordovan);
            box-shadow: 0 0 0 0.25rem rgba(140, 73, 72, 0.25);
        }
        .form-check-input:checked {
            background-color: var(--golden-brown);
            border-color: var(--golden-brown);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #ff3d39);
            border: none;
            width: 100%;
            padding: 10px;
            transition: all 0.4s ease;
            font-family: 'EB Garamond', serif;
            font-size: 1.1rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            border-radius: 0.5rem;
            box-shadow: 0 4px 15px rgba(227, 32, 28, 0.4);
            outline: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #ff3d39, var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(227, 32, 28, 0.5);
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -100%;
            width: 150%;
            height: 200%;
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(30deg);
            transition: all 0.8s ease;
        }
        
        .btn-primary:hover::after {
            left: 100%;
        }
        
        .form-control {
            border: 1px solid rgba(150, 93, 11, 0.2);
            border-radius: 0.5rem;
            padding: 0.7rem 1rem;
            font-size: 1.1rem;
            font-family: 'EB Garamond', serif;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(140, 73, 72, 0.25);
            border-color: var(--cordovan);
        }
        .alert-danger {
            background-color: rgba(227, 32, 28, 0.1);
            border-color: var(--light-coral);
            color: var(--black-bean);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="logo animate__animated animate__fadeIn" style="margin-bottom: 15px;">
                        <h1>Roue de la Fortune</h1>
                        <p>Tentez votre chance</p>
                    </div>
                    
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse e-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary animate__animated animate__pulse animate__infinite animate__slower">Se connecter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
