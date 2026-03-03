<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LdapController;
use App\Http\Controllers\AdminController;

// -------------------------------------------------------------------------
// ROTAS DE AUTENTICAÇÃO (LDAP)
// -------------------------------------------------------------------------

// Exibe a view de login
Route::get('/login', [LdapController::class, 'index'])->name('login');

// Processa a tentativa de login
Route::post('/login', [LdapController::class, 'authenticate'])->name('authenticate');

// Encerra a sessão
Route::post('/logout', [LdapController::class, 'logout'])->name('logout');

// -------------------------------------------------------------------------
// ROTAS PROTEGIDAS (Apenas para utilizadores autenticados)
// -------------------------------------------------------------------------

Route::middleware('auth')->group(function () {
    
    // Rota Home (Acessível após login)
    Route::get('/home', [LdapController::class, 'home'])->name('home');

    // Redireciona a raiz para a home se estiver logado
    Route::get('/', function () { 
        return redirect()->route('home'); 
    });

    // ---------------------------------------------------------------------
    // PAINEL DE ADMINISTRAÇÃO (Prefixo: /admin)
    // ---------------------------------------------------------------------
    Route::prefix('admin')->name('admin.')->group(function () {
        
        Route::get('/', [AdminController::class, 'home'])->name('home');
        Route::get('/devices', [AdminController::class, 'devices'])->name('devices.index');
        Route::get('/devices/{id}', [AdminController::class, 'showDevice'])->name('devices.show');
        Route::post('/devices/{id}/block', [AdminController::class, 'blockDevice'])->name('devices.block');
        
        Route::get('/working-hours', [AdminController::class, 'workingHours'])->name('working_hours.index');
        Route::post('/working-hours', [AdminController::class, 'storeWorkingHour'])->name('working_hours.store');

        Route::get('/user-activity', [AdminController::class, 'userActivity'])->name('user_activity.index');
    });
});