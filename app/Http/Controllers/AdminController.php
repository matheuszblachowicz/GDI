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
use App\Exports\DevicesExport; 
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    /**
     * Calcula o nível de saúde/compliance da máquina
     */
    private function calculateCompliance($device, array $vipsList, array $allowedAppsList)
    {
        $isVip = in_array($device->latestActivityLog->username ?? null, $vipsList);
        if ($isVip) return 100;

        $totalApps = $device->applications->count();
        if ($totalApps === 0 || empty($allowedAppsList)) return 0; 

        $unauthCount = $device->applications->filter(function ($app) use ($allowedAppsList) {
            foreach ($allowedAppsList as $allowed) { 
                if (stripos($app->name, trim($allowed)) !== false) return false; 
            }
            return true;
        })->count();

        return max(0, round((($totalApps - $unauthCount) / $totalApps) * 100));
    }

    // =========================================================================
    // UTILIZADORES VIP
    // =========================================================================

    public function vipUsers() 
    { 
        return view('admin.vip_users.index', ['vips' => DB::table('vip_users')->get()]); 
    }

    public function storeVipUser(Request $request)
    {
        $request->validate(['username' => 'required|string|max:255|unique:vip_users,username']);
        DB::table('vip_users')->insert(['username' => trim($request->username), 'created_at' => now(), 'updated_at' => now()]);
        Cache::forget('admin_mapa_locations');
        return back()->with('success', 'Utilizador VIP adicionado com sucesso!');
    }

    public function destroyVipUser($id) 
    { 
        DB::table('vip_users')->where('id', $id)->delete(); 
        Cache::forget('admin_mapa_locations'); 
        return back()->with('success', 'Privilégios VIP removidos.'); 
    }

    // =========================================================================
    // GESTÃO DE DISPOSITIVOS E INVENTÁRIO
    // =========================================================================

    public function exportDevices() 
    { 
        return Excel::download(new DevicesExport, 'dispositivos_platlog.xlsx'); 
    }

    public function devices(Request $request)
    {
        // Query ultraleve apenas na tabela de dispositivos
        $query = Device::query();
        
        if ($request->filled('search')) {
            $search = trim($request->search);
            
            // Pesquisa isolada e indexada apenas por Hostname ou IP
            $query->where(function($q) use ($search) {
                $q->where('hostname', 'like', "{$search}%")
                  ->orWhere('ip_address', 'like', "{$search}%");
            });
        }
        
        // Select reduzido (puxa apenas os campos que vão aparecer na tabela da view)
        $devices = $query->select(['id', 'hostname', 'ip_address', 'os_version', 'is_blocked', 'updated_at'])
                         ->orderBy('updated_at', 'desc')
                         ->simplePaginate(20)
                         ->withQueryString();
                         
        return view('admin.devices.index', compact('devices'));
    }

    public function showDevice(Request $request, $id)
    {
        // Na view de detalhes, precisamos carregar os relacionamentos completos
        $device = Device::with(['applications', 'latestActivityLog'])->findOrFail($id);
        
        $vipsList = DB::table('vip_users')->pluck('username')->toArray();
        $allowedAppsList = DB::table('allowed_applications')->pluck('name')->toArray();
        
        $isVip = in_array($device->latestActivityLog->username ?? null, $vipsList);
        $unauthorizedApps = $device->applications->filter(function ($app) use ($allowedAppsList) {
            foreach ($allowedAppsList as $allowed) { 
                if (stripos($app->name, trim($allowed)) !== false) return false; 
            }
            return true;
        });

        $complianceLevel = $this->calculateCompliance($device, $vipsList, $allowedAppsList);
        
        // --- 1. HISTÓRICO WEB (FILTRADO POR TEXTO, NAVEGADOR E DATA/HORA) ---
        $webQuery = UserActivityLog::where('device_id', $id)
            ->whereIn('process_name', ['chrome', 'msedge', 'firefox', 'brave', 'opera']);
            
        if ($request->filled('web_browser')) { $webQuery->where('process_name', $request->web_browser); }
        if ($request->filled('web_search')) { $webQuery->where('active_window_title', 'like', '%' . $request->web_search . '%'); }
        if ($request->filled('web_start')) { $webQuery->where('event_at', '>=', $request->web_start); }
        if ($request->filled('web_end')) { $webQuery->where('event_at', '<=', $request->web_end); }
        
        $webHistory = $webQuery->orderBy('event_at', 'desc')
            ->simplePaginate(15, ['*'], 'web_page')
            ->withQueryString();

        // --- 2. ATIVIDADE GERAL (FILTRADA POR TEXTO E DATA/HORA) ---
        $activityQuery = UserActivityLog::where('device_id', $id);
        
        if ($request->filled('activity_search')) {
            $activityQuery->where(function($q) use ($request) {
                $q->where('active_window_title', 'like', '%' . $request->activity_search . '%')
                  ->orWhere('process_name', 'like', '%' . $request->activity_search . '%');
            });
        }
        if ($request->filled('activity_start')) { $activityQuery->where('event_at', '>=', $request->activity_start); }
        if ($request->filled('activity_end')) { $activityQuery->where('event_at', '<=', $request->activity_end); }
        
        $activityLogs = $activityQuery->orderBy('event_at', 'desc')
            ->simplePaginate(15, ['*'], 'activity_page')
            ->withQueryString();

        $activeTab = $request->get('tab', 'apps');
        
        return view('admin.devices.show', compact('device', 'unauthorizedApps', 'complianceLevel', 'webHistory', 'activityLogs', 'isVip', 'activeTab'));
    }

    public function blockDevice(Request $request, $id) 
    { 
        $request->validate(['block_message' => 'required|string|max:500']); 
        $device = Device::findOrFail($id); 
        $device->is_blocked = true; 
        $device->block_message = $request->block_message; 
        $device->save(); 
        Cache::forget('admin_mapa_locations'); 
        return back()->with('success', 'Ordem de bloqueio registada com sucesso.'); 
    }

    public function unblockDevice($id) 
    { 
        $device = Device::findOrFail($id); 
        $device->is_blocked = false; 
        $device->block_message = null; 
        $device->save(); 
        Cache::forget('admin_mapa_locations'); 
        return back()->with('success', 'A estação foi desbloqueada com sucesso.'); 
    }

    public function updateLocation(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        $updateData = ['city' => trim($request->validate(['city' => 'required|string|max:255'])['city']), 'latitude' => null, 'longitude' => null];
        
        try {
            $geoResponse = Http::get("https://maps.googleapis.com/maps/api/geocode/json", ['address' => $updateData['city'] . ', Brasil', 'key' => env('GOOGLE_MAPS_KEY')]);
            if ($geoResponse->successful() && count($geoResponse->json()['results'] ?? []) > 0) {
                $updateData['latitude'] = $geoResponse->json()['results'][0]['geometry']['location']['lat']; 
                $updateData['longitude'] = $geoResponse->json()['results'][0]['geometry']['location']['lng'];
                foreach ($geoResponse->json()['results'][0]['address_components'] as $component) { 
                    if (in_array('locality', $component['types']) || in_array('administrative_area_level_2', $component['types'])) { 
                        $updateData['city'] = $component['long_name']; break; 
                    } 
                }
            }
        } catch (\Exception $e) {}
        
        $device->update($updateData); 
        Cache::forget('admin_mapa_locations'); 
        return back()->with('success', 'Localização atualizada! Alocada em: ' . $updateData['city']);
    }

    // =========================================================================
    // HORÁRIOS DE EXPEDIENTE (BLOQUEIOS FORA DE HORA)
    // =========================================================================

    public function workingHours() { return view('admin.working_hours.index', ['workingHours' => WorkingHour::all()]); }

    public function storeWorkingHour(Request $request) 
    { 
        $request->validate(['name' => 'required', 'ad_group' => 'required', 'start_time' => 'required', 'end_time' => 'required']); 
        WorkingHour::create(['name' => $request->name, 'start_time' => $request->start_time, 'end_time' => $request->end_time, 'ad_groups' => array_filter(array_map('trim', explode(',', $request->ad_group)))]); 
        return back()->with('success', 'Regra guardada!'); 
    }

    public function editWorkingHour($id) { return view('admin.working_hours.edit', ['workingHour' => WorkingHour::findOrFail($id)]); }

    public function updateWorkingHour(Request $request, $id) 
    { 
        $request->validate(['name' => 'required', 'ad_group' => 'required', 'start_time' => 'required', 'end_time' => 'required']); 
        WorkingHour::findOrFail($id)->update(['name' => $request->name, 'start_time' => $request->start_time, 'end_time' => $request->end_time, 'ad_groups' => array_filter(array_map('trim', explode(',', $request->ad_group)))]); 
        return redirect()->route('admin.working_hours.index')->with('success', 'Regra atualizada!'); 
    }

    public function destroyWorkingHour($id) { WorkingHour::findOrFail($id)->delete(); return back()->with('success', 'Regra excluída!'); }

    // =========================================================================
    // APLICAÇÕES AUTORIZADAS (WHITELIST)
    // =========================================================================

    public function allowedApps() { return view('admin.allowed_apps.index', ['apps' => DB::table('allowed_applications')->get()]); }

    public function storeAllowedApp(Request $request) 
    { 
        $request->validate(['name' => 'required|string|max:255|unique:allowed_applications,name']); 
        DB::table('allowed_applications')->insert(['name' => trim($request->name), 'is_mandatory' => $request->has('is_mandatory'), 'created_at' => now(), 'updated_at' => now()]); 
        Cache::forget('admin_mapa_locations'); 
        return back()->with('success', 'Aplicação adicionada!'); 
    }

    public function destroyAllowedApp($id) 
    { 
        DB::table('allowed_applications')->where('id', $id)->delete(); 
        Cache::forget('admin_mapa_locations'); 
        return back()->with('success', 'Aplicação removida.'); 
    }

    // =========================================================================
    // MAPA DE CALOR E LOCALIZAÇÃO GEOGRÁFICA
    // =========================================================================

    public function mapa()
    {
        $locations = Cache::remember('admin_mapa_locations', 300, function () {
            $devices = Device::with(['applications', 'latestActivityLog'])->where(function($query) { 
                $query->whereNotNull('latitude')->whereNotNull('longitude')->orWhereNotNull('city'); 
            })->get();
            
            $vipsList = DB::table('vip_users')->pluck('username')->toArray();
            $allowedAppsList = DB::table('allowed_applications')->pluck('name')->toArray();
            $locationsArray = [];
            
            foreach ($devices->groupBy(function ($d) { return $d->city ?? 'Local Desconhecido'; }) as $city => $group) {
                $baseLat = null; $baseLng = null;
                $deviceWithCoords = $group->whereNotNull('latitude')->whereNotNull('longitude')->first();
                if ($deviceWithCoords) { $baseLat = (float) $deviceWithCoords->latitude; $baseLng = (float) $deviceWithCoords->longitude; }
                if ($baseLat === null) continue;

                $normal = collect(); $blocked = collect(); $lowCompliance = collect();
                
                foreach ($group as $d) {
                    $compliance = $this->calculateCompliance($d, $vipsList, $allowedAppsList);
                    $d->dynamic_compliance = $compliance; 
                    if ($d->is_blocked) { $blocked->push($d); } elseif ($compliance < 80) { $lowCompliance->push($d); } else { $normal->push($d); }
                }

                $mapMachines = function($machines) { 
                    return $machines->map(function($d) { 
                        return ['hostname' => $d->hostname, 'ip' => $d->ip_address, 'user' => $d->latestActivityLog->username ?? 'Sem registo', 'blocked' => $d->is_blocked, 'compliance' => $d->dynamic_compliance]; 
                    })->toArray(); 
                };
                
                if ($normal->count() > 0) $locationsArray[] = ['city' => $city, 'type' => 'normal', 'lat' => $baseLat, 'lng' => $baseLng, 'total' => $normal->count(), 'machines' => $mapMachines($normal)];
                if ($blocked->count() > 0) $locationsArray[] = ['city' => $city . ' (Bloq)', 'type' => 'blocked', 'lat' => $baseLat + 0.0015, 'lng' => $baseLng + 0.0015, 'total' => $blocked->count(), 'machines' => $mapMachines($blocked)];
                if ($lowCompliance->count() > 0) $locationsArray[] = ['city' => $city . ' (Atenção)', 'type' => 'low_compliance', 'lat' => $baseLat - 0.0015, 'lng' => $baseLng - 0.0015, 'total' => $lowCompliance->count(), 'machines' => $mapMachines($lowCompliance)];
            }
            return $locationsArray;
        });
        
        return view('admin.mapa', compact('locations'));
    }

    // =========================================================================
    // LOGS GERAIS E LDAP
    // =========================================================================

    public function userActivity() 
    { 
        return view('admin.user_activity.index', ['logs' => UserActivityLog::orderBy('event_at', 'desc')->paginate(50), 'devices' => Device::pluck('hostname', 'id')]); 
    }

    public function ldapLogs(Request $request)
    {
        $query = LdapLog::query();
        if ($request->filled('search')) { 
            $search = $request->search; 
            $query->where(function($q) use ($search) { 
                $q->where('usuario_nome', 'like', "%{$search}%")->orWhere('samaccountname', 'like', "%{$search}%"); 
            }); 
        }
        if ($request->filled('acao')) { $query->where('acao', $request->acao); }
        return view('admin.ldap_logs.index', ['logs' => $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString()]);
    }

    // =========================================================================
    // NOTIFICAÇÕES E GESTORES
    // =========================================================================

    public function notifyManagers(Request $request)
    {
        $novosUsuarios = LdapLog::where('acao', 'CRIADO')->whereDate('created_at', now()->toDateString())->get();
        if ($novosUsuarios->isEmpty()) return back()->with('info', 'Nenhum novo utilizador importado no dia de hoje.');
        
        $template = EmailTemplate::where('name', 'notificacao_gestor')->first();
        if (!$template) return back()->with('error', 'Template de e-mail não cadastrado.');

        $emailsEnviados = 0;
        foreach ($novosUsuarios as $user) {
            foreach (Manager::where('department', $user->departamento)->get() as $gestor) {
                Mail::to($gestor->email)->queue(new ManagerNotificationMail($template->subject, str_replace(['[NOME_COLABORADOR]', '[LOGIN]', '[EMAIL_COLABORADOR]', '[NOME_GESTOR]', '[DATA]'], [$user->usuario_nome, $user->samaccountname, $user->email ?? 'N/A', $gestor->name, now()->format('d/m/Y')], $template->body)));
                $emailsEnviados++;
            }
        }
        return back()->with('success', "Notificações na fila de envio! ($emailsEnviados e-mails).");
    }

    public function managers() { return view('admin.managers.index', ['managers' => Manager::all()]); }
    public function storeManager(Request $request) { Manager::create($request->validate(['name' => 'required|string', 'email' => 'required|email', 'department' => 'required|string'])); return back()->with('success', 'Gestor cadastrado!'); }
    public function destroyManager($id) { Manager::findOrFail($id)->delete(); return back()->with('success', 'Gestor removido!'); }
    
    public function editEmailTemplate() { return view('admin.email_templates.edit', ['template' => EmailTemplate::firstOrCreate(['name' => 'notificacao_gestor'], ['subject' => 'Novo Colaborador', 'body' => "Olá..."])]); }
    public function updateEmailTemplate(Request $request) { EmailTemplate::where('name', 'notificacao_gestor')->firstOrFail()->update($request->validate(['subject' => 'required|string', 'body' => 'required|string'])); return back()->with('success', 'Layout atualizado!'); }
    
    // =========================================================================
    // API LIVE STATUS
    // =========================================================================
    
    public function liveStatus() { return response()->json(Device::select('id', 'hostname', 'is_blocked', 'updated_at')->get()); }
}