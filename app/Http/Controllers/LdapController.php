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
            $request->session()->regenerate();
            return redirect()->route('home');
        } else {
            return redirect()->route('login')->with('error', 'Credenciais inválidas ou erro de conexão com AD');
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