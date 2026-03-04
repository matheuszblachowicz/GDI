<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\WorkingHour;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ---------------------------------------------------------------------
    // DASHBOARD (HOME)
    // ---------------------------------------------------------------------
    public function home()
    {
        $devicesCount = Device::count();
        $blockedDevices = Device::where('is_blocked', true)->count();
        $logsToday = UserActivityLog::whereDate('event_at', Carbon::today())->count();
        
        $devices = Device::with('applications')->get();
        $allowedApps = DB::table('allowed_applications')->pluck('name')->map(fn($n) => strtolower($n))->toArray();
        
        $totalCompliance = 0;
        $devicesWithApps = 0;
        
        foreach($devices as $d) {
            $totalApps = $d->applications->count();
            if ($totalApps > 0) {
                $unauthorized = $d->applications->filter(function ($app) use ($allowedApps) {
                    foreach ($allowedApps as $allowed) {
                        if (stripos($app->name, trim($allowed)) !== false) return false;
                    }
                    return true;
                })->count();
                
                $totalCompliance += (($totalApps - $unauthorized) / $totalApps) * 100;
                $devicesWithApps++;
            }
        }
        
        $averageCompliance = $devicesWithApps > 0 ? round($totalCompliance / $devicesWithApps) : 100;

        return view('home', compact('devicesCount', 'blockedDevices', 'logsToday', 'averageCompliance'));
    }

    // ---------------------------------------------------------------------
    // GESTÃO DE HORÁRIOS (WORKING HOURS)
    // ---------------------------------------------------------------------
    public function workingHours()
    {
        $workingHours = WorkingHour::all();
        return view('admin.working_hours.index', compact('workingHours'));
    }

    public function storeWorkingHour(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ad_group' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $adGroupsArray = array_filter(array_map('trim', explode(',', $request->ad_group)));

        WorkingHour::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'ad_groups' => $adGroupsArray
        ]);

        return back()->with('success', 'Regra de horário salva com sucesso!');
    }

    // ---------------------------------------------------------------------
    // GESTÃO DE APLICAÇÕES PERMITIDAS (WHITELIST GLOBAL)
    // ---------------------------------------------------------------------
    public function allowedApps()
    {
        $apps = DB::table('allowed_applications')->get();
        return view('admin.allowed_apps.index', compact('apps'));
    }

    public function storeAllowedApp(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:allowed_applications,name'
        ]);

        DB::table('allowed_applications')->insert([
            'name' => trim($request->name),
            'is_mandatory' => $request->has('is_mandatory'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Aplicação adicionada à lista de permitidas!');
    }

    public function destroyAllowedApp($id)
    {
        DB::table('allowed_applications')->where('id', $id)->delete();
        return back()->with('success', 'Aplicação removida da lista.');
    }

    // ---------------------------------------------------------------------
    // ATIVIDADE DOS UTILIZADORES GERAL
    // ---------------------------------------------------------------------
    public function userActivity()
    {
        $logs = UserActivityLog::orderBy('event_at', 'desc')->paginate(50);
        $devices = Device::pluck('hostname', 'id');
        return view('admin.user_activity.index', compact('logs', 'devices'));
    }

    // ---------------------------------------------------------------------
    // GESTÃO DE DISPOSITIVOS E INSPEÇÃO INDIVIDUAL
    // ---------------------------------------------------------------------
    public function devices()
    {
        $devices = Device::with(['activityLogs' => function($query) {
            $query->latest('event_at');
        }])->get();
        
        return view('admin.devices.index', compact('devices'));
    }

    public function showDevice($id)
    {
        $device = Device::with('applications')->findOrFail($id);
        
        // Busca a whitelist
        $allowedApps = DB::table('allowed_applications')->pluck('name')->toArray();
        
        // Lógica de correspondência parcial (Fuzzy Match com stripos)
        $unauthorizedApps = $device->applications->filter(function ($app) use ($allowedApps) {
            foreach ($allowedApps as $allowed) {
                if (stripos($app->name, trim($allowed)) !== false) {
                    return false; // É autorizado (encontrou palavra-chave)
                }
            }
            return true; // Não encontrou, é não autorizado
        });

        $totalApps = $device->applications->count();
        $complianceLevel = $totalApps > 0 
            ? round((($totalApps - $unauthorizedApps->count()) / $totalApps) * 100) 
            : 100;

        // Histórico de Navegadores
        $webHistory = UserActivityLog::where('device_id', $id)
            ->whereIn('process_name', ['chrome.exe', 'msedge.exe', 'firefox.exe', 'brave.exe', 'opera.exe'])
            ->orderBy('event_at', 'desc')
            ->limit(100)
            ->get();

        // Atividade Geral (Todos os processos) daquela máquina específica
        $activityLogs = UserActivityLog::where('device_id', $id)
            ->orderBy('event_at', 'desc')
            ->limit(150)
            ->get();

        return view('admin.devices.show', compact('device', 'unauthorizedApps', 'complianceLevel', 'webHistory', 'activityLogs'));
    }

    public function blockDevice(Request $request, $id)
    {
        $request->validate(['block_message' => 'required|string|max:500']);

        $device = Device::findOrFail($id);
        $device->is_blocked = true;
        $device->block_message = $request->block_message;
        $device->save();

        return back()->with('success', 'Ordem de bloqueio registada.');
    }

    // ---------------------------------------------------------------------
    // MAPA
    // ---------------------------------------------------------------------
    public function mapa()
    {
        $devices = Device::whereNotNull('latitude')->whereNotNull('longitude')->get();

        $groupedDevices = $devices->groupBy(function ($device) {
            return $device->latitude . ',' . $device->longitude;
        });

        $locations = [];
        foreach ($groupedDevices as $coords => $group) {
            $parts = explode(',', $coords);
            $locations[] = [
                'lat' => $parts[0],
                'lng' => $parts[1],
                'total' => $group->count(),
                'machines' => $group->map(function($d) {
                    return [
                        'hostname' => $d->hostname,
                        'ip' => $d->ip_address,
                        'user' => $d->current_user ?? 'Sem registo',
                        'blocked' => $d->is_blocked
                    ];
                })->toArray()
            ];
        }

        return view('admin.mapa', compact('locations'));
    }
}