<?php

namespace App\Exports;

use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DevicesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $vipsList;
    protected $allowedAppsList;

    public function __construct()
    {
        // Carregamos as listas no construtor para não fazer uma query à base de dados
        // por cada dispositivo (evita lentidão no caso de existirem muitas máquinas)
        $this->vipsList = DB::table('vip_users')->pluck('username')->toArray();
        $this->allowedAppsList = DB::table('allowed_applications')->pluck('name')->toArray();
    }

    /**
    * Busca todos os dispositivos e traz também as aplicações para calcular o compliance
    */
    public function collection()
    {
        return Device::with('applications')->orderBy('hostname', 'asc')->get();
    }

    /**
    * Mapeia os dados de cada dispositivo para uma linha do Excel
    */
    public function map($device): array
    {
        // Lógica de cálculo de Compliance (adaptada do seu Controller)
        $isVip = in_array($device->current_user, $this->vipsList);
        $complianceLevel = 0;

        if ($isVip) {
            $complianceLevel = 100;
        } else {
            $totalApps = $device->applications->count();
            if ($totalApps > 0 && !empty($this->allowedAppsList)) {
                $unauthorizedCount = $device->applications->filter(function ($app) {
                    foreach ($this->allowedAppsList as $allowed) {
                        if (stripos($app->name, trim($allowed)) !== false) return false;
                    }
                    return true;
                })->count();
                $complianceLevel = max(0, round((($totalApps - $unauthorizedCount) / $totalApps) * 100));
            }
        }

        return [
            $device->hostname,
            $device->ip_address ?? 'N/A',
            $device->current_user ?? 'Sem registo',
            $isVip ? 'Sim' : 'Não',
            $device->city ?? 'Local Desconhecido',
            $device->is_blocked ? 'Bloqueado' : 'Ativo',
            $complianceLevel . '%',
            $device->updated_at ? $device->updated_at->format('d/m/Y H:i') : 'N/A',
        ];
    }

    /**
    * Define os Títulos das Colunas na primeira linha
    */
    public function headings(): array
    {
        return [
            'Hostname',
            'Endereço IP',
            'Utilizador Atual',
            'É VIP?',
            'Localização',
            'Status',
            'Taxa de Compliance',
            'Última Atualização',
        ];
    }

    /**
    * Estiliza o ficheiro (Ex: coloca a primeira linha a negrito e com fundo azul)
    */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para a linha 1 (Cabeçalhos)
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FF1E3A8A'] // Azul escuro
                ]
            ],
        ];
    }
}