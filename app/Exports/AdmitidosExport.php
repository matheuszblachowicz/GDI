<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AdmitidosExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $dados;

    public function __construct($dados)
    {
        // Tratamos os objetos que vieram do SOAP transformando-os em arrays associativos
        $this->dados = array_map(function ($item) {
            return [
                'Nome' => $item->nomFun ?? 'N/A',
                'CPF' => $item->numCpf,
                'Matrícula' => $item->numCad ?? 'N/A',
                'Cargo' => $item->titCar ?? 'N/A',
                'SITAFA' =>$item->sitAfa
                // Adicione as propriedades exatas retornadas pela Senior
            ];
        }, $dados);
    }

    public function array(): array
    {
        return $this->dados;
    }

    public function headings(): array
    {
        return [
            'Nome do Colaborador',
            'Matrícula',
            'Cargo',
        ];
    }
}