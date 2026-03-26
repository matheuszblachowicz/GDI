<?php

namespace App\Console\Commands\Integracao;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdmitidosExport;
use Carbon\Carbon;
use SoapClient;
use Exception;

class SeniorBuscarAdmitidos extends Command
{
    // O nome do comando que será chamado
    protected $signature = 'senior:buscar-admitidos';
    protected $description = 'Busca colaboradores admitidos no dia anterior no webservice da Senior e gera uma planilha';

    public function handle()
    {
     
        $ontem = Carbon::yesterday()->format('d/m/Y'); 
        
        $this->info("Iniciando busca de admitidos para a data: {$ontem}");

        
        $wsdl = env('SENIOR_WSDL_URL'); 
        $usuario = env('SENIOR_USER'); 
        $senha = env('SENIOR_PASSWORD'); 

        try {
        
            $client = new SoapClient($wsdl, [
                'trace' => 1,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_MEMORY
            ]);

            // Montar o payload conforme o seu XML
            $parametrosSoap = [
                'user' => $usuario,
                'password' => $senha,
                'encryption' => 0,
                'parameters' => [
                    'numEmp' => 301, // Exemplo: Código da Empresa
                    'iniPer' => $ontem,
                    'fimPer' => $ontem,
                    'tipBus' => 1, 
                ]
            ];

            // Fazer a requisição
            $resposta = $client->__soapCall('ColaboradoresAdmitidos', [$parametrosSoap]);

     
            $listaAdmitidos = $resposta->parametersOut->colaboradores ?? [];

            if (empty($listaAdmitidos)) {
                $this->warn('Nenhum colaborador foi admitido no dia anterior.');
                return;
            }
            if (!is_array($listaAdmitidos)) {
                $listaAdmitidos = [$listaAdmitidos];
            }

            $nomeArquivo = 'admitidos/Admitidos_' . Carbon::yesterday()->format('Y_m_d') . '.xlsx';
            
     
            Excel::store(new AdmitidosExport($listaAdmitidos), $nomeArquivo, 'local');

            $this->info("Sucesso! Planilha gerada em: storage/app/private/{$nomeArquivo}");

        } catch (Exception $e) {
            $this->error("Erro ao consumir o WebService: " . $e->getMessage());
            
        }
    }
}