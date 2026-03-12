<?php

namespace App\Imports;

use App\Ldap\User;
use App\Models\LdapLog;
use LdapRecord\Models\ActiveDirectory\Group;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class UsersImport implements
    OnEachRow,
    WithHeadingRow,
    WithChunkReading,
    SkipsEmptyRows,
    WithValidation,
    SkipsOnError
{
    use SkipsErrors;

    public function chunkSize(): int
    {
        return 200;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            '*.nomfun' => ['required'],
            '*.numcpf' => ['required'],
            '*.numcad' => ['required'],
            '*.sitafa' => ['required'],
        ];
    }

    public function onRow(Row $row)
    {
        try {
            $data = collect($row->toArray())
                ->mapWithKeys(fn ($v, $k) => [trim(strtolower($k)) => $v])
                ->toArray();

            if (!$data) return;

            $nome = $data['nomfun'] ?? null;
            $cpf = $data['numcpf'] ?? null;
            $numcad = $data['numcad'] ?? null;
            $sitafa = $data['sitafa'] ?? null;
            $departamento = $data['departamento'] ?? "Não informado";
            $cargo = $data['cargo'] ?? 'Funcionario';
            $empresa = $data['numemp'] ?? 1;

            if (!$nome || !$cpf) return;

            if ($sitafa == 2 || $sitafa == 7) {
                $this->sitafa($data);
                return;
            }

            $login = $this->generateUniqueUsername($nome);

            $primeiroNome = Str::before($nome, ' ');
            $sobrenome = Str::afterLast($nome, ' ');

            $user = new User();
            $user->cn = $nome;
            $user->givenname = $primeiroNome;
            $user->sn = $sobrenome;
            $user->displayname = $nome;
            $user->samaccountname = $login;
            $user->title = $cargo;
            $user->extensionattribute1 = $cpf;
            $user->extensionattribute3 = $numcad;
            $user->department = $departamento;

            if ($empresa == 1) {
                $email = $login."@refrio.com.br";
                $user->mail = $email;
                $user->physicalDeliveryOfficeName = "JDI";
            } elseif ($empresa == 301) {
                $email = $login."@platlog.com.br";
                $user->mail = $email;
                $user->physicalDeliveryOfficeName = "JDI";
                $user->streetAddress = "Rua Willhelm Winter,301";
                $user->postalCode = "13213907";
                $user->l = "Jundiaí";
                $user->st = "SP";
                $user->co = "BRASIL";
            }

            $user->inside("OU={$departamento},OU=JDI,OU=fastefood,DC=fastefood,DC=local");
            $user->save();

            $acessos=[
                
                      'Suprimentos' => [

                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []

                                         ],

                      'Atendimento' => [ 
                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         ],

                      'Controladoria' => [
                        
                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []

                                         ],
                      'Balanca' => [
                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         ],
                      'Comercial' => [
                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         
                                         ], 
                      'Controle de Fretes' => [

                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []

                                         ],
                       'Financeiro' => [
                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         
                                         ],
                       'Fiscal' => [
                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         
                                         ],
                       'Juridico' => [
                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         
                                         ],
                       'Manutencao' => [

                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         
                                         ],

                       'Ocorrencias' => [

                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         
                                         ],
                        'Rh Dp' => [
                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         
                                         ],
                        'Rouparia' => [
                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         
                                         ],
                        'Sac' => [
                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         
                                         ],
                        'Segurança do Trabalho' => [

                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []

                                         ],
                        'Transporte'=>[

                                         'Auxiliar' => [],
                                         'Assistente' => [],
                                         'Analista' => [],
                                         'Supervior' => [], 
                                         'Coordenador' => [], 
                                         'Gerente' => []
                                         
                                         ], 
            
            
            
            ];

                

                    if(isset($acessos[$departamento][$cargo])){

                        $grupos=$acessos[$departamento][$cargo];

                        foreach($grupos as $grupo){
                            $user->groups()->attach($grupo);
                        }
                    }else{

            
            LdapLog::create([
                'usuario_nome' => $nome,
                'samaccountname' => $login,
                'email' => $email ?? null,
                'acao' => 'CRIADO',
                'departamento' => $departamento,
                'detalhes' => 'Usuário criado via importação Excel'
            ]);
                    } 

        } catch (\Throwable $e) {
            Log::error("Erro importando usuário", [
                'linha' => $row->getIndex(),
                'erro' => $e->getMessage()
            ]);
        }
    }

    private function generateUniqueUsername($nomeCompleto)
    {
        $nomeLimpo = Str::lower(Str::ascii($nomeCompleto));
        $nomeLimpo = preg_replace('/\b(de|da|do|dos|das)\b/', '', $nomeLimpo);

        $partes = array_values(array_filter(explode(' ', $nomeLimpo)));

        $primeiroNome = $partes[0];
        $sobrenome = end($partes);
        
        // Tentativa 1: m.goncalves (Primeira letra + sobrenome)
        $login = substr($primeiroNome, 0, 1).$sobrenome;
        if ($this->isUsernameAvailable($login)) return $login;

        // Tentativas seguintes: magoncalves, matgoncalves, mathgoncalves...
        for ($i = 1; $i < strlen($primeiroNome); $i++) {
            $login = substr($primeiroNome, 0, $i + 1) . $sobrenome;
            if ($this->isUsernameAvailable($login)) {
                return $login;
            }
        }

        throw new \Exception("Não foi possível gerar login único para: " . $nomeCompleto);
    }

    private function isUsernameAvailable($username)
    {
        return !User::where('samaccountname', '=', $username)->exists();
    }

    private function sitafa($data)
    {
        $user = User::where('extensionattribute1', $data['numcpf'])
            ->orWhere('extensionattribute3', $data['numcad'])
            ->first();

        if (!$user) return;

        if ($data['sitafa'] == 2) {
            $user->accountExpires = now();
            $acao = "EXPIRADO";
        } elseif ($data['sitafa'] == 7) {
            $user->userAccountControl = 514;
            $acao = "DESABILITADO";
        }

        $user->save();

        LdapLog::create([
            'usuario_nome' => $data['nomfun'],
            'samaccountname' => $user->samaccountname,
            'email' => $user->mail ?? null,
            'acao' => $acao,
            'departamento' => $data['departamento'] ?? null,
            'detalhes' => 'Usuário alterado via importação Excel'
        ]);
    }
}