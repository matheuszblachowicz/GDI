<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class SingleDeviceExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $device;

    public function __construct($device)
    {
        $this->device = $device;
    }

    public function array(): array
    {
        $dados = [];
        
        // 1. Linha de Informações da Máquina
        $dados[] = [
            'INFO', 
            $this->device->hostname, 
            $this->device->ip_address, 
            $this->device->current_user, 
            $this->device->city ?? 'N/A', 
            $this->device->is_blocked ? 'Bloqueado' : 'Ativo'
        ];

        // Linha em branco
        $dados[] = ['', '', '', '', '', ''];

        // 2. Títulos para os Softwares
        $dados[] = ['SOFTWARES INSTALADOS', 'Versão', '', '', '', ''];

        // Softwares
        foreach ($this->device->applications as $app) {
            $dados[] = [$app->name, $app->version ?? 'N/A', '', '', '', ''];
        }

        return $dados;
    }

    public function headings(): array
    {
        return ['Seção / Nome', 'Detalhe 1', 'Detalhe 2', 'Usuário Atual', 'Localização', 'Status'];
    }

    public function title(): string
    {
        return 'Relatório - ' . $this->device->hostname;
    }
}