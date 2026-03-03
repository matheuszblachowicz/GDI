<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;

Route::post('/',[AgentController::class,'getApplications']);