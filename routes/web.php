<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Page d'accueil avec le formulaire d'inscription
Route::get('/', [ParticipantController::class, 'showRegistrationForm'])->name('home');

// Traitement de l'inscription
Route::post('/register', [ParticipantController::class, 'register'])->name('register');

// Affichage de la roue
Route::get('/wheel/{entry}', [ParticipantController::class, 'showWheel'])->name('wheel.show');

// Traitement du résultat de la roue
Route::post('/wheel/result', [ParticipantController::class, 'processWheelResult'])->name('wheel.result');

// Route AJAX pour le spin de la roue
Route::post('/wheel/spin', [ParticipantController::class, 'spinWheel'])->name('wheel.spin');

// Affichage du résultat
Route::get('/result/{entry}', [ParticipantController::class, 'showResult'])->name('result.show');

// Routes d'authentification pour l'administration (Filament)
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('filament.admin.auth.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('login');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('filament.admin.auth.logout');

// Route du tableau de bord (protégée par authentification)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Ajouter la route pour le règlement
Route::get('/rules', function () {
    return view('rules');
})->name('rules');
