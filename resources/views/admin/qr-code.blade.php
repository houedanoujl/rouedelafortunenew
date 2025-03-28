@extends('layouts.app')

@section('content')
<div class="admin-qr-code">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Code QR</h1>
        <a href="{{ route('admin.entries') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour
        </a>
    </div>
    
    @livewire('qr-code-generator', ['entryId' => $entryId])
</div>
@endsection
