@extends('layouts.main')
@section('title', 'Gestão de Horários')
@section('header', 'Working Hours por Grupo (AD)')

@section('content')

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
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
                <input type="text" name="name" placeholder="Ex: Horário Comercial - Vendas" class="w-full border rounded p-2 focus:ring-blue-500" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-sm">Grupo do AD (Exato)</label>
                <input type="text" name="ad_group" placeholder="Ex: G_Vendas" class="w-full border rounded p-2 focus:ring-blue-500" required>
                <p class="text-xs text-gray-500 mt-1">Podes separar vários grupos por vírgula.</p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Hora Início</label>
                    <input type="time" name="start_time" class="w-full border rounded p-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Hora Fim</label>
                    <input type="time" name="end_time" class="w-full border rounded p-2 focus:ring-blue-500" required>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">
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
                    <th class="p-4 text-sm text-gray-600">Horário Permitido</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workingHours as $wh)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 font-semibold">{{ $wh->name }}</td>
                        <td class="p-4">
                            @foreach($wh->ad_groups ?? [] as $group)
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded mr-1">{{ $group }}</span>
                            @endforeach
                        </td>
                        <td class="p-4 text-gray-700">
                            {{ \Carbon\Carbon::parse($wh->start_time)->format('H:i') }} às {{ \Carbon\Carbon::parse($wh->end_time)->format('H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-4 text-center text-gray-500">Nenhuma regra de horário cadastrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection