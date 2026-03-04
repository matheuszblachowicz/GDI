@extends('layouts.main')

@section('title', 'Dashboard - Platlog')
@section('header', 'Visão Geral da Operação WMS/AD')

@section('content')
<div class="space-y-8 animate-fade-in-up">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <div class="bg-gradient-to-br from-white to-blue-50/50 p-6 rounded-2xl shadow-sm border border-blue-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-600 text-white rounded-xl shadow-blue-200 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div>
                <h3 class="text-4xl font-extrabold text-slate-800 tracking-tight">{{ $devicesCount ?? 0 }}</h3>
                <p class="text-sm text-slate-500 font-semibold mt-1">Total de Máquinas</p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-red-50/50 p-6 rounded-2xl shadow-sm border border-red-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-red-500 text-white rounded-xl shadow-red-200 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
            </div>
            <div>
                <h3 class="text-4xl font-extrabold text-slate-800 tracking-tight">{{ $blockedDevices ?? 0 }}</h3>
                <p class="text-sm text-slate-500 font-semibold mt-1">Dispositivos Bloqueados</p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-emerald-50/50 p-6 rounded-2xl shadow-sm border border-emerald-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-emerald-500 text-white rounded-xl shadow-emerald-200 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div>
                <h3 class="text-4xl font-extrabold text-slate-800 tracking-tight">{{ $averageCompliance ?? 100 }}%</h3>
                <p class="text-sm text-slate-500 font-semibold mt-1">Conformidade Global</p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-purple-50/50 p-6 rounded-2xl shadow-sm border border-purple-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-500 text-white rounded-xl shadow-purple-200 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <div>
                <h3 class="text-4xl font-extrabold text-slate-800 tracking-tight">{{ $logsToday ?? 0 }}</h3>
                <p class="text-sm text-slate-500 font-semibold mt-1">Eventos Capturados Hoje</p>
            </div>
        </div>
    </div>

    </div>
@endsection