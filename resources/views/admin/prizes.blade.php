@extends('layouts.app')

@section('content')
<div class="admin-prizes">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des prix</h1>
    </div>
    
    @livewire('admin.prizes-manager')
</div>
@endsection
