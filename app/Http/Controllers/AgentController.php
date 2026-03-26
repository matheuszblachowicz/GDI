<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Termo;
use App\Models\Device;
use App\Models\DeviceApplication;
use App\Models\UserActivityLog;
use App\Models\WorkingHour;
use App\Mail\NewApplicationAlertMail; // Importação da nova Mailable para o alerta de software
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB; // IMPORTANTE para consultar a tabela de VIPs
use Illuminate\Support\Facades\Mail; // IMPORTANTE para envio de e-mail

class AgentController extends Controller
{
    /**
     * Valida Identidade, Localização e Bloqueio Manual
     */
    public function verifyMachine(Request $request)
    {
        $data = $request->json()->all();
        
        // Pega as coordenadas nativas do Windows (se o PowerShell conseguiu capturar)
        $lat = $data['latitude'] ?? null; 
        $lng = $data['longitude'] ?? null;
        $username = $data['username'] ?? null; // Captura o username do PowerShell
       

        // 1. GEOLOCALIZAÇÃO DINÂMICA (Wi-Fi Triangulation pelo Google)
        if (!empty($data['wifiAccessPoints']) && count($data['wifiAccessPoints']) >= 3) {
            try {
                $apiKey = env('GOOGLE_MAPS_KEY'); 
                $response = Http::post("https://www.googleapis.com/geolocation/v1/geolocate?key={$apiKey}", [
                    'wifiAccessPoints' => $data['wifiAccessPoints']
                ]);

                if ($response->successful()) {
                    $loc = $response->json()['location'];
                    $lat = $loc['lat'];
                    $lng = $loc['lng'];
                } else {
                    $status = $response->status();
                    $erroDoGoogle = json_encode($response->json());
                    Log::error("O GOOGLE REJEITOU O PEDIDO! Status HTTP: {$status} | Motivo: {$erroDoGoogle}");
                }
            } catch (\Exception $e) {
                Log::error("Erro Google Maps API: " . $e->getMessage());
            }
        }

        // 2. PREPARA OS DADOS PARA O BANCO
        $updateData = [
            'mac_address'  => $data['mac_address'] ?? '00:00:00:00:00:00',
            'os_version'   => $data['os_version'] ?? 'Desconhecido',
            'ip_address'   => $data['ip_address'] ?? "ip não enviado",
            'last_seen_at' => now(),
        ];

        if (!empty($lat) && !empty($lng)) {
            $updateData['latitude']  = $lat;
            $updateData['longitude'] = $lng;

            // --- REVERSE GEOCODING PARA OBTER A CIDADE ---
            try {
                $apiKey = env('GOOGLE_MAPS_KEY');
                $geoResponse = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
                    'latlng' => "{$lat},{$lng}",
                    'key' => $apiKey
                ]);

                if ($geoResponse->successful()) {
                    $results = $geoResponse->json()['results'] ?? [];
                    if (count($results) > 0) {
                        foreach ($results[0]['address_components'] as $component) {
                            if (in_array('locality', $component['types']) || in_array('administrative_area_level_2', $component['types'])) {
                                $updateData['city'] = $component['long_name'];
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Erro Google Maps Reverse Geocoding: " . $e->getMessage());
            }
        }

        // 3. REGISTRO/ATUALIZAÇÃO DO DISPOSITIVO
        $device = Device::updateOrCreate(
            ['hostname' => $data['hostname']],
            $updateData
        );

        // 4. VERIFICAÇÃO VIP (LIBERAÇÃO TOTAL) - MANTIDO!
        if ($username) {
            $isVip = DB::table('vip_users')->where('username', $username)->exists();
            if ($isVip) {
                return response()->json([
                    "allowed" => true, 
                    "action"  => "allow", 
                    "message" => "Liberação Total: Utilizador VIP reconhecido."
                ]);
            }
        }

        // 5. BLOQUEIO MANUAL
        if ($device->is_blocked) {
            return response()->json([
                "allowed" => false, 
                "action"  => "block_manual", 
                "message" => $device->block_message ?? "Acesso suspenso pelo administrador."
            ]);
        }
        
        // 6. BLOQUEIO POR TERMO DE RESPONSABILIDADE
        $termo = Termo::where('maquina', $data['hostname'])
                      ->where('cpf', $data['cpf'] ?? '')
                      ->first();

        if (!$termo) {
            return response()->json([
                "allowed" => false, 
                "action"  => "block_termo", 
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
        $data = $request->json()->all();
        $username = $data['username'] ?? null;

        // VERIFICAÇÃO VIP PARA HORÁRIOS: Se for VIP, trabalha à hora que quiser. - MANTIDO!
        if ($username) {
            $isVip = DB::table('vip_users')->where('username', $username)->exists();
            if ($isVip) {
                return response()->json([
                    "allowed" => true, 
                    "action"  => "allow", 
                    "message" => "Utilizador VIP: Restrição de horário ignorada."
                ]);
            }
        }

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

    // --- MÉTODOS DE SINCRONIZAÇÃO E ANÁLISE DE APPS ---

    public function getApplications(Request $request) {
        $data = $request->json()->all();
        $hostname = $data['hostname'] ?? null;
        $username = $data['username'] ?? 'Desconhecido';

        Log::info("Recebendo lista de apps da máquina: ".$hostname);

        if (!$hostname) {
            return response()->json(['error' => 'Hostname não informado'], 400);
        }

        $device = Device::firstOrCreate(
            ['hostname' => $hostname],
            ['mac_address' => '00:00:00:00:00:00', 'os_version' => 'Aguardando Sincronização']
        );

        if (isset($data['applications']) && is_array($data['applications'])) {
            try {
                // Pega a lista de aplicativos que já existem no banco ANTES de limpar (Para a IA saber o que é novo)
                $existingApps = $device->applications()->pluck('name')->toArray();

                $device->applications()->delete();
                
                $contador = 0;
                foreach ($data['applications'] as $app) {
                    $appName = $app['name'] ?? $app['Name'] ?? null;
                    $appVersion = $app['version'] ?? $app['Version'] ?? '1.0';

                    if (!empty($appName)) {
                        $device->applications()->create([
                            'name' => $appName,
                            'version' => $appVersion
                        ]);
                        $contador++;

                        // INTEGRAÇÃO GEMINI IA: Verifica se a aplicação é nova (não estava na lista anterior)
                        if (!empty($existingApps) && !in_array($appName, $existingApps)) {
                            $this->analyzeAndAlertNewApp($device, $appName, $username);
                        }
                    }
                }
                
                Log::info("Foram salvos {$contador} aplicativos para a máquina {$hostname}.");
                return response()->json(['message' => "{$contador} Apps salvos no banco com sucesso!"]);
                
            } catch (\Exception $e) {
                Log::error("Erro no foreach de apps do device {$hostname}: " . $e->getMessage());
                return response()->json(['error' => 'Falha interna ao salvar apps'], 500);
            }
        }

        Log::warning("A máquina {$hostname} enviou o payload sem o array de applications.");
        return response()->json(['message' => 'Nenhum app recebido']);
    }

    /**
     * Aciona a API do Gemini e envia email sobre nova instalação via Classe Mailable
     */
    private function analyzeAndAlertNewApp($device, $appName, $username)
    {
        try {
            $geminiApiKey = env('GEMINI_API_KEY'); 

            $prompt = "Aja como um especialista em segurança da informação. A aplicação Windows '{$appName}' foi instalada. Forneça estritamente: 1. Para que essa ferramenta é comumente usada. 2. Qual é o link oficial ou mais comum para download desta ferramenta.";

            $aiAnalysis = "Análise Pendente - Configurar credencial GEMINI_API_KEY no arquivo .env.";

            if ($geminiApiKey) {
                $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$geminiApiKey}", [
                    "contents" => [
                        ["role" => "user", "parts" => [["text" => $prompt]]]
                    ]
                ]);

                if ($response->successful()) {
                    $aiAnalysis = $response->json('candidates.0.content.parts.0.text') ?? 'Não foi possível extrair a resposta da IA.';
                } else {
                    Log::error("Erro ao consultar Gemini API: " . $response->body());
                }
            }

            // Envia o e-mail estilizado usando a Classe Mailable criada
            Mail::to('ti@platlog.com.br')->send(new NewApplicationAlertMail(
                $device->hostname,
                $username,
                $appName,
                $aiAnalysis
            ));

        } catch (\Exception $e) {
            Log::error("Falha ao analisar e enviar alerta da nova aplicação {$appName}: " . $e->getMessage());
        }
    }

    public function getUserInformation(Request $request) {
        $data = $request->json()->all();
        $hostname = $data['hostname'] ?? null;

        if (!$hostname) {
            return response()->json(['error' => 'Hostname não informado'], 400);
        }

        $device = Device::firstOrCreate(
            ['hostname' => $hostname],
            ['mac_address' => '00:00:00:00:00:00', 'os_version' => 'Aguardando Sincronização']
        );

        if (isset($data['events']) && is_array($data['events'])) {
            try {
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
                return response()->json(['message' => 'Logs OK']);
            } catch (\Exception $e) {
                Log::error("Erro ao salvar log de atividade do device {$hostname}: " . $e->getMessage());
                return response()->json(['error' => 'Falha interna ao salvar logs'], 500);
            }
        }
        
        return response()->json(['message' => 'Nenhum evento recebido']);
    }
}