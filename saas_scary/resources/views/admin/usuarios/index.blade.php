<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GESTIÓN DE USUARIOS - RICO POLLO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar -->
    <header class="glass-card mb-6 border-b rounded-none px-4 py-3"
        style="border-color:var(--color-card-border);background:var(--color-bg-alt)">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Brand Logo & Title -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-8">
                    <img src="{{ asset('assets/logo.svg') }}" alt="LOGO" class="w-full h-full object-contain">
                </div>
                <span class="text-base font-black text-[#FFE66D] tracking-wider uppercase">RICO POLLO</span>
            </div>

            <!-- Right Side Navigation Links (Desktop) -->
            <nav id="nav-menu" class="hidden md:flex items-center gap-1 text-xs font-bold uppercase tracking-wider">
                <a href="{{ route('admin.pedidos') }}"
                    class="px-3 py-2 rounded-lg transition-colors flex items-center gap-2 {{ request()->routeIs('admin.pedidos*') ? 'bg-[#FFE66D]/15 text-[#FFE66D] font-extrabold' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-clipboard-list text-sm"></i>Pedidos
                </a>

                <a href="{{ route('admin.productos') }}"
                    class="px-3 py-2 rounded-lg transition-colors flex items-center gap-2 {{ request()->routeIs('admin.productos*') ? 'bg-[#FFE66D]/15 text-[#FFE66D] font-extrabold' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-utensils text-sm"></i>Productos
                </a>

                @if(Session::get('is_super_admin') || Session::get('rolID') === 'ADMINISTRADOR')
                    <a href="{{ route('admin.usuarios') }}"
                        class="px-3 py-2 rounded-lg transition-colors flex items-center gap-2 {{ request()->routeIs('admin.usuarios*') ? 'bg-[#FFE66D]/15 text-[#FFE66D] font-extrabold' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                        <i class="fa-solid fa-users text-sm"></i>Usuarios
                    </a>
                @endif

                <a href="{{ route('admin.perfil') }}"
                    class="px-3 py-2 rounded-lg transition-colors flex items-center gap-2 {{ request()->routeIs('admin.perfil*') ? 'bg-[#FFE66D]/15 text-[#FFE66D] font-extrabold' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-user-gear text-sm"></i>Mi Perfil
                </a>

                <a href="{{ route('menu') }}" target="_blank"
                    class="px-3 py-2 rounded-lg transition-colors text-gray-300 hover:text-white hover:bg-white/5 flex items-center gap-2">
                    <i class="fa-solid fa-store text-sm"></i>Ver Tienda
                </a>

                <div class="h-4 w-px bg-white/10 mx-1"></div>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 rounded-lg text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors flex items-center gap-1.5 font-bold uppercase">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i>Salir
                    </button>
                </form>
            </nav>

            <!-- Mobile Hamburger Button -->
            <button id="mobile-menu-btn"
                class="md:hidden text-gray-300 hover:text-white p-2 focus:outline-none text-lg">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <!-- Mobile Dropdown Navigation Menu -->
        <div id="mobile-menu"
            class="hidden md:hidden mt-3 pt-3 border-t border-white/10 flex flex-col gap-1 text-right text-xs font-bold uppercase tracking-wider">
            <a href="{{ route('admin.pedidos') }}"
                class="px-4 py-2.5 rounded-lg flex items-center justify-end gap-2.5 {{ request()->routeIs('admin.pedidos*') ? 'bg-[#FFE66D]/15 text-[#FFE66D]' : 'text-gray-300 hover:bg-white/5' }}">
                <i class="fa-solid fa-clipboard-list text-sm"></i>Pedidos
            </a>

            <a href="{{ route('admin.productos') }}"
                class="px-4 py-2.5 rounded-lg flex items-center justify-end gap-2.5 {{ request()->routeIs('admin.productos*') ? 'bg-[#FFE66D]/15 text-[#FFE66D]' : 'text-gray-300 hover:bg-white/5' }}">
                <i class="fa-solid fa-utensils text-sm"></i>Productos
            </a>

            @if(Session::get('is_super_admin') || Session::get('rolID') === 'ADMINISTRADOR')
                <a href="{{ route('admin.usuarios') }}"
                    class="px-4 py-2.5 rounded-lg flex items-center justify-end gap-2.5 {{ request()->routeIs('admin.usuarios*') ? 'bg-[#FFE66D]/15 text-[#FFE66D]' : 'text-gray-300 hover:bg-white/5' }}">
                    <i class="fa-solid fa-users text-sm"></i>Usuarios
                </a>
            @endif

            <a href="{{ route('admin.perfil') }}"
                class="px-4 py-2.5 rounded-lg flex items-center justify-end gap-2.5 {{ request()->routeIs('admin.perfil*') ? 'bg-[#FFE66D]/15 text-[#FFE66D]' : 'text-gray-300 hover:bg-white/5' }}">
                <i class="fa-solid fa-user-gear text-sm"></i>Mi Perfil
            </a>

            <a href="{{ route('menu') }}" target="_blank"
                class="px-4 py-2.5 rounded-lg flex items-center justify-end gap-2.5 text-gray-300 hover:bg-white/5">
                <i class="fa-solid fa-store text-sm"></i>Ver Tienda
            </a>

            <form action="{{ route('logout') }}" method="POST" class="mt-1 pt-1 border-t border-white/10">
                @csrf
                <button type="submit"
                    class="w-full text-right px-4 py-2.5 rounded-lg text-red-400 hover:bg-red-500/10 flex items-center justify-end gap-2.5 font-bold uppercase">
                    <i class="fa-solid fa-right-from-bracket text-sm"></i>Salir
                </button>
            </form>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-black uppercase flex items-center gap-2">
                    <i class="fa-solid fa-users text-[#FFE66D]"></i> GESTIÓN DE USUARIOS
                </h2>
                <p class="text-xs text-gray-400 mt-1">Administra los usuarios y roles asignados en Rico Pollo.</p>
            </div>
            <a href="{{ route('admin.usuarios.create') }}"
                class="btn-primary text-xs font-black uppercase !py-2.5 !px-4 shadow-lg flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i> CREAR NUEVO USUARIO
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300 font-bold text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 font-bold text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-black/50 text-gray-400 uppercase font-black tracking-wider border-b"
                        style="border-color:var(--color-card-border)">
                        <tr>
                            <th class="p-4"># ID</th>
                            <th class="p-4">NOMBRE / USUARIO</th>
                            <th class="p-4">CORREO ELECTRÓNICO</th>
                            <th class="p-4">ROL ASIGNADO</th>
                            <th class="p-4">FECHA REGISTRO</th>
                            <th class="p-4 text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($usuarios as $user)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="p-4 font-bold text-[#FFE66D]">#{{ $user->id }}</td>
                                <td class="p-4 font-black uppercase text-white">{{ $user->name }}</td>
                                <td class="p-4 text-gray-300">{{ $user->email }}</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                                @if($user->rol === 'ADMINISTRADOR') bg-purple-500/20 text-purple-300 border border-purple-500/40
                                                @else bg-blue-500/20 text-blue-300 border border-blue-500/40
                                                @endif">
                                        {{ $user->rol ?: 'ADMINISTRADOR' }}
                                    </span>
                                </td>
                                <td class="p-4 text-gray-400">
                                    {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="p-4 text-center flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.usuarios.edit', $user->id) }}"
                                        class="btn-outline text-xs font-bold py-1 px-2.5">
                                        <i class="fa-solid fa-pen-to-square mr-1"></i>EDITAR
                                    </a>
                                    <a href="{{ route('admin.usuarios.destroy', $user->id) }}"
                                        onclick="return confirm('¿Estás seguro de eliminar este usuario?')"
                                        class="bg-red-600/80 hover:bg-red-600 text-white font-bold text-xs py-1 px-2.5 rounded-lg">
                                        <i class="fa-solid fa-trash mr-1"></i>ELIMINAR
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400">
                                    <i class="fa-solid fa-users-slash text-4xl mb-3 text-gray-500 block"></i>
                                    No hay usuarios registrados en este negocio.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function () {
            const menu = document.getElementById('mobile-menu');
            menu?.classList.toggle('hidden');
        });
    </script>
</body>

</html>