@extends('layouts.main')
@section('title', 'Máquinas')
@section('header', 'Gestão de Máquinas da Rede')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
        <h2 class="text-lg font-bold text-slate-800">Parque Informático</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 uppercase font-semibold text-xs border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Hostname</th>
                    <th class="px-6 py-4">Endereço IP</th>
                    <th class="px-6 py-4">Último Utilizador</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($devices as $device)
                <tr class="hover:bg-blue-50/50 transition-colors">
                    <td class="px-6 py-4 font-bold text-slate-800 flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        {{ $device->hostname }}
                    </td>
                    <td class="px-6 py-4 font-mono text-xs">{{ $device->ip_address ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold uppercase">
                                {{ substr($device->current_user, 0, 1) }}
                            </div>
                            <span class="font-medium text-slate-700">{{ $device->current_user }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($device->is_blocked)
                            <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Bloqueada
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Ativa
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.devices.show', $device->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-all shadow-sm">
                            Inspecionar
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection