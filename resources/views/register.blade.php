@extends('layouts.app')

@section('content')
<div class="register-container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Inscription au concours</h2>
                </div>
                <div class="card-body">
                    <p class="lead mb-4">Remplissez le formulaire ci-dessous pour participer à la Roue de la Fortune.</p>
                    
                    @livewire('registration-form', ['contestId' => $contestId])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
