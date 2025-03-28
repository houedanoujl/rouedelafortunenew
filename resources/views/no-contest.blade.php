@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h2 class="mb-0">Aucun concours actif</h2>
                </div>
                <div class="card-body text-center">
                    <p class="lead mb-4">Désolé, il n'y a actuellement aucun concours actif.</p>
                    <p>Veuillez revenir plus tard ou contacter l'administrateur.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
