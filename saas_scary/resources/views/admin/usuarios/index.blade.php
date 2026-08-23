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

    <!-- Navbar Unificada -->
    @include('layouts.admin_navbar')

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-black uppercase flex items-center gap-2 admin-text-main">
                    <i class="fa-solid fa-users text-[#FFE66D]"></i> GESTIÓN DE USUARIOS
                </h2>
                <p class="text-xs admin-text-muted mt-1">Administra los usuarios y roles asignados en Rico Pollo.</p>
            </div>
            <a href="{{ route('admin.usuarios.create') }}"
                class="btn-primary text-xs font-black uppercase !py-2.5 !px-4 shadow-lg flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i> CREAR NUEVO USUARIO
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-500 font-bold text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-500 font-bold text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                <table class="w-full text-left text-xs">
                    <thead
                        class="admin-subcard admin-text-muted uppercase font-black tracking-wider border-b font-extrabold"
                        style="border-color:var(--color-card-border)">
                        <tr>
                            <th class="p-4">NOMBRE / USUARIO</th>
                            <th class="p-4">CORREO ELECTRÓNICO</th>
                            <th class="p-4">ROL ASIGNADO</th>
                            <th class="p-4">FECHA REGISTRO</th>
                            <th class="p-4 text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y font-semibold" style="border-color:var(--color-card-border)">
                        @forelse ($usuarios as $user)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="p-4 font-black uppercase admin-text-main">{{ $user->name }}</td>
                                <td class="p-4 admin-text-muted">{{ $user->email }}</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                                        @if($user->rol === 'ADMINISTRADOR') bg-purple-500/20 text-purple-400 border border-purple-500/40
                                                        @else bg-blue-500/20 text-blue-400 border border-blue-500/40
                                                        @endif">
                                        {{ $user->rol ?: 'ADMINISTRADOR' }}
                                    </span>
                                </td>
                                <td class="p-4 admin-text-muted">
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
                                <td colspan="5" class="p-8 text-center admin-text-muted">
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