@extends('layouts.app')
@section('title', 'Detalhes: ' . $device->hostname)
@section('header', 'Detalhes da Máquina - ' . $device->hostname)

@section('content')
<div class="bg-white rounded-lg shadow p-6 mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-xl font-bold">Conformidade: <span class="{{ $complianceLevel >= 80 ? 'text-green-600' : 'text-red-600' }}">{{ $complianceLevel }}%</span></h2>
        <p class="text-sm text-gray-500">Com base nas aplicações da Whitelist</p>
    </div>
    <div>
        <button onclick="switchTab('actions')" class="bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700">
            Bloqueio Remoto
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="flex border-b">
        <button onclick="switchTab('apps')" id="tab-btn-apps" class="flex-1 py-3 text-center font-bold text-blue-600 border-b-2 border-blue-600 bg-gray-50">Aplicativos Instalados</button>
        <button onclick="switchTab('actions')" id="tab-btn-actions" class="flex-1 py-3 text-center font-bold text-gray-500 hover:bg-gray-50">Ações & Bloqueio</button>
    </div>

    <div class="p-6">
        <div id="tab-apps" class="block">
            <h3 class="text-lg font-bold mb-4">Software Instalado</h3>
            <ul class="space-y-2">
                @foreach($device->applications as $app)
                    @php
                        // Verifica se está na coleção de não autorizados
                        $isUnauthorized = $unauthorizedApps->contains('id', $app->id);
                    @endphp
                    <li class="p-3 border rounded {{ $isUnauthorized ? 'bg-red-50 border-red-200' : 'bg-gray-50' }}">
                        <span class="font-semibold">{{ $app->name }}</span> (Versão: {{ $app->version }})
                        @if($isUnauthorized)
                            <span class="ml-2 text-xs font-bold text-red-600">Não Autorizado</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <div id="tab-actions" class="hidden">
            <h3 class="text-lg font-bold text-red-600 mb-4">Disparar Bloqueio Remoto</h3>
            <p class="mb-4 text-gray-600">Esta ação enviará uma ordem via JSON na próxima vez que o agente verificar o estado. A máquina do utilizador ficará bloqueada exibindo a mensagem abaixo.</p>
            
            <form action="{{ route('admin.devices.block', $device->id) }}" method="POST" class="max-w-md">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-2">Mensagem de Bloqueio (Padrão ou Personalizada):</label>
                    <textarea name="block_message" rows="3" class="w-full border p-2 rounded focus:ring-red-500 focus:border-red-500" required>O seu acesso foi temporariamente suspenso pelo departamento de T.I. Por favor, entre em contacto com o suporte.</textarea>
                </div>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700 w-full font-bold">
                    Confirmar Bloqueio da Máquina
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tabName) {
        // Esconder todos os conteúdos
        document.getElementById('tab-apps').classList.add('hidden');
        document.getElementById('tab-actions').classList.add('hidden');
        
        // Resetar estilos dos botões
        document.getElementById('tab-btn-apps').className = 'flex-1 py-3 text-center font-bold text-gray-500 hover:bg-gray-50';
        document.getElementById('tab-btn-actions').className = 'flex-1 py-3 text-center font-bold text-gray-500 hover:bg-gray-50';

        // Mostrar tab ativa e alterar estilo do botão
        document.getElementById('tab-' + tabName).classList.remove('hidden');
        document.getElementById('tab-btn-' + tabName).className = 'flex-1 py-3 text-center font-bold text-blue-600 border-b-2 border-blue-600 bg-gray-50';
    }
</script>
@endpush