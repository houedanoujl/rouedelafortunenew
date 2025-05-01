@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
        <div class="text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-red-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Erreur lors de la génération du PDF</h1>
            
            <p class="text-gray-600 mb-6">{{ $message ?? 'Une erreur est survenue lors de la génération du PDF. Veuillez réessayer ou contacter l\'administrateur.' }}</p>
            
            <div class="flex flex-col space-y-3">
                <a href="javascript:history.back()" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition duration-200">
                    Retour à la page précédente
                </a>
                
                <a href="{{ route('home') }}" class="px-6 py-2 bg-gray-200 text-gray-800 font-medium rounded-md hover:bg-gray-300 transition duration-200">
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
