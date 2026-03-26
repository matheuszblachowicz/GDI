@extends('layouts.main')

@section('title', 'Gestão de Dispositivos')
@section('header', 'Estações de Trabalho Registadas')

@section('content')
<div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
    
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
        <h2 class="text-2xl font-black text-slate-800 tracking-tight">Lista de Dispositivos</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('exports.devices.pdf') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-red-50 text-red-600 font-black hover:bg-red-500 hover:text-white transition-all shadow-sm border border-red-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Relatório PDF
            </a>
            <a href="{{ route('exports.devices.xlsx') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-50 text-emerald-600 font-black hover:bg-emerald-500 hover:text-white transition-all shadow-sm border border-emerald-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Exportar Excel
            </a>
        </div>
    </div>

    <div class="mb-8 relative">
        <form action="{{ route('admin.devices.index') }}" method="GET">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Pressione ENTER para pesquisar por Hostname, IP ou Versão do SO..." 
                   class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-base focus:ring-2 focus:ring-indigo-500 outline-none transition-all shadow-inner">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="devicesTable">
            <thead>
                <tr class="border-b-2 border-slate-100">
                    <th class="py-5 px-4 text-slate-500 font-extrabold text-sm uppercase tracking-widest">Hostname</th>
                    <th class="py-5 px-4 text-slate-500 font-extrabold text-sm uppercase tracking-widest">IP Address</th>
                    <th class="py-5 px-4 text-slate-500 font-extrabold text-sm uppercase tracking-widest">SO Version</th>
                    <th class="py-5 px-4 text-slate-500 font-extrabold text-sm uppercase tracking-widest">Status</th>
                    <th class="py-5 px-4 text-slate-500 font-extrabold text-sm uppercase tracking-widest text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($devices as $device)
                <tr class="hover:bg-slate-50/80 transition device-row">
                    <td class="py-5 px-4">
                        <span class="text-lg font-black text-slate-800">{{ $device->hostname }}</span>
                    </td>
                    <td class="py-5 px-4 font-mono text-sm text-slate-500">
                        {{ $device->ip_address ?? 'N/A' }}
                    </td>
                    <td class="py-5 px-4 text-base text-slate-600 font-medium">
                        {{ $device->os_version }}
                    </td>
                    <td class="py-5 px-4">
                        @if($device->is_blocked)
                            <span class="px-3 py-1.5 bg-red-100 text-red-700 text-xs font-black rounded-xl uppercase border border-red-200">Bloqueado</span>
                        @else
                            <span class="px-3 py-1.5 bg-emerald-100 text-emerald-700 text-xs font-black rounded-xl uppercase border border-emerald-200">Ativo</span>
                        @endif
                    </td>
                    <td class="py-5 px-4 text-right">
                        <a href="{{ route('admin.devices.show', $device->id) }}" class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-slate-500 font-medium">
                        Nenhuma máquina encontrada.
                        @if(request('search'))
                            <a href="{{ route('admin.devices.index') }}" class="text-indigo-600 hover:underline ml-2">Limpar pesquisa</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $devices->links() }}
    </div>
</div>
@endsection