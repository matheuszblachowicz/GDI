<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf; 
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DevicesExport; 
use App\Exports\SingleDeviceExport; 
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    private array $appAuthorizationCache = [];
    private array $allowedAppsCache = [];

    // O(1) Dicionário de Memória - Sem loops de CPU pesados
    private function isAppAuthorized($appName)
    {
        if (isset($this->appAuthorizationCache[$appName])) {
            return $this->appAuthorizationCache[$appName];
        }

        foreach ($this->allowedAppsCache as $allowed) {
            if (stripos($appName, trim($allowed)) !== false) {
                return $this->appAuthorizationCache[$appName] = true;
            }
        }
        return $this->appAuthorizationCache[$appName] = false;
    }

    private function calculateCompliance($device)
    {
        $totalApps = $device->applications->count();
        if ($totalApps === 0 || empty($this->allowedAppsCache)) return 0; 
        
        $unauthorizedCount = 0;
        foreach ($device->applications as $app) {
            if (!$this->isAppAuthorized($app->name)) {
                $unauthorizedCount++;
            }
        }
        return max(0, round((($totalApps - $unauthorizedCount) / $totalApps) * 100));
    }

    public function allDevicesPdf()
    {
        // Aloca recursos para não quebrar em grandes volumes
        ini_set('memory_limit', '1024M'); 
        set_time_limit(300);

        $this->allowedAppsCache = DB::table('allowed_applications')->pluck('name')->toArray();

        /*
         * === A MÁGICA DA PERFORMANCE ESTÁ AQUI ===
         * Em vez de usar with('latestActivityLog') que trava o MySQL, usamos uma Subquery nativa.
         * Traz o nome do utilizador na mesma fração de segundo que traz a máquina.
         */
        $devices = Device::select(
            'devices.id', 'devices.hostname', 'devices.ip_address', 'devices.city', 'devices.cpu', 
            'devices.os_version', 'devices.ram', 'devices.storage', 'devices.disk_type', 'devices.is_blocked'
        )
        ->addSelect(['current_user_name' => DB::table('user_activity_logs')
            ->select('username')
            ->whereColumn('device_id', 'devices.id')
            ->orderBy('event_at', 'desc')
            ->limit(1)
        ])
        ->with(['applications:id,device_id,name'])
        ->orderBy('hostname', 'asc')
        ->get();

        $totalDevices = $devices->count();
        $blockedDevices = 0;
        $totalCompliance = 0;
        
        $osDistribution = []; $ramDistribution = []; $storageDistribution = [];
        $cpuDistribution = []; $diskTypeDistribution = [];
        $riskDistribution = ['Seguro (100%)' => 0, 'Atenção (80-99%)' => 0, 'Crítico (<80%)' => 0];
        $allUnauthorizedApps = [];

        foreach ($devices as $device) {
            if ($device->is_blocked) $blockedDevices++;
            
            // Mapeia o utilizador trazido pela subquery (evita invocar Accessors antigos)
            $device->user_display = !empty($device->current_user_name) ? $device->current_user_name : 'Sem registo';

            // Tratamento de hardware
            $device->os_display = !empty(trim($device->os_version)) ? trim($device->os_version) : 'Desconhecido';
            $device->ram_display = !empty(trim($device->ram)) ? trim($device->ram) : 'Não Informado';
            $device->storage_display = !empty(trim($device->storage)) ? trim($device->storage) : 'Não Informado';
            $device->disk_type_display = (!empty(trim($device->disk_type)) && trim($device->disk_type) !== 'Unspecified') ? trim($device->disk_type) : 'Não Especificado';
            $device->cpu_display = !empty(trim($device->cpu)) ? trim(preg_replace('/(Intel\(R\) Core\(TM\)|CPU|@|GHz|AMD)/i', '', $device->cpu)) : 'Desconhecido';

            $device->compliance_score = $this->calculateCompliance($device);
            $totalCompliance += $device->compliance_score;

            if ($device->compliance_score == 100) $riskDistribution['Seguro (100%)']++;
            elseif ($device->compliance_score >= 80) $riskDistribution['Atenção (80-99%)']++;
            else $riskDistribution['Crítico (<80%)']++;

            $osDistribution[$device->os_display] = ($osDistribution[$device->os_display] ?? 0) + 1;
            $ramDistribution[$device->ram_display] = ($ramDistribution[$device->ram_display] ?? 0) + 1;
            $cpuDistribution[$device->cpu_display] = ($cpuDistribution[$device->cpu_display] ?? 0) + 1;

            foreach ($device->applications as $app) {
                if (!$this->isAppAuthorized($app->name)) {
                    $allUnauthorizedApps[$app->name][] = $device->hostname;
                }
            }
        }

        $avgCompliance = $totalDevices > 0 ? round($totalCompliance / $totalDevices) : 0;
        $activeDevices = $totalDevices - $blockedDevices;

        return Pdf::view('admin.exports.pdf.all_devices', compact(
            'devices', 'totalDevices', 'blockedDevices', 'activeDevices', 'avgCompliance',
            'riskDistribution', 'osDistribution', 'ramDistribution', 'cpuDistribution', 'allUnauthorizedApps'
        ))
        ->withBrowsershot(function ($bs) {
            $bs->setChromePath('/usr/bin/google-chrome')
               ->setTimeout(240)
               ->waitUntilNetworkIdle(false) // Impede que o PDF espere por imagens/fontes externas que não existam
               ->setOption('waitUntil', 'domcontentloaded') // Dispara a geração imediatamente após ler o HTML
               ->setOption('javascriptEnabled', false) // Corta o carregamento do motor V8 do Chrome
               ->addArgs([
                   '--disable-gpu', 
                   '--no-sandbox', 
                   '--disable-dev-shm-usage',
                   '--single-process',
                   '--disable-software-rasterizer'
               ]);
        })
        ->format('a4')
        ->landscape()
        ->download('Relatorio_Auditoria_GDI.pdf');
    }
}