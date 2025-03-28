@extends('layouts.app')

@section('content')
<div class="wheel-container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Roue de la Fortune</h2>
                </div>
                <div class="card-body text-center">
                    <p class="lead mb-4">Bonjour {{ $entry->participant->first_name }} {{ $entry->participant->last_name }}, tournez la roue et tentez votre chance !</p>
                    
                    @livewire('fortune-wheel', ['entry' => $entry, 'prizes' => $prizes])
                    
                    <div class="mt-4">
                        <p class="text-muted">Concours: {{ $contest->name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
