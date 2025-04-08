@extends('layouts.app')

@section('content')
<div class="register-container d-flex justify-content-center align-items-center">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card" style="border: 1px solid #e0e0e0; border-radius: 4px; box-shadow: none;">
                <div class="card-header text-white" style="background-color: var(--honolulu-blue);">
                    <h2 class="mb-0">📝 Inscription au concours 🎁</h2>
                </div>
                <div class="card-body">
                    <p class="lead mb-4">
                        🔥 Tentez votre chance et remportez des cadeaux incroyables ! 🎉<br>
                        Remplissez le formulaire ci-dessous pour participer à la <span style="font-weight: normal; color: var(--primary-red);">Roue de la Fortune</span>. 🎰<br>
                                            </p>
                    
                    <!-- <h3>Test Component:</h3>
                    @livewire('test-component')
                     -->
                    <hr>
                    @livewire('registration-form', ['contestId' => $contestId])
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Définition des variables de couleur */
:root {
    --primary-red: #D03A2C;
    --success-green: #28a745;
    --text-highlight: #D03A2C;
}
</style>
@endsection
