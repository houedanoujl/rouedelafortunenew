<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

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

// Routes publiques
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/register/{contestId?}', [HomeController::class, 'register'])->name('register');
Route::get('/play/{participantId}/{contestId}', [HomeController::class, 'play'])->name('play');
Route::get('/result/{entryId}', [HomeController::class, 'result'])->name('result');
Route::get('/rules', [HomeController::class, 'rules'])->name('rules');

// Routes administratives (protégées par authentification)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/participants', [AdminController::class, 'participants'])->name('participants');
    Route::get('/entries', [AdminController::class, 'entries'])->name('entries');
    Route::get('/prizes', [AdminController::class, 'prizes'])->name('prizes');
    Route::get('/qr-code/{entryId}', [AdminController::class, 'qrCode'])->name('qr-code');
    Route::get('/scan-qr-code', [AdminController::class, 'scanQrCode'])->name('scan-qr-code');
    Route::post('/verify-qr-code', [AdminController::class, 'verifyQrCode'])->name('verify-qr-code');
});
