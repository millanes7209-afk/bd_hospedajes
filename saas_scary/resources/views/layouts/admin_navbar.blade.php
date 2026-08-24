<?php
$navTenant = isset($tenant) ? $tenant : null;
$nombreEmpresa = $navTenant ? ($navTenant->nombre ?? 'SAAS SCARY') : env('APP_NAME', 'SAAS SCARY');
$logoEmpresa = ($navTenant && !empty($navTenant->logo) && file_exists(public_path($navTenant->logo)))
    ? asset($navTenant->logo)
    : asset('assets/logo.svg');
?>

<header class="glass-card mb-6 border-b rounded-none px-4 py-3 sticky top-0 z-40 backdrop-blur-md"
    style="border-color:var(--color-card-border); background:var(--color-bg-alt)">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <!-- LOGO Y NOMBRE DE EMPRESA -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-8 flex items-center justify-center">
                <img src="{{ $logoEmpresa }}" alt="LOGO" class="max-w-full max-h-full object-contain">
            </div>
            <span class="text-sm md:text-base font-black tracking-wider uppercase"
                style="color:var(--color-primary, #FFE66D)">
                {{ strtoupper($nombreEmpresa) }}
            </span>
        </div>

        <!-- NAVEGACIÓN ESCRITORIO (DESKTOP) -->
        <nav class="hidden lg:flex items-center gap-1 text-xs font-bold uppercase tracking-wider">
            <a href="{{ route('admin.mesas') }}"
                class="px-3 py-2 rounded-lg transition-colors flex items-center gap-1.5 {{ request()->routeIs('admin.mesas*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
                style="color: {{ request()->routeIs('admin.mesas*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
                <i class="fa-solid fa-chair text-sm"></i>MESAS
            </a>

            <a href="{{ route('admin.pos') }}"
                class="px-3 py-2 rounded-lg transition-colors flex items-center gap-1.5 {{ request()->routeIs('admin.pos*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
                style="color: {{ request()->routeIs('admin.pos*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
                <i class="fa-solid fa-bolt text-sm"></i>POS RÁPIDO
            </a>

            <a href="{{ route('admin.pedidos') }}"
                class="px-3 py-2 rounded-lg transition-colors flex items-center gap-1.5 {{ request()->routeIs('admin.pedidos*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
                style="color: {{ request()->routeIs('admin.pedidos*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
                <i class="fa-solid fa-clipboard-list text-sm"></i>PEDIDOS
            </a>

            <a href="{{ route('admin.productos') }}"
                class="px-3 py-2 rounded-lg transition-colors flex items-center gap-1.5 {{ request()->routeIs('admin.productos*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
                style="color: {{ request()->routeIs('admin.productos*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
                <i class="fa-solid fa-utensils text-sm"></i>PRODUCTOS
            </a>

            <a href="{{ route('admin.reportes') }}"
                class="px-3 py-2 rounded-lg transition-colors flex items-center gap-1.5 {{ request()->routeIs('admin.reportes*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
                style="color: {{ request()->routeIs('admin.reportes*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
                <i class="fa-solid fa-chart-pie text-sm"></i>REPORTES
            </a>

            <a href="{{ route('admin.configuracion') }}"
                class="px-3 py-2 rounded-lg transition-colors flex items-center gap-1.5 {{ request()->routeIs('admin.configuracion*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
                style="color: {{ request()->routeIs('admin.configuracion*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
                <i class="fa-solid fa-sliders text-sm"></i>AJUSTES
            </a>

            @if(Session::get('is_super_admin') || Session::get('rolID') === 'ADMINISTRADOR')
                <a href="{{ route('admin.usuarios') }}"
                    class="px-3 py-2 rounded-lg transition-colors flex items-center gap-1.5 {{ request()->routeIs('admin.usuarios*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
                    style="color: {{ request()->routeIs('admin.usuarios*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
                    <i class="fa-solid fa-users text-sm"></i>USUARIOS
                </a>
            @endif

            <a href="{{ route('menu') }}" target="_blank"
                class="px-3 py-2 rounded-lg transition-colors hover:bg-white/10 flex items-center gap-1.5 text-amber-500 font-bold">
                <i class="fa-solid fa-store text-sm"></i>TIENDA
            </a>

            <!-- BOTÓN CAMBIO DE TEMA CLARO / OSCURO -->
            <button onclick="toggleAdminTheme()"
                class="p-2 rounded-lg transition-colors hover:bg-white/10 text-amber-500"
                title="Cambiar Modo Claro/Oscuro">
                <i id="admin-theme-icon" class="fa-solid fa-sun text-base"></i>
            </button>

            <div class="h-4 w-px bg-current opacity-20 mx-1"></div>

            <!-- BOTÓN CERRAR SESIÓN -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                    class="px-3 py-2 rounded-lg text-red-500 hover:text-red-400 hover:bg-red-500/10 transition-colors flex items-center gap-1.5 font-bold uppercase">
                    <i class="fa-solid fa-right-from-bracket text-sm"></i>SALIR
                </button>
            </form>
        </nav>

        <!-- BOTÓN MENÚ HAMBURGUESA (MÓVIL / TAB) -->
        <div class="flex items-center gap-2 lg:hidden">
            <button onclick="toggleAdminTheme()" class="p-2 rounded-lg text-amber-500 hover:bg-white/10">
                <i id="admin-theme-icon-mobile" class="fa-solid fa-sun text-base"></i>
            </button>
            <button id="admin-mobile-menu-btn" onclick="toggleAdminMobileMenu()"
                class="p-2 rounded-lg transition-colors hover:bg-white/10 text-lg" style="color:var(--color-text)">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- MENÚ DESPLEGABLE MÓVIL (HAMBURGUESA) -->
    <div id="admin-mobile-menu"
        class="hidden lg:hidden mt-3 pt-3 border-t flex flex-col gap-1 text-xs font-bold uppercase tracking-wider"
        style="border-color:var(--color-card-border)">
        <a href="{{ route('admin.mesas') }}"
            class="px-4 py-2.5 rounded-lg flex items-center gap-2.5 {{ request()->routeIs('admin.mesas*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
            style="color: {{ request()->routeIs('admin.mesas*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
            <i class="fa-solid fa-chair text-sm w-5 text-center"></i>MESAS
        </a>

        <a href="{{ route('admin.pos') }}"
            class="px-4 py-2.5 rounded-lg flex items-center gap-2.5 {{ request()->routeIs('admin.pos*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
            style="color: {{ request()->routeIs('admin.pos*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
            <i class="fa-solid fa-bolt text-sm w-5 text-center"></i>POS RÁPIDO
        </a>

        <a href="{{ route('admin.pedidos') }}"
            class="px-4 py-2.5 rounded-lg flex items-center gap-2.5 {{ request()->routeIs('admin.pedidos*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
            style="color: {{ request()->routeIs('admin.pedidos*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
            <i class="fa-solid fa-clipboard-list text-sm w-5 text-center"></i>PEDIDOS
        </a>

        <a href="{{ route('admin.productos') }}"
            class="px-4 py-2.5 rounded-lg flex items-center gap-2.5 {{ request()->routeIs('admin.productos*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
            style="color: {{ request()->routeIs('admin.productos*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
            <i class="fa-solid fa-utensils text-sm w-5 text-center"></i>PRODUCTOS
        </a>

        <a href="{{ route('admin.reportes') }}"
            class="px-4 py-2.5 rounded-lg flex items-center gap-2.5 {{ request()->routeIs('admin.reportes*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
            style="color: {{ request()->routeIs('admin.reportes*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
            <i class="fa-solid fa-chart-pie text-sm w-5 text-center"></i>REPORTES
        </a>

        <a href="{{ route('admin.configuracion') }}"
            class="px-4 py-2.5 rounded-lg flex items-center gap-2.5 {{ request()->routeIs('admin.configuracion*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
            style="color: {{ request()->routeIs('admin.configuracion*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
            <i class="fa-solid fa-sliders text-sm w-5 text-center"></i>AJUSTES
        </a>

        @if(Session::get('is_super_admin') || Session::get('rolID') === 'ADMINISTRADOR')
            <a href="{{ route('admin.usuarios') }}"
                class="px-4 py-2.5 rounded-lg flex items-center gap-2.5 {{ request()->routeIs('admin.usuarios*') ? 'bg-amber-500/20 font-black text-amber-500' : 'hover:bg-white/10' }}"
                style="color: {{ request()->routeIs('admin.usuarios*') ? 'var(--color-primary, #FFE66D)' : 'var(--color-text)' }}">
                <i class="fa-solid fa-users text-sm w-5 text-center"></i>USUARIOS
            </a>
        @endif

        <a href="{{ route('menu') }}" target="_blank"
            class="px-4 py-2.5 rounded-lg flex items-center gap-2.5 hover:bg-white/10 text-amber-500">
            <i class="fa-solid fa-store text-sm w-5 text-center"></i>VER TIENDA
        </a>

        <form action="{{ route('logout') }}" method="POST" class="mt-2 pt-2 border-t"
            style="border-color:var(--color-card-border)">
            @csrf
            <button type="submit"
                class="w-full text-left px-4 py-2.5 rounded-lg text-red-500 hover:bg-red-500/10 flex items-center gap-2.5 font-bold">
                <i class="fa-solid fa-right-from-bracket text-sm w-5 text-center"></i>CERRAR SESIÓN
            </button>
        </form>
    </div>
</header>

<script>
    function toggleAdminMobileMenu() {
        const menu = document.getElementById('admin-mobile-menu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    function toggleAdminTheme() {
        const isDark = document.documentElement.classList.contains('dark-mode');
        const newTheme = isDark ? 'light' : 'dark';
        document.documentElement.className = newTheme === 'dark' ? 'dark-mode' : 'light-mode';
        localStorage.setItem('rp_theme', newTheme);
        updateAdminThemeIcons(newTheme);
    }

    function updateAdminThemeIcons(theme) {
        const iconDesk = document.getElementById('admin-theme-icon');
        const iconMob = document.getElementById('admin-theme-icon-mobile');
        const iconClass = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        if (iconDesk) iconDesk.className = iconClass;
        if (iconMob) iconMob.className = iconClass;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const currentTheme = document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light';
        updateAdminThemeIcons(currentTheme);
    });
</script>