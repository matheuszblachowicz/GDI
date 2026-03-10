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

class AdminController extends Controller
{
    private function calculateCompliance($device)
    {
        $allowedApps = DB::table('allowed_applications')->pluck('name')->toArray();
        $totalApps = $device->applications->count();
        
        // CORREÇÃO: Se o dispositivo não reportou nenhuma aplicação, 
        // a saúde deve ser considerada 0% (ou 1%) para alertar o TI, e não 100%.
        if ($totalApps === 0) {
            return 0; 
        }

        // Se a whitelist estiver vazia, significa que TODAS as aplicações são consideradas não autorizadas
        if (empty($allowedApps)) {
            return 0;
        }

        $unauthorizedCount = $device->applications->filter(function ($app) use ($allowedApps) {
            foreach ($allowedApps as $allowed) {
                // Se o nome da aplicação instalada contiver o nome da permitida, está OK
                if (stripos($app->name, trim($allowed)) !== false) {
                    return false;
                }
            }
            // Se passou pelo loop e não encontrou correspondência na whitelist, é não autorizada
            return true;
        })->count();

        // Calcula a percentagem de saúde
        $compliance = round((($totalApps - $unauthorizedCount) / $totalApps) * 100);

        // Garante que o valor nunca é negativo
        return max(0, $compliance);
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
    // MAPA DE LOCALIDADES (CLUSTERS DINÂMICOS E COMPLIANCE)
    // ---------------------------------------------------------------------
    public function mapa()
    {
        // Carrega relações 'applications' e 'activityLogs' para evitar N+1 queries na hora de calcular compliance e pegar o user
        $devices = Device::with(['applications', 'activityLogs'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $groupedDevices = $devices->groupBy(function ($device) {
            return $device->city ?? 'Local Desconhecido';
        });

        $locations = [];
        
        foreach ($groupedDevices as $city => $group) {
            $firstDevice = $group->first();
            $baseLat = (float) $firstDevice->latitude;
            $baseLng = (float) $firstDevice->longitude;

            $normal = collect();
            $blocked = collect();
            $lowCompliance = collect();

            // Separa os dispositivos nos 3 clusters dinâmicos
            foreach ($group as $d) {
                $compliance = $this->calculateCompliance($d);
                $d->dynamic_compliance = $compliance; // Guarda no objeto temporariamente para uso abaixo

                if ($d->is_blocked) {
                    $blocked->push($d);
                } elseif ($compliance < 80) { // Limite estipulado para Compliance Baixo (pode alterar conforme a necessidade)
                    $lowCompliance->push($d);
                } else {
                    $normal->push($d);
                }
            }

            // Função anônima para mapear os dados da máquina que vão para o frontend
            $mapMachines = function($machines) {
                return $machines->map(function($d) {
                    return [
                        'hostname' => $d->hostname,
                        'ip' => $d->ip_address,
                        'user' => $d->current_user ?? 'Sem registo',
                        'blocked' => $d->is_blocked,
                        'compliance' => $d->dynamic_compliance
                    ];
                })->toArray();
            };

            // Adiciona cluster Normal
            if ($normal->count() > 0) {
                $locations[] = [
                    'city' => $city,
                    'type' => 'normal',
                    'lat' => $baseLat,
                    'lng' => $baseLng,
                    'total' => $normal->count(),
                    'machines' => $mapMachines($normal)
                ];
            }

            // Adiciona cluster de Bloqueados (Adicionamos um offset geográfico bem pequeno)
            if ($blocked->count() > 0) {
                $locations[] = [
                    'city' => $city . ' (Bloqueadas)',
                    'type' => 'blocked',
                    'lat' => $baseLat + 0.0015, 
                    'lng' => $baseLng + 0.0015,
                    'total' => $blocked->count(),
                    'machines' => $mapMachines($blocked)
                ];
            }

            // Adiciona cluster de Compliance Baixo (Offset pro lado oposto)
            if ($lowCompliance->count() > 0) {
                $locations[] = [
                    'city' => $city . ' (Compliance Baixo)',
                    'type' => 'low_compliance',
                    'lat' => $baseLat - 0.0015, 
                    'lng' => $baseLng - 0.0015,
                    'total' => $lowCompliance->count(),
                    'machines' => $mapMachines($lowCompliance)
                ];
            }
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

        $logs = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        
        return view('admin.ldap_logs.index', compact('logs'));
    }

    /**
     * Envia um e-mail INDIVIDUAL aos gestores para cada novo colaborador importado no dia.
     */
    public function notifyManagers(Request $request)
    {
        $novosUsuarios = LdapLog::where('acao', 'CRIADO')
                                ->whereDate('created_at', now()->toDateString())
                                ->get();

        if ($novosUsuarios->isEmpty()) {
            return back()->with('info', 'Nenhum novo utilizador importado no dia de hoje.');
        }

        $template = EmailTemplate::where('name', 'notificacao_gestor')->first();

        if (!$template) {
            return back()->with('error', 'Não foi possível notificar: Template de e-mail não cadastrado.');
        }

        $emailsEnviados = 0;

        foreach ($novosUsuarios as $user) {
            $gestores = Manager::where('department', $user->departamento)->get();

            if ($gestores->isEmpty()) {
                continue; 
            }

            $emailExibicao = $user->email ? $user->email : 'Sem e-mail cadastrado';

            foreach ($gestores as $gestor) {
                $corpoPersonalizado = $template->body;
                
                $corpoPersonalizado = str_replace('[NOME_COLABORADOR]', $user->usuario_nome, $corpoPersonalizado);
                $corpoPersonalizado = str_replace('[LOGIN]', $user->samaccountname, $corpoPersonalizado);
                $corpoPersonalizado = str_replace('[EMAIL_COLABORADOR]', $emailExibicao, $corpoPersonalizado);
                $corpoPersonalizado = str_replace('[NOME_GESTOR]', $gestor->name, $corpoPersonalizado);
                $corpoPersonalizado = str_replace('[DATA]', now()->format('d/m/Y'), $corpoPersonalizado);

                Mail::to($gestor->email)->send(new ManagerNotificationMail($template->subject, $corpoPersonalizado));
                
                $emailsEnviados++;
            }
        }

        if ($emailsEnviados === 0) {
            return back()->with('warning', 'Foram encontrados novos utilizadores, mas nenhum gestor correspondente aos departamentos deles está cadastrado.');
        }

        return back()->with('success', "Notificações individuais enviadas com sucesso! ($emailsEnviados e-mails disparados).");
    }

    // ---------------------------------------------------------------------
    // GESTÃO DE GESTORES E EMAILS
    // ---------------------------------------------------------------------
    public function managers()
    {
        $managers = Manager::all();
        return view('admin.managers.index', compact('managers'));
    }

    public function storeManager(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'department' => 'required|string|max:255',
        ]);

        Manager::create($request->all());

        return redirect()->route('admin.managers.index')->with('success', 'Gestor cadastrado com sucesso!');
    }

    public function destroyManager($id)
    {
        Manager::findOrFail($id)->delete();
        return redirect()->route('admin.managers.index')->with('success', 'Gestor removido com sucesso!');
    }

    public function editEmailTemplate()
    {
        $template = EmailTemplate::firstOrCreate(
            ['name' => 'notificacao_gestor'],
            [
                'subject' => 'Novo Colaborador Adicionado ao Departamento',
                'body' => "Olá [NOME_GESTOR],\n\nInformamos que no dia [DATA], um novo colaborador foi importado para o seu departamento.\n\nDetalhes do Acesso:\n- Nome: [NOME_COLABORADOR]\n- Login: [LOGIN]\n- E-mail: [EMAIL_COLABORADOR]\n\nAtenciosamente,\nEquipa de TI"
            ]
        );

        return view('admin.email_templates.edit', compact('template'));
    }

    public function updateEmailTemplate(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $template = EmailTemplate::where('name', 'notificacao_gestor')->firstOrFail();
        $template->update([
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        return redirect()->route('admin.email_template.edit')->with('success', 'Layout do e-mail atualizado com sucesso!');
    }
}