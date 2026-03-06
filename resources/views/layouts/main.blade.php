<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SinaTech AD - Platlog')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    // Se você tiver o código Hexadecimal exato da Platlog, 
                    // descomente a linha abaixo e coloque a cor aqui:
                    // colors: { brand: '#CODIGO_HEX' }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-900 h-screen flex overflow-hidden">
    
    <x-sidebar />

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 z-10">
            <h1 class="text-xl font-semibold text-slate-800">
                @yield('header', 'Painel de Controlo')
            </h1>
            
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-600">{{ Auth::user()->getFirstAttribute('displayname') }}</span>
                <div class="h-9 w-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold border border-blue-200">
                    {{ data_get(Auth::user()->initials, 0, 'AD') }}
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>