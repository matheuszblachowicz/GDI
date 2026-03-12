@extends('layouts.main')
@section('title', 'Aplicações Permitidas')
@section('header', 'Gestão de Software Autorizado (Whitelist)')

@section('content')

{{-- Alertas de Sucesso / Erro --}}
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3 animate-fade-in">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
    <span class="font-medium">{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 animate-fade-in">
    <ul class="list-disc list-inside text-sm font-medium">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Card de Dica sobre a Correspondência Parcial --}}
<div class="bg-blue-50 border border-blue-200 p-5 rounded-2xl mb-6 flex gap-4 items-start shadow-sm">
    <div class="bg-blue-100 p-2 rounded-lg text-blue-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>
    <div>
        <h4 class="font-bold text-blue-900 mb-1">Como funciona o cadastro inteligente?</h4>
        <p class="text-sm text-blue-800 leading-relaxed">
            Você não precisa digitar o nome exato que aparece na máquina do cliente. O sistema utiliza <strong>correspondência parcial</strong>. <br>
            Exemplo: Se você cadastrar <span class="bg-white px-1.5 py-0.5 rounded border border-blue-200 font-mono text-xs font-bold text-blue-900">Chrome</span>, o sistema irá aprovar automaticamente "Google Chrome", "Chrome 120.0" e "Chrome.exe". 
            Cadastre apenas a <strong>palavra-chave principal</strong> do software.
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Coluna 1: Formulário de Cadastro --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sticky top-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nova Aplicação
            </h3>
            
            <form action="{{ route('admin.allowed_apps.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Palavra-chave do Software *</label>
                    <input type="text" name="name" class="w-full border-slate-200 p-3 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm" placeholder="Ex: AnyDesk, Office, Adobe" required>
                </div>
                
                <div class="mb-6 flex items-center gap-2">
                    <input type="checkbox" name="is_mandatory" id="is_mandatory" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <label for="is_mandatory" class="text-sm font-medium text-slate-600 cursor-pointer">Marcar como Obrigatório na máquina</label>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-3 rounded-lg shadow-md hover:bg-blue-700 transition-all font-bold w-full flex justify-center items-center gap-2">
                    Adicionar à Whitelist
                </button>
            </form>
        </div>
    </div>

    {{-- Coluna 2: Lista de Aplicações --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">Aplicações Autorizadas</h3>
                <span class="bg-blue-100 text-blue-700 py-1 px-3 rounded-full text-xs font-bold">{{ $apps->count() }} Registadas</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-semibold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Nome da Aplicação</th>
                            <th class="px-6 py-4 text-center">Obrigatório?</th>
                            <th class="px-6 py-4 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($apps as $app)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800">
                                {{ $app->name }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($app->is_mandatory)
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded border border-amber-200">Sim</span>
                                @else
                                    <span class="text-slate-400 text-xs font-medium">Não</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('admin.allowed_apps.destroy', $app->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta aplicação da whitelist? As máquinas com ela instalada passarão a acusar inconformidade.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-colors" title="Remover">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <p class="font-medium text-lg">Nenhuma aplicação registada.</p>
                                <p class="text-sm mt-1">Utilize o formulário ao lado para começar a criar a sua whitelist.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .animate-fade-in { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush