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
            --honolulu-blue: #0079B2ff;
            --apple-green: #86B942ff;
            --school-bus-yellow: #F7DB15ff;
            --persian-red: #D03A2Cff;
            --sea-green: #049055ff;
            --light-gray: #f5f5f5;
            --dark-gray: #333333;
            --primary-color: var(--honolulu-blue);
        }
        
        body {
            background-color: var(--light-gray);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-gray);
            font-family: 'EB Garamond', serif;
            letter-spacing: 0.03em;
            position: relative;
        }
        
        /* Suppression du motif de fond pour un design plat */
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px 15px;
            background-color: #fff;
            border-radius: 0.25rem;
            border: 1px solid #e0e0e0; height:100vh;
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
            background-color: var(--primary-color);
        }
        
        /* Suppression du motif décoratif pour un design plat */
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: var(--primary-color);
            font-weight: bold;
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
            background-color: var(--primary-color);
        }
        
        .logo p {
            color: var(--persian-red);
            font-style: italic;
            margin-top: 3px;
            font-size: 1rem;
            font-weight: 500;
        }
        .form-label {
            color: var(--dark-gray);
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.03em;
            margin-bottom: 0.4rem;
        }
        .form-control:focus {
            border-color: var(--honolulu-blue);
            box-shadow: none;
        }
        .form-check-input:checked {
            background-color: var(--honolulu-blue);
            border-color: var(--honolulu-blue);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            width: 100%;
            padding: 10px;
            transition: all 0.3s ease;
            font-family: 'EB Garamond', serif;
            font-size: 1.1rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 600;
            position: relative;
            border-radius: 0.25rem;
            outline: none;
        }
        .btn-primary:hover {
            background-color: #006699;
            opacity: 0.9;
        }
        
        /* Suppression de l'effet de brillance pour un design plat */
        
        .form-control {
            border: 1px solid #e0e0e0; height:100vh;
            border-radius: 0.25rem;
            padding: 0.7rem 1rem;
            font-size: 1.1rem;
            font-family: 'EB Garamond', serif;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: var(--honolulu-blue);
        }
        .alert-danger {
            background-color: rgba(208, 58, 44, 0.05);
            border-color: var(--persian-red);
            color: var(--persian-red);
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
