@extends('layouts.app')

@section('title', 'Página Inicial - SinaTech')
@section('header', 'Dashboard Geral')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium uppercase">Total de Máquinas</p>
                <p class="text-2xl font-bold text-gray-800">{{ $devicesCount ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-red-100 rounded-lg text-red-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium uppercase">Bloqueadas</p>
                <p class="text-2xl font-bold text-gray-800">{{ $blockedDevices ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-green-100 rounded-lg text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium uppercase">Conformidade</p>
                <p class="text-2xl font-bold text-gray-800">92%</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-purple-100 rounded-lg text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium uppercase">Atividade Hoje</p>
                <p class="text-2xl font-bold text-gray-800">145 logs</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Ações Rápidas</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('admin.devices.index') }}" class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition flex flex-col items-center text-center">
                    <span class="text-blue-500 mb-2 font-bold">Listar Máquinas</span>
                    <span class="text-xs text-gray-500">Gerencie todos os dispositivos ativos</span>
                </a>
                <a href="{{ route('admin.working_hours.index') }}" class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition flex flex-col items-center text-center">
                    <span class="text-purple-500 mb-2 font-bold">Configurar Horários</span>
                    <span class="text-xs text-gray-500">Defina regras de acesso por grupo AD</span>
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Informações do Sistema</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Servidor AD:</span>
                    <span class="font-mono font-bold text-gray-700">172.20.39.109</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Domínio:</span>
                    <span class="font-bold text-gray-700">fastefood.local</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Status da API:</span>
                    <span class="text-green-600 font-bold">Online</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection