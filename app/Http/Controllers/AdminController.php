<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\WorkingHour;
use App\Models\UserActivityLog;
use App\Models\LdapLog; // Adicionado para os logs do LDAP
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\DevicesExport; 
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail; // Adicionado para o envio de e-mails

class AdminController extends Controller
{
   
    private function calculateCompliance($device)
    {
        $allowedApps = DB::table('allowed_applications')->pluck('name')->toArray();
        $totalApps = $device->applications->count();
        
        if ($totalApps === 0) return 100;

        $unauthorizedCount = $device->applications->filter(function ($app) use ($allowedApps) {
            foreach ($allowedApps as $allowed) {
                if (stripos($app->name, trim($allowed)) !== false) return false;
            }
            return true;
        })->count();

        return round((($totalApps - $unauthorizedCount) / $totalApps) * 100);
    }

    // ---------------------------------------------------------------------
    // EXPORTAÇÃO
    // ---------------------------------------------------------------------
    public function exportDevices() 
    {
        return Excel::download(new DevicesExport, 'dispositivos_platlog.xlsx');
    }

    // ---------------------------------------------------------------------
    // GESTÃO DE DISPOSITIVOS E INSPEÇÃO
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
        
        $allowedApps = DB::table('allowed_applications')->pluck('name')->toArray();
        
        $unauthorizedApps = $device->applications->filter(function ($app) use ($allowedApps) {
            foreach ($allowedApps as $allowed) {
                if (stripos($app->name, trim($allowed)) !== false) return false;
            }
            return true;
        });

        $complianceLevel = $this->calculateCompliance($device);

        $webHistory = UserActivityLog::where('device_id', $id)
            ->whereIn('process_name', ['chrome', 'msedge', 'firefox', 'brave', 'opera'])
            ->orderBy('event_at', 'desc')
            ->limit(100)
            ->get();

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

        return back()->with('success', 'Ordem de bloqueio registada com sucesso.');
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
    // WHITELIST DE APLICAÇÕES
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
    // MAPA DE LOCALIDADES
    // ---------------------------------------------------------------------
    public function mapa()
    {
        $devices = Device::whereNotNull('latitude')->whereNotNull('longitude')->get();

        $groupedDevices = $devices->groupBy(function ($device) {
            return $device->city ?? 'Local Desconhecido';
        });

        $locations = [];
        foreach ($groupedDevices as $city => $group) {
            $firstDevice = $group->first();
            
            $locations[] = [
                'city' => $city,
                'lat' => $firstDevice->latitude,
                'lng' => $firstDevice->longitude,
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

    public function userActivity()
    {
        $logs = UserActivityLog::orderBy('event_at', 'desc')->paginate(50);
        $devices = Device::pluck('hostname', 'id');
        return view('admin.user_activity.index', compact('logs', 'devices'));
    }

    // ---------------------------------------------------------------------
    // AUDITORIA E LOGS LDAP
    // ---------------------------------------------------------------------
    
    /**
     * Exibe a view de auditoria de logs do LDAP com suporte a pesquisa e filtros
     */
    public function ldapLogs(Request $request)
    {
        $query = LdapLog::query();

        // Filtro de texto (Nome ou Login)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('usuario_nome', 'like', "%{$search}%")
                  ->orWhere('samaccountname', 'like', "%{$search}%");
            });
        }

        // Filtro por Tipo de Ação
        if ($request->filled('acao')) {
            $query->where('acao', $request->acao);
        }

        // Busca os logs ordenados do mais recente para o mais antigo,
        // com paginação e mantendo os parâmetros de query (filtros) na URL para a paginação funcionar.
        $logs = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        
        return view('admin.ldap_logs.index', compact('logs'));
    }

    /**
     * Envia o e-mail para os gestores com base nos novos acessos
     * (Este método pode ser acionado futuramente na nova vista de envio de e-mails)
     */
    public function notifyManagers(Request $request)
    {
        // Busca os utilizadores criados nas últimas 24 horas
        $novosUsuarios = LdapLog::where('acao', 'Criado')
                                ->where('created_at', '>=', now()->subDay())
                                ->get();

        if ($novosUsuarios->isEmpty()) {
            return back()->with('info', 'Nenhum novo utilizador para notificar nas últimas 24 horas.');
        }


        return back()->with('success', 'Notificações processadas com sucesso! (Lógica de envio pendente da criação da tabela de Gestores)');
    }
}