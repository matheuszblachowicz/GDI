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
        // Contadores gerais para exibir na página de entrada do painel
        $devicesCount = Device::count();
        $blockedDevices = Device::where('is_blocked', true)->count();
        
        // Logs reais de hoje (eventos capturados)
        $logsToday = UserActivityLog::whereDate('event_at', Carbon::today())->count();
        
        // Calcular Conformidade Real Média de todas as máquinas
        $devices = Device::with('applications')->get();
        // Vai buscar a lista de aplicações permitidas (whitelist)
        $allowedApps = DB::table('allowed_appliactions')->pluck('name')->map(fn($n) => strtolower($n))->toArray();
        
        $totalCompliance = 0;
        $devicesWithApps = 0;
        
        foreach($devices as $d) {
            $totalApps = $d->applications->count();
            if ($totalApps > 0) {
                // Conta quantas aplicações da máquina não estão na whitelist
                $unauthorized = $d->applications->filter(fn($app) => !in_array(strtolower($app->name), $allowedApps))->count();
                // Calcula a percentagem de conformidade desta máquina específica
                $totalCompliance += (($totalApps - $unauthorized) / $totalApps) * 100;
                $devicesWithApps++;
            }
        }
        
        // Faz a média global de todas as máquinas que têm aplicações registadas
        $averageCompliance = $devicesWithApps > 0 ? round($totalCompliance / $devicesWithApps) : 100;

        return view('home', compact('devicesCount', 'blockedDevices', 'logsToday', 'averageCompliance'));
    }

    // ---------------------------------------------------------------------
    // GESTÃO DE HORÁRIOS (WORKING HOURS)
    // ---------------------------------------------------------------------
    public function workingHours()
    {
        // Vai buscar as regras já cadastradas à base de dados
        $workingHours = WorkingHour::all();
        
        return view('admin.working_hours.index', compact('workingHours'));
    }

    public function storeWorkingHour(Request $request)
    {
        // Valida os dados inseridos no formulário
        $request->validate([
            'name' => 'required|string|max:255',
            'ad_group' => 'required|string', // O grupo (ou grupos) do AD digitado pelo admin
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        // Como a base de dados espera um JSON (array) em 'ad_groups', 
        // convertemos a string digitada num array. Se o utilizador digitar
        // "G_Vendas, G_TI", o explode e o array_map separam tudo num array limpo.
        $adGroupsArray = array_filter(array_map('trim', explode(',', $request->ad_group)));

        // Cria a nova regra na base de dados
        WorkingHour::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'ad_groups' => $adGroupsArray
        ]);

        return back()->with('success', 'Regra de horário e grupos do AD salvos com sucesso!');
    }

    // ---------------------------------------------------------------------
    // ATIVIDADE DOS UTILIZADORES
    // ---------------------------------------------------------------------
    public function userActivity()
    {
        // Vai buscar os logs, ordenados do mais recente para o mais antigo.
        // Usamos paginação (paginate) para não sobrecarregar a tela caso existam milhares de registos
        $logs = UserActivityLog::orderBy('event_at', 'desc')->paginate(50);
        
        // Vai buscar os devices para conseguirmos mapear o hostname no ecrã (através do ID)
        $devices = Device::pluck('hostname', 'id');

        return view('admin.user_activity.index', compact('logs', 'devices'));
    }

    // ---------------------------------------------------------------------
    // GESTÃO DE DISPOSITIVOS / MÁQUINAS (DEVICES)
    // ---------------------------------------------------------------------
    public function devices()
    {
        // Carrega as máquinas já com o último log associado, para ser possível 
        // apresentar de forma rápida o utilizador atual do equipamento.
        $devices = Device::with(['activityLogs' => function($query) {
            $query->latest('event_at');
        }])->get();
        
        return view('admin.devices.index', compact('devices'));
    }

    public function showDevice($id)
    {
        // Busca a máquina e as aplicações instaladas nela
        $device = Device::with('applications')->findOrFail($id);
        
        // Vai buscar a Whitelist de aplicações permitidas na empresa
        $allowedApps = DB::table('allowed_applications')->pluck('name')->toArray();
        
        // Filtra as aplicações que NÃO estão na Whitelist (ignorando maiúsculas/minúsculas)
        $unauthorizedApps = $device->applications->filter(function ($app) use ($allowedApps) {
            return !in_array(strtolower($app->name), array_map('strtolower', $allowedApps));
        });

        // Calcula a percentagem do nível de conformidade da máquina
        $totalApps = $device->applications->count();
        $complianceLevel = $totalApps > 0 
            ? round((($totalApps - $unauthorizedApps->count()) / $totalApps) * 100) 
            : 100;

        // Histórico Web Real: filtra apenas os processos relacionados com navegadores web conhecidos
        $webHistory = UserActivityLog::where('device_id', $id)
            ->whereIn('process_name', ['chrome.exe', 'msedge.exe', 'firefox.exe', 'brave.exe', 'opera.exe'])
            ->orderBy('event_at', 'desc')
            ->limit(100)
            ->get();

        return view('admin.devices.show', compact('device', 'unauthorizedApps', 'complianceLevel', 'webHistory'));
    }

    public function blockDevice(Request $request, $id)
    {
        // Valida a mensagem personalizada de bloqueio
        $request->validate([
            'block_message' => 'required|string|max:500'
        ]);

        // Encontra o dispositivo e atualiza as flags de bloqueio
        $device = Device::findOrFail($id);
        $device->is_blocked = true;
        $device->block_message = $request->block_message;
        $device->save();

        return back()->with('success', 'Ordem de bloqueio registada. A máquina será bloqueada exibindo a sua mensagem na próxima sincronização do agente.');
    }

    // ---------------------------------------------------------------------
    // MAPA DE GEOLOCALIZAÇÃO
    // ---------------------------------------------------------------------
    public function mapa()
    {
        // 1. Obtém as máquinas que têm geolocalização guardada
        $devices = Device::whereNotNull('latitude')
                         ->whereNotNull('longitude')
                         ->get();

        // 2. Agrupa as máquinas pela mesma coordenada exata (Localidade/Filial)
        $groupedDevices = $devices->groupBy(function ($device) {
            return $device->latitude . ',' . $device->longitude;
        });

        // 3. Formata os dados para o JavaScript do Mapa
        $locations = [];
        foreach ($groupedDevices as $coords => $group) {
            $parts = explode(',', $coords);
            
            $locations[] = [
                'lat' => $parts[0],
                'lng' => $parts[1],
                'total' => $group->count(),
                // Pega os detalhes de cada máquina neste local para mostrar no popup
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