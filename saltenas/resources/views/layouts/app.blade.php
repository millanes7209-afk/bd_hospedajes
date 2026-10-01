<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Salteñas — Sistema Administrativo')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Theme Script to Prevent Flash -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="h-full font-sans antialiased bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 selection:bg-amber-500 selection:text-slate-950 transition-colors duration-150">

    <!-- Top Navigation Bar -->
    <nav class="sticky top-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-b border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo & Title -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-amber-400 flex items-center justify-center shadow-md shadow-amber-500/20 text-slate-950 font-black text-xl">
                            🥟
                        </div>
                        <div>
                            <span
                                class="font-black text-lg tracking-tight text-slate-900 dark:text-amber-400">
                                SALTEÑAS
                            </span>
                            <span class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest -mt-1">
                                Control & Bóveda
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Nav Links -->
                <div class="flex items-center gap-1 sm:gap-1.5 overflow-x-auto py-2">
                    <a href="{{ route('dashboard') }}"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase transition-all flex items-center gap-1.5 {{ request()->routeIs('dashboard') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-amber-600 dark:hover:text-amber-400' }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Inicio</span>
                    </a>

                    <a href="{{ route('boveda.index') }}"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase transition-all flex items-center gap-1.5 {{ request()->routeIs('boveda.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-amber-600 dark:hover:text-amber-400' }}">
                        <i class="fa-solid fa-vault text-amber-500 dark:text-amber-400"></i>
                        <span>Bóveda</span>
                    </a>

                    <a href="{{ route('cierres.index') }}"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase transition-all flex items-center gap-1.5 {{ request()->routeIs('cierres.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-amber-600 dark:hover:text-amber-400' }}">
                        <i class="fa-solid fa-calendar-check text-emerald-600 dark:text-emerald-400"></i>
                        <span>Cierre Diario</span>
                    </a>

                    <a href="{{ route('compras.index') }}"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase transition-all flex items-center gap-1.5 {{ request()->routeIs('compras.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-amber-600 dark:hover:text-amber-400' }}">
                        <i class="fa-solid fa-cart-shopping text-cyan-600 dark:text-cyan-400"></i>
                        <span>Compras</span>
                    </a>

                    <a href="{{ route('carritos.index') }}"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase transition-all flex items-center gap-1.5 {{ request()->routeIs('carritos.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-amber-600 dark:hover:text-amber-400' }}">
                        <i class="fa-solid fa-store text-purple-600 dark:text-purple-400"></i>
                        <span>Carritos</span>
                    </a>

                    <a href="{{ route('productos.index') }}"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase transition-all flex items-center gap-1.5 {{ request()->routeIs('productos.*') || request()->routeIs('insumos.*') || request()->routeIs('preparaciones.*') || request()->routeIs('variantes.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-amber-600 dark:hover:text-amber-400' }}">
                        <i class="fa-solid fa-boxes-stacked text-amber-500 dark:text-amber-400"></i>
                        <span>Productos & Recetas</span>
                    </a>

                    <a href="{{ route('promociones.index') }}"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase transition-all flex items-center gap-1.5 {{ request()->routeIs('promociones.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-amber-600 dark:hover:text-amber-400' }}">
                        <i class="fa-solid fa-tags text-indigo-600 dark:text-indigo-400"></i>
                        <span>Promos</span>
                    </a>

                    <!-- User Actions: Theme Toggle & Logout -->
                    <div class="ml-2 pl-2 border-l border-slate-200 dark:border-slate-800 flex items-center gap-1">
                        <!-- Theme Toggle Button -->
                        <button type="button" id="theme-toggle" title="Cambiar Modo Claro/Oscuro"
                            class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:text-amber-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all text-xs flex items-center gap-1">
                            <i id="theme-toggle-dark-icon" class="fa-solid fa-moon hidden"></i>
                            <i id="theme-toggle-light-icon" class="fa-solid fa-sun hidden"></i>
                        </button>

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" title="Cerrar Sesión"
                                class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all text-xs">
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
                class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-extrabold text-xs uppercase flex items-center justify-between shadow-sm mb-4">
                <span class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base"></i>
                    {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100">✕</button>
            </div>
        @endif

        @if (session('warning'))
            <div
                class="p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-400 font-extrabold text-xs uppercase flex items-center justify-between shadow-sm mb-4">
                <span class="flex items-center gap-2"><i
                        class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 text-base"></i>
                    {{ session('warning') }}</span>
                <button onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div
                class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 font-bold text-xs uppercase shadow-sm mb-4">
                <div class="flex items-center gap-2 mb-1"><i
                        class="fa-solid fa-triangle-exclamation text-red-600 dark:text-red-400 text-base"></i> Revisa los siguientes errores:
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
    <footer class="border-t border-slate-200 dark:border-slate-900 bg-white dark:bg-slate-950/80 py-6 mt-12 text-center text-xs text-slate-500 dark:text-slate-500">
        <p class="font-bold">SALTEÑAS © {{ date('Y') }} — Bóveda Central & Control de Carritos</p>
    </footer>

    @yield('scripts')
    <script>
        // Automatic decimal comma normalization
        document.addEventListener('input', function (e) {
            if (e.target && e.target.tagName === 'INPUT') {
                if (e.target.type === 'number' || e.target.getAttribute('step') || e.target.name?.includes('precio') || e.target.name?.includes('monto') || e.target.name?.includes('cantidad')) {
                    if (typeof e.target.value === 'string' && e.target.value.includes(',')) {
                        e.target.value = e.target.value.replace(',', '.');
                    }
                }
            }
        });

        // Theme Toggle Logic
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        if (document.documentElement.classList.contains('dark')) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        const themeToggleBtn = document.getElementById('theme-toggle');
        themeToggleBtn.addEventListener('click', function() {
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
</body>

</html>