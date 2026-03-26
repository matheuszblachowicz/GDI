<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\WorkingHour;
use App\Models\UserActivityLog;
use App\Models\LdapLog;
use App\Models\Manager;
use App\Models\EmailTemplate;
use App\Mail\ManagerNotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\DevicesExport; 
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    /**
     * Otimizado: Recebe as listas como parâmetros para evitar o problema N+1.
     */
    private function calculateCompliance($device, array $vipsList, array $allowedAppsList)
    {
        $isVip = in_array($device->current_user, $vipsList);
        if ($isVip) return 100;

        $totalApps = $device->applications->count();
        if ($totalApps === 0 || empty($allowedAppsList)) return 0; 

        $unauthorizedCount = $device->applications->filter(function ($app) use ($allowedAppsList) {
            foreach ($allowedAppsList as $allowed) {
                if (stripos($app->name, trim($allowed)) !== false) return false;
            }
            return true;
        })->count();

        return max(0, round((($totalApps - $unauthorizedCount) / $totalApps) * 100));
    }

    public function vipUsers()
    {
        $vips = DB::table('vip_users')->get();
        return view('admin.vip_users.index', compact('vips'));
    }

    public function storeVipUser(Request $request)
    {
        $request->validate(['username' => 'required|string|max:255|unique:vip_users,username']);
        DB::table('vip_users')->insert([
            'username' => trim($request->username),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        
        // Limpa a cache do mapa pois as regras VIP mudaram
        Cache::forget('admin_mapa_locations');
        
        return back()->with('success', 'Utilizador VIP adicionado com sucesso! A liberação total já está ativa para ele.');
    }

    public function destroyVipUser($id)
    {
        DB::table('vip_users')->where('id', $id)->delete();
        Cache::forget('admin_mapa_locations'); // Limpa a cache
        return back()->with('success', 'Privilégios VIP removidos com sucesso.');
    }

    public function exportDevices() 
    {
        return Excel::download(new DevicesExport, 'dispositivos_platlog.xlsx');
    }

    public function devices(Request $request)
    {
        // Otimizado: Carrega os dispositivos juntamente com APENAS o último log de atividade
        $query = Device::with('latestActivityLog');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Pesquisa nativa nas colunas da tabela devices
                $q->where('hostname', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  
                  // Pesquisa otimizada pelo nome de utilizador na tabela de logs (usando a relação)
                  ->orWhereHas('latestActivityLog', function($logQuery) use ($search) {
                      $logQuery->where('username', 'like', "%{$search}%");
                  });
            });
        }

        // Ordena e pagina mantendo a string de busca na URL
        $devices = $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString();
        
        return view('admin.devices.index', compact('devices'));
    }

    public function showDevice($id)
    {
        $device = Device::with('applications')->findOrFail($id);
        
        // Carrega as listas para o cálculo
        $vipsList = DB::table('vip_users')->pluck('username')->toArray();
        $allowedAppsList = DB::table('allowed_applications')->pluck('name')->toArray();
        
        $isVip = in_array($device->current_user, $vipsList);
        
        $unauthorizedApps = $device->applications->filter(function ($app) use ($allowedAppsList) {
            foreach ($allowedAppsList as $allowed) {
                if (stripos($app->name, trim($allowed)) !== false) return false;
            }
            return true;
        });

        // Otimizado: Usando o novo método calculateCompliance
        $complianceLevel = $this->calculateCompliance($device, $vipsList, $allowedAppsList);
        
        $webHistory = UserActivityLog::where('device_id', $id)->whereIn('process_name', ['chrome', 'msedge', 'firefox', 'brave', 'opera'])->orderBy('event_at', 'desc')->limit(100)->get();
        $activityLogs = UserActivityLog::where('device_id', $id)->orderBy('event_at', 'desc')->limit(150)->get();

        return view('admin.devices.show', compact('device', 'unauthorizedApps', 'complianceLevel', 'webHistory', 'activityLogs', 'isVip'));
    }

    public function blockDevice(Request $request, $id)
    {
        $request->validate(['block_message' => 'required|string|max:500']);
        $device = Device::findOrFail($id);
        $device->is_blocked = true; $device->block_message = $request->block_message; $device->save();
        
        Cache::forget('admin_mapa_locations'); // Limpa a cache do mapa para refletir o bloqueio
        
        return back()->with('success', 'Ordem de bloqueio registada com sucesso.');
    }

    public function unblockDevice($id)
    {
        $device = Device::findOrFail($id);
        $device->is_blocked = false; $device->block_message = null; $device->save();
        
        Cache::forget('admin_mapa_locations'); // Limpa a cache
        
        return back()->with('success', 'A estação foi desbloqueada com sucesso.');
    }

    public function updateLocation(Request $request, $id)
    {
        $validated = $request->validate(['city' => 'required|string|max:255']);
        $device = Device::findOrFail($id);
        $enderecoDigitado = trim($validated['city']);
        $updateData = ['city' => $enderecoDigitado, 'latitude' => null, 'longitude' => null];

        try {
            $apiKey = env('GOOGLE_MAPS_KEY');
            $geoResponse = Http::get("https://maps.googleapis.com/maps/api/geocode/json", ['address' => $enderecoDigitado . ', Brasil', 'key' => $apiKey]);

            if ($geoResponse->successful()) {
                $results = $geoResponse->json()['results'] ?? [];
                if (count($results) > 0) {
                    $location = $results[0]['geometry']['location'];
                    $updateData['latitude'] = $location['lat']; $updateData['longitude'] = $location['lng'];
                    
                    $cidadeEncontrada = null;
                    foreach ($results[0]['address_components'] as $component) {
                        if (in_array('locality', $component['types']) || in_array('administrative_area_level_2', $component['types'])) {
                            $cidadeEncontrada = $component['long_name']; break;
                        }
                    }
                    if ($cidadeEncontrada) $updateData['city'] = $cidadeEncontrada;
                }
            }
        } catch (\Exception $e) { Log::error("Erro no Google Geocoding: " . $e->getMessage()); }

        $device->update($updateData);
        Cache::forget('admin_mapa_locations'); // Limpa a cache para nova localização
        
        return back()->with('success', 'Localização atualizada! A máquina foi alocada na cidade de: ' . $updateData['city']);
    }

    public function workingHours()
    {
        $workingHours = WorkingHour::all();
        return view('admin.working_hours.index', compact('workingHours'));
    }

    public function storeWorkingHour(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'ad_group' => 'required|string', 'start_time' => 'required', 'end_time' => 'required']);
        $adGroupsArray = array_filter(array_map('trim', explode(',', $request->ad_group)));
        WorkingHour::create(['name' => $request->name, 'start_time' => $request->start_time, 'end_time' => $request->end_time, 'ad_groups' => $adGroupsArray]);
        return back()->with('success', 'Regra de horário guardada com sucesso!');
    }

    public function editWorkingHour($id)
    {
        $workingHour = WorkingHour::findOrFail($id);
        return view('admin.working_hours.edit', compact('workingHour'));
    }

    public function updateWorkingHour(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255', 'ad_group' => 'required|string', 'start_time' => 'required', 'end_time' => 'required']);
        $workingHour = WorkingHour::findOrFail($id);
        $adGroupsArray = array_filter(array_map('trim', explode(',', $request->ad_group)));
        $workingHour->update(['name' => $request->name, 'start_time' => $request->start_time, 'end_time' => $request->end_time, 'ad_groups' => $adGroupsArray]);
        return redirect()->route('admin.working_hours.index')->with('success', 'Regra de horário atualizada com sucesso!');
    }

    public function destroyWorkingHour($id)
    {
        $workingHour = WorkingHour::findOrFail($id);
        $workingHour->delete();
        return back()->with('success', 'Regra de horário excluída com sucesso!');
    }

    public function allowedApps()
    {
        $apps = DB::table('allowed_applications')->get();
        return view('admin.allowed_apps.index', compact('apps'));
    }

    public function storeAllowedApp(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:allowed_applications,name']);
        DB::table('allowed_applications')->insert(['name' => trim($request->name), 'is_mandatory' => $request->has('is_mandatory'), 'created_at' => now(), 'updated_at' => now()]);
        Cache::forget('admin_mapa_locations');
        return back()->with('success', 'Aplicação adicionada à lista de permitidas!');
    }

    public function destroyAllowedApp($id)
    {
        DB::table('allowed_applications')->where('id', $id)->delete();
        Cache::forget('admin_mapa_locations');
        return back()->with('success', 'Aplicação removida da lista.');
    }

    public function mapa()
    {
        // Otimizado: Faz Cache dos dados pesados do mapa por 5 minutos (300 segundos)
        $locations = Cache::remember('admin_mapa_locations', 300, function () {
            
            // Otimizado: Removido 'activityLogs' daqui. O mapa só precisa das applications.
            $devices = Device::with(['applications'])
                ->where(function($query) { 
                    $query->whereNotNull('latitude')->whereNotNull('longitude')->orWhereNotNull('city'); 
                })->get();

            // Otimizado: Carregamos as regras apenas 1 vez para todo o mapa.
            $vipsList = DB::table('vip_users')->pluck('username')->toArray();
            $allowedAppsList = DB::table('allowed_applications')->pluck('name')->toArray();

            $groupedDevices = $devices->groupBy(function ($device) { return $device->city ?? 'Local Desconhecido'; });
            $locationsArray = [];
            
            foreach ($groupedDevices as $city => $group) {
                $baseLat = null; $baseLng = null;
                $deviceWithCoords = $group->whereNotNull('latitude')->whereNotNull('longitude')->first();
                if ($deviceWithCoords) { $baseLat = (float) $deviceWithCoords->latitude; $baseLng = (float) $deviceWithCoords->longitude; }
                if ($baseLat === null || $baseLng === null) continue;

                $normal = collect(); $blocked = collect(); $lowCompliance = collect();
                
                foreach ($group as $d) {
                    // Otimizado: Usa as listas em memória, eliminando a query N+1.
                    $compliance = $this->calculateCompliance($d, $vipsList, $allowedAppsList);
                    $d->dynamic_compliance = $compliance; 
                    
                    if ($d->is_blocked) { 
                        $blocked->push($d); 
                    } elseif ($compliance < 80) { 
                        $lowCompliance->push($d); 
                    } else { 
                        $normal->push($d); 
                    }
                }

                $mapMachines = function($machines) {
                    return $machines->map(function($d) { 
                        return ['hostname' => $d->hostname, 'ip' => $d->ip_address, 'user' => $d->current_user ?? 'Sem registo', 'blocked' => $d->is_blocked, 'compliance' => $d->dynamic_compliance]; 
                    })->toArray();
                };

                if ($normal->count() > 0) $locationsArray[] = ['city' => $city, 'type' => 'normal', 'lat' => $baseLat, 'lng' => $baseLng, 'total' => $normal->count(), 'machines' => $mapMachines($normal)];
                if ($blocked->count() > 0) $locationsArray[] = ['city' => $city . ' (Bloqueadas)', 'type' => 'blocked', 'lat' => $baseLat + 0.0015, 'lng' => $baseLng + 0.0015, 'total' => $blocked->count(), 'machines' => $mapMachines($blocked)];
                if ($lowCompliance->count() > 0) $locationsArray[] = ['city' => $city . ' (Compliance Baixo)', 'type' => 'low_compliance', 'lat' => $baseLat - 0.0015, 'lng' => $baseLng - 0.0015, 'total' => $lowCompliance->count(), 'machines' => $mapMachines($lowCompliance)];
            }
            
            return $locationsArray;
        });

        return view('admin.mapa', compact('locations'));
    }

    public function userActivity()
    {
        $logs = UserActivityLog::orderBy('event_at', 'desc')->paginate(50);
        $devices = Device::pluck('hostname', 'id');
        return view('admin.user_activity.index', compact('logs', 'devices'));
    }

    public function ldapLogs(Request $request)
    {
        $query = LdapLog::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) { $q->where('usuario_nome', 'like', "%{$search}%")->orWhere('samaccountname', 'like', "%{$search}%"); });
        }
        if ($request->filled('acao')) { $query->where('acao', $request->acao); }
        $logs = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        return view('admin.ldap_logs.index', compact('logs'));
    }

    public function notifyManagers(Request $request)
    {
        $novosUsuarios = LdapLog::where('acao', 'CRIADO')->whereDate('created_at', now()->toDateString())->get();
        if ($novosUsuarios->isEmpty()) return back()->with('info', 'Nenhum novo utilizador importado no dia de hoje.');
        $template = EmailTemplate::where('name', 'notificacao_gestor')->first();
        if (!$template) return back()->with('error', 'Não foi possível notificar: Template de e-mail não cadastrado.');

        $emailsEnviados = 0;
        foreach ($novosUsuarios as $user) {
            $gestores = Manager::where('department', $user->departamento)->get();
            if ($gestores->isEmpty()) continue; 
            $emailExibicao = $user->email ? $user->email : 'Sem e-mail cadastrado';

            foreach ($gestores as $gestor) {
                $corpoPersonalizado = str_replace(['[NOME_COLABORADOR]', '[LOGIN]', '[EMAIL_COLABORADOR]', '[NOME_GESTOR]', '[DATA]'], [$user->usuario_nome, $user->samaccountname, $emailExibicao, $gestor->name, now()->format('d/m/Y')], $template->body);
                
                // Otimizado: Substituído send() por queue() para disparo assíncrono
                Mail::to($gestor->email)->queue(new ManagerNotificationMail($template->subject, $corpoPersonalizado));
                $emailsEnviados++;
            }
        }
        if ($emailsEnviados === 0) return back()->with('warning', 'Novos utilizadores encontrados, mas nenhum gestor correspondente cadastrado.');
        
        return back()->with('success', "Notificações colocadas na fila de envio! ($emailsEnviados e-mails).");
    }

    public function managers()
    {
        $managers = Manager::all();
        return view('admin.managers.index', compact('managers'));
    }

    public function storeManager(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255', 'department' => 'required|string|max:255']);
        Manager::create($request->all());
        return redirect()->route('admin.managers.index')->with('success', 'Gestor cadastrado!');
    }

    public function destroyManager($id)
    {
        Manager::findOrFail($id)->delete();
        return redirect()->route('admin.managers.index')->with('success', 'Gestor removido!');
    }

    public function editEmailTemplate()
    {
        $template = EmailTemplate::firstOrCreate(['name' => 'notificacao_gestor'], ['subject' => 'Novo Colaborador', 'body' => "Olá..."]);
        return view('admin.email_templates.edit', compact('template'));
    }

    public function updateEmailTemplate(Request $request)
    {
        $request->validate(['subject' => 'required|string|max:255', 'body' => 'required|string']);
        $template = EmailTemplate::where('name', 'notificacao_gestor')->firstOrFail();
        $template->update(['subject' => $request->subject, 'body' => $request->body]);
        return redirect()->route('admin.email_template.edit')->with('success', 'Layout atualizado!');
    }

    public function liveStatus()
    {
        $devices = Device::select('id', 'hostname', 'is_blocked', 'updated_at')->get();
        return response()->json($devices);
    }
}