<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DevicesExport; // O geral que você já tem
use App\Exports\SingleDeviceExport; // O novo que vamos criar
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    // Função auxiliar para calcular compliance
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

    // 1. EXPORTAR TODOS (XLSX)
    public function allDevicesXlsx()
    {
        return Excel::download(new DevicesExport, 'relatorio_geral_dispositivos.xlsx');
    }

    // 2. EXPORTAR TODOS (PDF)
    public function allDevicesPdf()
    {
        $devices = Device::all(); // Pode colocar paginação/limite se forem milhares
        $pdf = Pdf::loadView('admin.exports.pdf.all_devices', compact('devices'))
                  ->setPaper('a4', 'landscape'); // Retrato geral fica melhor em Paisagem
        
        return $pdf->download('relatorio_geral_dispositivos.pdf');
    }

    // 3. EXPORTAR INDIVIDUAL (XLSX)
    public function singleDeviceXlsx($id)
    {
        $device = Device::with('applications')->findOrFail($id);
        return Excel::download(new SingleDeviceExport($device), 'dispositivo_' . $device->hostname . '.xlsx');
    }

    // 4. EXPORTAR INDIVIDUAL (PDF) - O COMPLETO COM CAPA E SUMÁRIO
    public function singleDevicePdf($id)
    {
        $device = Device::with('applications')->findOrFail($id);
        
        $vipsList = DB::table('vip_users')->pluck('username')->toArray();
        $allowedAppsList = DB::table('allowed_applications')->pluck('name')->toArray();
        
        $isVip = in_array($device->current_user, $vipsList);
        $complianceLevel = $this->calculateCompliance($device, $vipsList, $allowedAppsList);
        
        $unauthorizedApps = $device->applications->filter(function ($app) use ($allowedAppsList) {
            foreach ($allowedAppsList as $allowed) {
                if (stripos($app->name, trim($allowed)) !== false) return false;
            }
            return true;
        });

        // Limita a 100 registos para o PDF não estoirar a memória
        $activityLogs = UserActivityLog::where('device_id', $id)->orderBy('event_at', 'desc')->limit(100)->get();

        // Passa o caminho físico da logo em base64 (DomPDF lida melhor com imagens locais convertidas em base64)
        // Coloque a logo do seu sistema na pasta public/images/logo.png
        $logoPath = public_path('images/logo.png'); 
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('admin.exports.pdf.single_device', compact(
            'device', 'complianceLevel', 'unauthorizedApps', 'activityLogs', 'isVip', 'logoBase64'
        ))->setPaper('A4', 'portrait');

        return $pdf->download('Relatorio_' . $device->hostname . '.pdf');
    }
}