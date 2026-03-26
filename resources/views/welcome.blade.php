<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Empleado - TCSE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc]">

    <nav class="bg-[#1e3a5f] text-white py-4 px-6 shadow-lg">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            
            <div class="flex items-center space-x-4">
                <div class="bg-white/10 p-1 rounded-full">
                    <img src="{{ asset('img/escudo-santiago.png') }}" alt="Escudo TCSE" class="h-14 w-auto drop-shadow-md">
                </div>
                
                <div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <h1 class="text-xl font-bold tracking-tight">Portal del Empleado</h1>
                    </div>
                    <p class="text-xs text-blue-200 opacity-90 uppercase tracking-widest font-medium">Tribunal de Cuentas de Santiago del Estero</p>
                    
                    @auth
                        <p class="text-[11px] text-blue-300 mt-1 font-normal italic">
                            {{ auth()->user()->name }} • Legajo: {{ auth()->user()->legajo ?? 'EMP001' }}
                        </p>
                    @endauth
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button class="flex items-center px-4 py-2 border border-blue-400/50 rounded-lg hover:bg-blue-800 transition text-sm font-medium">
                    Ayuda
                </button>

                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center px-4 py-2 bg-white text-[#1e3a5f] font-bold rounded-lg hover:bg-blue-50 transition text-sm shadow-sm">
                            Cerrar Sesión
                        </button>
                    </form>
                @else
                    <a href="{{ url('/admin/login') }}" class="flex items-center px-6 py-2 bg-white text-[#1e3a5f] font-bold rounded-lg hover:bg-blue-50 transition text-sm shadow-sm">
                        Ingresar al Sistema
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-6">
        
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800">Bienvenido/a, {{ auth()->user()->name ?? 'Agente' }}</h2>
            <p class="text-slate-500">Panel de autogestión del Tribunal de Cuentas</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <a href="{{ route('recibos.index') }}" class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition cursor-pointer group block">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-[#1e3a5f] group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-700 uppercase text-sm tracking-wide">Mis Recibos</h3>
                <p class="text-xs text-slate-500 mt-2">Visualiza y descarga tus recibos de sueldo digitales.</p>
                <div class="mt-4 text-blue-600 text-xs font-semibold group-hover:underline">Ver últimos 3 →</div>
            </a>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition cursor-pointer group">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-green-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-700 uppercase text-sm tracking-wide">Solicitar Licencia</h3>
                <p class="text-xs text-slate-500 mt-2">Gestiona tus vacaciones, permisos o certificados médicos.</p>
                <div class="mt-4 text-green-600 text-xs font-semibold group-hover:underline">Iniciar solicitud →</div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition cursor-pointer group">
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-amber-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-700 uppercase text-sm tracking-wide">Mis Trámites</h3>
                <p class="text-xs text-slate-500 mt-2">Seguimiento de expedientes y notas internas presentadas.</p>
                <div class="mt-4 text-amber-600 text-xs font-semibold group-hover:underline">Revisar estado →</div>
            </div>

        </div>
    </main>

</body>
</html>