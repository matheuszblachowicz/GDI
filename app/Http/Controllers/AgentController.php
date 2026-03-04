<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Termo;
use App\Models\Device;
use App\Models\DeviceApplication;
use App\Models\UserActivityLog;
use App\Models\WorkingHour;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AgentController extends Controller
{
    /**
     * Valida Identidade, Localização e Bloqueio Manual
     */
    public function verifyMachine(Request $request)
    {
        $data = $request->json()->all();
        $lat = null; $lng = null;

        // 1. GEOLOCALIZAÇÃO DINÂMICA (Wi-Fi Triangulation)
        if (!empty($data['wifiAccessPoints'])) {
            try {
                $apiKey = env('GOOGLE_MAPS_KEY'); 
                $response = Http::post("https://www.googleapis.com/geolocation/v1/geolocate?key={$apiKey}", [
                    'wifiAccessPoints' => $data['wifiAccessPoints']
                ]);

                if ($response->successful()) {
                    $loc = $response->json()['location'];
                    $lat = $loc['lat'];
                    $lng = $loc['lng'];
                }
            } catch (\Exception $e) {
                Log::error("Erro Google Maps API: " . $e->getMessage());
            }
        }

        // 2. REGISTRO/ATUALIZAÇÃO DO DISPOSITIVO
        $device = Device::updateOrCreate(
            ['hostname' => $data['hostname']],
            [    
                'mac_address' => $data['mac_address'] ?? '00:00:00:00:00:00',
                'os_version'  => $data['os_version'] ?? 'Desconhecido',
                'latitude'    => $lat,
                'longitude'   => $lng,
                'ip_address'  => $data['ip_address'] ?? "ip não enviado",
                'last_seen_at'=> now(),
            ]
        );

        // 3. BLOQUEIO MANUAL (Mensagem que vem da sua View de Device)
        if ($device->is_blocked) {
            return response()->json([
                "allowed" => false, 
                "action"  => "block", 
                "message" => $device->block_message ?? "Acesso suspenso pelo administrador."
            ]);
        }
        
        // 4. BLOQUEIO POR TERMO DE RESPONSABILIDADE
        $termo = Termo::where('maquina', $data['hostname'])
                      ->where('cpf', $data['cpf'] ?? '')
                      ->first();

        if (!$termo) {
            return response()->json([
                "allowed" => false, 
                "action"  => "block", 
                "message" => "O termo de responsabilidade não foi assinado para este CPF."
            ]);
        }

        return response()->json(["allowed" => true, "action" => "allow"]);
    }

    /**
     * Valida Horário de Trabalho
     */
    public function checkWorkingHours(Request $request)
    {
        $currentTime = now()->format('H:i:s');
        $workingHours = WorkingHour::all();

        if ($workingHours->count() > 0) {
            $isAllowed = false;
            foreach ($workingHours as $wh) {
                if ($currentTime >= $wh->start_time && $currentTime <= $wh->end_time) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                return response()->json([
                    "allowed" => false, 
                    "action"  => "block", 
                    "message" => "Fora do horário de expediente permitido pela Platlog."
                ]);
            }
        }

        return response()->json(["allowed" => true, "action" => "allow"]);
    }

    // --- MÉTODOS DE SINCRONIZAÇÃO ---

    public function getApplications(Request $request) {
        $data = $request->json()->all();
        $device = Device::where('hostname', $data['hostname'] ?? '')->first();
        if ($device && isset($data['applications'])) {
            $device->applications()->delete();
            foreach ($data['applications'] as $app) {
                $device->applications()->create([
                    'name' => $app['Name'],
                    'version' => $app['Version'] ?? '1.0'
                ]);
            }
        }
        return response()->json(['message' => 'Apps OK']);
    }

    public function getUserInformation(Request $request) {
        $data = $request->json()->all();
        $device = Device::where('hostname', $data['hostname'] ?? '')->first();
        if ($device && isset($data['events'])) {
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
        return response()->json(['message' => 'Logs OK']);
    }
}