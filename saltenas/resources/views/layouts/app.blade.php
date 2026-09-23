<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Salteñas — Analítica & Ventas')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                        }
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 selection:bg-amber-500 selection:text-slate-950">

    <!-- Top Navigation Bar -->
    <nav class="sticky top-0 z-50 bg-slate-900/90 backdrop-blur border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo & Title -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-amber-400 flex items-center justify-center shadow-lg shadow-amber-500/20 text-slate-950 font-black text-xl">
                            🥟
                        </div>
                        <div>
                            <span
                                class="font-black text-lg tracking-tight bg-gradient-to-r from-amber-400 via-amber-200 to-amber-500 bg-clip-text text-transparent">
                                SALTEÑAS
                            </span>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest -mt-1">
                                Control & Analítica
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Nav Links -->
                <div class="flex items-center gap-1 sm:gap-2">
                    <a href="{{ route('dashboard') }}"
                        class="px-3 py-2 rounded-lg text-xs font-extrabold uppercase transition-all flex items-center gap-2 {{ request()->routeIs('dashboard') ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-amber-400' }}">
                        <i class="fa-solid fa-chart-pie text-sm"></i>
                        <span class="hidden md:inline">Dashboard</span>
                    </a>

                    <a href="{{ route('cierres.index') }}"
                        class="px-3 py-2 rounded-lg text-xs font-extrabold uppercase transition-all flex items-center gap-2 {{ request()->routeIs('cierres.*') ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-amber-400' }}">
                        <i class="fa-solid fa-calendar-check text-sm"></i>
                        <span>Cierre Diario</span>
                    </a>

                    <a href="{{ route('sucursales.index') }}"
                        class="px-3 py-2 rounded-lg text-xs font-extrabold uppercase transition-all flex items-center gap-2 {{ request()->routeIs('sucursales.*') ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-amber-400' }}">
                        <i class="fa-solid fa-store text-sm"></i>
                        <span class="hidden sm:inline">Sucursales</span>
                    </a>

                    <a href="{{ route('costos.index') }}"
                        class="px-3 py-2 rounded-lg text-xs font-extrabold uppercase transition-all flex items-center gap-2 {{ request()->routeIs('costos.*') ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-amber-400' }}">
                        <i class="fa-solid fa-coins text-sm"></i>
                        <span class="hidden sm:inline">Costos & Receta</span>
                    </a>

                    <!-- User Info & Logout -->
                    <div class="ml-2 pl-2 border-l border-slate-800 flex items-center gap-2">
                        <span class="hidden lg:inline text-xs font-bold text-slate-400">
                            <i class="fa-solid fa-user text-amber-500 mr-1"></i> {{ Auth::user()->name ?? 'Admin' }}
                        </span>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" title="Cerrar Sesión"
                                class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-all text-xs">
                                <i class="fa-solid fa-power-off"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if (session('success'))
            <div
                class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-extrabold text-xs uppercase flex items-center justify-between shadow-sm mb-4">
                <span class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                    {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div
                class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 font-bold text-xs uppercase shadow-sm mb-4">
                <div class="flex items-center gap-2 mb-1"><i
                        class="fa-solid fa-triangle-exclamation text-red-400 text-base"></i> Revisa los siguientes errores:
                </div>
                <ul class="list-disc list-inside text-[11px] opacity-90 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Global Footer -->
    <footer class="border-t border-slate-900 bg-slate-950/80 py-6 mt-12 text-center text-xs text-slate-500">
        <p class="font-bold">SALTEÑAS © {{ date('Y') }} — Control de Ventas, Clima & Costos</p>
    </footer>

    @yield('scripts')
</body>

</html>