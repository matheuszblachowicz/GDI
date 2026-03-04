<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Ldap\User;
use Maatwebsite\Excel\Facades\Excel;

class LdapController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function authenticate(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        // Tenta o login via LDAP
        if (Auth::attempt(['samaccountname' => $username, 'password' => $password])) {
            $user = Auth::user();
            
            // Verifica se o utilizador pertence à OU de T.I.
            if(str_contains($user->getDn(), 'OU=Ti,OU=JDI,OU=FASTEFOOD,DC=fastefood,DC=local')){
                // Regenera a sessão por segurança e redireciona para a home
                $request->session()->regenerate();
                return redirect()->route('home');
            }
            
            // Se a password está correta mas o utilizador NÃO é de T.I., faz logout e recusa o acessoS
            Auth::logout();
            return redirect()->route('login')->with('error', 'Acesso restrito: Apenas utilizadores do departamento de T.I.');
            
        } else {
            // Se as credenciais estiverem erradas ou não houver ligação ao AD
            return redirect()->route('login')->with('error', 'Credenciais inválidas');
        }
    }

    public function logout()
    {
        Auth::logout();
        // Corrigido para a rota 'login' que existe no web.php
        return redirect()->route('login');
    }

    public function home()
    {
        return view('home');
    }
    
    private function proccessRequest($request)
    {
        if ($request->isJson()) {
            try {
                $data = $request->json()->all();
                return response()->json(['message' => 'Dados recebidos com sucesso'], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro ao processar os dados', 'error' => $e->getMessage()], 400);
            }
        } elseif ($request->is('*/xml') || $request->is('text/xml')) {
            try {
                $data = $request->getContent();
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro ao processar os dados', 'error' => $e->getMessage()], 400);
            }
        } else {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx'
            ], [
                'file.mimes' => 'O arquivo deve ser do tipo CSV ou xlsx'
            ]);

            return $request->file('file');
        }
    }

    public function createUser(Request $request)
    {
        try {
            $data = $this->proccessRequest($request);
            Excel::import(new UsersImport(), $data);
            return response()->json(['message' => 'Importação concluída com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar os dados', 'error' => $e->getMessage()]);
        }
    }
}