@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0 text-center">
                        <i class="fas fa-gift me-2"></i>Roue de la Fortune
                    </h3>
                </div>
                <div class="card-body text-center p-4">
                    @include('components.no-stock-wheel')
                </div>
                <div class="card-footer text-center text-muted">
                    <small> {{ date('Y') }} Dinor 70 ans - Tous droits réservés</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
