<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu dinor 70 ans - Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-header {
            background-color: #dc3545;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #dc3545;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
        }
    </style>
</head>
<body>
    <header class="dashboard-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Jeu dinor 70 ans</h1>
                <a href="{{ route('filament.admin.auth.logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Déconnexion
                </a>

                <form id="logout-form" action="{{ route('filament.admin.auth.logout') }}" method="POST" style="display: none;">
                    @csrf</form>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Concours actifs</h5>
                    </div>
                    <div class="card-body">
                        <p>Nombre de concours actifs : {{ \App\Models\Contest::where('active', true)->count() }}</p>
                        <a href="{{ route('filament.admin.resources.contests.index') }}" class="btn btn-danger">Gérer les concours</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Participants</h5>
                    </div>
                    <div class="card-body">
                        <p>Nombre total de participants : {{ \App\Models\Participant::count() }}</p>
                        <a href="{{ route('filament.admin.resources.participants.index') }}" class="btn btn-danger">Gérer les participants</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Prix disponibles</h5>
                    </div>
                    <div class="card-body">
                        <p>Nombre de prix disponibles : {{ \App\Models\Prize::count() }}</p>
                        <a href="{{ route('filament.admin.resources.prizes.index') }}" class="btn btn-danger">Gérer les prix</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Participations récentes</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach(\App\Models\Entry::latest()->take(5)->get() as $entry)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $entry->participant->name ?? 'Participant inconnu' }}
                                    <span class="badge bg-danger rounded-pill">{{ $entry->created_at->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('filament.admin.resources.entries.index') }}" class="btn btn-danger mt-3">Voir toutes les participations</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Codes QR générés</h5>
                    </div>
                    <div class="card-body">
                        <p>Nombre total de codes QR : {{ \App\Models\QrCode::count() }}</p>
                        <a href="{{ route('filament.admin.resources.qr-codes.index') }}" class="btn btn-danger">Gérer les codes QR</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
