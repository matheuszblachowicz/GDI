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
        // 1. Busca os utilizadores criados HOJE a partir do log
        $novosUsuarios = LdapLog::where('acao', 'CRIADO')
                                ->whereDate('created_at', now()->toDateString())
                                ->get();

        if ($novosUsuarios->isEmpty()) {
            return back()->with('info', 'Nenhum novo utilizador importado no dia de hoje.');
        }

        // 2. Puxa o template do banco
        $template = EmailTemplate::where('name', 'notificacao_gestor')->first();

        if (!$template) {
            return back()->with('error', 'Não foi possível notificar: Template de e-mail não cadastrado.');
        }

        $emailsEnviados = 0;

        // 3. Itera sobre CADA utilizador novo individualmente
        foreach ($novosUsuarios as $user) {
            
            // Busca os gestores do departamento DESTE utilizador específico
            $gestores = Manager::where('department', $user->departamento)->get();

            // Se não houver gestor cadastrado para este departamento, pula para o próximo funcionário
            if ($gestores->isEmpty()) {
                continue; 
            }

            // Tratamento caso o e-mail não tenha sido gerado
            $emailExibicao = $user->email ? $user->email : 'Sem e-mail cadastrado';

            // 4. Dispara UM e-mail para cada gestor notificando sobre ESTE utilizador
            foreach ($gestores as $gestor) {
                
                // Copia o layout base para substituir as variáveis
                $corpoPersonalizado = $template->body;
                
                // --- SUBSTITUIÇÕES EXCLUSIVAS DESTE COLABORADOR ---
                $corpoPersonalizado = str_replace('[NOME_COLABORADOR]', $user->usuario_nome, $corpoPersonalizado);
                $corpoPersonalizado = str_replace('[LOGIN]', $user->samaccountname, $corpoPersonalizado);
                $corpoPersonalizado = str_replace('[EMAIL_COLABORADOR]', $emailExibicao, $corpoPersonalizado);

                // --- SUBSTITUIÇÕES DO GESTOR E DATA ---
                $corpoPersonalizado = str_replace('[NOME_GESTOR]', $gestor->name, $corpoPersonalizado);
                $corpoPersonalizado = str_replace('[DATA]', now()->format('d/m/Y'), $corpoPersonalizado);

                // Envia o e-mail
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

    // --- MÉTODOS PARA O TEMPLATE DE E-MAIL ---
    public function editEmailTemplate()
    {
        // Busca o template padrão ou cria um vazio se não existir.
        // Já inclui as novas variáveis individuais como exemplo.
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