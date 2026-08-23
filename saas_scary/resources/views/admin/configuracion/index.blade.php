<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CONFIGURACIÓN DE EMPRESA & LOGO - PANEL ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '{{ $tenant->primary_color ?? "#FFE66D" }}', accent: '{{ $tenant->accent_color ?? "#E23E1A" }}', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar Unificada -->
    @include('layouts.admin_navbar')

    <div class="max-w-4xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-black uppercase flex items-center gap-2" style="color:var(--color-text)"><i
                    class="fa-solid fa-sliders text-amber-400"></i>PERFIL DE EMPRESA Y CONFIGURACIÓN DE LOGO</h2>
        </div>

        @if (session('success'))
            <div
                class="mb-4 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-600 dark:text-green-300 font-bold text-sm uppercase">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="mb-4 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-600 dark:text-red-300 font-bold text-sm uppercase space-y-1">
                @foreach ($errors->all() as $error)
                    <p><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.configuracion.update') }}" method="POST" enctype="multipart/form-data"
            class="admin-box glass-card p-6 border rounded-2xl space-y-6">
            @csrf

            <!-- LOGO DE LA EMPRESA -->
            <div class="admin-subcard p-5 rounded-xl border">
                <label class="block text-xs font-black uppercase text-amber-500 dark:text-amber-400 mb-3">
                    <i class="fa-solid fa-image mr-1.5"></i>LOGO OFICIAL DE LA EMPRESA
                </label>

                <div class="flex flex-col md:flex-row items-center gap-6">
                    <div
                        class="w-36 h-36 admin-box border rounded-2xl p-3 flex items-center justify-center relative overflow-hidden group">
                        @if(!empty($tenant->logo) && file_exists(public_path($tenant->logo)))
                            <img id="logo-preview" src="{{ asset($tenant->logo) }}" alt="Preview"
                                class="w-full h-full object-contain">
                        @else
                            <img id="logo-preview" src="{{ asset('assets/logo.svg') }}" alt="Preview"
                                class="w-full h-full object-contain">
                        @endif
                    </div>

                    <div class="flex-1 space-y-2">
                        <p class="text-xs admin-text-muted font-semibold">Selecciona una imagen desde tu dispositivo
                            para actualizar el logo en la tienda pública y el panel de administración.</p>
                        <p class="text-[11px] admin-text-muted opacity-80">Formatos permitidos: PNG, JPG, JPEG, SVG,
                            WEBP (Máx. 2MB)</p>
                        <input type="file" name="logo" id="input-logo" accept="image/*"
                            class="w-full admin-input border text-xs rounded-xl p-2.5 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-amber-400 file:text-black hover:file:bg-amber-300 cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- DATOS GENERALES -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase mb-1 admin-text-main">NOMBRE DE LA EMPRESA</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $tenant->nombre) }}"
                        class="w-full admin-input border rounded-xl p-3 text-xs font-bold uppercase" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase mb-1 admin-text-main">SUBDOMINIO (SISTEMA)</label>
                    <input type="text" value="{{ $tenant->subdominio }}.alloggibolivia.com"
                        class="w-full admin-input border opacity-75 rounded-xl p-3 text-xs font-bold uppercase"
                        readonly>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase mb-1 admin-text-main">ESLOGAN / SUBTÍTULO DEL
                        MENÚ</label>
                    <input type="text" name="eslogan"
                        value="{{ old('eslogan', $tenant->eslogan ?? 'Sabor que cruje, pasión que deleita') }}"
                        class="w-full admin-input border rounded-xl p-3 text-xs font-bold"
                        placeholder="Ej: Las mejores salteñas y refrescos tradicionales">
                </div>
            </div>

            <!-- PALETA DE COLORES -->
            <div class="border-t pt-4" style="border-color:var(--color-card-border)">
                <h3 class="text-xs font-black uppercase text-amber-500 dark:text-amber-400 mb-3">
                    <i class="fa-solid fa-palette mr-1.5"></i>PALETA DE COLORES DE LA MARCA
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 admin-subcard p-3 rounded-xl border">
                        <input type="color" name="primary_color" value="{{ $tenant->primary_color ?? '#FFE66D' }}"
                            class="w-10 h-10 rounded-lg cursor-pointer border-0">
                        <div>
                            <span class="block text-xs font-bold uppercase admin-text-main">COLOR PRIMARIO</span>
                            <span class="text-[10px] admin-text-muted">Usado en títulos y resaltados</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 admin-subcard p-3 rounded-xl border">
                        <input type="color" name="accent_color" value="{{ $tenant->accent_color ?? '#E23E1A' }}"
                            class="w-10 h-10 rounded-lg cursor-pointer border-0">
                        <div>
                            <span class="block text-xs font-bold uppercase admin-text-main">COLOR SECUNDARIO
                                (ACENTO)</span>
                            <span class="text-[10px] admin-text-muted">Usado en botones de acción y carritos</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit"
                    class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-black text-xs py-3.5 px-6 rounded-xl shadow-lg uppercase tracking-wider flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk text-sm"></i>GUARDAR CAMBIOS Y LOGO
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('input-logo').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('logo-preview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>