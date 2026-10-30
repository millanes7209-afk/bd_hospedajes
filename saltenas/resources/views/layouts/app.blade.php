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
    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    class="h-full font-sans antialiased bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 selection:bg-amber-500 selection:text-slate-950 transition-colors duration-150">

    <!-- Top Navigation Bar (Responsive with Dynamic Mobile Hamburger) -->
    <nav x-data="{ mobileMenuOpen: false }"
        class="sticky top-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800 shadow-sm">
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
                            <span class="font-black text-lg tracking-tight text-slate-900 dark:text-amber-400">
                                SALTEÑAS
                            </span>
                            <span
                                class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest -mt-1">
                                Control & Bóveda
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links (Hidden on Mobile, Visible on md+) -->
                <div class="hidden md:flex items-center gap-1 sm:gap-1.5 py-2">
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
                        @php
                            $cierreBadgeCount = \App\Models\CierreDiario::where('estado', 'pendiente')->where('origen', 'remoto')->count();
                        @endphp
                        <span id="nav-cierres-badge" class="{{ $cierreBadgeCount > 0 ? '' : 'hidden' }} ml-1 px-1.5 py-0.5 rounded-full text-[9px] font-black bg-rose-600 text-white animate-pulse">
                            {{ $cierreBadgeCount }}
                        </span>
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

                    <a href="{{ route('despachos.index') }}"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase transition-all flex items-center gap-1.5 {{ request()->routeIs('despachos.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-amber-600 dark:hover:text-amber-400' }}">
                        <i class="fa-solid fa-truck-fast text-orange-500 dark:text-orange-400"></i>
                        <span>Despachos</span>
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

                    <a href="{{ route('usuarios.index') }}"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold uppercase transition-all flex items-center gap-1.5 {{ request()->routeIs('usuarios.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-amber-600 dark:hover:text-amber-400' }}">
                        <i class="fa-solid fa-users text-rose-500 dark:text-rose-400"></i>
                        <span>Usuarios</span>
                    </a>

                    <!-- User Actions: Notification Bell, Theme Toggle & Logout -->
                    <div class="ml-2 pl-2 border-l border-slate-200 dark:border-slate-800 flex items-center gap-1">
                        <!-- Campana de Notificaciones de Cierres de Carritos -->
                        <div class="relative" x-data="{ openNotifs: false }">
                            <button @click="openNotifs = !openNotifs" type="button" title="Cierres de Carritos Pendientes"
                                class="relative p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:text-amber-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all text-xs flex items-center">
                                <i class="fa-solid fa-bell text-sm {{ $cierreBadgeCount > 0 ? 'text-amber-500 animate-pulse' : '' }}" id="global-bell-icon"></i>
                                <span id="global-bell-badge" class="{{ $cierreBadgeCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 px-1.5 py-0.5 rounded-full text-[9px] font-black bg-rose-600 text-white animate-bounce">
                                    {{ $cierreBadgeCount }}
                                </span>
                            </button>

                            <!-- Dropdown Flotante de Cierres Pendientes -->
                            <div x-show="openNotifs" @click.away="openNotifs = false" x-cloak
                                class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl z-50 overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                                    <span class="text-xs font-black uppercase text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <i class="fa-solid fa-bell text-amber-500"></i> Cierres Remotos
                                    </span>
                                    <span id="notif-count-label" class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase">
                                        {{ $cierreBadgeCount }} pendiente(s)
                                    </span>
                                </div>
                                <div id="notif-dropdown-list" class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    @if($cierreBadgeCount > 0)
                                        <div class="p-3 bg-amber-50/50 dark:bg-amber-500/10 text-amber-800 dark:text-amber-300 font-bold text-center">
                                            ¡Tienes cierres de turnos recibidos esperando tu aprobación!
                                        </div>
                                    @else
                                        <div class="p-4 text-center text-slate-400 font-semibold text-xs">
                                            No hay cierres pendientes por aprobar
                                        </div>
                                    @endif
                                </div>
                                <div class="p-2 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
                                    <a href="{{ route('cierres.index') }}" class="block text-center py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase transition-all shadow-sm">
                                        Revisar y Aprobar en Cierres
                                    </a>
                                </div>
                            </div>
                        </div>

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

                <!-- Right Side Actions on Mobile (Theme Toggle + Hamburger) -->
                <div class="flex items-center gap-2 md:hidden">
                    <button type="button" onclick="document.getElementById('theme-toggle').click()"
                        title="Cambiar Modo Claro/Oscuro"
                        class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:text-amber-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all text-sm">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>

                    <!-- Dynamic Hamburger Toggle Button -->
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-amber-500 transition-all focus:outline-none flex items-center justify-center border border-slate-200 dark:border-slate-700"
                        aria-label="Abrir Menú de Navegación">
                        <i class="fa-solid"
                            :class="mobileMenuOpen ? 'fa-xmark text-lg text-rose-500' : 'fa-bars text-lg text-amber-500'"></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- Dynamic Mobile Menu (Automatically expands vertically with more options) -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur px-4 pt-3 pb-5 space-y-1.5 shadow-xl max-h-[85vh] overflow-y-auto"
            x-cloak>

            <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false"
                class="px-3.5 py-3 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-between {{ request()->routeIs('dashboard') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="flex items-center gap-3"><i
                        class="fa-solid fa-chart-pie text-sm text-amber-600 dark:text-amber-400"></i> Inicio /
                    Dashboard</span>
                <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
            </a>

            <a href="{{ route('boveda.index') }}" @click="mobileMenuOpen = false"
                class="px-3.5 py-3 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-between {{ request()->routeIs('boveda.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="flex items-center gap-3"><i class="fa-solid fa-vault text-amber-500 text-sm"></i> Bóveda
                    Central</span>
                <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
            </a>

            <a href="{{ route('cierres.index') }}" @click="mobileMenuOpen = false"
                class="px-3.5 py-3 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-between {{ request()->routeIs('cierres.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="flex items-center gap-3">
                    <i class="fa-solid fa-calendar-check text-emerald-600 text-sm"></i> Cierre Diario
                    <span id="mobile-cierres-badge" class="{{ $cierreBadgeCount > 0 ? '' : 'hidden' }} ml-1 px-2 py-0.5 rounded-full text-[9px] font-black bg-rose-600 text-white">
                        {{ $cierreBadgeCount }} pendiente(s)
                    </span>
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
            </a>

            <a href="{{ route('compras.index') }}" @click="mobileMenuOpen = false"
                class="px-3.5 py-3 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-between {{ request()->routeIs('compras.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="flex items-center gap-3"><i class="fa-solid fa-cart-shopping text-cyan-600 text-sm"></i>
                    Compras & Precios</span>
                <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
            </a>

            <a href="{{ route('carritos.index') }}" @click="mobileMenuOpen = false"
                class="px-3.5 py-3 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-between {{ request()->routeIs('carritos.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="flex items-center gap-3"><i class="fa-solid fa-store text-purple-600 text-sm"></i> Carritos
                    (Puntos de Venta)</span>
                <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
            </a>

            <a href="{{ route('despachos.index') }}" @click="mobileMenuOpen = false"
                class="px-3.5 py-3 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-between {{ request()->routeIs('despachos.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="flex items-center gap-3"><i class="fa-solid fa-truck-fast text-orange-500 text-sm"></i>
                    Despachos Diarios</span>
                <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
            </a>

            <a href="{{ route('productos.index') }}" @click="mobileMenuOpen = false"
                class="px-3.5 py-3 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-between {{ request()->routeIs('productos.*') || request()->routeIs('insumos.*') || request()->routeIs('preparaciones.*') || request()->routeIs('variantes.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="flex items-center gap-3"><i class="fa-solid fa-boxes-stacked text-amber-500 text-sm"></i>
                    Productos & Recetas</span>
                <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
            </a>

            <a href="{{ route('promociones.index') }}" @click="mobileMenuOpen = false"
                class="px-3.5 py-3 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-between {{ request()->routeIs('promociones.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="flex items-center gap-3"><i class="fa-solid fa-tags text-indigo-600 text-sm"></i>
                    Promociones</span>
                <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
            </a>

            <a href="{{ route('usuarios.index') }}" @click="mobileMenuOpen = false"
                class="px-3.5 py-3 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-between {{ request()->routeIs('usuarios.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="flex items-center gap-3"><i class="fa-solid fa-users text-rose-500 text-sm"></i>
                    Usuarios</span>
                <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
            </a>


            <div class="pt-3 mt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Sesión de Usuario</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-extrabold uppercase flex items-center gap-1.5 transition-all">
                        <i class="fa-solid fa-power-off"></i> Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if (session('success'))
            <div
                class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-extrabold text-xs uppercase flex items-center justify-between shadow-sm mb-4">
                <span class="flex items-center gap-2"><i
                        class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base"></i>
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
                        class="fa-solid fa-triangle-exclamation text-red-600 dark:text-red-400 text-base"></i> Revisa los
                    siguientes errores:
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
    <footer
        class="border-t border-slate-200 dark:border-slate-900 bg-white dark:bg-slate-950/80 py-6 mt-12 text-center text-xs text-slate-500 dark:text-slate-500">
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
        themeToggleBtn.addEventListener('click', function () {
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

        // Polling en tiempo real para notificar de nuevos cierres recibidos desde Carritos
        function verificarCierresPendientesCentral() {
            fetch('{{ route("notificaciones.pendientes") }}')
                .then(r => r.json())
                .then(data => {
                    const count = data.count || 0;
                    const bellIcon = document.getElementById('global-bell-icon');
                    const bellBadge = document.getElementById('global-bell-badge');
                    const navBadge = document.getElementById('nav-cierres-badge');
                    const mobBadge = document.getElementById('mobile-cierres-badge');
                    const countLabel = document.getElementById('notif-count-label');
                    const listContainer = document.getElementById('notif-dropdown-list');

                    if (count > 0) {
                        if (bellIcon) bellIcon.classList.add('text-amber-500', 'animate-pulse');
                        if (bellBadge) {
                            bellBadge.innerText = count;
                            bellBadge.classList.remove('hidden');
                        }
                        if (navBadge) {
                            navBadge.innerText = count;
                            navBadge.classList.remove('hidden');
                        }
                        if (mobBadge) {
                            mobBadge.innerText = count + ' pendiente(s)';
                            mobBadge.classList.remove('hidden');
                        }
                        if (countLabel) countLabel.innerText = count + ' pendiente(s)';

                        if (listContainer && data.cierres && data.cierres.length > 0) {
                            let itemsHtml = '';
                            data.cierres.forEach(c => {
                                itemsHtml += `
                                    <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-black text-purple-700 dark:text-purple-400 uppercase text-[11px]">${c.carrito}</span>
                                            <span class="text-slate-400 text-[10px]">${c.fecha}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-[11px]">
                                            <span class="text-slate-600 dark:text-slate-300">Total Cobrado:</span>
                                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400">Bs. ${c.total}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Ef: Bs. ${c.efectivo} | QR: Bs. ${c.qr}
                                        </div>
                                    </div>
                                `;
                            });
                            listContainer.innerHTML = itemsHtml;
                        }
                    } else {
                        if (bellIcon) bellIcon.classList.remove('text-amber-500', 'animate-pulse');
                        if (bellBadge) bellBadge.classList.add('hidden');
                        if (navBadge) navBadge.classList.add('hidden');
                        if (mobBadge) mobBadge.classList.add('hidden');
                        if (countLabel) countLabel.innerText = '0 pendientes';
                        if (listContainer) {
                            listContainer.innerHTML = '<div class="p-4 text-center text-slate-400 font-semibold text-xs">No hay cierres pendientes por aprobar</div>';
                        }
                    }
                })
                .catch(() => {});
        }

        // Consultar de inmediato y luego cada 15 segundos
        verificarCierresPendientesCentral();
        setInterval(verificarCierresPendientesCentral, 15000);
    </script>
    @stack('scripts')
</body>

</html>