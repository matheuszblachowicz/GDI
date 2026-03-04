<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Termo;
use App\Models\Device;
use App\Models\DeviceApplication;
use App\Models\UserActivityLog;
use App\Models\WorkingHour;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    // Verifica se a máquina tem o termo assinado e regista a sua localização
    public function verifyMachine(Request $request)
    {
        $data = $request->json()->all();

        // Atualiza ou regista a máquina e a sua geolocalização
        $device = Device::updateOrCreate(
            ['hostname' => $data['hostname']],
            [    
                'mac_address'=>$data['mac_address'] ?? '00:00:00:00:00:00',
                'os_version' => $data['os_version'] ?? 'Desconhecido',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'ip_address' => $data['ip_address'] ?? $request->ip(),
                'last_seen_at' => now(),
            ]
        );
        
        // Verifica se existe o termo de responsabilidade
        $termo = Termo::where('maquina', $data['hostname'])
                      ->where('cpf', $data['cpf'] ?? '')
                      ->first();

        // Se existir o termo, permite; caso contrário, bloqueia
        if ($termo) {
            return response()->json(["allowed" => true, "action" => "allow"]);
        } else {
            return response()->json(["allowed" => false, "action" => "block"]);
        }
    }

    // Recebe a lista de programas instalados na máquina
    public function getApplications(Request $request)
    {
        $data = $request->json()->all();
        
        $device = Device::where('hostname', $data['hostname'] ?? '')->first();

        if ($device && isset($data['applications'])) {
            // Limpa as aplicações antigas para evitar dados duplicados
            $device->applications()->delete();

            foreach ($data['applications'] as $app) {
                $application = new DeviceApplication;
                $application->device_id = $device->id;
                $application->name = $app['Name'];
                $application->version = $app['Version'] ?? 'Desconhecida';
                $application->save();
            }
        }
        
        return response()->json(['message' => 'Aplicações sincronizadas com sucesso']);
    }

    // Recebe e regista as atividades do utilizador na máquina (login, idle, etc.)
    public function getUserInformation(Request $request)
    {
        $data = $request->json()->all();
        
        $device = Device::where('hostname', $data['hostname'] ?? '')->first();

        if (!$device) {
            return response()->json(["error" => "Dispositivo não encontrado."], 404);
        }

        if (isset($data['events']) && is_array($data['events'])) {
            foreach ($data['events'] as $event) {
                UserActivityLog::create([
                    'device_id' => $device->id,
                    'username' => $event['username'] ?? 'Desconhecido',
                    'event_type' => $event['event_type'], 
                    'active_window_title' => $event['active_window_title'] ?? null,
                    'process_name' => $event['process_name'] ?? null,
                    'event_at' => $event['event_at'] ?? now(),
                ]);
            }
        }

        return response()->json(['message' => 'Logs de atividade registados com sucesso']);
    }

    // Verifica se o utilizador está dentro do horário de trabalho permitido para o seu grupo de AD
    public function checkWorkingHours(Request $request)
    {
        $data = $request->json()->all();
        
        $userGroups = $data['ad_groups'] ?? []; 
        $currentTime = now()->format('H:i:s');
        
        $isAllowed = false;

        // Vai buscar as regras cadastradas no painel
        $workingHours = WorkingHour::all();

        foreach ($workingHours as $wh) {
            // O Laravel converte automaticamente o JSON do banco para Array graças ao "casts" no Model
            $allowedGroups = $wh->ad_groups ?? [];

            // Verifica se há intersecção entre os grupos do utilizador e os permitidos na regra
            $hasMatchingGroup = count(array_intersect($userGroups, $allowedGroups)) > 0;

            if ($hasMatchingGroup) {
                if ($currentTime >= $wh->start_time && $currentTime <= $wh->end_time) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        if ($isAllowed) {
            return response()->json([
                "allowed" => true, 
                "action" => "allow",
                "message" => "Dentro do horário de expediente."
            ]);
        }

        return response()->json([
            "allowed" => false, 
            "action" => "block",
            "message" => "Fora do horário de trabalho permitido para o seu departamento."
        ]);
    }
}