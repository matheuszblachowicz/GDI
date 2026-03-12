@extends('layouts.main')
@section('title', 'Editar Regra de Horário')
@section('header', 'Edição de Regra de Horário')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-sm border">
    <div class="flex items-center justify-between mb-6 border-b pb-4">
        <h3 class="text-xl font-bold text-gray-800">Editar Regra: {{ $workingHour->name }}</h3>
        <a href="{{ route('admin.working_hours.index') }}" class="text-gray-500 hover:text-gray-800 transition">Voltar</a>
    </div>

    <form action="{{ route('admin.working_hours.update', $workingHour->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-5">
            <label class="block text-gray-700 font-bold mb-2 text-sm">Nome da Regra</label>
            <input type="text" name="name" value="{{ $workingHour->name }}" class="w-full border rounded p-3 focus:ring-blue-500 outline-none" required>
        </div>
        
        <div class="mb-5">
            <label class="block text-gray-700 font-bold mb-2 text-sm">Grupo do AD</label>
            <input type="text" name="ad_group" value="{{ implode(', ', $workingHour->ad_groups ?? []) }}" class="w-full border rounded p-3 focus:ring-blue-500 outline-none" required>
            <p class="text-xs text-gray-500 mt-1">Pode separar vários grupos por vírgula.</p>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-gray-700 font-bold mb-2 text-sm">Hora Início</label>
                <input type="time" name="start_time" value="{{ \Carbon\Carbon::parse($workingHour->start_time)->format('H:i') }}" class="w-full border rounded p-3 focus:ring-blue-500 outline-none" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2 text-sm">Hora Fim</label>
                <input type="time" name="end_time" value="{{ \Carbon\Carbon::parse($workingHour->end_time)->format('H:i') }}" class="w-full border rounded p-3 focus:ring-blue-500 outline-none" required>
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded hover:bg-blue-700 transition text-lg">
            Atualizar Regra
        </button>
    </form>
</div>
@endsection