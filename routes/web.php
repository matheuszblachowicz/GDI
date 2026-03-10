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
    
    // Rota Home (Acessível após login) - Dashboard Principal
    Route::get('/home', [LdapController::class, 'home'])->name('home');

    // Redireciona a raiz para a home se estiver logado
    Route::get('/', function () { 
        return redirect()->route('home'); 
    });

    // ---------------------------------------------------------------------
    // PAINEL DE ADMINISTRAÇÃO (Prefixo URL: /admin | Prefixo Nome: admin.)
    // ---------------------------------------------------------------------
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Rota: admin.home
        Route::get('/', [AdminController::class, 'home'])->name('home');
        
        // Dispositivos (Rotas: admin.devices.index, admin.devices.show, admin.devices.block)
        Route::get('/devices', [AdminController::class, 'devices'])->name('devices.index');
        Route::get('/devices/{id}', [AdminController::class, 'showDevice'])->name('devices.show');
        Route::post('/devices/{id}/block', [AdminController::class, 'blockDevice'])->name('devices.block');
        
        // Regras de Horário (Rotas: admin.working_hours.index, admin.working_hours.store)
        Route::get('/working-hours', [AdminController::class, 'workingHours'])->name('working_hours.index');
        Route::post('/working-hours', [AdminController::class, 'storeWorkingHour'])->name('working_hours.store');

        // Log de Atividades (Rota: admin.user_activity.index)
        Route::get('/user-activity', [AdminController::class, 'userActivity'])->name('user_activity.index');

        // Whitelist de Aplicações (Rotas: admin.allowed_apps.index, admin.allowed_apps.store, admin.allowed_apps.destroy)
        // CORRIGIDO: Removido o "admin." de dentro do name()
        Route::get('/allowed-apps', [AdminController::class, 'allowedApps'])->name('allowed_apps.index');
        Route::post('/allowed-apps', [AdminController::class, 'storeAllowedApp'])->name('allowed_apps.store');
        Route::delete('/allowed-apps/{id}', [AdminController::class, 'destroyAllowedApp'])->name('allowed_apps.destroy');

        // Mapa Global (Rota: admin.mapa)
        // CORRIGIDO: Nome apenas 'mapa', que com o grupo se torna 'admin.mapa'
        Route::get('/mapa', [AdminController::class, 'mapa'])->name('mapa');

        Route::get('/ldap-logs', [AdminController::class, 'ldapLogs'])->name('ldap_logs.index');
        Route::post('/ldap-logs/notify', [AdminController::class, 'notifyManagers'])->name('ldap_logs.notify');


        Route::get('/managers', [AdminController::class, 'managers'])->name('managers.index');
        Route::post('/managers', [AdminController::class, 'storeManager'])->name('managers.store');
        Route::delete('/managers/{id}', [AdminController::class, 'destroyManager'])->name('managers.destroy');

        // Layout de E-mail
        Route::get('/email-template', [AdminController::class, 'editEmailTemplate'])->name('email_template.edit');
        Route::post('/email-template', [AdminController::class, 'updateEmailTemplate'])->name('email_template.update');



    });
});