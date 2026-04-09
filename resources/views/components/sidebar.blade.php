<aside class="w-64 bg-slate-900 text-slate-300 flex flex-col min-h-screen transition-all duration-300 font-sans shadow-2xl relative z-20">
    <div class="h-20 flex items-center justify-center border-b border-slate-800 bg-slate-950/50">
        <div class="flex items-center gap-3">
            <span class="text-xl font-black text-white tracking-wider">Plat<span class="text-blue-500">ID</span></span>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto custom-scrollbar">
        <p class="px-3 text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 mt-2">Geral</p>
        
        <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('home') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span class="font-medium text-sm">Dashboard</span>
        </a>

        <a href="{{ route('admin.mapa') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.mapa') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
            <span class="font-medium text-sm">Mapa Global</span>
        </a>

        <div class="h-4"></div>
        <p class="px-3 text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Gestão do Parque</p>

        <a href="{{ route('admin.devices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.devices.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            <span class="font-medium text-sm">Estações de Trabalho</span>
        </a>

        <a href="{{ route('admin.allowed_apps.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.allowed_apps.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <span class="font-medium text-sm">Aplicações Permitidas</span>
        </a>

        <a href="{{ route('admin.user_activity.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.user_activity.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            <span class="font-medium text-sm">Log de Atividades</span>
        </a>

        <div class="h-4"></div>
        <p class="px-3 text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Segurança</p>

        <a href="{{ route('admin.working_hours.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.working_hours.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium text-sm">Regras de Horário</span>
        </a>
        
        <a href="{{ route('admin.vip_users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.vip_users.*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.vip_users.*') ? 'text-white' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
            <span class="font-medium text-sm">Privilégios VIP</span>
        </a>

        <div class="h-4"></div>
        <p class="px-3 text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Auditoria</p>

        <a href="{{ route('admin.ldap_logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.ldap_logs.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span class="font-medium text-sm">Logs do LDAP</span>
        </a>

        <div class="h-4"></div>
        <p class="px-3 text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Configurações</p>

        <a href="{{ route('admin.managers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.managers.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <span class="font-medium text-sm">Gestores</span>
        </a>

        <a href="{{ route('admin.email_template.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.email_template.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            <span class="font-medium text-sm">Template de E-mail</span>
        </a>
    </nav>

    <div class="p-4 border-t border-slate-800 bg-slate-950/30">
        <form method="POST" action="{{ route('logout') ?? '#' }}" class="w-full">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold text-slate-400 hover:text-white hover:bg-red-500/10 hover:text-red-500 rounded-lg transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Terminar Sessão
            </button>
        </form>
    </div>
</aside>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #475569; }
</style>