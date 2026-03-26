<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DevicesExport; 
use App\Exports\SingleDeviceExport; 
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    /**
     * Função auxiliar para calcular o nível de compliance de uma máquina.
     * Centralizamos isto aqui para ser reutilizado nas exportações individuais.
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

    // =========================================================================
    // EXPORTAÇÕES GERAIS (TODAS AS MÁQUINAS)
    // =========================================================================

    /**
     * 1. Exportar XLSX com todas as máquinas
     */
    public function allDevicesXlsx()
    {
        return Excel::download(new DevicesExport, 'relatorio_geral_dispositivos.xlsx');
    }

    /**
     * 2. Exportar PDF Dossiê Completo (Com Histórico Web e Gráficos)
     */
    public function allDevicesPdf()
    {
        // 1. Carrega os dispositivos com as aplicações (Evita N+1)
        $devices = Device::with('applications')->orderBy('hostname', 'asc')->get();
        
        // 2. Carrega as regras VIP e Whitelist da base de dados apenas uma vez
        $vipsList = DB::table('vip_users')->pluck('username')->toArray();
        $allowedAppsList = DB::table('allowed_applications')->pluck('name')->toArray();

        // 3. Carrega o histórico web recente de toda a rede (Limitamos a 200 para segurança de memória do PDF)
        $webHistory = UserActivityLog::with('device') // Otimizado para trazer a relação se existir
            ->whereIn('process_name', ['chrome', 'msedge', 'firefox', 'brave', 'opera'])
            ->orderBy('event_at', 'desc')
            ->limit(200)
            ->get();

        // 4. Renderiza a View em Paisagem (Landscape) passando as variáveis
        $pdf = Pdf::loadView('admin.exports.pdf.all_devices', compact('devices', 'vipsList', 'allowedAppsList', 'webHistory'))
                  ->setPaper('a4', 'landscape'); 
        
        return $pdf->download('Dossie_Auditoria_Geral_GDI.pdf');
    }

    // =========================================================================
    // EXPORTAÇÕES INDIVIDUAIS (POR MÁQUINA)
    // =========================================================================

    /**
     * 3. Exportar XLSX de uma única máquina
     */
    public function singleDeviceXlsx($id)
    {
        $device = Device::with('applications')->findOrFail($id);
        
        return Excel::download(new SingleDeviceExport($device), 'dispositivo_' . $device->hostname . '.xlsx');
    }

    /**
     * 4. Exportar PDF detalhado de uma única máquina (Com Capa e Sumário)
     */
    public function singleDevicePdf($id)
    {
        $device = Device::with('applications')->findOrFail($id);
        
        $vipsList = DB::table('vip_users')->pluck('username')->toArray();
        $allowedAppsList = DB::table('allowed_applications')->pluck('name')->toArray();
        
        $isVip = in_array($device->current_user, $vipsList);
        $complianceLevel = $this->calculateCompliance($device, $vipsList, $allowedAppsList);
        
        // Filtra os softwares não autorizados
        $unauthorizedApps = $device->applications->filter(function ($app) use ($allowedAppsList) {
            foreach ($allowedAppsList as $allowed) {
                if (stripos($app->name, trim($allowed)) !== false) return false;
            }
            return true;
        });

        // Limita a 100 registos de atividade para o PDF não ficar gigante e não exceder a memória
        $activityLogs = UserActivityLog::where('device_id', $id)
                            ->orderBy('event_at', 'desc')
                            ->limit(100)
                            ->get();

        // Passa o caminho físico da logo em base64 (DomPDF lida melhor com imagens locais convertidas)
        $logoPath = public_path('images/logo.png'); 
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        // Renderiza a view em formato Retrato (Portrait)
        $pdf = Pdf::loadView('admin.exports.pdf.single_device', compact(
            'device', 'complianceLevel', 'unauthorizedApps', 'activityLogs', 'isVip', 'logoBase64'
        ))->setPaper('A4', 'portrait');

        // Formata o nome do ficheiro para remover espaços ou caracteres estranhos do hostname
        $nomeLimpo = preg_replace('/[^A-Za-z0-9\-]/', '', $device->hostname);
        
        return $pdf->download('Relatorio_' . $nomeLimpo . '.pdf');
    }
}