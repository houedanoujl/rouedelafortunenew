@extends('layouts.app')

@section('content')
<div class="play-container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Tournez la roue de la fortune</h2>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <p class="lead">C'est le moment de tenter votre chance !</p>
                    </div>
                    
                    @livewire('fortune-wheel', ['participantId' => $participantId, 'contestId' => $contestId])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
