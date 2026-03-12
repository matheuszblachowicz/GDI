@extends('layouts.main')
@section('title', 'Lista de Utilizadores VIP')
@section('header', 'Utilizadores VIP (Liberação Total)')

@section('content')

@if(session('success'))
    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
        <p class="text-emerald-700 font-bold">{{ session('success') }}</p>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
            <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center border-2 border-amber-100 shadow-sm mb-6">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
            </div>
            <h3 class="text-xl font-black text-slate-800 mb-2">Adicionar VIP</h3>
            <p class="text-sm text-slate-500 mb-6">Máquinas onde este utilizador fizer login terão 100% de compliance automática e alertas suprimidos.</p>
            
            <form action="{{ route('admin.vip_users.store') }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Login do Utilizador (AD/Windows)</label>
                    <input type="text" name="username" required class="w-full border-slate-200 p-4 rounded-xl text-slate-700 focus:border-amber-500 focus:ring-amber-500 font-mono" placeholder="Ex: platlog\diretor01">
                    @error('username') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-black py-4 rounded-xl shadow-lg shadow-amber-200 transition-all active:scale-95 uppercase tracking-widest text-sm">
                    Conceder Liberação VIP
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-slate-800">Lista de Privilegiados</h3>
                <span class="px-3 py-1 bg-slate-200 text-slate-600 rounded-lg text-xs font-black">{{ $vips->count() }} VIPs</span>
            </div>
            
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 text-slate-400 uppercase font-black text-[10px] tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-4">Utilizador (Username)</th>
                        <th class="px-8 py-4">Data de Adição</th>
                        <th class="px-8 py-4 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($vips as $vip)
                    <tr class="hover:bg-slate-50/80 transition-all">
                        <td class="px-8 py-5">
                            <span class="font-mono text-amber-700 font-bold bg-amber-50 px-3 py-1 rounded-lg border border-amber-100">
                                {{ $vip->username }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-sm text-slate-500 font-medium">
                            {{ \Carbon\Carbon::parse($vip->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-8 py-5 text-right">
                            <form action="{{ route('admin.vip_users.destroy', $vip->id) }}" method="POST" onsubmit="return confirm('Retirar privilégios VIP deste utilizador?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors p-2 rounded-lg hover:bg-red-50" title="Remover VIP">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-8 py-16 text-center">
                            <div class="text-slate-300 mb-2"><svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div>
                            <p class="text-slate-500 font-bold">Nenhum utilizador VIP configurado.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection