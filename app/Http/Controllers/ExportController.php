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
    // Removido o array $vipsList, cálculo agora é estritamente baseado em software
    private function calculateCompliance($device, array $allowedAppsList)
    {
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

    public function allDevicesXlsx() 
    { 
        return Excel::download(new DevicesExport, 'relatorio_geral_dispositivos.xlsx'); 
    }

    public function allDevicesPdf()
    {
        ini_set('memory_limit', '1024M'); 
        set_time_limit(300);

        // Query limpa: Traz apenas o dispositivo e as aplicações. 
        // Zero acesso à tabela user_activity_logs.
        $devices = Device::with(['applications' => function ($query) {
            $query->select('id', 'device_id', 'name'); 
        }])->orderBy('hostname', 'asc')->get();

        $allowedAppsList = DB::table('allowed_applications')->pluck('name')->toArray();

        // Variáveis do Dashboard
        $totalDevices = $devices->count();
        $blockedDevices = 0;
        $totalCompliance = 0;
        
        $osDistribution = [];
        $ramDistribution = [];
        $storageDistribution = [];
        $cpuDistribution = [];
        $diskTypeDistribution = [];
        $riskDistribution = ['Seguro (100%)' => 0, 'Atenção (80-99%)' => 0, 'Crítico (<80%)' => 0];
        $allUnauthorizedApps = [];

        foreach ($devices as $device) {
            if ($device->is_blocked) $blockedDevices++;
            
            // Score puro de aplicações (Sem bypass de VIP)
            $device->compliance_score = $this->calculateCompliance($device, $allowedAppsList);
            $totalCompliance += $device->compliance_score;

            // Alimenta Dashboard
            if ($device->compliance_score == 100) $riskDistribution['Seguro (100%)']++;
            elseif ($device->compliance_score >= 80) $riskDistribution['Atenção (80-99%)']++;
            else $riskDistribution['Crítico (<80%)']++;

            $os = $device->os_version ?? 'Desconhecido';
            $osDistribution[$os] = ($osDistribution[$os] ?? 0) + 1;

            $ram = empty($device->ram) ? 'Não Informado' : $device->ram;
            $ramDistribution[$ram] = ($ramDistribution[$ram] ?? 0) + 1;

            $storage = empty($device->storage) ? 'Não Informado' : $device->storage;
            $storageDistribution[$storage] = ($storageDistribution[$storage] ?? 0) + 1;

            $cpu = empty($device->cpu) ? 'Desconhecido' : preg_replace('/(Intel\(R\) Core\(TM\)|CPU|@|GHz|AMD)/i', '', $device->cpu);
            $cpuDistribution[trim($cpu)] = ($cpuDistribution[trim($cpu)] ?? 0) + 1;

            $dType = empty($device->disk_type) || $device->disk_type == 'Unspecified' ? 'Não Especificado' : $device->disk_type;
            $diskTypeDistribution[$dType] = ($diskTypeDistribution[$dType] ?? 0) + 1;

            // Agrupa Softwares Irregulares
            if ($device->applications->count() > 0 && !empty($allowedAppsList)) {
                foreach ($device->applications as $app) {
                    $isAuthorized = false;
                    foreach ($allowedAppsList as $allowed) {
                        if (stripos($app->name, trim($allowed)) !== false) {
                            $isAuthorized = true;
                            break;
                        }
                    }
                    if (!$isAuthorized) {
                        if (!isset($allUnauthorizedApps[$app->name])) $allUnauthorizedApps[$app->name] = [];
                        if (!in_array($device->hostname, $allUnauthorizedApps[$app->name])) {
                            $allUnauthorizedApps[$app->name][] = $device->hostname;
                        }
                    }
                }
            }
        }

        arsort($osDistribution); 
        arsort($ramDistribution); 
        arsort($storageDistribution);
        arsort($cpuDistribution); 
        arsort($diskTypeDistribution);
        uasort($allUnauthorizedApps, function($a, $b) { return count($b) <=> count($a); });

        $avgCompliance = $totalDevices > 0 ? round($totalCompliance / $totalDevices) : 0;
        $activeDevices = $totalDevices - $blockedDevices;

        return Pdf::view('admin.exports.pdf.all_devices', compact(
            'devices', 'totalDevices', 'blockedDevices', 'activeDevices', 'avgCompliance',
            'riskDistribution', 'osDistribution', 'ramDistribution', 'storageDistribution',
            'cpuDistribution', 'diskTypeDistribution', 'allUnauthorizedApps'
        ))
        ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
            $browsershot->setNodeBinary('/usr/local/bin/node')
                        ->setNpmBinary('/usr/local/bin/npm')
                        ->setChromePath('/usr/bin/google-chrome')
                        ->setTimeout(240)
                        ->waitUntilNetworkIdle(false) // Não espera rede externa
                        ->setOption('waitUntil', 'domcontentloaded') // Dispara o PDF mais cedo
                        ->setOption('javascriptEnabled', false) // Desliga JS no Chrome
                        ->addArgs([
                            '--disable-gpu',
                            '--disable-dev-shm-usage',
                            '--disable-software-rasterizer',
                            '--disable-features=IsolateOrigins,site-per-process', 
                            '--mute-audio',
                            '--no-sandbox',
                            '--single-process' 
                        ]);
        })
        ->format('a4')
        ->landscape()
        ->download('Relatorio_Auditoria_Geral_GDI.pdf');
    }

    public function singleDeviceXlsx($id) 
    { 
        return Excel::download(new SingleDeviceExport(Device::with(['applications'])->findOrFail($id)), 'dispositivo.xlsx'); 
    }

    public function singleDevicePdf(Request $request, $id)
    {
        ini_set('memory_limit', '512M'); 
        set_time_limit(120);

        $device = Device::with(['applications' => function ($query) {
            $query->select('id', 'device_id', 'name', 'version');
        }])->findOrFail($id);

        $allowedAppsList = DB::table('allowed_applications')->pluck('name')->toArray();
        
        $unauthorizedApps = $device->applications->filter(function ($app) use ($allowedAppsList) { 
            foreach ($allowedAppsList as $allowed) { 
                if (stripos($app->name, trim($allowed)) !== false) return false; 
            } 
            return true; 
        });

        // Removida a checagem VIP aqui também para padronizar
        return Pdf::view('admin.exports.pdf.single_device', [
            'device'           => $device, 
            'complianceLevel'  => $this->calculateCompliance($device, $allowedAppsList), 
            'unauthorizedApps' => $unauthorizedApps
        ])
        ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
            $browsershot->setNodeBinary('/usr/local/bin/node')
                        ->setNpmBinary('/usr/local/bin/npm')
                        ->setChromePath('/usr/bin/google-chrome')
                        ->setTimeout(120)
                        ->addArgs(['--disable-gpu', '--disable-dev-shm-usage', '--no-sandbox'])
                        ->showBackground()
                        ->margins(15, 15, 15, 15);
        })
        ->format('a4')
        ->download('Relatorio_' . preg_replace('/[^A-Za-z0-9\-]/', '', $device->hostname) . '.pdf');
    }
}