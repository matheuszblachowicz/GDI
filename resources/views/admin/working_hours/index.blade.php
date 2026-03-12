@extends('layouts.main')
@section('title', 'Gestão de Horários')
@section('header', 'Working Hours por Grupo (AD)')

@section('content')

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 shadow-sm">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-sm border lg:col-span-1 h-fit">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Nova Regra de Horário</h3>
        <form action="{{ route('admin.working_hours.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-sm">Nome da Regra</label>
                <input type="text" name="name" placeholder="Ex: Horário Comercial - Vendas" class="w-full border rounded p-2 focus:ring-blue-500 outline-none" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-sm">Grupo do AD (Exato)</label>
                <input type="text" name="ad_group" placeholder="Ex: G_Vendas" class="w-full border rounded p-2 focus:ring-blue-500 outline-none" required>
                <p class="text-xs text-gray-500 mt-1">Podes separar vários grupos por vírgula.</p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Hora Início</label>
                    <input type="time" name="start_time" class="w-full border rounded p-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Hora Fim</label>
                    <input type="time" name="end_time" class="w-full border rounded p-2 focus:ring-blue-500 outline-none" required>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition">
                Salvar Regra
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border lg:col-span-2 overflow-hidden">
        <div class="p-6 border-b bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Regras Cadastradas</h3>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="p-4 text-sm text-gray-600">Nome da Regra</th>
                    <th class="p-4 text-sm text-gray-600">Grupos AD</th>
                    <th class="p-4 text-sm text-gray-600 text-center">Horário Permitido</th>
                    <th class="p-4 text-sm text-gray-600 text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workingHours as $wh)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-4 font-semibold text-gray-800">{{ $wh->name }}</td>
                        <td class="p-4">
                            @foreach($wh->ad_groups ?? [] as $group)
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded mr-1 inline-block mt-1">{{ $group }}</span>
                            @endforeach
                        </td>
                        <td class="p-4 text-gray-700 font-mono text-center">
                            {{ \Carbon\Carbon::parse($wh->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($wh->end_time)->format('H:i') }}
                        </td>
                        <td class="p-4 text-right flex justify-end gap-2">
                            <a href="{{ route('admin.working_hours.edit', $wh->id) }}" class="text-blue-500 hover:bg-blue-100 p-2 rounded transition" title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            
                            <form action="{{ route('admin.working_hours.destroy', $wh->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta regra?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:bg-red-100 p-2 rounded transition" title="Excluir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500 italic">Nenhuma regra de horário cadastrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection