<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($usuario) && is_object($usuario) ? 'EDITAR' : 'CREAR' }} USUARIO - MONAKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-input-theme {
            background-color: var(--color-bg-alt, rgba(255, 255, 255, 0.05));
            color: var(--color-text, #ffffff);
            border: 1px solid var(--color-border, rgba(255, 255, 255, 0.15));
        }
        .form-input-theme:focus {
            border-color: #FFE66D;
            outline: none;
        }
        .form-select-option {
            background-color: var(--color-bg, #15151e);
            color: var(--color-text, #ffffff);
        }
    </style>
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar Unificada -->
    @include('layouts.admin_navbar')

    <div class="max-w-3xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-black uppercase flex items-center gap-2">
                <i class="fa-solid {{ (isset($usuario) && is_object($usuario)) ? 'fa-user-pen' : 'fa-user-plus' }} text-[#FFE66D]"></i>
                {{ (isset($usuario) && is_object($usuario)) ? 'EDITAR USUARIO' : 'CREAR NUEVO USUARIO' }}
            </h2>
            <a href="{{ route('admin.usuarios') }}" class="btn-outline text-xs font-bold uppercase !py-2 !px-3">
                <i class="fa-solid fa-arrow-left mr-1.5"></i>VOLVER A LISTA
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="glass-card p-6 border border-white/10 rounded-2xl">
            <form
                action="{{ (isset($usuario) && is_object($usuario)) ? route('admin.usuarios.update', $usuario->id) : route('admin.usuarios.store') }}"
                method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase mb-2" style="color:var(--color-text-muted, #9ca3af);">
                        NOMBRE COMPLETO / USUARIO *
                    </label>
                    <input type="text" name="name" value="{{ old('name', (isset($usuario) && is_object($usuario)) ? $usuario->name : '') }}" required
                        placeholder="EJ: JUAN PÉREZ (CAJERO)"
                        oninput="this.value = this.value.toUpperCase();"
                        style="text-transform: uppercase;"
                        class="w-full form-input-theme rounded-xl px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase mb-2" style="color:var(--color-text-muted, #9ca3af);">
                        CORREO ELECTRÓNICO *
                    </label>
                    <input type="email" name="email" value="{{ old('email', (isset($usuario) && is_object($usuario)) ? $usuario->email : '') }}" required
                        placeholder="ejemplo@monaka.com"
                        class="w-full form-input-theme rounded-xl px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase mb-2" style="color:var(--color-text-muted, #9ca3af);">
                        ROL EN EL SISTEMA *
                    </label>
                    <select name="rol" required class="w-full form-input-theme rounded-xl px-4 py-3 text-sm">
                        <option value="ADMINISTRADOR" class="form-select-option" {{ old('rol', (isset($usuario) && is_object($usuario)) ? $usuario->rol : '') === 'ADMINISTRADOR' ? 'selected' : '' }}>ADMINISTRADOR (Acceso Total)</option>
                        <option value="CAJERO" class="form-select-option" {{ old('rol', (isset($usuario) && is_object($usuario)) ? $usuario->rol : '') === 'CAJERO' ? 'selected' : '' }}>CAJERO (Atención de Pedidos y Operación)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase mb-2" style="color:var(--color-text-muted, #9ca3af);">
                        CONTRASEÑA {{ (isset($usuario) && is_object($usuario)) ? '(Dejar en blanco para conservar la actual)' : '*' }}
                    </label>
                    <input type="password" name="password" {{ (isset($usuario) && is_object($usuario)) ? '' : 'required' }}
                        placeholder="Mínimo 6 caracteres"
                        class="w-full form-input-theme rounded-xl px-4 py-3 text-sm">
                </div>

                <div class="pt-4 border-t border-white/10 flex justify-end gap-3">
                    <a href="{{ route('admin.usuarios') }}"
                        class="btn-outline text-xs font-bold uppercase !py-2.5 !px-5">CANCELAR</a>
                    <button type="submit" class="btn-primary text-xs font-black uppercase !py-2.5 !px-6 shadow-lg">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i>{{ (isset($usuario) && is_object($usuario)) ? 'GUARDAR CAMBIOS' : 'CREAR USUARIO' }}
                    </button>
                </div>
            </form>
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