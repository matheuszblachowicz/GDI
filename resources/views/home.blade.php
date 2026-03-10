@extends('layouts.main')

@section('title', 'Dashboard Principal')
@section('header', 'Painel de Controle GDI')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10 animate-fade-in">
    
    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-indigo-500/5 hover:scale-105 transition-transform duration-300">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ativos Totais</span>
        </div>
        <h3 class="text-4xl font-black text-slate-800">{{ $totalDevices }}</h3>
        <p class="text-xs text-slate-400 mt-2 font-bold uppercase">Máquinas Registradas</p>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-emerald-500/5 hover:scale-105 transition-transform duration-300">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sinal Recente</span>
        </div>
        <h3 class="text-4xl font-black text-slate-800">{{ $activeToday }}</h3>
        <p class="text-xs text-emerald-500 mt-2 font-bold uppercase">Vistas hoje</p>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-rose-500/5 hover:scale-105 transition-transform duration-300">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Suspensas</span>
        </div>
        <h3 class="text-4xl font-black text-slate-800">{{ $blockedDevices }}</h3>
        <p class="text-xs text-rose-500 mt-2 font-bold uppercase">Acesso Interrompido</p>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-amber-500/5 hover:scale-105 transition-transform duration-300">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Atenção</span>
        </div>
        <h3 class="text-4xl font-black text-slate-800">{{ $criticalDevices }}</h3>
        <p class="text-xs text-amber-600 mt-2 font-bold uppercase">Compliance Crítico</p>
    </div>
</div>

<div class="bg-indigo-900 rounded-[3rem] p-10 text-white relative overflow-hidden shadow-2xl animate-fade-in-up">
    <div class="relative z-10">
        <h2 class="text-3xl font-black mb-4 tracking-tight">Sistema de GDI Ativo</h2>
        <p class="text-indigo-200 max-w-xl text-lg font-medium">Monitoramento em tempo real do parque Tecnologico da Platlog. Utilize o menu lateral para gerir segurança e inventário.</p>
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="{{ route('admin.devices.index') }}" class="bg-white text-indigo-900 px-8 py-4 rounded-2xl font-black uppercase text-xs shadow-lg hover:bg-indigo-50 transition-all active:scale-95">Gerir Estações</a>
            <a href="{{ route('admin.mapa') }}" class="bg-indigo-700 text-white px-8 py-4 rounded-2xl font-black uppercase text-xs border border-indigo-600 hover:bg-indigo-800 transition-all active:scale-95 text-center">Visualizar Mapa Global</a>
        </div>
    </div>
    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-indigo-800 rounded-full opacity-50"></div>
    <div class="absolute right-20 -top-20 w-40 h-40 bg-indigo-700 rounded-full opacity-30"></div>
</div>
@endsection

@push('styles')
<style>
    .animate-fade-in { animation: fadeIn 0.6s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.8s ease-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush