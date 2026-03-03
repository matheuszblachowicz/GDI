@extends('layouts.main')
@section('title', 'Máquinas')
@section('header', 'Gestão de Máquinas')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-100 border-b">
                <th class="p-4">Hostname</th>
                <th class="p-4">IP</th>
                <th class="p-4">Status</th>
                <th class="p-4">Ação</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devices as $device)
            <tr class="border-b hover:bg-gray-50">
                <td class="p-4 font-semibold">{{ $device->hostname }}</td>
                <td class="p-4">{{ $device->ip_address }}</td>
                <td class="p-4">
                    @if($device->is_blocked)
                        <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded">Bloqueada</span>
                    @else
                        <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">Ativa</span>
                    @endif
                </td>
                <td class="p-4">
                    <a href="{{ route('admin.devices.show', $device->id) }}" class="text-blue-600 hover:underline">Ver Detalhes</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
