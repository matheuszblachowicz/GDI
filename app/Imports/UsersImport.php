<?php

namespace App\Imports;

use App\Ldap\User;
use App\Ldap\Group; 
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UsersImport implements OnEachRow, WithHeadingRow
{
    public function onEachRow(Row $row)
    {
        $data = $row->toArray();

        $login = $this->generateUniqueUsername($data['nomefunc']);
        $primeiro_nome = Str::before($data['nomefunc'], ' ');
        $sobrenome = Str::afterLast($data['nomefunc'], ' ');
        
        $user = new User();
        $user->cn = $data['nomefunc'];
        $user->givenname = $primeiro_nome;
        $user->sn = $sobrenome;
        $user->displayname = $primeiro_nome . "  " . $sobrenome;      
        $user->samaccountname = $login;
        $user->title = $data['cargo'];
        $user->extensionattribute1 = $data['cpf'];
        $user->extensionattribute3 = $data['numfunc'];
        $user->department = $data['departamento'];
        $user->inside("OU=" . $data['departamento'] . ",OU=JDI,DC=FASTEFOOD,DC=local");
        
        if($data['sitafa'] == 2 || $data['sitafa'] == 7){
            $this->sitafa($data);
            return; 
        }
        
        if($data['numemp'] == 1){
            $user->mail = $login . "@refrio.com.br";
            $user->physicalDeliveryOfficeName = "JDI";
            $user->streetAddress = " ";
            $user->postalCode = " ";
            $user->l = " ";
            $user->st = " ";
            $user->co = " ";

        } elseif($data['numemp'] == 301) {
            $user->mail = $login . "@platlog.com.br";
            $user->physicalDeliveryOfficeName = "JDI";
            $user->streetAddress = "Rua Willhelm Winter,301";
            $user->postalCode = "13213907";
            $user->l = "Jundiaí";
            $user->st = "SP";
            $user->co = "BRASIL";
        }
        $user->save();

        $departamento = $data['departamento'];
        $cargo = $data['cargo'];
        $acessos = ['Suprimentos' => ['Analista' => [], 'Supervisor' => []]];

        if(isset($acessos[$departamento][$cargo])){
            foreach($acessos[$departamento][$cargo] as $nomeDoGrupo){
                $grupoAD = Group::where('cn', '=', $nomeDoGrupo)->first();
                
                if($grupoAD) {
                    
                    $user->groups()->attach($grupoAD);
                    $user->save();
                } else {
                    Log::warning("Grupo '{$nomeDoGrupo}' não encontrado para associar ao usuário {$login}");
                }
            }
        }
    } 
    private function generateUniqueUsername($nomeCompleto)
    {
        $nomeLimpo = Str::lower(Str::ascii($nomeCompleto));
        $nomeLimpo = preg_replace('/\b(de|da|do|das|dos)\b/', '', $nomeLimpo);
        $partes = array_values(array_filter(explode(' ', $nomeLimpo)));
        
        if (count($partes) === 0) {
            throw new \Exception("Nome em branco ou inválido encontrado na planilha.");
        }

        $primeiroNome = $partes[0];
        $sobrenome = count($partes) > 1 ? end($partes) : '';
        $tamanhoPrimeiroNome = strlen($primeiroNome);

        for ($i = 1; $i <= $tamanhoPrimeiroNome; $i++) {
            $prefixo = substr($primeiroNome, 0, $i);

            if ($i === $tamanhoPrimeiroNome && $sobrenome !== '') {
                $sugestao = $prefixo . '.' . $sobrenome;
            } else {
                $sugestao = $prefixo . $sobrenome;
            }

            $sugestao = rtrim(substr($sugestao, 0, 20), '.');

            if ($this->isUsernameAvailable($sugestao)) {
                return $sugestao;
            }
        }

        $baseFallback = rtrim(substr($primeiroNome . '.' . $sobrenome, 0, 15), '.');
        $contador = 1;
        while (true) {
            $sugestaoFallback = $baseFallback . $contador;
            if ($this->isUsernameAvailable($sugestaoFallback)) {
                return $sugestaoFallback;
            }
            $contador++;
            if ($contador > 99) {
                throw new \Exception("Impossível gerar login para {$nomeCompleto}. Muitas colisões.");
            }
        }
    }

    private function isUsernameAvailable($username)
    {
        try {
            $existe = User::where('samaccountname', '=', $username)->first();
            return !$existe;
        } catch (\Exception $e) {
            throw new \Exception("Falha ao comunicar com o AD: " . $e->getMessage());
        }
    }

    private function sitafa($data)
    {
        $search = User::where('extensionattribute1', $data['cpf'])
                      ->orWhere('extensionattribute3', $data['numfunc'])
                      ->first();

        if (!$search) {
            Log::warning("Sitafa {$data['sitafa']}: Funcionário {$data['nomefunc']} (CPF: {$data['cpf']}) não existe no AD para ser inativado.");
            return;
        }

        if($data['sitafa'] == 2) {
            
            $search->accountExpires = now();
            $search->save();
            Log::info("Usuário {$search->samaccountname} teve a conta expirada.");

        } elseif($data['sitafa'] == 7) {
            
            $search->userAccountControl = 514;
            $search->save();
            Log::info("Usuário {$search->samaccountname} teve a conta desabilitada.");
        }
    }
}