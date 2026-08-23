<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GESTIÓN DE CATEGORÍAS - PANEL ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '{{ $tenant->primary_color ?? "#FFE66D" }}', accent: '{{ $tenant->accent_color ?? "#E23E1A" }}', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar Unificada -->
    @include('layouts.admin_navbar')

    <div class="max-w-6xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-black uppercase admin-text-main"><i
                    class="fa-solid fa-layer-group mr-2 text-[#FFE66D]"></i>GESTIÓN DE CATEGORÍAS</h2>
            <button onclick="openModalNueva()"
                class="bg-[#E23E1A] hover:bg-red-600 text-white font-black text-xs py-3 px-5 rounded-xl uppercase tracking-wider flex items-center gap-2 shadow-lg transition-all">
                <i class="fa-solid fa-plus text-sm"></i>NUEVA CATEGORÍA
            </button>
        </div>

        @if (session('success'))
            <div
                class="mb-4 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-500 font-bold text-sm uppercase">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-4 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-500 font-bold text-sm uppercase">
                {{ session('error') }}
            </div>
        @endif

        <!-- Tabla de Categorías -->
        <div class="glass-card overflow-hidden border border-white/10 rounded-2xl">
            <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="admin-subcard admin-text-muted font-extrabold uppercase border-b"
                            style="border-color:var(--color-card-border)">
                            <th class="p-4">NOMBRE DE CATEGORÍA</th>
                            <th class="p-4">PRODUCTOS ASOCIADOS</th>
                            <th class="p-4">ESTADO</th>
                            <th class="p-4 text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y font-semibold" style="border-color:var(--color-card-border)">
                        @forelse($categorias as $cat)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="p-4 font-black uppercase admin-text-main tracking-wide">
                                    {{ $cat['nombre'] }}
                                </td>
                                <td class="p-4">
                                    <span
                                        class="admin-subcard admin-text-main border px-3 py-1 rounded-full text-[11px] font-bold"
                                        style="border-color:var(--color-card-border)">
                                        <i class="fa-solid fa-box mr-1 text-[#FFE66D]"></i>{{ $cat['total_productos'] }}
                                        Productos
                                    </span>
                                </td>
                                <td class="p-4">
                                    @if($cat['activo'])
                                        <span
                                            class="bg-green-500/20 border border-green-500/40 text-green-500 px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                            <i class="fa-solid fa-circle text-[7px] mr-1"></i>ACTIVA
                                        </span>
                                    @else
                                        <span
                                            class="bg-red-500/20 border border-red-500/40 text-red-500 px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                            <i class="fa-solid fa-circle text-[7px] mr-1"></i>INACTIVA
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-right space-x-2">
                                    <button onclick="openModalEditar({{ json_encode($cat) }})"
                                        class="px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-500 hover:bg-amber-500/30 font-bold uppercase transition-colors">
                                        <i class="fa-solid fa-pen-to-square mr-1"></i>EDITAR
                                    </button>
                                    <a href="{{ route('admin.categorias.estado', $cat['categoriaID']) }}"
                                        class="px-3 py-1.5 rounded-lg {{ $cat['activo'] ? 'bg-red-500/20 text-red-500 hover:bg-red-500/30' : 'bg-green-500/20 text-green-500 hover:bg-green-500/30' }} font-bold uppercase transition-colors">
                                        <i
                                            class="fa-solid {{ $cat['activo'] ? 'fa-eye-slash' : 'fa-eye' }} mr-1"></i>{{ $cat['activo'] ? 'DESACTIVAR' : 'ACTIVAR' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center admin-text-muted font-bold uppercase">
                                    NO HAY CATEGORÍAS REGISTRADAS
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL NUEVA CATEGORÍA -->
    <div id="modal-nueva"
        class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="glass-card max-w-md w-full p-6 border border-white/10 rounded-2xl relative">
            <button onclick="closeModalNueva()" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg"><i
                    class="fa-solid fa-xmark"></i></button>
            <h3 class="text-lg font-black uppercase text-[#FFE66D] mb-4"><i
                    class="fa-solid fa-folder-plus mr-2"></i>NUEVA CATEGORÍA</h3>

            <form action="{{ route('admin.categorias.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase mb-1 admin-text-muted">NOMBRE DE LA CATEGORÍA
                        *</label>
                    <input type="text" name="nombre" required placeholder="EJ: SALTEÑAS, REFRESCOS, COMBOS..."
                        class="admin-input w-full p-3 text-xs font-bold uppercase">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="closeModalNueva()"
                        class="px-4 py-2.5 rounded-xl border border-slate-700 text-xs font-bold uppercase text-gray-300 hover:bg-white/5">CANCELAR</button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#E23E1A] text-white text-xs font-black uppercase shadow-lg hover:bg-red-600">GUARDAR
                        CATEGORÍA</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR CATEGORÍA -->
    <div id="modal-editar"
        class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="glass-card max-w-md w-full p-6 border border-white/10 rounded-2xl relative">
            <button onclick="closeModalEditar()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg"><i
                    class="fa-solid fa-xmark"></i></button>
            <h3 class="text-lg font-black uppercase text-[#FFE66D] mb-4"><i
                    class="fa-solid fa-pen-to-square mr-2"></i>EDITAR CATEGORÍA</h3>

            <form id="form-editar-cat" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase mb-1">NOMBRE DE LA CATEGORÍA *</label>
                    <input type="text" name="nombre" id="edit-nombre" required
                        class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl p-3 text-xs font-bold uppercase">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="activo" id="edit-activo" value="1"
                        class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-amber-500">
                    <label for="edit-activo" class="text-xs font-bold uppercase text-gray-300 cursor-pointer">Categoría
                        Activa (Visible en el Menú)</label>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="closeModalEditar()"
                        class="px-4 py-2.5 rounded-xl border border-slate-700 text-xs font-bold uppercase text-gray-300 hover:bg-white/5">CANCELAR</button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-amber-500 text-black text-xs font-black uppercase shadow-lg hover:bg-amber-400">ACTUALIZAR</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModalNueva() {
            document.getElementById('modal-nueva').classList.remove('hidden');
        }
        function closeModalNueva() {
            document.getElementById('modal-nueva').classList.add('hidden');
        }
        function openModalEditar(cat) {
            document.getElementById('edit-nombre').value = cat.nombre;
            document.getElementById('edit-activo').checked = cat.activo == 1;
            document.getElementById('form-editar-cat').action = '/admin/categorias/update/' + cat.categoriaID;
            document.getElementById('modal-editar').classList.remove('hidden');
        }
        function closeModalEditar() {
            document.getElementById('modal-editar').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', () => {
                    menu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>

</html>