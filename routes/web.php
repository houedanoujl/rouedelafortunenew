<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpinController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\SpinResultController;
use App\Http\Controllers\TestModeController;
use App\Http\Controllers\CookieController;
use App\Http\Controllers\WhatsappTestController;
use App\Http\Controllers\MetaWhatsappTestController;
use App\Http\Controllers\GreenApiWhatsappController;

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

// Route alternative pour home (résoudre l'erreur 404 avec /home)
Route::get('/home', [ParticipantController::class, 'showRegistrationForm']);

// Traitement de l'inscription
Route::post('/register', [ParticipantController::class, 'register'])->name('register');

// Routes pour la roue
Route::get('/wheel/{entry}', [SpinController::class, 'show'])->name('wheel.show');
Route::get('/spin/result/{entry}', [SpinController::class, 'result'])->name('spin.result');

// Route pour le QR code
Route::get('/qrcode/{code}', [QrCodeController::class, 'show'])->name('qrcode.result');
Route::get('/qrcode/{code}/download/pdf', [QrCodeController::class, 'downloadPdf'])->name('qrcode.download.pdf');
Route::get('/qrcode/{code}/download/png', [QrCodeController::class, 'downloadJpg'])->name('qrcode.download.png');

// Route pour enregistrer le résultat réel du spin (AJAX)
Route::post('/spin/record-result', [SpinResultController::class, 'recordResult'])->name('spin.record-result');

// Affichage du résultat
Route::get('/result/{entry}', [ParticipantController::class, 'showResult'])->name('result.show');

// Routes d'authentification pour l'administration (Filament)
Route::get('/admin/login', function () {
    // Si l'utilisateur est déjà authentifié, rediriger vers le tableau de bord
    if (auth()->check()) {
        return redirect('/admin');
    }
    
    // Sinon, afficher la page de connexion Filament
    return view('filament.admin.auth.login');
})->name('filament.admin.auth.login');

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

// Route ultra simplifiée pour nettoyer les cookies (utilisée en mode test)
Route::get('/clear-cookies', [CookieController::class, 'clearCookies'])->name('clear.cookies');

// Page de test WhatsApp Infobip
Route::get('/whatsapp-test', [WhatsappTestController::class, 'showForm'])->name('whatsapp.test');
Route::post('/whatsapp-test', [WhatsappTestController::class, 'sendMessage'])->name('whatsapp.send');

// Page de test WhatsApp Meta (Facebook)
Route::get('/meta-whatsapp-test', [MetaWhatsappTestController::class, 'showForm'])->name('meta.whatsapp.test');
Route::post('/meta-whatsapp-test', [MetaWhatsappTestController::class, 'sendMessage'])->name('meta.whatsapp.send');

// Page de test WhatsApp Green API
Route::get('/green-api-whatsapp-test', [GreenApiWhatsappController::class, 'showForm'])->name('green.whatsapp.test');
Route::post('/green-api-whatsapp-test', [GreenApiWhatsappController::class, 'sendMessage'])->name('green.whatsapp.send');
