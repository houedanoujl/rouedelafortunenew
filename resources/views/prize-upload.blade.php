@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h1 class="mb-4">Gestionnaire d'images des prix</h1>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Instructions</h5>
                </div>
                <div class="card-body">
                    <p>Utilisez cet outil pour uploader les images de vos prix qui seront utilisées dans le panneau d'administration.</p>
                    <ol>
                        <li>Uploadez vos images en utilisant le formulaire ci-dessous</li>
                        <li>Copiez l'URL complète de l'image (avec <code>http://localhost:8888/assets/prizes/...</code>)</li>
                        <li>Dans le panneau d'administration Filament, collez cette URL dans le champ "Image du prix"</li>
                    </ol>
                    <div class="alert alert-info">
                        <strong>Astuce :</strong> Pour un meilleur rendu, utilisez des images carrées d'au moins 300x300 pixels.
                    </div>
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="btn btn-primary" target="_blank">
                        Ouvrir le panneau d'administration
                    </a>
                </div>
            </div>
            
            <x-upload-helper />
        </div>
    </div>
</div>
@endsection
