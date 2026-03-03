<aside class="w-64 bg-gray-800 text-white min-h-screen flex flex-col shadow-lg">
    <div class="p-6 text-2xl font-bold border-b border-gray-700">
        SinaTech Admin
    </div>
    <nav class="flex-1 p-4 space-y-2">
        <a href="{{ route('admin.home') }}" class="block px-4 py-2 rounded hover:bg-gray-700 transition">
            🏠 Início
        </a>
        <a href="{{ route('admin.user_activity.index') }}" class="block px-4 py-3 rounded hover:bg-gray-800 transition">
            📊 Atividade dos Usuários
        </a>
        <a href="{{ route('admin.devices.index') }}" class="block px-4 py-2 rounded hover:bg-gray-700 transition">
            💻 Máquinas
        </a>
        <a href="{{ route('admin.working_hours.index') }}" class="block px-4 py-2 rounded hover:bg-gray-700 transition">
            ⏱️ Horários (Working Hours)
        </a>
        <a href="/mapa" class="block px-4 py-2 rounded hover:bg-gray-700 transition">
            🗺️ Mapa Geográfico
        </a>
    </nav>
</aside>