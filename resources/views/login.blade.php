<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Platlog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-100 min-h-screen flex items-center justify-center p-4 antialiased font-sans">
    
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-blue-200/40 blur-[100px]"></div>
        <div class="absolute top-[60%] -right-[10%] w-[40%] h-[60%] rounded-full bg-blue-300/30 blur-[120px]"></div>
    </div>

    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden relative">
        <div class="p-8 sm:p-10">
            
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600 text-white mb-5 shadow-lg shadow-blue-200 transform transition hover:scale-105 duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">PlatTrust<span class="text-blue-600">AD</span></h2>
                <p class="text-slate-500 text-sm font-medium mt-2">Plataforma de Gestão Platlog</p>
            </div>

            @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3 animate-bounce-short">
                <div class="p-1 bg-white rounded-full text-red-500 shadow-sm shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <p class="text-sm font-bold text-red-800 leading-tight">{{ session('error') }}</p>
            </div>
            @endif

            <form action="{{ route('authenticate') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label for="username" class="block text-sm font-bold text-slate-700 mb-1.5 ml-1">Utilizador AD</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input type="text" id="username" name="username" required 
                            class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                            placeholder="ex: nome.sobrenome">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-1.5 ml-1">Palavra-passe</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" id="password" name="password" required 
                            class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                            placeholder="••••••••">
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold text-lg py-3.5 px-4 rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 group">
                        <span>Iniciar Sessão</span>
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="bg-slate-50 p-5 text-center border-t border-slate-100 flex flex-col gap-1">
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Acesso Restrito</span>
            <span class="text-xs text-slate-500 font-medium">&copy; {{ date('Y') }} JDILAB - Platlog GDI</span>
        </div>
    </div>

    <style>
        .animate-bounce-short {
            animation: bounceShort 0.5s ease-in-out 1;
        }
        @keyframes bounceShort {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
    </style>
</body>
</html>