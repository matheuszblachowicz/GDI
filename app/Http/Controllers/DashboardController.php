<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function mapa()
    {
        // Obtém apenas as máquinas que têm geolocalização guardada
        $devices = Device::whereNotNull('latitude')
                         ->whereNotNull('longitude')
                         ->get(['hostname', 'ip_address', 'latitude', 'longitude']);
                         
        return view('dashboard.mapa', compact('devices'));
    }
}