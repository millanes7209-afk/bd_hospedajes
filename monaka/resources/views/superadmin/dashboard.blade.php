<!doctype html>
<html lang="es" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DASHBOARD SUPERADMIN - SCARY CONTROL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #090a0f;
            color: #f3f4f6;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .card-panel {
            background: #12141d;
            border: 1px solid rgba(255, 255, 255, 0.07);
        }

        .modal-bg {
            background: rgba(5, 6, 10, 0.85);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="min-h-screen pb-12">

    <!-- HEADER SUPERADMIN -->
    <header class="bg-[#12141d]/90 backdrop-blur-md border-b border-white/10 sticky top-0 z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-amber-400 to-red-500 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/20 font-black text-black">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h1 class="text-lg font-black tracking-wider uppercase text-white flex items-center gap-2">
                        SCARY CONTROL <span
                            class="text-xs bg-amber-500/20 text-amber-400 border border-amber-500/40 px-2.5 py-0.5 rounded-full font-bold">SUPERADMIN</span>
                    </h1>
                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">scary.alloggibolivia.com
                        &bull; saas_control</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <span
                        class="block text-xs font-black uppercase text-white">{{ Session::get('superadmin_nombre', 'SUPERADMIN') }}</span>
                    <span class="block text-[10px] text-gray-400 font-bold">{{ Session::get('superadmin_email',
                        'admin@scary.com') }}</span>
                </div>
                <form action="{{ route('superadmin.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 px-3.5 py-2 rounded-xl text-xs font-black uppercase flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-right-from-bracket"></i> SALIR
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-6 py-8">

        <!-- MENSAJES DE ÉXITO -->
        @if (session('success'))
            <div
                class="mb-6 p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-bold text-xs uppercase flex items-center gap-3 shadow-lg">
                <i class="fa-solid fa-circle-check text-lg"></i> {{ session('success') }}
            </div>
        @endif

        <!-- HEADER ACCIONES Y MÉTRICAS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
            <div class="card-panel p-5 rounded-2xl flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400 text-xl font-bold">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">TOTAL
                        EMPRESAS</span>
                    <span class="text-2xl font-black text-white">{{ $totalTenants }}</span>
                </div>
            </div>

            <div class="card-panel p-5 rounded-2xl flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-xl font-bold">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">EMPRESAS
                        ACTIVAS</span>
                    <span class="text-2xl font-black text-emerald-400">{{ $activeTenants }}</span>
                </div>
            </div>

            <div class="card-panel p-5 rounded-2xl flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-purple-400 text-xl font-bold">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">RUBROS
                        REGISTRADOS</span>
                    <span class="text-2xl font-black text-purple-300">{{ $rubrosCount }}</span>
                </div>
            </div>
        </div>

        <!-- BARRA SUPERIOR DE LA TABLA -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-xl font-black uppercase tracking-wider text-white flex items-center gap-2">
                    <i class="fa-solid fa-store text-amber-400"></i> GESTIÓN DE EMPRESAS / TENANTS
                </h2>
                <p class="text-xs text-gray-400 font-bold mt-0.5">Control centralizado de conexiones a bases de datos y
                    marcas</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-black font-black text-xs uppercase px-5 py-3 rounded-xl shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-plus text-sm"></i> REGISTRAR NUEVA EMPRESA
            </button>
        </div>

        <!-- TABLA DE TENANTS -->
        <div class="card-panel rounded-2xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-black/40 text-gray-400 uppercase tracking-wider font-black border-b border-white/10 text-[11px]">
                        <tr>
                            <th class="py-4 px-5">EMPRESA / LOGO</th>
                            <th class="py-4 px-5">SUBDOMINIO</th>
                            <th class="py-4 px-5">RUBRO</th>
                            <th class="py-4 px-5">CONEXIÓN BASE DE DATOS</th>
                            <th class="py-4 px-5">COLORES</th>
                            <th class="py-4 px-5 text-center">ESTADO</th>
                            <th class="py-4 px-5 text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 font-bold">
                        @forelse($tenants as $t)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ asset($t->logo ?: 'assets/logo.svg') }}" alt="{{ $t->nombre }}"
                                            class="w-10 h-10 rounded-xl object-contain bg-black/50 p-1 border border-white/10">
                                        <div>
                                            <span
                                                class="block font-black text-sm text-white uppercase">{{ $t->nombre }}</span>
                                            <span class="block text-[10px] text-gray-400 font-bold">ID: #{{ $t->id }} &bull;
                                                {{ $t->created_at ? $t->created_at->format('d/m/Y') : '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <span
                                        class="bg-amber-500/10 text-amber-300 border border-amber-500/30 px-2.5 py-1 rounded-lg font-black text-xs">
                                        {{ $t->subdominio }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 uppercase text-gray-300">{{ $t->rubro }}</td>
                                <td class="py-4 px-5">
                                    <div class="text-[11px] space-y-0.5">
                                        <span class="block text-gray-400"><i
                                                class="fa-solid fa-server mr-1 text-gray-500"></i>{{ $t->db_host }}</span>
                                        <span class="block text-emerald-400 font-black"><i
                                                class="fa-solid fa-database mr-1"></i>{{ $t->db_nombre }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full border border-white/20 shadow-sm"
                                            style="background: {{ $t->primary_color ?? '#FFE66D' }}"
                                            title="Primary: {{ $t->primary_color }}"></span>
                                        <span class="w-4 h-4 rounded-full border border-white/20 shadow-sm"
                                            style="background: {{ $t->accent_color ?? '#E23E1A' }}"
                                            title="Accent: {{ $t->accent_color }}"></span>
                                    </div>
                                </td>
                                <td class="py-4 px-5 text-center">
                                    @if($t->_estado === 'A')
                                        <span
                                            class="inline-flex items-center gap-1 bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> ACTIVO
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 bg-red-500/15 text-red-400 border border-red-500/30 px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> INACTIVO
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="openEditModal({{ json_encode($t) }})"
                                            class="bg-blue-500/10 hover:bg-blue-500/20 text-blue-300 border border-blue-500/30 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                        </button>
                                        <a href="{{ route('superadmin.tenants.estado', $t->id) }}"
                                            class="{{ $t->_estado === 'A' ? 'bg-amber-500/10 text-amber-300 border-amber-500/30' : 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30' }} hover:opacity-80 border px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                            <i class="fa-solid fa-power-off"></i>
                                            {{ $t->_estado === 'A' ? 'Desactivar' : 'Activar' }}
                                        </a>
                                        <a href="https://{{ $t->subdominio }}.alloggibolivia.com" target="_blank"
                                            class="bg-white/5 hover:bg-white/10 text-gray-300 border border-white/10 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Tienda
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-gray-500 font-bold uppercase tracking-wider">
                                    No hay empresas registradas actualmente
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL REGISTRAR NUEVA EMPRESA -->
    <div id="modalCreate" class="fixed inset-0 z-50 hidden modal-bg flex items-center justify-center p-4">
        <div class="card-panel w-full max-w-2xl rounded-3xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-white/10">
                <h3 class="text-lg font-black uppercase text-white flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-amber-400"></i> REGISTRAR NUEVA EMPRESA (TENANT)
                </h3>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-white font-bold text-lg">✕</button>
            </div>

            <form action="{{ route('superadmin.tenants.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-4 text-xs font-bold">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">NOMBRE DE LA EMPRESA *</label>
                        <input type="text" name="nombre" required placeholder="Ej: Pizzería POPULAR"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-white focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">SUBDOMINIO *</label>
                        <input type="text" name="subdominio" required placeholder="ej: popular"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-amber-300 font-black focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">RUBRO / CATEGORÍA *</label>
                        <input type="text" name="rubro" required placeholder="Ej: PIZZERIA, RESTAURANTE, SALTEÑERIA"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-white focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">LOGO DE LA EMPRESA (SVG / PNG)</label>
                        <input type="file" name="logo" accept="image/*"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-2.5 text-gray-300 focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">ESLOGAN DE LA EMPRESA</label>
                        <input type="text" name="eslogan" placeholder="Ej: El auténtico sabor de la salteña"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-amber-200 focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">TEXTO PIE DE PÁGINA (FOOTER)</label>
                        <input type="text" name="footer_texto" placeholder="Ej: Tradición, jugo y sabor inigualables"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-gray-300 focus:border-amber-400 focus:outline-none">
                    </div>
                </div>

                <hr class="border-white/10 my-4">
                <h4 class="text-amber-400 font-black uppercase tracking-wider text-[11px]">DATOS DE CONEXIÓN BASE DE
                    DATOS</h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">DB HOST *</label>
                        <input type="text" name="db_host" required value="sdb-56.hosting.stackcp.net"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-white focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">NOMBRE BASE DE DATOS *</label>
                        <input type="text" name="db_nombre" required placeholder="ej: popular-353031334944"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-emerald-400 focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">USUARIO BASE DE DATOS *</label>
                        <input type="text" name="db_usuario" required placeholder="ej: popular-353031334944"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-white focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">CONTRASEÑA BASE DE DATOS *</label>
                        <input type="password" name="db_password" required value="SCARYmovie1."
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-white focus:border-amber-400 focus:outline-none">
                    </div>
                </div>

                <hr class="border-white/10 my-4">
                <h4 class="text-amber-400 font-black uppercase tracking-wider text-[11px]">PALETA DE COLORES DE LA MARCA
                </h4>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">COLOR PRIMARIO</label>
                        <input type="color" name="primary_color" value="#FFE66D"
                            class="w-full h-10 rounded-xl cursor-pointer bg-black/50 border border-white/10 p-1">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">COLOR ACENTO</label>
                        <input type="color" name="accent_color" value="#E23E1A"
                            class="w-full h-10 rounded-xl cursor-pointer bg-black/50 border border-white/10 p-1">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">FONDO OSCURO</label>
                        <input type="color" name="dark_bg_color" value="#09090c"
                            class="w-full h-10 rounded-xl cursor-pointer bg-black/50 border border-white/10 p-1">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">TARJETA OSCURA</label>
                        <input type="color" name="dark_card_color" value="#15151e"
                            class="w-full h-10 rounded-xl cursor-pointer bg-black/50 border border-white/10 p-1">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-white/10">
                    <button type="button" onclick="closeCreateModal()"
                        class="px-5 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 uppercase">CANCELAR</button>
                    <button type="submit"
                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 text-black font-black uppercase shadow-lg shadow-amber-500/20">GUARDAR
                        EMPRESA</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR EMPRESA -->
    <div id="modalEdit" class="fixed inset-0 z-50 hidden modal-bg flex items-center justify-center p-4">
        <div class="card-panel w-full max-w-2xl rounded-3xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-white/10">
                <h3 class="text-lg font-black uppercase text-white flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-blue-400"></i> EDITAR EMPRESA (TENANT)
                </h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-white font-bold text-lg">✕</button>
            </div>

            <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-bold">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">NOMBRE DE LA EMPRESA *</label>
                        <input type="text" id="edit_nombre" name="nombre" required
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-white focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">SUBDOMINIO *</label>
                        <input type="text" id="edit_subdominio" name="subdominio" required
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-amber-300 font-black focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">RUBRO / CATEGORÍA *</label>
                        <input type="text" id="edit_rubro" name="rubro" required
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-white focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">CAMBIAR LOGO (OPCIONAL)</label>
                        <input type="file" name="logo" accept="image/*"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-2.5 text-gray-300 focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">ESLOGAN DE LA EMPRESA</label>
                        <input type="text" id="edit_eslogan" name="eslogan"
                            placeholder="Ej: El auténtico sabor de la salteña"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-amber-200 focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">TEXTO PIE DE PÁGINA (FOOTER)</label>
                        <input type="text" id="edit_footer_texto" name="footer_texto"
                            placeholder="Ej: Tradición, jugo y sabor inigualables"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-gray-300 focus:border-amber-400 focus:outline-none">
                    </div>
                </div>

                <hr class="border-white/10 my-4">
                <h4 class="text-amber-400 font-black uppercase tracking-wider text-[11px]">DATOS DE CONEXIÓN BASE DE
                    DATOS</h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">DB HOST *</label>
                        <input type="text" id="edit_db_host" name="db_host" required
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-white focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">NOMBRE BASE DE DATOS *</label>
                        <input type="text" id="edit_db_nombre" name="db_nombre" required
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-emerald-400 focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">USUARIO BASE DE DATOS *</label>
                        <input type="text" id="edit_db_usuario" name="db_usuario" required
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-white focus:border-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">CAMBIAR CONTRASEÑA DB (SI APLICA)</label>
                        <input type="password" name="db_password" placeholder="Dejar en blanco si no cambia"
                            class="w-full bg-black/50 border border-white/10 rounded-xl p-3 text-white focus:border-amber-400 focus:outline-none">
                    </div>
                </div>

                <hr class="border-white/10 my-4">
                <h4 class="text-amber-400 font-black uppercase tracking-wider text-[11px]">PALETA DE COLORES DE LA MARCA
                </h4>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">COLOR PRIMARIO</label>
                        <input type="color" id="edit_primary_color" name="primary_color"
                            class="w-full h-10 rounded-xl cursor-pointer bg-black/50 border border-white/10 p-1">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">COLOR ACENTO</label>
                        <input type="color" id="edit_accent_color" name="accent_color"
                            class="w-full h-10 rounded-xl cursor-pointer bg-black/50 border border-white/10 p-1">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">FONDO OSCURO</label>
                        <input type="color" id="edit_dark_bg_color" name="dark_bg_color"
                            class="w-full h-10 rounded-xl cursor-pointer bg-black/50 border border-white/10 p-1">
                    </div>
                    <div>
                        <label class="block uppercase text-gray-300 mb-1">TARJETA OSCURA</label>
                        <input type="color" id="edit_dark_card_color" name="dark_card_color"
                            class="w-full h-10 rounded-xl cursor-pointer bg-black/50 border border-white/10 p-1">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-white/10">
                    <button type="button" onclick="closeEditModal()"
                        class="px-5 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 uppercase">CANCELAR</button>
                    <button type="submit"
                        class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black uppercase shadow-lg">ACTUALIZAR
                        DATOS</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('modalCreate').classList.remove('hidden');
        }
        function closeCreateModal() {
            document.getElementById('modalCreate').classList.add('hidden');
        }

        function openEditModal(t) {
            const form = document.getElementById('editForm');
            form.action = `/superadmin/tenants/update/${t.id}`;
            document.getElementById('edit_nombre').value = t.nombre;
            document.getElementById('edit_subdominio').value = t.subdominio;
            document.getElementById('edit_rubro').value = t.rubro;
            document.getElementById('edit_eslogan').value = t.eslogan || '';
            document.getElementById('edit_footer_texto').value = t.footer_texto || '';
            document.getElementById('edit_db_host').value = t.db_host;
            document.getElementById('edit_db_nombre').value = t.db_nombre;
            document.getElementById('edit_db_usuario').value = t.db_usuario;
            document.getElementById('edit_primary_color').value = t.primary_color || '#FFE66D';
            document.getElementById('edit_accent_color').value = t.accent_color || '#E23E1A';
            document.getElementById('edit_dark_bg_color').value = t.dark_bg_color || '#09090c';
            document.getElementById('edit_dark_card_color').value = t.dark_card_color || '#15151e';

            document.getElementById('modalEdit').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('modalEdit').classList.add('hidden');
        }
    </script>
</body>

</html>