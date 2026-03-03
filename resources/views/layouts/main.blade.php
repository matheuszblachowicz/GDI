<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SinaTech')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-900 flex">
    
    <x-sidebar />

    <div class="flex-1 flex flex-col min-h-screen">
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="py-4 px-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    @yield('header', 'Painel de Controlo')
                </h1>
            </div>
        </header>

        <main class="flex-grow p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>