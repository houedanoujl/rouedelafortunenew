@extends('layouts.app')

@section('content')
<div class="result-container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Votre résultat</h2>
                </div>
                <div class="card-body">
                    @livewire('qr-code-generator', ['entryId' => $entryId])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
