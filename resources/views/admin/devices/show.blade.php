@extends('layouts.main')
@section('title', 'Inspeção: ' . $device->hostname)
@section('header', 'Inspeção de Máquina')

@section('content')

@if(session('success'))
    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
        <p class="text-emerald-700 font-bold">{{ session('success') }}</p>
    </div>
@endif

<div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
    <a href="{{ route('admin.devices.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-black hover:bg-slate-200 transition-all shadow-sm border border-slate-200">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Voltar à Lista
    </a>
    <div class="flex gap-3">
        <a href="{{ route('exports.device.pdf', $device->id) }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-red-50 text-red-600 font-black hover:bg-red-500 hover:text-white transition-all shadow-sm border border-red-100">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            PDF Completo
        </a>
        <a href="{{ route('exports.device.xlsx', $device->id) }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-emerald-50 text-emerald-600 font-black hover:bg-emerald-500 hover:text-white transition-all shadow-sm border border-emerald-100">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Exportar Excel
        </a>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-10 mb-8 animate-fade-in">
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-10">
        <div class="flex items-center gap-8">
            <div class="w-24 h-24 bg-indigo-50 text-indigo-600 rounded-[2rem] flex items-center justify-center border-2 border-indigo-100 shadow-sm relative">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <h2 class="text-4xl font-black text-slate-800 tracking-tight mb-3">{{ $device->hostname }}</h2>
                <div class="flex flex-wrap items-center gap-4">
                    <span class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-black font-mono border border-slate-200 shadow-sm">
                        MAC: {{ $device->mac_address ?? 'N/A' }}
                    </span>
                    <span class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-xl text-sm font-black border border-indigo-200">
                        {{ $device->os_version }}
                    </span>
                    
                    <form action="{{ route('admin.devices.update-location', $device->id) }}" method="POST" class="flex items-center gap-2" title="Prima Enter para salvar">
                        @csrf
                        @method('PUT')
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                        <input type="text" name="city" value="{{ $device->city }}" 
                               class="bg-slate-50 border border-slate-200 hover:border-indigo-300 focus:border-indigo-500 focus:bg-white outline-none rounded-lg px-2 py-1 text-slate-600 font-bold text-base w-48 transition-all shadow-sm"
                               onchange="this.form.submit()" placeholder="Definir cidade...">
                    </form>
                    
                    @if($device->is_blocked)
                    <span class="px-4 py-2 bg-red-100 text-red-700 rounded-xl text-sm font-black border border-red-200 shadow-sm animate-pulse">
                        ⚠️ ESTAÇÃO BLOQUEADA
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-12 py-6 xl:py-0 border-y xl:border-none border-slate-100 w-full xl:w-auto">
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Endereço IP</p>
                <p class="font-mono text-lg text-slate-800 font-black">{{ $device->ip_address ?? 'Sem IP' }}</p>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Utilizador Ativo</p>
                <p class="text-lg text-slate-800 font-black flex items-center gap-3">
                    <span class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></span>
                    {{ $device->current_user ?? 'Desconhecido' }}
                    @if($isVip)
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-lg text-[10px] font-black uppercase tracking-widest border border-amber-200 shadow-sm" title="Utilizador com Liberação Total">👑 VIP</span>
                    @endif
                </p>
            </div>
        </div>
        
        <div class="text-left xl:text-right min-w-[250px] w-full xl:w-auto bg-slate-50 xl:bg-transparent p-6 xl:p-0 rounded-3xl">
            <p class="text-sm font-black text-slate-500 mb-3 uppercase tracking-wider">Saúde do Ativo</p>
            <div class="flex items-center xl:justify-end gap-5">
                <div class="w-full xl:w-40 bg-slate-200 rounded-full h-4 overflow-hidden shadow-inner">
                    <div class="h-4 rounded-full transition-all duration-1000 {{ $complianceLevel >= 80 ? 'bg-emerald-500' : ($isVip ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $complianceLevel }}%"></div>
                </div>
                <span class="text-4xl font-black {{ $complianceLevel >= 80 ? 'text-emerald-600' : ($isVip ? 'text-amber-600' : 'text-red-600') }}">{{ $complianceLevel }}%</span>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden mb-12">
    <div class="flex border-b border-slate-200 bg-slate-50/50">
        <button onclick="switchTab('apps')" id="tab-btn-apps" class="flex-1 py-6 text-center text-base font-black text-indigo-600 border-b-4 border-indigo-600 bg-white transition-all">Aplicações</button>
        <button onclick="switchTab('web')" id="tab-btn-web" class="flex-1 py-6 text-center text-base font-bold text-slate-500 hover:text-indigo-600 transition-all border-b-4 border-transparent">Histórico Web</button>
        <button onclick="switchTab('activity')" id="tab-btn-activity" class="flex-1 py-6 text-center text-base font-bold text-slate-500 hover:text-indigo-600 transition-all border-b-4 border-transparent">Atividade Geral</button>
        <button onclick="switchTab('actions')" id="tab-btn-actions" class="flex-1 py-6 text-center text-xs font-black {{ $device->is_blocked ? 'text-emerald-600 hover:bg-emerald-50' : 'text-red-500 hover:bg-red-50' }} border-l border-slate-200 transition-all uppercase tracking-widest">
            Ações da Estação
        </button>
    </div>

    <div class="p-10">
        <div id="tab-apps" class="block animate-fade-in">
            <h3 class="text-xl font-black text-slate-800 mb-8">Software Reportado</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($device->applications as $app)
                    @php 
                        $isUnauthorized = false;
                        if(isset($unauthorizedApps)) {
                            // Convert to collection if it's an array to use contains, or handle directly
                            $appsCollection = is_array($unauthorizedApps) ? collect($unauthorizedApps) : $unauthorizedApps;
                            $isUnauthorized = $appsCollection->contains('name', $app->name);
                        }
                    @endphp
                    <div class="p-6 rounded-[1.5rem] border-2 flex items-center justify-between {{ $isUnauthorized && !$isVip ? 'bg-red-50 border-red-200 shadow-md' : ($isVip && $isUnauthorized ? 'bg-amber-50/30 border-amber-100 shadow-sm' : 'bg-emerald-50/30 border-emerald-100 shadow-sm') }}">
                        <div class="overflow-hidden">
                            <span class="font-black text-lg {{ $isUnauthorized && !$isVip ? 'text-red-800' : ($isVip && $isUnauthorized ? 'text-amber-800' : 'text-emerald-800') }} block truncate" title="{{ $app->name }}">{{ $app->name }}</span>
                            <span class="text-xs text-slate-400 font-black font-mono">VERSÃO: {{ $app->version ?? '1.0' }}</span>
                        </div>
                        @if($isVip && $isUnauthorized)
                            <span class="px-3 py-1.5 bg-amber-500 text-white text-[10px] font-black rounded-xl uppercase tracking-wider shadow-sm">Ignorado (VIP)</span>
                        @elseif($isUnauthorized)
                            <span class="px-3 py-1.5 bg-red-600 text-white text-[10px] font-black rounded-xl uppercase tracking-wider shadow-sm">Alerta Não Autorizado</span>
                        @else
                            <span class="px-3 py-1.5 bg-emerald-600 text-white text-[10px] font-black rounded-xl uppercase tracking-wider shadow-sm">Autorizado</span>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center bg-slate-50 rounded-[2rem] border-4 border-dashed border-slate-200">
                        <p class="text-slate-400 font-black text-lg uppercase tracking-widest">Nenhum software detetado</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div id="tab-web" class="hidden animate-fade-in">
            <div class="overflow-hidden border-2 border-slate-100 rounded-3xl shadow-sm">
                <table class="w-full text-left text-base text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-black text-xs border-b-2 border-slate-100">
                        <tr><th class="px-6 py-5">Data / Hora</th><th class="px-6 py-5">Browser</th><th class="px-6 py-5">Site / Janela</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($webHistory as $log)
                        <tr class="hover:bg-slate-50/80 transition"><td class="px-6 py-5 font-mono text-sm">{{ \Carbon\Carbon::parse($log->event_at)->format('d/m/Y H:i:s') }}</td><td class="px-6 py-5"><span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-black uppercase">{{ str_replace('.exe', '', $log->process_name) }}</span></td><td class="px-6 py-5 truncate max-w-md">{{ \Illuminate\Support\Str::limit($log->active_window_title, 70) }}</td></tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-16 text-center text-slate-400 italic">Sem logs de navegação.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-activity" class="hidden animate-fade-in">
            <div class="overflow-hidden border-2 border-slate-100 rounded-3xl shadow-sm">
                <table class="w-full text-left text-base text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-black text-xs border-b-2 border-slate-100">
                        <tr><th class="px-6 py-5">Data / Hora</th><th class="px-6 py-5">Processo</th><th class="px-6 py-5">Janela</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($activityLogs as $log)
                        <tr class="hover:bg-slate-50 transition"><td class="px-6 py-5 font-mono text-sm">{{ \Carbon\Carbon::parse($log->event_at)->format('d/m/Y H:i:s') }}</td><td class="px-6 py-5 font-black text-indigo-600 uppercase text-xs">{{ $log->process_name }}</td><td class="px-6 py-5 truncate max-w-md">{{ \Illuminate\Support\Str::limit($log->active_window_title, 80) }}</td></tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-16 text-center text-slate-400 italic">Sem atividade reportada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-actions" class="hidden animate-fade-in">
            <div class="max-w-3xl mx-auto py-6 text-center">
                @if($device->is_blocked)
                    <h3 class="text-3xl font-black text-emerald-600 mb-6 uppercase tracking-tight">Desbloquear Estação</h3>
                    <div class="bg-red-50 p-6 rounded-3xl mb-6 border-2 border-red-100 text-left"><p class="text-red-800 font-bold uppercase tracking-wider text-sm mb-2">Motivo do Bloqueio Atual:</p><p class="text-red-600 text-lg">{{ $device->block_message }}</p></div>
                    <form action="{{ route('admin.devices.unblock', $device->id) }}" method="POST" class="bg-emerald-50 p-10 rounded-[3rem] border-2 border-emerald-100 shadow-xl">
                        @csrf <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-10 py-5 rounded-[2rem] font-black w-full shadow-2xl shadow-emerald-200 transition-all active:scale-95 uppercase tracking-widest text-lg">Desbloquear Estação e Liberar Acesso</button>
                    </form>
                @else
                    <h3 class="text-3xl font-black text-red-600 mb-6 uppercase tracking-tight">Bloqueio Crítico</h3>
                    <form action="{{ route('admin.devices.block', $device->id) }}" method="POST" class="bg-red-50 p-10 rounded-[3rem] border-2 border-red-100 shadow-xl">
                        @csrf
                        <div class="mb-8"><label class="block text-sm font-black text-red-900 mb-4 uppercase tracking-widest text-left">Mensagem de Bloqueio:</label><textarea name="block_message" rows="4" class="w-full border-red-200 p-6 rounded-[2rem] text-lg font-medium focus:ring-red-500 shadow-inner" placeholder="Ex: Violação de Segurança Identificada.">{{ $device->block_message ?? 'Acesso suspenso pela Administração de TI.' }}</textarea></div>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-10 py-5 rounded-[2rem] font-black w-full shadow-2xl shadow-red-200 transition-all active:scale-95 uppercase tracking-widest text-lg">Confirmar Bloqueio da Estação</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tabName) {
        let isBlocked = {{ $device->is_blocked ? 'true' : 'false' }};
        ['apps', 'web', 'activity', 'actions'].forEach(tab => {
            document.getElementById('tab-' + tab).classList.add('hidden');
            document.getElementById('tab-btn-' + tab).className = "flex-1 py-6 text-center text-base font-bold transition-all text-slate-500 hover:text-indigo-600 border-b-4 border-transparent " + (tab==='actions' ? 'border-l border-slate-200 text-xs font-black' : '');
        });
        document.getElementById('tab-' + tabName).classList.remove('hidden');
        let activeBtn = document.getElementById('tab-btn-' + tabName);
        if(tabName === 'actions') {
            activeBtn.className = isBlocked ? "flex-1 py-6 text-center text-xs font-black transition-all text-emerald-700 bg-emerald-50 border-b-4 border-emerald-600 border-l border-slate-200" : "flex-1 py-6 text-center text-xs font-black transition-all text-red-700 bg-red-50 border-b-4 border-red-600 border-l border-slate-200";
        } else {
            activeBtn.className = "flex-1 py-6 text-center text-base font-black transition-all text-indigo-600 border-b-4 border-indigo-600 bg-white";
        }
    }
</script>
@endpush