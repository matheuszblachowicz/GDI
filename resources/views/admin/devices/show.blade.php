@extends('layouts.main')
@section('title', 'Inspeção: ' . $device->hostname)
@section('header', 'Inspeção de Máquina')

@section('content')

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 mb-6 flex flex-col md:flex-row justify-between items-center gap-6">
    <div class="flex items-center gap-6">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center border border-blue-100">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        </div>
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800">{{ $device->hostname }}</h2>
            <p class="text-slate-500 font-medium text-sm flex items-center gap-2 mt-1">
                <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-700">{{ $device->ip_address ?? 'Sem IP' }}</span>
                &bull; Utilizador Atual: <strong class="text-slate-800">{{ $device->current_user ?? 'Desconhecido' }}</strong>
            </p>
        </div>
    </div>
    
    <div class="text-center md:text-right min-w-[200px]">
        <p class="text-sm font-bold text-slate-500 mb-2 uppercase tracking-wider">Nível de Conformidade</p>
        <div class="flex items-center justify-end gap-4">
            <div class="w-full bg-slate-100 rounded-full h-3">
                <div class="h-3 rounded-full {{ $complianceLevel >= 80 ? 'bg-emerald-500' : 'bg-red-500' }}" style="width: {{ $complianceLevel }}%"></div>
            </div>
            <span class="text-2xl font-black {{ $complianceLevel >= 80 ? 'text-emerald-600' : 'text-red-600' }}">{{ $complianceLevel }}%</span>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="flex border-b border-slate-200">
        <button onclick="switchTab('apps')" id="tab-btn-apps" class="flex-1 py-4 text-center font-bold text-blue-600 border-b-2 border-blue-600 bg-blue-50/30 transition-all">Aplicações</button>
        <button onclick="switchTab('web')" id="tab-btn-web" class="flex-1 py-4 text-center font-bold text-slate-500 hover:text-blue-600 hover:bg-slate-50 transition-all border-b border-transparent">Histórico Web</button>
        <button onclick="switchTab('activity')" id="tab-btn-activity" class="flex-1 py-4 text-center font-bold text-slate-500 hover:text-blue-600 hover:bg-slate-50 transition-all border-b border-transparent">Atividade Geral</button>
        <button onclick="switchTab('actions')" id="tab-btn-actions" class="flex-1 py-4 text-center font-bold text-red-500 hover:text-red-600 hover:bg-red-50 transition-all border-l border-slate-200 border-b border-transparent">Ações de Segurança</button>
    </div>

    <div class="p-8">
        <div id="tab-apps" class="block animate-fade-in">
            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">Software Instalado</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($device->applications as $app)
                    @php $isUnauthorized = isset($unauthorizedApps) && $unauthorizedApps->contains('name', $app->name); @endphp
                    <div class="p-4 rounded-xl border flex items-center justify-between {{ $isUnauthorized ? 'bg-red-50/50 border-red-200' : 'bg-slate-50 border-slate-200' }}">
                        <div>
                            <span class="font-bold text-slate-800 block">{{ $app->name }}</span>
                            <span class="text-xs text-slate-500 font-mono">v{{ $app->version ?? 'N/A' }}</span>
                        </div>
                        @if($isUnauthorized)
                            <span class="px-2.5 py-1 bg-red-100 text-red-700 text-xs font-bold rounded border border-red-200">Não Autorizado</span>
                        @endif
                    </div>
                @empty
                    <p class="text-slate-500 italic">Nenhum software reportado ainda.</p>
                @endforelse
            </div>
        </div>

        <div id="tab-web" class="hidden animate-fade-in">
            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">Tráfego de Navegadores</h3>
            <div class="overflow-hidden border border-slate-200 rounded-xl">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-semibold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Data / Hora</th>
                            <th class="px-4 py-3">Utilizador</th>
                            <th class="px-4 py-3">Browser</th>
                            <th class="px-4 py-3">Site / Janela</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($webHistory as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs">{{ \Carbon\Carbon::parse($log->event_at)->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $log->username }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-bold capitalize">{{ str_replace('.exe', '', $log->process_name) }}</span></td>
                            <td class="px-4 py-3 truncate max-w-md" title="{{ $log->active_window_title }}">{{ \Illuminate\Support\Str::limit($log->active_window_title, 60) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Nenhum registo de navegação encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-activity" class="hidden animate-fade-in">
            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">Atividade Geral do Utilizador</h3>
            <div class="overflow-hidden border border-slate-200 rounded-xl">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-semibold text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Data / Hora</th>
                            <th class="px-4 py-3">Utilizador</th>
                            <th class="px-4 py-3">Processo</th>
                            <th class="px-4 py-3">Janela Ativa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($activityLogs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs">{{ \Carbon\Carbon::parse($log->event_at)->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $log->username }}</td>
                            <td class="px-4 py-3 font-medium text-blue-600">{{ $log->process_name }}</td>
                            <td class="px-4 py-3 truncate max-w-md" title="{{ $log->active_window_title }}">{{ \Illuminate\Support\Str::limit($log->active_window_title, 80) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Nenhum registo de atividade encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-actions" class="hidden animate-fade-in">
            <div class="max-w-2xl">
                <h3 class="text-xl font-bold text-red-600 mb-2">Bloqueio Preventivo</h3>
                <form action="{{ route('admin.devices.block', $device->id) }}" method="POST" class="bg-red-50 p-6 rounded-xl border border-red-100 mt-4">
                    @csrf
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-red-900 mb-2">Mensagem a exibir no ecrã bloqueado:</label>
                        <textarea name="block_message" rows="3" class="w-full border-red-200 p-3 rounded-lg focus:ring-red-500 focus:border-red-500" required>{{ $device->block_message ?? 'Acesso temporariamente suspenso.' }}</textarea>
                    </div>
                    <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-lg font-bold w-full">Bloquear Estação Agora</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

@push('scripts')
<script>
    function switchTab(tabName) {
        ['apps', 'web', 'activity', 'actions'].forEach(tab => {
            document.getElementById('tab-' + tab).classList.add('hidden');
            let btn = document.getElementById('tab-btn-' + tab);
            btn.className = "flex-1 py-4 text-center font-bold transition-all text-slate-500 hover:text-blue-600 hover:bg-slate-50 border-b border-transparent " + (tab==='actions' ? 'border-l border-slate-200' : '');
        });
        
        document.getElementById('tab-' + tabName).classList.remove('hidden');
        let activeBtn = document.getElementById('tab-btn-' + tabName);
        
        if(tabName === 'actions') {
            activeBtn.className = "flex-1 py-4 text-center font-bold transition-all text-red-700 bg-red-50 border-b-2 border-red-600 border-l border-slate-200";
        } else {
            activeBtn.className = "flex-1 py-4 text-center font-bold transition-all text-blue-600 border-b-2 border-blue-600 bg-blue-50/30";
        }
    }
</script>
@endpush