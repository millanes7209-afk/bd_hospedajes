@extends('layouts.app')

@section('title', 'PRODUCCIÓN CENTRAL & ARMADO — SALTEÑAS')

@section('content')
    <div class="space-y-6" x-data="produccionManager({
        activeTab: '{{ $tab }}',
        preparaciones: {{ json_encode($preparacionesJson) }},
        variantes: {{ json_encode($variantesJson) }}
    })">

        <!-- Header Page & Título -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-kitchen-set text-rose-500"></i> PRODUCCIÓN CENTRAL & COCINA
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase mt-0.5">
                    FASE 3: PRODUCCIÓN EN 2 TIEMPOS — TANDAS INTERMEDIAS (MASA/JIGOTE) Y MESA DE ARMADO LIBRE
                </p>
            </div>
        </div>

        <!-- Alertas de Éxito / Error -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 font-bold text-xs uppercase flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-circle-check text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 font-bold text-xs uppercase flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- KPI Cards: Inventario Intermedio en Cámara Fría / Mesa -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm">
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                    🥟 TAPAS EN FRÍO (MASAS)
                </span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ number_format($totalTapasStock, 0) }} <span class="text-xs text-slate-400 font-bold uppercase">UDS.</span>
                </span>
                <span class="text-[10px] font-bold text-slate-400 block mt-1 uppercase">
                    LISTAS PARA ARMAR
                </span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm">
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                    🥣 JIGOTE EN FRÍO (APROX.)
                </span>
                <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">
                    ~{{ number_format($totalJigoteStock, 0) }} <span class="text-xs text-slate-400 font-bold uppercase">PORCIONES</span>
                </span>
                <span class="text-[10px] font-bold text-slate-400 block mt-1 uppercase">
                    EN OLLAS / RECIPIENTES
                </span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm">
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                    🥟 SALTEÑAS ARMADAS LISTAS
                </span>
                <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                    {{ number_format($totalSaltenasArmadas, 0) }} <span class="text-xs text-slate-400 font-bold uppercase">EN CENTRAL</span>
                </span>
                <span class="text-[10px] font-bold text-slate-400 block mt-1 uppercase">
                    LISTAS PARA DESPACHO/HORNO
                </span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm">
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                    📦 MATERIA PRIMA (ALMACÉN)
                </span>
                <span class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">
                    {{ $insumos->count() }} <span class="text-xs text-slate-400 font-bold uppercase">INSUMOS</span>
                </span>
                <span class="text-[10px] font-bold text-slate-400 block mt-1 uppercase">
                    HARINA, CARNES, CONDIMENTOS
                </span>
            </div>
        </div>

        <!-- Navegación por Pestañas -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 overflow-x-auto">
            <button type="button" @click="activeTab = 'tandas'; history.replaceState(null, '', '?tab=tandas')"
                :class="activeTab === 'tandas' ? 'bg-rose-600 text-white font-black shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 font-bold'"
                class="px-4 py-2 rounded-lg text-xs uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-bowl-rice"></i>
                <span>PASO 1: PREPARAR TANDAS (MASA & JIGOTE)</span>
            </button>

            <button type="button" @click="activeTab = 'armado'; history.replaceState(null, '', '?tab=armado')"
                :class="activeTab === 'armado' ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 font-bold'"
                class="px-4 py-2 rounded-lg text-xs uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-cookie"></i>
                <span>PASO 2: MESA DE ARMADO (SALTEÑAS LIBRES)</span>
            </button>

            <button type="button" @click="activeTab = 'historial'; history.replaceState(null, '', '?tab=historial')"
                :class="activeTab === 'historial' ? 'bg-slate-800 text-white font-black shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 font-bold'"
                class="px-4 py-2 rounded-lg text-xs uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>HISTORIAL DE PRODUCCIÓN</span>
            </button>
        </div>

        <!-- ============================================================== -->
        <!-- PESTAÑA 1: PASO 1 — PREPARAR TANDAS (MASA & JIGOTE)            -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'tandas'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Formulario de Registro de Tanda (Col 5) -->
                <div class="lg:col-span-5">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                            <h2 class="text-xs font-black text-slate-900 dark:text-rose-400 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-plus-circle text-rose-500"></i> REGISTRAR TANDA PRODUCIDA
                            </h2>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">
                                Descuenta automáticamente harina, manteca, carne, etc. de compras y suma las tapas o porciones listas a tu frío.
                            </p>
                        </div>

                        <form action="{{ route('produccion.tanda.store') }}" method="POST" class="space-y-4" x-data="tandaForm()">
                            @csrf
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                        FECHA <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-rose-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                        TANDAS A HACER <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="number" step="any" min="0.1" name="tandas" x-model.number="tandas" required placeholder="1"
                                        @focus="$event.target.select()" @click="$event.target.select()"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 uppercase focus:border-rose-500 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    ¿QUÉ PREPARASTE? (MASA / JIGOTE) <span class="text-amber-500">*</span>
                                </label>
                                <select name="preparacion_id" x-model="selectedPrepId" @change="onPrepChange()" required
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-rose-500 focus:outline-none">
                                    <option value="">SELECCIONAR PREPARACIÓN...</option>
                                    <template x-for="p in preparacionesList" :key="p.id">
                                        <option :value="p.id" x-text="`${p.nombre.toUpperCase()} (RINDE ~${p.rinde_cantidad} UDS.)`"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Campo Cantidad Producida (Exacto para tapas / Aprox para jigote) -->
                            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
                                <label class="block text-[11px] font-bold uppercase text-slate-700 dark:text-slate-300">
                                    <span x-text="esJigote() ? 'PORCIONES OBTENIDAS (APROXIMADO):' : 'TAPAS OBTENIDAS (CONTEO EXACTO):'"></span>
                                    <span class="text-amber-500">*</span>
                                </label>
                                <input type="number" step="any" min="1" name="cantidad_producida" x-model.number="cantidadProducida" required
                                    @focus="$event.target.select()" @click="$event.target.select()"
                                    class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-sm font-black text-rose-600 dark:text-rose-400 focus:border-rose-500 focus:outline-none uppercase">
                                <p class="text-[9px] font-bold text-slate-400 uppercase" x-show="esJigote()">
                                    ℹ️ En el jigote es un rendimiento estimado; el sistema descontará de manera flexible sin trancar si sobra un poco en la olla.
                                </p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase" x-show="!esJigote()">
                                    ℹ️ En la masa puedes anotar el número exacto de tapas que cortaste y contaste al finalizar.
                                </p>
                            </div>

                            <!-- Desglose de Insumos que se Descontarán de Almacén -->
                            <div x-show="currentReceta.length > 0" class="space-y-2">
                                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block">
                                    📉 INSUMOS QUE SE DESCONTARÁN DE ALMACÉN:
                                </span>
                                <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                                    <table class="w-full text-left text-[11px]">
                                        <thead class="bg-slate-50 dark:bg-slate-950 text-[9px] font-black text-slate-400 uppercase border-b border-slate-200 dark:border-slate-800">
                                            <tr>
                                                <th class="py-1.5 px-2">Insumo</th>
                                                <th class="py-1.5 px-2 text-right">A Descontar</th>
                                                <th class="py-1.5 px-2 text-right">Stock Almacén</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            <template x-for="r in currentReceta" :key="r.insumo_id">
                                                <tr>
                                                    <td class="py-1.5 px-2 font-bold text-slate-800 dark:text-slate-200 uppercase" x-text="r.insumo_nombre"></td>
                                                    <td class="py-1.5 px-2 text-right font-black text-rose-600 dark:text-rose-400" x-text="`${(r.cantidad_base * tandas).toFixed(3)} ${r.unidad.toUpperCase()}`"></td>
                                                    <td class="py-1.5 px-2 text-right font-bold" :class="r.stock_disponible < (r.cantidad_base * tandas) ? 'text-rose-500' : 'text-slate-400'"
                                                        x-text="`${r.stock_disponible.toFixed(2)} ${r.unidad.toUpperCase()}`"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">NOTAS / OBSERVACIONES (OPCIONAL)</label>
                                <input type="text" name="observaciones" placeholder="Ej. Tanda doble, harina marca X"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-800 dark:text-white uppercase focus:border-rose-500 focus:outline-none">
                            </div>

                            <button type="submit"
                                class="w-full py-2.5 px-4 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-bowl-rice"></i> REGISTRAR TANDA & DESCONTAR INSUMOS
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista y Control de Stock de Preparaciones en Frío (Col 7) -->
                <div class="lg:col-span-7 space-y-4" x-data="{ adjustingId: null, nuevoStock: '' }">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-cubes-stacked text-rose-500"></i> INVENTARIO INTERMEDIO EN CÁMARA FRÍA
                            </h2>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $preparaciones->count() }} TIPOS</span>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($preparaciones as $prep)
                                @php
                                    $esMasa = str_contains(strtolower($prep->nombre), 'masa') || str_contains(strtolower($prep->nombre), 'tapa') || strtolower($prep->tipo_preparacion) === 'masa';
                                @endphp
                                <div class="p-4 space-y-2 hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-black text-slate-900 dark:text-white text-sm uppercase flex items-center gap-2">
                                                {{ $esMasa ? '🥟' : '🥣' }} {{ strtoupper($prep->nombre) }}
                                                <span class="text-[9px] px-2 py-0.5 rounded uppercase font-black {{ $esMasa ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400' }}">
                                                    {{ $esMasa ? 'MASA / TAPAS' : 'JIGOTE / RELLENO' }}
                                                </span>
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase block mt-0.5">
                                                Rinde receta base: {{ $prep->rinde_cantidad }} uds. • Costo: Bs. {{ number_format($prep->costoEnFecha(), 3) }} / ud.
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <div class="text-right">
                                                <span class="text-[9px] font-black text-slate-400 uppercase block">STOCK DISPONIBLE</span>
                                                <span class="text-base font-black {{ $prep->stock_actual > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }} uppercase">
                                                    {{ number_format((float)$prep->stock_actual, 0) }} {{ $esMasa ? 'TAPAS' : 'PORCIONES' }}
                                                </span>
                                            </div>

                                            <button type="button" @click="adjustingId = (adjustingId === {{ $prep->id }} ? null : {{ $prep->id }}); nuevoStock = '{{ (float)$prep->stock_actual }}'"
                                                class="px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-[10px] font-bold uppercase transition-all"
                                                title="Ajustar Stock Manualmente">
                                                <i class="fa-solid fa-sliders"></i> AJUSTAR
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Formulario Inline de Ajuste de Stock -->
                                    <div x-show="adjustingId === {{ $prep->id }}" x-cloak class="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-lg border border-amber-200 dark:border-amber-800 mt-2">
                                        <form action="{{ route('produccion.ajuste', $prep->id) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            <span class="text-[10px] font-bold text-amber-800 dark:text-amber-300 uppercase shrink-0">CORREGIR CONTEO:</span>
                                            <input type="number" step="any" min="0" name="nuevo_stock" x-model="nuevoStock" required
                                                @focus="$event.target.select()" @click="$event.target.select()"
                                                class="w-28 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-black text-slate-900 dark:text-white">
                                            <button type="submit" class="px-2.5 py-1 rounded bg-amber-500 text-slate-950 font-black text-[10px] uppercase shadow">
                                                GUARDAR AJUSTE
                                            </button>
                                            <button type="button" @click="adjustingId = null" class="text-slate-400 hover:text-slate-600 text-xs">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-slate-400 font-bold uppercase text-xs">
                                    NO HAY PREPARACIONES REGISTRADAS. CREA RECETAS DE MASA Y JIGOTE EN "PRODUCTOS & RECETAS".
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ============================================================== -->
        <!-- PESTAÑA 2: PASO 2 — MESA DE ARMADO (SALTEÑAS LIBRES)           -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'armado'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Formulario de Mesa de Armado Libre (Col 5) -->
                <div class="lg:col-span-5">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                            <h2 class="text-xs font-black text-slate-900 dark:text-amber-400 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-cookie text-amber-500"></i> ARMAR SALTEÑAS (MESA DE TRABAJO)
                            </h2>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">
                                Elige libremente qué masa y qué jigote usar para armar cada salteña.
                            </p>
                        </div>

                        <form action="{{ route('produccion.armado.store') }}" method="POST" class="space-y-4" x-data="armadoForm()">
                            @csrf
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                        FECHA <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-amber-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                        CANTIDAD ARMADA <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="number" step="any" min="1" name="cantidad_armada" x-model.number="cantidadArmada" @input="syncCantidades()" required placeholder="Ej. 80"
                                        @focus="$event.target.select()" @click="$event.target.select()"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-sm font-black text-amber-600 dark:text-amber-400 uppercase focus:border-amber-500 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    SALTEÑA QUE ESTÁS ARMANDO <span class="text-amber-500">*</span>
                                </label>
                                <select name="variante_id" x-model="selectedVarId" required
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-amber-500 focus:outline-none">
                                    <option value="">SELECCIONAR VARIANTE...</option>
                                    <template x-for="v in variantesList" :key="v.id">
                                        <option :value="v.id" x-text="`${v.nombre.toUpperCase()} (EN CENTRAL: ${v.stock_armadas} UDS.)`"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- SELECCIÓN LIBRE DE MASA / TAPAS -->
                            <div class="p-3 rounded-lg bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 space-y-2">
                                <label class="block text-[11px] font-black uppercase text-amber-700 dark:text-amber-400">
                                    🥟 1. MASA / TAPAS A UTILIZAR (ELECCIÓN LIBRE)
                                </label>
                                <div class="grid grid-cols-12 gap-2">
                                    <div class="col-span-8">
                                        <select name="tapa_preparacion_id" x-model="selectedTapaId"
                                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase">
                                            <option value="">NINGUNA / SIN DESCONTAR MASA</option>
                                            <template x-for="p in preparacionesList" :key="p.id">
                                                <option :value="p.id" x-text="`${p.nombre.toUpperCase()} (STOCK: ${p.stock_actual})`"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-span-4">
                                        <input type="number" step="any" min="0" name="tapas_usadas" x-model.number="tapasUsadas" placeholder="Uds."
                                            @focus="$event.target.select()" @click="$event.target.select()"
                                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase" title="Tapas usadas">
                                    </div>
                                </div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">
                                    Puedes elegir Tapas 50g, Tapas Dulces o cualquier masa que tengas en frío.
                                </span>
                            </div>

                            <!-- SELECCIÓN LIBRE DE JIGOTE (APROXIMADO) -->
                            <div class="p-3 rounded-lg bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 space-y-2">
                                <label class="block text-[11px] font-black uppercase text-rose-700 dark:text-rose-400">
                                    🥣 2. JIGOTE A UTILIZAR (APROXIMADO)
                                </label>
                                <div class="grid grid-cols-12 gap-2">
                                    <div class="col-span-8">
                                        <select name="jigote_preparacion_id" x-model="selectedJigoteId"
                                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase">
                                            <option value="">NINGUNO / SIN DESCONTAR JIGOTE</option>
                                            <template x-for="p in preparacionesList" :key="p.id">
                                                <option :value="p.id" x-text="`${p.nombre.toUpperCase()} (STOCK APROX: ${p.stock_actual})`"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-span-4">
                                        <input type="number" step="any" min="0" name="jigote_porciones_usadas" x-model.number="jigoteUsado" placeholder="Aprox."
                                            @focus="$event.target.select()" @click="$event.target.select()"
                                            class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase" title="Porciones de jigote usadas">
                                    </div>
                                </div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">
                                    El descuento de jigote es aproximado para adaptarse a la olla real.
                                </span>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">NOTAS / OBSERVACIONES (OPCIONAL)</label>
                                <input type="text" name="observaciones" placeholder="Ej. Masa salada con jigote suave"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-800 dark:text-white uppercase focus:border-amber-500 focus:outline-none">
                            </div>

                            <button type="submit"
                                class="w-full py-2.5 px-4 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cookie"></i> CONFIRMAR ARMADO & DESCONTAR PREPARACIONES
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Stock de Salteñas Armadas Listas en Central (Col 7) -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-boxes-packing text-amber-500"></i> SALTEÑAS ARMADAS EN CENTRAL (LISTAS)
                            </h2>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">PARA HORNO / DESPACHOS</span>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($variantes as $v)
                                <div class="p-4 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                    <div>
                                        <span class="font-black text-slate-900 dark:text-white text-sm uppercase flex items-center gap-2">
                                            🥟 {{ strtoupper($v->nombre) }}
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase block mt-0.5">
                                            Precio Venta: Bs. {{ number_format($v->precio_venta, 2) }}
                                        </span>
                                    </div>

                                    <div class="text-right">
                                        <span class="text-[9px] font-black text-slate-400 uppercase block">ARMADAS DISPONIBLES</span>
                                        <span class="text-lg font-black {{ $v->stock_armadas > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }} uppercase">
                                            {{ number_format((float)$v->stock_armadas, 0) }} UDS.
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-slate-400 font-bold uppercase text-xs">
                                    NO HAY VARIANTES REGISTRADAS.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ============================================================== -->
        <!-- PESTAÑA 3: HISTORIAL & AUDITORÍA DE PRODUCCIÓN                 -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'historial'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Historial de Tandas de Masa & Jigote -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                        <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-bowl-rice text-rose-500"></i> ÚLTIMAS TANDAS PRODUCIDAS
                        </h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-[9px] font-black text-slate-400 uppercase">
                                <tr>
                                    <th class="py-2 px-3">Fecha</th>
                                    <th class="py-2 px-3">Preparación</th>
                                    <th class="py-2 px-2 text-center">Tandas</th>
                                    <th class="py-2 px-3 text-right">Obtenido</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($historialTandas as $ht)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                        <td class="py-2.5 px-3 font-bold text-slate-500 uppercase">{{ $ht->fecha->format('d/m/Y') }}</td>
                                        <td class="py-2.5 px-3 font-black text-slate-900 dark:text-white uppercase">{{ $ht->preparacion->nombre ?? 'Masa' }}</td>
                                        <td class="py-2.5 px-2 text-center font-bold text-amber-600">{{ (float)$ht->tandas }}</td>
                                        <td class="py-2.5 px-3 text-right font-black text-rose-600 dark:text-rose-400">+{{ number_format((float)$ht->cantidad_producida, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-6 text-center text-slate-400 font-bold uppercase text-xs">No hay tandas registradas aún.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historial de Salteñas Armadas en Mesa -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                        <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-cookie text-amber-500"></i> ÚLTIMOS ARMADOS EN MESA
                        </h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-[9px] font-black text-slate-400 uppercase">
                                <tr>
                                    <th class="py-2 px-3">Fecha</th>
                                    <th class="py-2 px-3">Salteña Armada</th>
                                    <th class="py-2 px-3 text-right">Armadas</th>
                                    <th class="py-2 px-3">Masa & Jigote Usados</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($historialArmados as $ha)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                        <td class="py-2.5 px-3 font-bold text-slate-500 uppercase">{{ $ha->fecha->format('d/m/Y') }}</td>
                                        <td class="py-2.5 px-3 font-black text-slate-900 dark:text-white uppercase">{{ $ha->variante->nombre ?? 'Salteña' }}</td>
                                        <td class="py-2.5 px-3 text-right font-black text-emerald-600 dark:text-emerald-400">+{{ number_format((float)$ha->cantidad_armada, 0) }}</td>
                                        <td class="py-2.5 px-3 text-[10px] font-bold text-slate-400 uppercase">
                                            @if($ha->tapaPreparacion)
                                                <span>{{ $ha->tapaPreparacion->nombre }} ({{ (float)$ha->tapas_usadas }})</span>
                                            @endif
                                            @if($ha->jigotePreparacion)
                                                <span class="block text-rose-500">{{ $ha->jigotePreparacion->nombre }} (~{{ (float)$ha->jigote_porciones_usadas }})</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-6 text-center text-slate-400 font-bold uppercase text-xs">No hay armados registrados aún.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        function produccionManager(config) {
            return {
                activeTab: config.activeTab || 'tandas',
                preparacionesList: config.preparaciones || [],
                variantesList: config.variantes || [],
            };
        }

        function tandaForm() {
            return {
                tandas: 1,
                selectedPrepId: '',
                cantidadProducida: 100,
                currentReceta: [],
                onPrepChange() {
                    const prep = this.preparacionesList.find(p => p.id == this.selectedPrepId);
                    if (prep) {
                        this.currentReceta = prep.receta || [];
                        this.cantidadProducida = (parseFloat(prep.rinde_cantidad) || 100) * (parseFloat(this.tandas) || 1);
                    } else {
                        this.currentReceta = [];
                        this.cantidadProducida = 100;
                    }
                },
                esJigote() {
                    const prep = this.preparacionesList.find(p => p.id == this.selectedPrepId);
                    if (!prep) return false;
                    const name = prep.nombre.toLowerCase();
                    return name.includes('jigote') || name.includes('relleno') || (prep.tipo_preparacion === 'jigote');
                }
            };
        }

        function armadoForm() {
            return {
                cantidadArmada: 80,
                selectedVarId: '',
                selectedTapaId: '',
                tapasUsadas: 80,
                selectedJigoteId: '',
                jigoteUsado: 80,
                syncCantidades() {
                    const cant = parseFloat(this.cantidadArmada) || 0;
                    this.tapasUsadas = cant;
                    this.jigoteUsado = cant;
                }
            };
        }
    </script>
@endsection
