<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>PANEL - {{ strtoupper($tenant->nombre ?? 'RICO POLLO') }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom CSS Styles -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary:
                {{ $tenant->primary_color ?? "#FFE66D" }}
            ;
            --color-accent:
                {{ $tenant->accent_color ?? "#E23E1A" }}
            ;
            --color-bg:
                {{ $tenant->dark_bg_color ?? "#09090c" }}
            ;
            --color-card:
                {{ $tenant->dark_card_color ?? "#15151e" }}
            ;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col" style="background-color:var(--color-bg); color:var(--color-text);">

    <!-- Header Navigation -->
    <header class="border-b border-white/10 px-6 py-4 flex items-center justify-between"
        style="background-color: var(--color-card);">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-black/80 flex items-center justify-center border border-white/10">
                <i class="fa-solid {{ $tenant->rubro == 'RESTAURANTE' ? 'fa-utensils' : 'fa-building' }}"
                    style="color: var(--color-primary);"></i>
            </div>
            <div>
                <h1 class="font-bold brand-font text-lg leading-none">{{ strtoupper($tenant->nombre ?? 'RICO POLLO') }}
                </h1>
                <span class="text-[10px] uppercase tracking-wider font-bold"
                    style="color: var(--color-accent);">{{ $tenant->rubro ?? 'RESTAURANTE' }}</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button id="modeToggle" class="mode-toggle-btn" title="Cambiar modo">
                <span id="modeIcon">☀️</span>
            </button>
            <div class="text-right hidden sm:block">
                <p class="text-xs font-bold text-gray-400 uppercase">BIENVENIDO</p>
                <p class="text-sm font-semibold text-white">{{ $user->name }}</p>
            </div>
            <a href="{{ route('logout') }}" class="btn-accent text-xs px-4 py-2 flex items-center gap-2">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>SALIR</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 p-6 max-w-7xl mx-auto w-full">
        <!-- Welcome Card -->
        <div class="glass-card p-6 mb-8">
            <h2 class="text-2xl font-bold brand-font mb-2">¡HOLA, {{ strtoupper($user->name) }}!</h2>
            <p class="text-gray-400 text-sm">Bienvenido al Panel Administrativo de
                {{ $tenant->nombre ?? 'Rico Pollo' }}.</p>
        </div>

        <!-- Quick Modules Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                class="glass-card p-6 flex flex-col justify-between hover:border-[#FFE66D]/50 transition-all cursor-pointer">
                <div>
                    <div
                        class="w-12 h-12 rounded-xl bg-amber-500/10 text-[#FFE66D] flex items-center justify-center text-xl mb-4 border border-amber-500/20">
                        <i class="fa-solid fa-[#FFE66D] fa-cart-shopping"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-1">VENTAS Y PEDIDOS</h3>
                    <p class="text-xs text-gray-400">Gestiona los pedidos de la mesa y ventas en caja.</p>
                </div>
                <div class="mt-6 flex items-center text-xs font-bold text-[#FFE66D] gap-2">
                    <span>ACCEDER AL MÓDULO</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>

            <div
                class="glass-card p-6 flex flex-col justify-between hover:border-[#FFE66D]/50 transition-all cursor-pointer">
                <div>
                    <div
                        class="w-12 h-12 rounded-xl bg-red-500/10 text-red-500 flex items-center justify-center text-xl mb-4 border border-red-500/20">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-1">INVENTARIO Y MENÚ</h3>
                    <p class="text-xs text-gray-400">Administra tus platillos, bebidas y stock de insumos.</p>
                </div>
                <div class="mt-6 flex items-center text-xs font-bold text-red-500 gap-2">
                    <span>ACCEDER AL MÓDULO</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>

            <div
                class="glass-card p-6 flex flex-col justify-between hover:border-[#FFE66D]/50 transition-all cursor-pointer">
                <div>
                    <div
                        class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl mb-4 border border-blue-500/20">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-1">REPORTES Y CAJA</h3>
                    <p class="text-xs text-gray-400">Consulta ingresos diarios, arqueos y caja chica.</p>
                </div>
                <div class="mt-6 flex items-center text-xs font-bold text-blue-400 gap-2">
                    <span>ACCEDER AL MÓDULO</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>
        </div>
    </main>

    <script>
        // ─── TEMA OSCURO / CLARO ───────────────────────────────────────
        const html2 = document.documentElement;
        const modeBtn2 = document.getElementById('modeToggle');
        const modeIcon2 = document.getElementById('modeIcon');
        function applyTheme2(theme) {
            if (theme === 'light') {
                html2.className = 'light-mode';
                modeIcon2.textContent = '🌙';
            } else {
                html2.className = 'dark-mode';
                modeIcon2.textContent = '☀️';
            }
            localStorage.setItem('rp_theme', theme);
        }
        applyTheme2(localStorage.getItem('rp_theme') || 'dark');
        modeBtn2.addEventListener('click', function () {
            applyTheme2(html2.classList.contains('light-mode') ? 'dark' : 'light');
        });
    </script>
</body>

</html>