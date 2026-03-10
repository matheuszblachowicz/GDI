@extends('layouts.main')

@section('content')
<div class="container mx-auto p-4">
    
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Gestores por Departamento</h2>
        
        <form action="{{ route('admin.ldap_logs.notify') }}" method="POST">
            @csrf
            <button type="submit" onclick="return confirm('Deseja disparar os e-mails para os gestores com os novos acessos de hoje?');" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors shadow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Disparar E-mails de Hoje
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            {{ session('info') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            {{ session('warning') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded shadow mb-6">
        <form action="{{ route('admin.managers.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Nome do Gestor</label>
                    <input type="text" name="name" class="w-full border rounded p-2 focus:ring focus:ring-blue-200" required placeholder="Ex: Carlos Silva">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">E-mail</label>
                    <input type="email" name="email" class="w-full border rounded p-2 focus:ring focus:ring-blue-200" required placeholder="carlos@empresa.com">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Departamento (Exato)</label>
                    <input type="text" name="department" class="w-full border rounded p-2 focus:ring focus:ring-blue-200" required placeholder="Ex: Financeiro">
                </div>
            </div>
            <button type="submit" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
                Cadastrar Gestor
            </button>
        </form>
    </div>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="p-4 font-semibold text-gray-700">Nome</th>
                    <th class="p-4 font-semibold text-gray-700">E-mail</th>
                    <th class="p-4 font-semibold text-gray-700">Departamento</th>
                    <th class="p-4 font-semibold text-gray-700 text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($managers as $manager)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-4">{{ $manager->name }}</td>
                    <td class="p-4">{{ $manager->email }}</td>
                    <td class="p-4">
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            {{ $manager->department }}
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        <form action="{{ route('admin.managers.destroy', $manager->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este gestor? Ele deixará de receber os e-mails.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium transition-colors">
                                Remover
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-6 text-center text-gray-500">
                        Nenhum gestor cadastrado ainda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection