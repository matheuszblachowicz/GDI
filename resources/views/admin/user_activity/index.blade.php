@extends('layouts.app')
@section('title', 'Atividade dos Utilizadores')
@section('header', 'Logs de Atividade')

@section('content')
<div class="bg-white rounded-lg shadow-sm border overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center bg-gray-50">
        <h3 class="text-lg font-bold text-gray-800">Histórico de Eventos</h3>
        <span class="text-sm text-gray-500">Mostrando os últimos registos</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="p-4 text-sm font-bold text-gray-600">Data e Hora</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Usuário</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Máquina</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Evento</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Janela / Processo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 whitespace-nowrap text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($log->event_at)->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="p-4 font-semibold text-gray-800">{{ $log->username }}</td>
                        <td class="p-4 text-sm text-gray-600">
                            {{ $devices[$log->device_id] ?? 'ID: ' . $log->device_id }}
                        </td>
                        <td class="p-4">
                            @php
                                $badgeClass = match($log->event_type) {
                                    'login' => 'bg-green-100 text-green-800',
                                    'logout' => 'bg-red-100 text-red-800',
                                    'lock' => 'bg-yellow-100 text-yellow-800',
                                    'unlock' => 'bg-blue-100 text-blue-800',
                                    'idle_start' => 'bg-orange-100 text-orange-800',
                                    'idle_end' => 'bg-gray-200 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="{{ $badgeClass }} text-xs font-bold px-2 py-1 rounded uppercase tracking-wide">
                                {{ str_replace('_', ' ', $log->event_type) }}
                            </span>
                        </td>
                        <td class="p-4 text-sm">
                            <div class="text-gray-900 font-medium truncate max-w-xs" title="{{ $log->active_window_title }}">
                                {{ $log->active_window_title ?? '-' }}
                            </div>
                            <div class="text-gray-500 text-xs truncate max-w-xs" title="{{ $log->process_name }}">
                                {{ $log->process_name ?? '-' }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">Nenhuma atividade registada até ao momento.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-4 bg-white border-t">
        {{ $logs->links() }}
    </div>
</div>
@endsection