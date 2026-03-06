@extends('layouts.main')

@section('content')
<div class="p-6 sm:p-10 space-y-6 bg-slate-50 min-h-screen">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/30">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                Auditoria LDAP
            </h1>
            <p class="text-sm text-slate-500 mt-2 font-medium">Registo de atividades de criação e alteração de utilizadores no Active Directory.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <form action="{{ route('admin.ldap_logs.index') }}" method="GET" class="flex flex-col md:flex-row gap-5 items-end">
            
            <div class="flex-1 w-full">
                <label for="search" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pesquisar Utilizador ou Login</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" class="block w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all outline-none" placeholder="Ex: Hitalo ou hitalo.paixao">
                </div>
            </div>

            <div class="w-full md:w-72">
                <label for="acao" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filtrar por Ação</label>
                <div class="relative">
                    <select name="acao" id="acao" class="block w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all outline-none appearance-none">
                        <option value="">Todas as Ações</option>
                        <option value="Criado" {{ request('acao') == 'Criado' ? 'selected' : '' }}>Criado</option>
                        <option value="Expirado" {{ request('acao') == 'Expirado' ? 'selected' : '' }}>Expirado</option>
                        <option value="Desativado" {{ request('acao') == 'Desativado' ? 'selected' : '' }}>Desativado</option>
                        <option value="Erro na Importação" {{ request('acao') == 'Erro na Importação' ? 'selected' : '' }}>Erros / Falhas</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-none inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-xl shadow-md shadow-blue-600/20 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-all">
                    Filtrar
                </button>
                <a href="{{ route('admin.ldap_logs.index') }}" class="flex-1 md:flex-none inline-flex justify-center items-center px-6 py-2.5 border border-slate-200 rounded-xl text-sm font-bold text-slate-600 bg-white hover:bg-slate-50 transition-all">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Data e Hora</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Funcionário</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Login AD</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Ação</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Departamento</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Detalhes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-slate-500">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-slate-900">{{ $log->usuario_nome }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->samaccountname)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ $log->samaccountname }}
                                    </span>
                                @else
                                    <span class="text-sm text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(str_contains(strtolower($log->acao), 'criado'))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> {{ $log->acao }}
                                    </span>
                                @elseif(str_contains(strtolower($log->acao), 'expirado') || str_contains(strtolower($log->acao), 'desativado'))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div> {{ $log->acao }}
                                    </span>
                                @elseif(str_contains(strtolower($log->acao), 'erro') || str_contains(strtolower($log->acao), 'falha'))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div> {{ $log->acao }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">{{ $log->acao }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $log->departamento ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openModal('modal-{{ $log->id }}')" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors font-semibold">
                                    Ver Detalhes
                                </button>

                                <div id="modal-{{ $log->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('modal-{{ $log->id }}')"></div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-200">
                                            <div class="bg-white px-6 pt-6 pb-4">
                                                <div class="flex items-center justify-between mb-5">
                                                    <h3 class="text-lg leading-6 font-black text-slate-900 flex items-center gap-2" id="modal-title">
                                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        Detalhes da Operação
                                                    </h3>
                                                    <button onclick="closeModal('modal-{{ $log->id }}')" class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full p-1.5 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </div>
                                                <div class="mt-2 space-y-4">
                                                    <div>
                                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Funcionário Afetado</p>
                                                        <p class="text-sm text-slate-900 font-semibold mt-1">{{ $log->usuario_nome }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status da Ação</p>
                                                        <p class="text-sm text-slate-900 font-semibold mt-1">{{ $log->acao }}</p>
                                                    </div>
                                                    <div class="pt-2">
                                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Log Técnico do Sistema</p>
                                                        <div class="bg-slate-900 text-green-400 p-4 rounded-xl text-xs font-mono break-words border border-slate-700 shadow-inner">
                                                            {{ $log->detalhes }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-slate-50 px-6 py-4 flex justify-end rounded-b-2xl border-t border-slate-100">
                                                <button type="button" class="px-5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all shadow-sm" onclick="closeModal('modal-{{ $log->id }}')">
                                                    Fechar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm font-semibold text-slate-500">Nenhum registo encontrado com os filtros atuais.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="bg-white px-6 py-4 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        // Pequeno atraso para a animação
        setTimeout(() => {
            modal.querySelector('.transform').classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            modal.querySelector('.transform').classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }, 10);
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.querySelector('.transform').classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        modal.querySelector('.transform').classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200); // Tempo da transição Tailwind
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #94a3b8; }
</style>
@endsection