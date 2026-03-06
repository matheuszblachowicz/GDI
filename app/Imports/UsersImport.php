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

    /**
     * TAMANHO DOS BLOCOS
     */
    public function chunkSize(): int
    {
        return 200;
    }

    /**
     * LINHA DO CABEÇALHO
     */
    public function headingRow(): int
    {
        return 1;
    }

    /**
     * VALIDAÇÃO DAS COLUNAS
     */
    public function rules(): array
    {
        return [
            '*.nomfun' => ['required'],
            '*.numcpf' => ['required'],
            '*.numcad' => ['required'],
            '*.sitafa' => ['required'],
        ];
    }

    /**
     * PROCESSAMENTO DA LINHA
     */
    public function onRow(Row $row)
    {
        try {

            $data = collect($row->toArray())
                ->mapWithKeys(fn ($v, $k) => [trim(strtolower($k)) => $v])
                ->toArray();

            if (!$data) {
                return;
            }

            $nome = $data['nomfun'] ?? null;
            $cpf = $data['numcpf'] ?? null;
            $numcad = $data['numcad'] ?? null;
            $sitafa = $data['sitafa'] ?? null;
            $departamento = $data['departamento'] ?? 'Geral';
            $cargo = $data['cargo'] ?? 'Funcionario';
            $empresa = $data['numemp'] ?? 1;

            if (!$nome || !$cpf) {
                return;
            }

            /**
             * BLOQUEIO / INATIVAÇÃO
             */
            if ($sitafa == 2 || $sitafa == 7) {
                $this->sitafa($data);
                return;
            }

            /**
             * GERAR LOGIN
             */
            $login = $this->generateUniqueUsername($nome);

            $primeiroNome = Str::before($nome, ' ');
            $sobrenome = Str::afterLast($nome, ' ');

            /**
             * CRIA USUÁRIO
             */
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

            /**
             * EMAIL
             */
            if ($empresa == 1) {
                $email=$login."@refrio.com.br";
                $user->mail =$email;

                $user->physicalDeliveryOfficeName = "JDI";

            } elseif ($empresa == 301) {
                $email=$login."@platlog.com.br";
                $user->mail = $email;

                $user->physicalDeliveryOfficeName = "JDI";

                $user->streetAddress = "Rua Willhelm Winter,301";
                $user->postalCode = "13213907";
                $user->l = "Jundiaí";
                $user->st = "SP";
                $user->co = "BRASIL";
            }

            /**
             * OU
             */
            $user->inside("OU={$departamento},OU=JDI,OU=fastefood,DC=fastefood,DC=local");

            $user->save();

            /**
             * LOG
             */
            LdapLog::create([
                'usuario_nome' => $nome,
                'samaccountname' => $login,
                'email' => $email ?? null,
                'acao' => 'CRIADO',
                'departamento' => $departamento,
                'detalhes' => 'Usuário criado via importação Excel'
            ]);

        } catch (\Throwable $e) {

            Log::error("Erro importando usuário", [
                'linha' => $row->getIndex(),
                'erro' => $e->getMessage()
            ]);
        }
    }

    /**
     * GERAR LOGIN ÚNICO
     */
    private function generateUniqueUsername($nomeCompleto)
    {
        $nomeLimpo = Str::lower(Str::ascii($nomeCompleto));
        $nomeLimpo = preg_replace('/\b(de|da|do|dos|das)\b/', '', $nomeLimpo);

        $partes = array_values(array_filter(explode(' ', $nomeLimpo)));

        $primeiro = $partes[0];
        $ultimo = end($partes);

        $base = substr($primeiro . '.' . $ultimo, 0, 20);

        $login = $base;
        $i = 1;

        while (!$this->isUsernameAvailable($login)) {

            $login = substr($base, 0, 18) . $i;

            $i++;

            if ($i > 99) {
                throw new \Exception("Não foi possível gerar login único");
            }
        }

        return $login;
    }

    /**
     * VERIFICA LOGIN
     */
    private function isUsernameAvailable($username)
    {
        return !User::where('samaccountname', '=', $username)->exists();
    }

    /**
     * BLOQUEIO / INATIVAÇÃO
     */
    private function sitafa($data)
    {

        $user = User::where('extensionattribute1', $data['numcpf'])
            ->orWhere('extensionattribute3', $data['numcad'])
            ->first();

        if (!$user) {
            return;
        }

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