@extends('layouts.app')

@section('title', 'PRODUCTOS & COSTEO DINÁMICO — SALTEÑAS')

@section('content')
    <div class="space-y-6" x-data="productosManager({
        activeTab: '{{ $tab }}',
        insumos: {{ json_encode($insumosJson) }},
        preparaciones: {{ json_encode($preparacionesJson) }},
        variantes: {{ json_encode($variantesJson) }}
    })">

        <!-- Header Page & Título -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-coins text-amber-500"></i> PRODUCTOS, RECETAS & COSTEO
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase mt-0.5">
                    FASE 2: COSTEO DINÁMICO EN TIEMPO REAL CON EL ÚLTIMO PRECIO DE COMPRA
                </p>
            </div>
        </div>

        <!-- KPI Cards: Métricas de Costeo & Rentabilidad (Fase 2) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm">
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                    🪙 COSTO PROM. / SALTEÑA
                </span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    BS. {{ number_format($costoPromedio, 2) }}
                </span>
                <span class="text-[10px] font-bold text-slate-400 block mt-1 uppercase">
                    BASADO EN ÚLTIMAS COMPRAS
                </span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm">
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                    📈 MARGEN BRUTO PROM.
                </span>
                <span class="text-xl sm:text-2xl font-black {{ $margenPromedio >= 50 ? 'text-emerald-600 dark:text-emerald-400' : ($margenPromedio >= 30 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }} tracking-tight">
                    {{ number_format($margenPromedio, 1) }}%
                </span>
                <span class="text-[10px] font-bold text-slate-400 block mt-1 uppercase">
                    GANANCIA SOBRE P. VENTA
                </span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm">
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                    🏆 MÁS RENTABLE
                </span>
                <span class="text-sm sm:text-base font-black text-indigo-600 dark:text-indigo-400 tracking-tight truncate block">
                    {{ $varianteMasRentable ? strtoupper($varianteMasRentable->nombre) : 'SIN RECETA' }}
                </span>
                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 block mt-1 uppercase">
                    {{ $varianteMasRentable ? $varianteMasRentable->margenPorcentaje() . '% DE MARGEN' : 'DEFINIR RECETAS' }}
                </span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm">
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block mb-1">
                    🛒 INSUMOS COTIZADOS
                </span>
                <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">
                    {{ $insumosConPrecio }} / {{ $insumos->count() }}
                </span>
                <span class="text-[10px] font-bold text-slate-400 block mt-1 uppercase">
                    CON PRECIO DE COMPRA
                </span>
            </div>
        </div>

        <!-- Navegación por Pestañas (Minimalista Estilo Bootstrap) -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 overflow-x-auto">
            <button type="button" @click="activeTab = 'insumos'; history.replaceState(null, '', '?tab=insumos')"
                :class="activeTab === 'insumos' ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 font-bold'"
                class="px-4 py-2 rounded-lg text-xs uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>1. INSUMOS ({{ $insumos->count() }})</span>
            </button>

            <button type="button" @click="activeTab = 'preparaciones'; history.replaceState(null, '', '?tab=preparaciones')"
                :class="activeTab === 'preparaciones' ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 font-bold'"
                class="px-4 py-2 rounded-lg text-xs uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-bowl-rice"></i>
                <span>2. MASA & SUB-RECETAS ({{ $preparaciones->count() }})</span>
            </button>

            <button type="button" @click="activeTab = 'variantes'; history.replaceState(null, '', '?tab=variantes')"
                :class="activeTab === 'variantes' ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 font-bold'"
                class="px-4 py-2 rounded-lg text-xs uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-cookie"></i>
                <span>3. SALTEÑAS & RECETAS ({{ $variantes->count() }})</span>
            </button>

            <button type="button" @click="activeTab = 'costeo'; history.replaceState(null, '', '?tab=costeo')"
                :class="activeTab === 'costeo' ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 font-bold'"
                class="px-4 py-2 rounded-lg text-xs uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-chart-line"></i>
                <span>4. MATRIZ DE RENTABILIDAD</span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 1: INSUMOS / MATERIA PRIMA         -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'insumos'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Formulario Insumos (Col 5) -->
                <div class="lg:col-span-5">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                        <h2 class="text-xs font-black text-slate-900 dark:text-amber-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <i class="fa-solid fa-plus-circle text-amber-500"></i> NUEVO INSUMO / MATERIA PRIMA
                        </h2>

                        <form action="{{ route('insumos.store') }}" method="POST" class="space-y-4">
                            <input type="hidden" name="tab" value="insumos">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    NOMBRE DEL INSUMO <span class="text-amber-500">*</span>
                                </label>
                                <input type="text" name="nombre" required placeholder="EJ. HARINA DE TRIGO, MANTECA, POLLO"
                                    @focus="$event.target.select()" @click="$event.target.select()"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none uppercase">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    UNIDAD DE MEDIDA <span class="text-amber-500">*</span>
                                </label>
                                <select name="unidad_medida" required
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 focus:border-amber-500 focus:outline-none uppercase">
                                    <option value="">SELECCIONAR UNIDAD...</option>
                                    <optgroup label="⚖️ PESO">
                                        <option value="kg">KG — KILOGRAMO</option>
                                        <option value="g">G — GRAMO</option>
                                        <option value="quintal">QUINTAL (46 KG)</option>
                                        <option value="libra">LIBRA (500 G)</option>
                                    </optgroup>
                                    <optgroup label="🧪 VOLUMEN">
                                        <option value="litro">LITRO</option>
                                        <option value="ml">ML — MILILITRO</option>
                                    </optgroup>
                                    <optgroup label="📦 CANTIDAD">
                                        <option value="unidad">UNIDAD</option>
                                        <option value="docena">DOCENA (12 UDS.)</option>
                                        <option value="paquete">PAQUETE</option>
                                        <option value="lata">LATA</option>
                                        <option value="bolsa">BOLSA</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    STOCK INICIAL EN ALMACÉN (OPCIONAL)
                                </label>
                                <input type="number" step="any" min="0" name="stock_actual" placeholder="0.00"
                                    @focus="$event.target.select()" @click="$event.target.select()"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none uppercase">
                            </div>

                            <button type="submit"
                                class="w-full py-2.5 px-4 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-save"></i> GUARDAR INSUMO
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista Insumos (Col 7) -->
                <div class="lg:col-span-7" x-data="{ editingInsumoId: null }">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-boxes-stacked text-amber-500"></i> CATÁLOGO DE INSUMOS
                            </h2>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $insumos->count() }} REGISTRADOS</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                                        <th class="py-2.5 px-4">INSUMO</th>
                                        <th class="py-2.5 px-3 text-center">UNIDAD</th>
                                        <th class="py-2.5 px-3 text-right">ÚLTIMO PRECIO</th>
                                        <th class="py-2.5 px-3 text-right">STOCK ALMACÉN</th>
                                        <th class="py-2.5 px-4 text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($insumos as $ins)
                                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 {{ (float)$ins->stock_actual <= 0 ? 'opacity-75 bg-slate-50/40 dark:bg-slate-950/40' : '' }}">
                                            <td class="py-3 px-4 font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                                                @if((float)$ins->stock_actual > 0)
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Disponible en Almacén"></span>
                                                @else
                                                    <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0" title="Agotado / Sin Stock"></span>
                                                @endif
                                                <span>{{ $ins->nombre }}</span>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase">
                                                    {{ $ins->unidad_medida }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-right font-black">
                                                @if($ins->ultimo_precio > 0)
                                                    <span class="text-amber-600 dark:text-amber-400">
                                                        Bs. {{ number_format($ins->ultimo_precio, 2) }}
                                                    </span>
                                                @else
                                                    <span class="text-[10px] text-slate-400 italic">SIN COMPRA</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-right font-bold">
                                                @if((float)$ins->stock_actual > 0)
                                                    <span class="text-emerald-600 dark:text-emerald-400 font-black">
                                                        {{ number_format((float)$ins->stock_actual, 3) }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 font-bold text-[10px] uppercase">
                                                        0.000 (AGOTADO)
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <button type="button" @click="editingInsumoId = (editingInsumoId === {{ $ins->id }} ? null : {{ $ins->id }})"
                                                        class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-amber-500 transition-all text-xs">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <form action="{{ route('insumos.destroy', $ins->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar este insumo?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 rounded hover:bg-rose-50 dark:hover:bg-rose-950/40 text-slate-400 hover:text-rose-600 transition-all text-xs">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Fila de Edición de Insumo -->
                                        <tr x-show="editingInsumoId === {{ $ins->id }}" class="bg-amber-50/40 dark:bg-amber-950/20" x-cloak>
                                            <td colspan="5" class="p-4">
                                                <form action="{{ route('insumos.update', $ins->id) }}" method="POST" class="space-y-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="tab" value="insumos">
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                        <div>
                                                            <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">NOMBRE INSUMO</label>
                                                            <input type="text" name="nombre" value="{{ $ins->nombre }}" required
                                                                @focus="$event.target.select()" @click="$event.target.select()"
                                                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-amber-500 focus:outline-none">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">UNIDAD MEDIDA</label>
                                                            <select name="unidad_medida" required
                                                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-amber-600 dark:text-amber-400 uppercase focus:border-amber-500 focus:outline-none">
                                                                <option value="kg" {{ strtolower($ins->unidad_medida) === 'kg' ? 'selected' : '' }}>KG — KILOGRAMO</option>
                                                                <option value="g" {{ strtolower($ins->unidad_medida) === 'g' ? 'selected' : '' }}>G — GRAMO</option>
                                                                <option value="quintal" {{ strtolower($ins->unidad_medida) === 'quintal' ? 'selected' : '' }}>QUINTAL (46 KG)</option>
                                                                <option value="libra" {{ strtolower($ins->unidad_medida) === 'libra' ? 'selected' : '' }}>LIBRA (500 G)</option>
                                                                <option value="litro" {{ strtolower($ins->unidad_medida) === 'litro' ? 'selected' : '' }}>LITRO</option>
                                                                <option value="ml" {{ strtolower($ins->unidad_medida) === 'ml' ? 'selected' : '' }}>ML — MILILITRO</option>
                                                                <option value="unidad" {{ strtolower($ins->unidad_medida) === 'unidad' ? 'selected' : '' }}>UNIDAD</option>
                                                                <option value="docena" {{ strtolower($ins->unidad_medida) === 'docena' ? 'selected' : '' }}>DOCENA</option>
                                                                <option value="paquete" {{ strtolower($ins->unidad_medida) === 'paquete' ? 'selected' : '' }}>PAQUETE</option>
                                                                <option value="lata" {{ strtolower($ins->unidad_medida) === 'lata' ? 'selected' : '' }}>LATA</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">STOCK ALMACÉN</label>
                                                            <input type="number" step="any" min="0" name="stock_actual" value="{{ (float)$ins->stock_actual }}" required
                                                                @focus="$event.target.select()" @click="$event.target.select()"
                                                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-amber-500 focus:outline-none">
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" @click="editingInsumoId = null"
                                                            class="px-3 py-1.5 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase">
                                                            CANCELAR
                                                        </button>
                                                        <button type="submit"
                                                            class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black uppercase shadow-sm flex items-center gap-1.5">
                                                            <i class="fa-solid fa-save"></i> GUARDAR CAMBIOS
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-slate-400 font-bold uppercase">
                                                NO HAY INSUMOS REGISTRADOS AÚN.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 2: MASA / PREPARACIONES BASE       -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'preparaciones'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Formulario Preparaciones / Masa (Col 5) -->
                <div class="lg:col-span-5">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                        <h2 class="text-xs font-black text-slate-900 dark:text-rose-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <i class="fa-solid fa-bowl-rice text-rose-500"></i> NUEVA PREPARACIÓN (MASA / JIGOTE)
                        </h2>

                        @if($insumos->isEmpty())
                            <div class="p-4 rounded-xl bg-amber-50 dark:bg-rose-500/10 border border-amber-200 dark:border-rose-500/30 text-amber-800 dark:text-rose-400 text-xs font-bold space-y-2 uppercase">
                                <p>PRIMERO REGISTRA INSUMOS (EJ. HARINA, MANTECA, AGUA) EN LA PESTAÑA DE INSUMOS.</p>
                                <button type="button" @click="activeTab = 'insumos'"
                                    class="px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-black text-xs uppercase shadow">+ IR A INSUMOS</button>
                            </div>
                        @else
                            <form action="{{ route('preparaciones.store') }}" method="POST" class="space-y-4" x-data="preparacionForm()">
                                <input type="hidden" name="tab" value="preparaciones">
                                @csrf
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                            NOMBRE <span class="text-amber-500">*</span>
                                        </label>
                                        <input type="text" name="nombre" required placeholder="EJ. MASA DE SALTEÑA"
                                            @focus="$event.target.select()" @click="$event.target.select()"
                                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-rose-500 focus:outline-none uppercase">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                            RINDE (SALTEÑAS) <span class="text-amber-500">*</span>
                                        </label>
                                        <input type="number" step="any" min="1" name="rinde_cantidad" x-model.number="rinde" required placeholder="EJ. 100"
                                            @focus="$event.target.select()" @click="$event.target.select()"
                                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 focus:border-rose-500 focus:outline-none uppercase">
                                    </div>
                                </div>

                                <div class="space-y-3 bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-[11px] font-bold uppercase text-rose-600 dark:text-rose-400">
                                            INSUMOS DE ESTA TANDA
                                        </label>
                                        <button type="button" @click="addRow()"
                                            class="text-[10px] font-black text-rose-600 dark:text-rose-400 hover:underline uppercase flex items-center gap-1">
                                            <i class="fa-solid fa-plus-circle"></i> + AGREGAR INSUMO
                                        </button>
                                    </div>

                                    <template x-for="(row, idx) in rows" :key="idx">
                                        <div class="grid grid-cols-12 gap-2 items-center bg-white dark:bg-slate-900 p-2 rounded-lg border border-slate-200 dark:border-slate-800 shadow-sm">
                                            <div class="col-span-6">
                                                <select :name="`insumos[${idx}][insumo_id]`" x-model="row.insumo_id" @change="updateRow(idx)" required
                                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-rose-500 focus:outline-none">
                                                    <option value="" class="uppercase">SELECCIONAR INSUMO...</option>
                                                    <template x-for="ins in insumosList" :key="ins.id">
                                                        <option :value="ins.id" x-text="`${ins.nombre.toUpperCase()} (${ins.unidad_medida.toUpperCase()}) - BS. ${ins.ultimo_precio.toFixed(2)}`"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="col-span-5 flex items-center gap-1">
                                                <input type="number" step="any" min="0.0001" :name="`insumos[${idx}][cantidad_usada]`"
                                                    x-model.number="row.cantidad_usada" required placeholder="CANT."
                                                    @focus="$event.target.select()" @click="$event.target.select()"
                                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white uppercase focus:border-rose-500 focus:outline-none">
                                                <span x-show="row.unidad" class="text-[9px] font-black px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 uppercase shrink-0"
                                                    x-text="row.unidad"></span>
                                            </div>
                                            <div class="col-span-1 text-right">
                                                <button type="button" @click="removeRow(idx)" x-show="rows.length > 1"
                                                    class="text-slate-400 hover:text-rose-500 text-xs p-1">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Tarjeta de Costeo Dinámico en Vivo de la Tanda -->
                                <div class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="text-[10px] font-black text-rose-700 dark:text-rose-400 uppercase block">
                                            COSTO TOTAL LOTE:
                                        </span>
                                        <span class="text-sm font-black text-slate-900 dark:text-white" x-text="`BS. ${calcularTotalLote().toFixed(2)}`"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] font-black text-rose-700 dark:text-rose-400 uppercase block">
                                            COSTO POR SALTEÑA (TAPA):
                                        </span>
                                        <span class="text-base font-black text-rose-600 dark:text-rose-400" x-text="`BS. ${calcularCostoUnitario().toFixed(3)}`"></span>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="w-full py-2.5 px-4 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-save"></i> GUARDAR PREPARACIÓN (MASA)
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Lista Preparaciones Definidas con Costeo Dinámico (Col 7) -->
                <div class="lg:col-span-7" x-data="{ expandedPrep: null }">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-list text-rose-500"></i> PREPARACIONES & COSTO POR TAPA
                            </h2>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $preparaciones->count() }} DEFINIDAS</span>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($preparaciones as $prep)
                                @php
                                    $costoUnitario = $prep->costoEnFecha();
                                    $costoLote = $prep->costoTotalLote();
                                    $desglose = $prep->desgloseCostos();
                                @endphp
                                <div class="p-4 space-y-3 hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-black text-slate-900 dark:text-white text-sm flex items-center gap-2 uppercase">
                                                🥣 {{ strtoupper($prep->nombre) }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">
                                                RINDE {{ $prep->rinde_cantidad }} UNIDADES • LOTE TOTAL: BS. {{ number_format($costoLote, 2) }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <div class="text-right">
                                                <span class="text-[10px] font-black text-slate-400 block uppercase">COSTO UNITARIO</span>
                                                <span class="text-sm font-black text-rose-600 dark:text-rose-400 uppercase">
                                                    BS. {{ number_format($costoUnitario, 3) }}
                                                </span>
                                            </div>

                                            <button type="button" @click="expandedPrep = (expandedPrep === {{ $prep->id }} ? null : {{ $prep->id }})"
                                                class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-[10px] font-black uppercase transition-all flex items-center gap-1">
                                                <i class="fa-solid" :class="expandedPrep === {{ $prep->id }} ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                                <span>RECETA</span>
                                            </button>

                                            <form action="{{ route('preparaciones.destroy', $prep->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta preparación? Las recetas que la usen quedarán sin masa.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded text-slate-400 hover:text-rose-600 transition-all text-xs" title="Eliminar Preparación">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Desglose de Insumos y Costos Desplegable -->
                                    <div x-show="expandedPrep === {{ $prep->id }}" x-cloak
                                        class="p-3 bg-slate-50 dark:bg-slate-950/80 rounded-lg border border-slate-200 dark:border-slate-800 text-xs space-y-2">
                                        <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block">
                                            DESGLOSE DE COSTOS DEL LOTE (CON ÚLTIMO PRECIO DE COMPRA):
                                        </span>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left text-[11px]">
                                                <thead>
                                                    <tr class="text-slate-400 uppercase border-b border-slate-200 dark:border-slate-800 text-[9px] font-black">
                                                        <th class="py-1">INSUMO</th>
                                                        <th class="py-1 text-right">CANT. LOTE</th>
                                                        <th class="py-1 text-right">ÚLTIMO PRECIO</th>
                                                        <th class="py-1 text-right">SUBTOTAL</th>
                                                        <th class="py-1 text-right">APORTE / TAPA</th>
                                                        <th class="py-1 text-right">%</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-200/50 dark:divide-slate-800/50">
                                                    @foreach($desglose as $item)
                                                        <tr>
                                                            <td class="py-1.5 font-bold text-slate-800 dark:text-slate-200 uppercase">
                                                                {{ $item['nombre'] }}
                                                            </td>
                                                            <td class="py-1.5 text-right font-medium">
                                                                {{ $item['cantidad_lote'] }} {{ strtoupper($item['unidad']) }}
                                                            </td>
                                                            <td class="py-1.5 text-right font-medium">
                                                                Bs. {{ number_format($item['precio_compra'], 2) }}
                                                            </td>
                                                            <td class="py-1.5 text-right font-black text-slate-900 dark:text-white">
                                                                Bs. {{ number_format($item['subtotal_lote'], 2) }}
                                                            </td>
                                                            <td class="py-1.5 text-right font-black text-rose-600 dark:text-rose-400">
                                                                Bs. {{ number_format($item['costo_por_saltena'], 3) }}
                                                            </td>
                                                            <td class="py-1.5 text-right font-bold text-slate-400">
                                                                {{ $item['porcentaje_lote'] }}%
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-slate-400 font-bold uppercase text-xs">
                                    NO HAY PREPARACIONES (MASAS) CREADAS AÚN.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 3: VARIANTES DE SALTEÑA & RECETAS  -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'variantes'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Formulario Dinámico de Variante (Col 5) -->
                <div class="lg:col-span-5">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                        <h2 class="text-xs font-black text-slate-900 dark:text-amber-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <i class="fa-solid fa-cookie text-amber-500"></i> NUEVA VARIANTE DE SALTEÑA
                        </h2>

                        <div id="debugVarianteAlertBox" class="hidden mb-3"></div>

                        <form id="formCrearVariante" action="{{ route('variantes.store') }}" method="POST" class="space-y-4" x-data="varianteForm()">
                            <input type="hidden" name="tab" value="variantes">
                            @csrf
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                        NOMBRE SALTEÑA <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="text" name="nombre" x-model="nombre" required placeholder="EJ. SALTEÑA DE CARNE"
                                        @focus="$event.target.select()" @click="$event.target.select()"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none uppercase">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                        PRECIO VENTA (BS.) <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="number" step="any" min="0.5" name="precio_venta" x-model.number="precioVenta" required placeholder="EJ. 8.00"
                                        @focus="$event.target.select()" @click="$event.target.select()"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 focus:border-amber-500 focus:outline-none uppercase">
                                </div>
                            </div>

                            <!-- Constructor Dinámico de Componentes de Receta -->
                            <div class="space-y-3 bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200 dark:border-slate-800">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-bold uppercase text-amber-600 dark:text-amber-400">
                                        COMPONENTES DE RECETA (MASA + INSUMOS)
                                    </label>
                                    <button type="button" @click="addComponente()"
                                        class="text-[10px] font-black text-amber-600 dark:text-amber-400 hover:underline uppercase flex items-center gap-1">
                                        <i class="fa-solid fa-plus-circle"></i> + AGREGAR COMPONENTE
                                    </button>
                                </div>

                                <template x-for="(comp, idx) in componentes" :key="idx">
                                    <div class="p-2.5 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 space-y-2 shadow-sm">
                                        <div class="grid grid-cols-12 gap-2 items-center">
                                            <!-- Tipo: Preparación vs Insumo -->
                                            <div class="col-span-4">
                                                <select :name="`componentes[${idx}][tipo_componente]`" x-model="comp.tipo_componente" @change="onTipoChange(idx)"
                                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-[11px] font-bold text-slate-800 dark:text-slate-200 uppercase">
                                                    <option value="preparacion">🥣 MASA/BASE</option>
                                                    <option value="insumo">📦 INSUMO DIRECTO</option>
                                                </select>
                                            </div>

                                            <!-- Selector de Elemento -->
                                            <div class="col-span-8">
                                                <!-- Si es preparación -->
                                                <template x-if="comp.tipo_componente === 'preparacion'">
                                                    <select :name="`componentes[${idx}][preparacion_id]`" x-model="comp.preparacion_id" @change="updateCompCost(idx)"
                                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-[11px] font-bold text-slate-900 dark:text-white uppercase">
                                                        <option value="">SELECCIONAR MASA...</option>
                                                        <template x-for="p in preparacionesList" :key="p.id">
                                                            <option :value="p.id" x-text="`${p.nombre.toUpperCase()} (COSTO: BS. ${p.costo_unitario.toFixed(3)})`"></option>
                                                        </template>
                                                    </select>
                                                </template>

                                                <!-- Si es insumo -->
                                                <template x-if="comp.tipo_componente === 'insumo'">
                                                    <select :name="`componentes[${idx}][insumo_id]`" x-model="comp.insumo_id" @change="updateCompCost(idx)"
                                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-[11px] font-bold text-slate-900 dark:text-white uppercase">
                                                        <option value="">SELECCIONAR INSUMO...</option>
                                                        <template x-for="ins in insumosList" :key="ins.id">
                                                            <option :value="ins.id" x-text="`${ins.nombre.toUpperCase()} (BS. ${ins.ultimo_precio.toFixed(2)}/${ins.unidad_medida})`"></option>
                                                        </template>
                                                    </select>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Cantidad y Costo -->
                                        <div class="grid grid-cols-12 gap-2 items-center pt-1 border-t border-slate-100 dark:border-slate-800">
                                            <div class="col-span-6 flex items-center gap-1.5">
                                                <label class="text-[9px] font-black text-slate-400 uppercase">CANTIDAD:</label>
                                                <input type="number" step="any" min="0.0001" :name="`componentes[${idx}][cantidad_usada]`"
                                                    x-model.number="comp.cantidad_usada" @input="updateCompCost(idx)" placeholder="1"
                                                    @focus="$event.target.select()" @click="$event.target.select()"
                                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-bold text-slate-900 dark:text-white uppercase">
                                            </div>
                                            <div class="col-span-5 text-right">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">SUBTOTAL:</span>
                                                <span class="text-xs font-black text-amber-600 dark:text-amber-400 uppercase ml-1"
                                                    x-text="`BS. ${(comp.subtotal || 0).toFixed(3)}`"></span>
                                            </div>
                                            <div class="col-span-1 text-right">
                                                <button type="button" @click="removeComponente(idx)" x-show="componentes.length > 1"
                                                    class="text-slate-400 hover:text-rose-500 text-xs">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Tarjeta de Rentabilidad en Vivo de la Salteña -->
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">PRECIO VENTA AL PÚBLICO:</span>
                                    <span class="font-black text-slate-900 dark:text-white" x-text="`BS. ${(precioVenta || 0).toFixed(2)}`"></span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-[10px] font-bold text-rose-500 uppercase">COSTO ESTIMADO PRODUCCIÓN:</span>
                                    <span class="font-black text-rose-600 dark:text-rose-400" x-text="`BS. ${calcularCostoTotal().toFixed(2)}`"></span>
                                </div>
                                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center">
                                    <div>
                                        <span class="text-[10px] font-black text-slate-400 uppercase block">MARGEN BRUTO</span>
                                        <span class="text-sm font-black text-slate-900 dark:text-white" x-text="`BS. ${calcularMargenBruto().toFixed(2)}`"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] font-black text-slate-400 uppercase block">RENTABILIDAD</span>
                                        <span class="text-sm font-black px-2 py-0.5 rounded uppercase"
                                            :class="calcularMargenPorcentaje() >= 50 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300' : (calcularMargenPorcentaje() >= 30 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300')"
                                            x-text="`${calcularMargenPorcentaje().toFixed(1)}%`">
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full py-2.5 px-4 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-save"></i> GUARDAR VARIANTE DE SALTEÑA
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista Variantes Definidas con Semáforo de Rentabilidad (Col 7) -->
                <div class="lg:col-span-7" x-data="{ expandedVar: null, editingVarId: null }">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-cookie-bite text-amber-500"></i> VARIANTES & ANÁLISIS DE COSTEO
                            </h2>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $variantes->count() }} REGISTRADAS</span>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($variantes as $var)
                                @php
                                    $costoProd = $var->costo_produccion;
                                    $margen = $var->margenBruto();
                                    $porcMargen = $var->margenPorcentaje();
                                    $estado = $var->estadoMargen();
                                    $desglose = $var->desgloseCostos();
                                @endphp
                                <div class="p-4 space-y-3 hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-black text-slate-900 dark:text-white text-sm uppercase">
                                                    🥟 {{ strtoupper($var->nombre) }}
                                                </span>
                                                @if(!$var->activo)
                                                    <span class="text-[9px] px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold uppercase">INACTIVO</span>
                                                @endif
                                            </div>
                                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 block mt-0.5">
                                                PRECIO VENTA: <span class="text-amber-600 dark:text-amber-400">BS. {{ number_format($var->precio_venta, 2) }}</span>
                                            </span>
                                        </div>

                                        <!-- Indicadores de Costo y Margen -->
                                        <div class="flex items-center gap-3">
                                            <div class="text-right">
                                                <span class="text-[9px] font-black text-slate-400 block uppercase">COSTO PRODUCCIÓN</span>
                                                <span class="text-xs font-black text-rose-600 dark:text-rose-400 uppercase">
                                                    BS. {{ number_format($costoProd, 2) }}
                                                </span>
                                            </div>

                                            <div class="text-right">
                                                <span class="text-[9px] font-black text-slate-400 block uppercase">MARGEN NETO</span>
                                                <span class="text-xs font-black px-2 py-0.5 rounded uppercase inline-block {{ $estado === 'excelente' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : ($estado === 'aceptable' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : ($estado === 'sin_receta' ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300')) }}">
                                                    Bs. {{ number_format($margen, 2) }} ({{ $porcMargen }}%)
                                                </span>
                                            </div>

                                            <button type="button" @click="expandedVar = (expandedVar === {{ $var->id }} ? null : {{ $var->id }})"
                                                class="px-2 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-[10px] font-black uppercase transition-all flex items-center gap-1">
                                                <i class="fa-solid" :class="expandedVar === {{ $var->id }} ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                                <span>COSTOS</span>
                                            </button>

                                            <!-- Toggle Activo / Inactivo -->
                                            <form action="{{ route('variantes.toggle', $var->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded text-slate-400 hover:text-amber-500 transition-all text-xs" title="Activar/Desactivar">
                                                    <i class="fa-solid {{ $var->activo ? 'fa-eye text-emerald-500' : 'fa-eye-slash text-slate-400' }}"></i>
                                                </button>
                                            </form>

                                            <!-- Eliminar -->
                                            <form action="{{ route('variantes.destroy', $var->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta variante de salteña?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded text-slate-400 hover:text-rose-600 transition-all text-xs" title="Eliminar Variante">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Desglose Desplegable de Costos por Ingrediente -->
                                    <div x-show="expandedVar === {{ $var->id }}" x-cloak
                                        class="p-3 bg-slate-50 dark:bg-slate-950/80 rounded-lg border border-slate-200 dark:border-slate-800 text-xs space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                                DESGLOSE DE INGREDIENTES & APORTE AL COSTO:
                                            </span>
                                            <span class="text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase">
                                                TOTAL: BS. {{ number_format($costoProd, 2) }} / SALTEÑA
                                            </span>
                                        </div>

                                        @if(empty($desglose))
                                            <span class="text-[11px] text-slate-400 italic uppercase block">
                                                ESTA VARIANTE NO TIENE COMPONENTES ASIGNADOS EN SU RECETA.
                                            </span>
                                        @else
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-left text-[11px]">
                                                    <thead>
                                                        <tr class="text-slate-400 uppercase border-b border-slate-200 dark:border-slate-800 text-[9px] font-black">
                                                            <th class="py-1">COMPONENTE</th>
                                                            <th class="py-1">TIPO</th>
                                                            <th class="py-1 text-right">CANTIDAD</th>
                                                            <th class="py-1 text-right">PRECIO BASE</th>
                                                            <th class="py-1 text-right">APORTE A 1 SALTEÑA</th>
                                                            <th class="py-1 text-right">% COSTO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-200/50 dark:divide-slate-800/50">
                                                        @foreach($desglose as $item)
                                                            <tr>
                                                                <td class="py-1.5 font-bold text-slate-800 dark:text-slate-200 uppercase">
                                                                    {{ $item['nombre'] }}
                                                                </td>
                                                                <td class="py-1.5">
                                                                    <span class="text-[9px] font-black px-1.5 py-0.5 rounded uppercase {{ $item['tipo'] === 'preparacion' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' }}">
                                                                        {{ $item['tipo'] === 'preparacion' ? 'MASA' : 'INSUMO' }}
                                                                    </span>
                                                                </td>
                                                                <td class="py-1.5 text-right font-medium">
                                                                    {{ $item['cantidad'] }} {{ strtoupper($item['unidad']) }}
                                                                </td>
                                                                <td class="py-1.5 text-right font-medium">
                                                                    Bs. {{ number_format($item['precio_unitario'], 2) }}
                                                                </td>
                                                                <td class="py-1.5 text-right font-black text-rose-600 dark:text-rose-400">
                                                                    Bs. {{ number_format($item['costo_aporte'], 3) }}
                                                                </td>
                                                                <td class="py-1.5 text-right font-bold text-slate-400">
                                                                    {{ $item['porcentaje'] }}%
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-slate-400 font-bold uppercase text-xs">
                                    NO HAY VARIANTES DE SALTEÑA DEFINIDAS AÚN.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 4: MATRIZ DE RENTABILIDAD & COSTOS -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'costeo'" class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                            <i class="fa-solid fa-scale-balanced text-amber-500"></i> MATRIZ GENERAL DE COSTOS & MÁRGENES
                        </h2>
                        <p class="text-xs text-slate-400 uppercase mt-0.5">
                            ANÁLISIS COMPARATIVO DE PRECIO DE VENTA VS COSTO DE INSUMOS
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                                <th class="py-3 px-4">VARIANTE DE SALTEÑA</th>
                                <th class="py-3 px-3 text-right">PRECIO VENTA</th>
                                <th class="py-3 px-3 text-right">COSTO PRODUCCIÓN</th>
                                <th class="py-3 px-3 text-right">GANANCIA NETA</th>
                                <th class="py-3 px-3 text-right">% MARGEN</th>
                                <th class="py-3 px-4 text-center">SEMÁFORO / ESTADO</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($variantes as $v)
                                @php
                                    $costo = $v->costo_produccion;
                                    $ganancia = $v->margenBruto();
                                    $porcentaje = $v->margenPorcentaje();
                                    $estado = $v->estadoMargen();
                                @endphp
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                                    <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                                        <span>🥟 {{ strtoupper($v->nombre) }}</span>
                                        @if(!$v->activo)
                                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold uppercase">INACTIVO</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-black text-amber-600 dark:text-amber-400">
                                        Bs. {{ number_format($v->precio_venta, 2) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-black text-rose-600 dark:text-rose-400">
                                        Bs. {{ number_format($costo, 2) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-black text-emerald-600 dark:text-emerald-400">
                                        Bs. {{ number_format($ganancia, 2) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-black text-slate-900 dark:text-white">
                                        {{ $porcentaje }}%
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($estado === 'excelente')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                🟢 ALTA RENTABILIDAD (>50%)
                                            </span>
                                        @elseif($estado === 'aceptable')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                🟡 RENTABILIDAD MEDIA (30-50%)
                                            </span>
                                        @elseif($estado === 'sin_receta')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                                ⚪ SIN RECETA ASIGNADA
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 animate-pulse">
                                                🔴 MARGEN BAJO O PÉRDIDA
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 font-bold uppercase">
                                        NO HAY SALTEÑAS REGISTRADAS PARA MOSTRAR LA MATRIZ DE RENTABILIDAD.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        function productosManager(config) {
            return {
                activeTab: config.activeTab || 'insumos',
                insumosList: config.insumos || [],
                preparacionesList: config.preparaciones || [],
                variantesList: config.variantes || [],
            };
        }

        function preparacionForm() {
            return {
                rinde: 100,
                rows: [
                    { insumo_id: '', cantidad_usada: 1, unidad: '', ultimo_precio: 0 }
                ],
                addRow() {
                    this.rows.push({ insumo_id: '', cantidad_usada: 1, unidad: '', ultimo_precio: 0 });
                },
                removeRow(idx) {
                    if (this.rows.length > 1) {
                        this.rows.splice(idx, 1);
                    }
                },
                updateRow(idx) {
                    const row = this.rows[idx];
                    const ins = this.insumosList.find(i => i.id == row.insumo_id);
                    if (ins) {
                        row.unidad = ins.unidad_medida.toUpperCase();
                        row.ultimo_precio = ins.ultimo_precio || 0;
                    } else {
                        row.unidad = '';
                        row.ultimo_precio = 0;
                    }
                },
                calcularTotalLote() {
                    let total = 0;
                    this.rows.forEach(r => {
                        const cant = parseFloat(r.cantidad_usada) || 0;
                        const precio = parseFloat(r.ultimo_precio) || 0;
                        total += (cant * precio);
                    });
                    return total;
                },
                calcularCostoUnitario() {
                    const r = parseFloat(this.rinde) || 1;
                    return r > 0 ? (this.calcularTotalLote() / r) : 0;
                }
            };
        }

        function varianteForm() {
            return {
                nombre: '',
                precioVenta: 8.00,
                componentes: [
                    { tipo_componente: 'preparacion', preparacion_id: '', insumo_id: '', cantidad_usada: 1, subtotal: 0 },
                    { tipo_componente: 'insumo', preparacion_id: '', insumo_id: '', cantidad_usada: 1, subtotal: 0 }
                ],
                addComponente() {
                    this.componentes.push({
                        tipo_componente: 'insumo',
                        preparacion_id: '',
                        insumo_id: '',
                        cantidad_usada: 1,
                        subtotal: 0
                    });
                },
                removeComponente(idx) {
                    if (this.componentes.length > 1) {
                        this.componentes.splice(idx, 1);
                    }
                },
                onTipoChange(idx) {
                    this.componentes[idx].preparacion_id = '';
                    this.componentes[idx].insumo_id = '';
                    this.componentes[idx].subtotal = 0;
                },
                updateCompCost(idx) {
                    const comp = this.componentes[idx];
                    const cant = parseFloat(comp.cantidad_usada) || 0;
                    let unitCost = 0;

                    if (comp.tipo_componente === 'preparacion') {
                        const prep = this.preparacionesList.find(p => p.id == comp.preparacion_id);
                        if (prep) {
                            unitCost = parseFloat(prep.costo_unitario) || 0;
                        }
                    } else if (comp.tipo_componente === 'insumo') {
                        const ins = this.insumosList.find(i => i.id == comp.insumo_id);
                        if (ins) {
                            unitCost = parseFloat(ins.ultimo_precio) || 0;
                        }
                    }

                    comp.subtotal = cant * unitCost;
                },
                calcularCostoTotal() {
                    let total = 0;
                    this.componentes.forEach(c => {
                        total += (parseFloat(c.subtotal) || 0);
                    });
                    return total;
                },
                calcularMargenBruto() {
                    const pv = parseFloat(this.precioVenta) || 0;
                    return pv - this.calcularCostoTotal();
                },
                calcularMargenPorcentaje() {
                    const pv = parseFloat(this.precioVenta) || 0;
                    if (pv <= 0) return 0;
                    return (this.calcularMargenBruto() / pv) * 100;
                }
            };
        }

        // Depurador interactivo de consola para el registro de variantes
        (function () {
            console.log('%c🥟 [FASE 2: COSTEO DINÁMICO] Módulo de Recetas y Costeo Activo', 'background: #f59e0b; color: #020617; font-weight: bold; font-size: 12px; padding: 4px 8px; border-radius: 4px;');

            const formCrear = document.getElementById('formCrearVariante');
            const alertBox = document.getElementById('debugVarianteAlertBox');

            function mostrarAlerta(tipo, mensaje) {
                if (!alertBox) return;
                alertBox.classList.remove('hidden');
                if (tipo === 'error') {
                    alertBox.className = 'p-3 rounded-lg text-xs font-bold uppercase border bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800 space-y-1 mb-3';
                    alertBox.innerHTML = '<div class="flex items-center gap-2"><i class="fa-solid fa-circle-exclamation text-base"></i> <span>' + mensaje + '</span></div>';
                } else {
                    alertBox.className = 'p-3 rounded-lg text-xs font-bold uppercase border bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800 flex items-center gap-2 mb-3';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-check text-base"></i> <span>' + mensaje + '</span>';
                }
            }

            if (formCrear) {
                formCrear.addEventListener('submit', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const submitBtn = formCrear.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> GUARDANDO Y CALCULANDO COSTO...';
                    }

                    console.group('%c🚀 [DEPURADOR VARIANTE] Envío de Receta', 'color: #d97706; font-size: 13px; font-weight: bold;');
                    const formData = new FormData(formCrear);

                    fetch(formCrear.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(async response => {
                        let data = null;
                        try {
                            data = await response.json();
                        } catch (err) {}

                        if (response.ok && data && data.success) {
                            console.log('✅ Variante guardada correctamente:', data);
                            console.groupEnd();
                            mostrarAlerta('success', data.message || 'Variante de Salteña guardada correctamente.');
                            setTimeout(function () {
                                window.location.href = '?tab=variantes';
                            }, 800);
                        } else {
                            let mensajeError = (data && data.error) ? data.error : 'Error al guardar la variante.';
                            console.error('❌ Error al guardar:', mensajeError);
                            console.groupEnd();
                            mostrarAlerta('error', mensajeError);
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> REINTENTAR GUARDAR';
                            }
                        }
                    })
                    .catch(err => {
                        console.error('❌ Error de red:', err);
                        console.groupEnd();
                        mostrarAlerta('error', 'Error de conexión: ' + err.message);
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> REINTENTAR GUARDAR';
                        }
                    });
                });
            }
        })();
    </script>
@endsection
