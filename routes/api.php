<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;

// -------------------------------------------------------------------------
// ENDPOINTS DO AGENTE (Acedidos via POST para /api/agent/...)
// -------------------------------------------------------------------------

Route::prefix('agent')->group(function () {
    
    // 1. Verifica se a máquina tem termo assinado e recebe estado de bloqueio
    Route::post('/verify', [AgentController::class, 'verifyMachine']);
    
    // 2. Recebe e regista a lista de software instalado
    Route::post('/applications', [AgentController::class, 'getApplications']);
    
    // 3. Recebe e regista os eventos de uso (login, logout, idle, janelas ativas)
    Route::post('/user-info', [AgentController::class, 'getUserInformation']);
    
    // 4. Valida se o utilizador atual pode usar a máquina com base no seu AD Group
    Route::post('/check-working-hours', [AgentController::class, 'checkWorkingHours']);
    
});