<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MI PERFIL - RICO POLLO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar -->
    <header class="glass-card mb-6 p-4 border-b rounded-none"
        style="border-color:var(--color-card-border);background:var(--color-bg-alt)">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center justify-between w-full md:w-auto">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-8">
                        <img src="{{ asset('assets/logo.svg') }}" alt="LOGO" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="text-base md:text-lg font-black text-[#FFE66D] tracking-wider uppercase">RICO POLLO -
                            PANEL ADMIN</h1>
                    </div>
                </div>
                <button id="mobile-menu-btn"
                    class="md:hidden text-xl text-gray-300 hover:text-white p-2 focus:outline-none">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <div id="nav-menu" class="hidden md:flex items-center gap-2 flex-wrap w-full md:w-auto">
                <a href="{{ route('admin.pedidos') }}" class="btn-outline text-xs font-bold uppercase !py-2 !px-3">
                    <i class="fa-solid fa-clipboard-list mr-1.5"></i>PEDIDOS
                </a>
                <a href="{{ route('admin.productos') }}" class="btn-outline text-xs font-bold uppercase !py-2 !px-3">
                    <i class="fa-solid fa-utensils mr-1.5"></i>PRODUCTOS
                </a>
                @if(Session::get('is_super_admin') || Session::get('rolID') === 'ADMINISTRADOR')
                    <a href="{{ route('admin.usuarios') }}" class="btn-outline text-xs font-bold uppercase !py-2 !px-3">
                        <i class="fa-solid fa-users mr-1.5"></i>USUARIOS
                    </a>
                @endif
                <a href="{{ route('admin.perfil') }}"
                    class="btn-primary text-xs font-black uppercase !py-2 !px-4 shadow-lg">
                    <i class="fa-solid fa-user-gear mr-1.5"></i>MI PERFIL
                </a>
                <a href="{{ route('menu') }}" class="btn-outline text-xs font-bold uppercase !py-2 !px-3"
                    target="_blank">
                    <i class="fa-solid fa-store mr-1.5"></i>TIENDA
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="btn-outline text-xs font-bold uppercase !py-2 !px-3 border-red-500/40 text-red-400 hover:bg-red-950/30">
                        <i class="fa-solid fa-right-from-bracket mr-1.5"></i>SALIR
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="max-w-3xl mx-auto px-4 py-6">
        <div class="mb-6">
            <h2 class="text-2xl font-black uppercase flex items-center gap-2">
                <i class="fa-solid fa-user-gear text-[#FFE66D]"></i> MI PERFIL Y SEGURIDAD
            </h2>
            <p class="text-xs text-gray-400 mt-1">Modifica tu contraseña personal de acceso al sistema.</p>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300 font-bold text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 font-bold text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="glass-card p-6 border border-white/10 rounded-2xl space-y-6">
            <div class="flex items-center gap-4 pb-6 border-b border-white/10">
                <div
                    class="w-14 h-14 rounded-full bg-[#FFE66D]/10 border border-[#FFE66D]/30 flex items-center justify-center text-[#FFE66D] text-2xl font-black">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black uppercase text-white">
                        {{ $isSuperAdmin ? 'DESARROLLADOR SCARY (SUPER ADMIN)' : ($user->name ?? 'USUARIO') }}</h3>
                    <p class="text-xs text-gray-400">{{ $isSuperAdmin ? 'micklanessz@gmail.com' : ($user->email ?? '')
                        }}</p>
                    <span
                        class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-500/20 text-purple-300 border border-purple-500/40">
                        ROL: {{ Session::get('rolID', 'USUARIO') }}
                    </span>
                </div>
            </div>

            <form action="{{ route('admin.perfil.password') }}" method="POST" class="space-y-5">
                @csrf
                <h4 class="text-sm font-black uppercase text-[#FFE66D] flex items-center gap-2">
                    <i class="fa-solid fa-key"></i> CAMBIAR MI CONTRASEÑA
                </h4>

                <div>
                    <label class="block text-xs font-bold text-gray-300 uppercase mb-2">CONTRASEÑA ACTUAL *</label>
                    <input type="password" name="current_password" required placeholder="Ingresa tu contraseña actual"
                        class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#FFE66D] text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-300 uppercase mb-2">NUEVA CONTRASEÑA *</label>
                    <input type="password" name="new_password" required placeholder="Mínimo 6 caracteres"
                        class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#FFE66D] text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-300 uppercase mb-2">CONFIRMAR NUEVA CONTRASEÑA
                        *</label>
                    <input type="password" name="new_password_confirmation" required
                        placeholder="Repite la nueva contraseña"
                        class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#FFE66D] text-white">
                </div>

                <div class="pt-4 border-t border-white/10 flex justify-end">
                    <button type="submit" class="btn-primary text-xs font-black uppercase !py-2.5 !px-6 shadow-lg">
                        <i class="fa-solid fa-shield-halved mr-1.5"></i>ACTUALIZAR CONTRASEÑA
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function () {
            const menu = document.getElementById('nav-menu');
            menu.classList.toggle('hidden');
            menu.classList.toggle('flex');
            menu.classList.toggle('flex-col');
        });
    </script>
</body>

</html>