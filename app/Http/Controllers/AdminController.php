<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\WorkingHour;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        
        return view('admin.home', compact('devicesCount', 'blockedDevices'));
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
        // Lista todas as máquinas registadas
        $devices = Device::all();
        return view('admin.devices.index', compact('devices'));
    }

    public function showDevice($id)
    {
        // Busca a máquina e as aplicações instaladas nela
        $device = Device::with('applications')->findOrFail($id);
        
        // Vai buscar a Whitelist de aplicações permitidas na empresa
        $allowedApps = DB::table('allowed_appliactions')->pluck('name')->toArray();
        
        // Filtra as aplicações que NÃO estão na Whitelist (ignorando maiúsculas/minúsculas)
        $unauthorizedApps = $device->applications->filter(function ($app) use ($allowedApps) {
            return !in_array(strtolower($app->name), array_map('strtolower', $allowedApps));
        });

        // Calcula a percentagem do nível de conformidade
        $totalApps = $device->applications->count();
        $complianceLevel = $totalApps > 0 
            ? round((($totalApps - $unauthorizedApps->count()) / $totalApps) * 100) 
            : 100;

        return view('admin.devices.show', compact('device', 'unauthorizedApps', 'complianceLevel'));
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

        return back()->with('success', 'Ordem de bloqueio registada. A máquina será bloqueada exibindo a sua mensagem na próxima sincronização.');
    }
}