@extends('layouts.app')

@section('title', 'PRODUCTOS & RECETAS — SALTEÑAS')

@section('content')
    <div class="space-y-6" x-data="{ activeTab: '{{ $tab }}' }">

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
                <span>2. MASA / PREPARACIONES ({{ $preparaciones->count() }})</span>
            </button>

            <button type="button" @click="activeTab = 'variantes'; history.replaceState(null, '', '?tab=variantes')"
                :class="activeTab === 'variantes' ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 font-bold'"
                class="px-4 py-2 rounded-lg text-xs uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-cookie"></i>
                <span>3. VARIANTES DE SALTEÑA ({{ $variantes->count() }})</span>
            </button>
        </div>

        <!-- PESTAÑA 1: INSUMOS / MATERIA PRIMA -->
        <div x-show="activeTab === 'insumos'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Formulario Insumos (Col 5) -->
                <div class="lg:col-span-5">
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                        <h2
                            class="text-xs font-black text-slate-900 dark:text-amber-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <i class="fa-solid fa-plus-circle text-amber-500"></i> NUEVO INSUMO / MATERIA PRIMA
                        </h2>

                        <form action="{{ route('insumos.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">NOMBRE
                                    DEL INSUMO <span class="text-amber-500">*</span></label>
                                <input type="text" name="nombre" required placeholder="EJ. HARINA DE TRIGO, HARINA, POLLO"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none uppercase">
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">UNIDAD
                                    DE MEDIDA <span class="text-amber-500">*</span></label>
                                <select name="unidad_medida" required
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 focus:border-amber-500 focus:outline-none uppercase">
                                    <option value="">SELECCIONAR UNIDAD...</option>
                                    <optgroup label="── PESO ──">
                                        <option value="kg">KG — KILOGRAMO</option>
                                        <option value="g">G — GRAMO</option>
                                        <option value="quintal">QUINTAL (46 KG)</option>
                                        <option value="libra">LIBRA (500 G)</option>
                                    </optgroup>
                                    <optgroup label="── VOLUMEN ──">
                                        <option value="litro">LITRO</option>
                                        <option value="ml">ML — MILILITRO</option>
                                        <option value="taza">TAZA (250 ML)</option>
                                        <option value="cucharada">CUCHARADA (15 ML)</option>
                                        <option value="cucharadita">CUCHARADITA (5 ML)</option>
                                    </optgroup>
                                    <optgroup label="── CANTIDAD ──">
                                        <option value="unidad">UNIDAD</option>
                                        <option value="docena">DOCENA (12 UDS.)</option>
                                        <option value="paquete">PAQUETE</option>
                                        <option value="caja">CAJA</option>
                                        <option value="bolsa">BOLSA</option>
                                        <option value="lata">LATA</option>
                                        <option value="botella">BOTELLA</option>
                                        <option value="porcion">PORCIÓN</option>
                                    </optgroup>
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full py-2 px-4 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-save"></i> GUARDAR INSUMO
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista Insumos (Col 7) -->
                <div class="lg:col-span-7">
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                            <h2
                                class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-boxes-stacked text-amber-500"></i> CATÁLOGO DE INSUMOS
                            </h2>
                        </div>

                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr
                                    class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                                    <th class="py-2.5 px-4">NOMBRE</th>
                                    <th class="py-2.5 px-4">UNIDAD MEDIDA</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @forelse($insumos as $ins)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                        <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white uppercase">📦
                                            {{ strtoupper($ins->nombre) }}
                                        </td>
                                        <td class="py-2.5 px-4 font-bold text-amber-600 dark:text-amber-400 uppercase">
                                            {{ strtoupper($ins->unidad_medida) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-8 text-center text-slate-400 font-bold uppercase">NO HAY
                                            INSUMOS REGISTRADOS AÚN.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 2: MASA / PREPARACIONES -->
        <div x-show="activeTab === 'preparaciones'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Formulario Preparaciones (Col 5) -->
                <div class="lg:col-span-5">
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                        <h2
                            class="text-xs font-black text-slate-900 dark:text-rose-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <i class="fa-solid fa-bowl-rice text-rose-500"></i> NUEVA PREPARACIÓN / MASA
                        </h2>

                        @if($insumos->isEmpty())
                            <div
                                class="p-4 rounded-xl bg-amber-50 dark:bg-rose-500/10 border border-amber-200 dark:border-rose-500/30 text-amber-800 dark:text-rose-400 text-xs font-bold space-y-2 uppercase">
                                <p>PRIMERO DEBES REGISTRAR INSUMOS (EJ. HARINA, MANTECA, AGUA) EN LA PESTAÑA DE INSUMOS.</p>
                                <button type="button" @click="activeTab = 'insumos'"
                                    class="px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-black text-xs uppercase shadow">+
                                    IR A INSUMOS</button>
                            </div>
                        @else
                            <form action="{{ route('preparaciones.store') }}" method="POST" class="space-y-4" x-data="{
                                        rows: [{ insumo_id: '', cantidad_usada: '', unidad: '' }],
                                        insumosMap: {{ json_encode($insumos->keyBy('id')->toArray()) }},
                                        updateUnidad(idx) {
                                            const id = this.rows[idx].insumo_id;
                                            this.rows[idx].unidad = (id && this.insumosMap[id]) ? this.insumosMap[id].unidad_medida.toUpperCase() : '';
                                        },
                                        addRow() {
                                            this.rows.push({ insumo_id: '', cantidad_usada: '', unidad: '' });
                                        },
                                        removeRow(idx) {
                                            if (this.rows.length > 1) {
                                                this.rows.splice(idx, 1);
                                            }
                                        }
                                    }">
                                @csrf
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">NOMBRE
                                            <span class="text-amber-500">*</span></label>
                                        <input type="text" name="nombre" required placeholder="EJ. MASA DE SALTEÑA"
                                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-rose-500 focus:outline-none uppercase">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">RINDE
                                            (CANT. SALTEÑAS) <span class="text-amber-500">*</span></label>
                                        <input type="number" step="any" name="rinde_cantidad" required placeholder="EJ. 100"
                                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 focus:border-rose-500 focus:outline-none uppercase">
                                    </div>
                                </div>

                                <div
                                    class="space-y-3 bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center justify-between">
                                        <label
                                            class="block text-[11px] font-bold uppercase text-rose-600 dark:text-rose-400">INSUMOS
                                            PARA ESTA MASA</label>
                                        <button type="button" @click="addRow()"
                                            class="text-[10px] font-black text-rose-600 dark:text-rose-400 hover:underline uppercase flex items-center gap-1">
                                            <i class="fa-solid fa-plus-circle"></i> + AGREGAR OTRO INSUMO
                                        </button>
                                    </div>

                                    <template x-for="(row, idx) in rows" :key="idx">
                                        <div
                                            class="grid grid-cols-12 gap-2 items-center bg-white dark:bg-slate-900 p-2 rounded-lg border border-slate-200 dark:border-slate-800 shadow-sm">
                                            <div class="col-span-6">
                                                <select :name="`insumos[${idx}][insumo_id]`" x-model="row.insumo_id"
                                                    @change="updateUnidad(idx)" required
                                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-rose-500 focus:outline-none uppercase">
                                                    <option value="" class="uppercase">SELECCIONAR INSUMO...</option>
                                                    @foreach($insumos as $ins)
                                                        <option value="{{ $ins->id }}" class="uppercase">
                                                            {{ strtoupper($ins->nombre) }} ({{ strtoupper($ins->unidad_medida) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-span-5 flex items-center gap-1">
                                                <input type="number" step="any" :name="`insumos[${idx}][cantidad_usada]`"
                                                    x-model="row.cantidad_usada" required placeholder="CANT."
                                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-rose-500 focus:outline-none uppercase">
                                                <span x-show="row.unidad"
                                                    class="text-[9px] font-black px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 uppercase shrink-0"
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

                                <button type="submit"
                                    class="w-full py-2 px-4 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-save"></i> GUARDAR PREPARACIÓN (MASA)
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Lista Preparaciones (Col 7) -->
                <div class="lg:col-span-7">
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                            <h2
                                class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-list text-rose-500"></i> PREPARACIONES DEFINIDAS
                            </h2>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @forelse($preparaciones as $prep)
                                <div class="p-4 space-y-2 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="font-black text-slate-900 dark:text-white text-sm flex items-center gap-2 uppercase">
                                            🥣 {{ strtoupper($prep->nombre) }}
                                        </span>
                                        <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase">RINDE
                                            {{ $prep->rinde_cantidad }} UNIDADES</span>
                                    </div>

                                    <div
                                        class="bg-slate-50 dark:bg-slate-950/80 p-3 rounded-lg border border-slate-200 dark:border-slate-800 text-xs space-y-1">
                                        <span
                                            class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">INSUMOS
                                            UTILIZADOS:</span>
                                        @foreach($prep->receta as $rec)
                                            <div class="flex justify-between text-slate-700 dark:text-slate-300 uppercase">
                                                <span class="font-bold text-amber-600 dark:text-orange-400 uppercase">•
                                                    {{ strtoupper($rec->insumo->nombre ?? '—') }}</span>
                                                <span
                                                    class="text-rose-600 dark:text-rose-400 font-bold uppercase">{{ $rec->cantidad_usada }}
                                                    {{ strtoupper($rec->insumo->unidad_medida ?? '') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-slate-400 font-bold uppercase text-xs">
                                    NO HAY PREPARACIONES CREADAS AÚN.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 3: VARIANTES DE SALTEÑA -->
        <div x-show="activeTab === 'variantes'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Formulario Variante (Col 5) -->
                <div class="lg:col-span-5">
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                        <h2
                            class="text-xs font-black text-slate-900 dark:text-amber-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <i class="fa-solid fa-plus-circle text-amber-500"></i> NUEVA VARIANTE DE SALTEÑA
                        </h2>

                        <form action="{{ route('variantes.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">NOMBRE
                                        <span class="text-amber-500">*</span></label>
                                    <input type="text" name="nombre" required placeholder="EJ. SALTEÑA DE POLLO"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none uppercase">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">PRECIO
                                        VENTA (BS.) <span class="text-amber-500">*</span></label>
                                    <input type="number" step="any" name="precio_venta" required placeholder="EJ. 8.00"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 focus:border-amber-500 focus:outline-none uppercase">
                                </div>
                            </div>

                            <div class="space-y-3">
                                <label
                                    class="block text-[11px] font-bold uppercase text-amber-600 dark:text-amber-400">COMPONENTES
                                    DE RECETA (OPCIONAL)</label>

                                @if(!$preparaciones->count() && !$insumos->count())
                                    <div
                                        class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 font-bold uppercase">
                                        💡 NO HAY INSUMOS NI PREPARACIONES CREADAS AÚN. PUEDES CREAR ESTA VARIANTE AHORA Y
                                        AGREGAR SU RECETA MÁS ADELANTE.
                                    </div>
                                @endif

                                <!-- Preparación Intermedia (Masa) -->
                                @if($preparaciones->count())
                                    <div
                                        class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200 dark:border-slate-800 space-y-2">
                                        <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase">→
                                            PREPARACIÓN INTERMEDIA (MASA)</span>
                                        <div class="grid grid-cols-12 gap-2">
                                            <div class="col-span-8">
                                                <select name="componentes[0][preparacion_id]"
                                                    class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none uppercase">
                                                    <option value="" class="uppercase">NINGUNA</option>
                                                    @foreach($preparaciones as $prep)
                                                        <option value="{{ $prep->id }}" class="uppercase">
                                                            {{ strtoupper($prep->nombre) }} (RINDE {{ $prep->rinde_cantidad }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-span-4">
                                                <input type="number" step="any" name="componentes[0][cantidad_usada]"
                                                    placeholder="CANT."
                                                    class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none uppercase">
                                            </div>
                                            <input type="hidden" name="componentes[0][tipo_componente]" value="preparacion">
                                            <input type="hidden" name="componentes[0][insumo_id]" value="">
                                        </div>
                                    </div>
                                @endif

                                <!-- Insumo Directo -->
                                @if($insumos->count())
                                    <div
                                        class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200 dark:border-slate-800 space-y-2">
                                        <span class="text-[10px] font-bold text-amber-600 dark:text-orange-400 uppercase">→
                                            INSUMO DIRECTO (EJ. RELLENO)</span>
                                        <div class="grid grid-cols-12 gap-2">
                                            <div class="col-span-8">
                                                <select name="componentes[1][insumo_id]"
                                                    class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none uppercase">
                                                    <option value="" class="uppercase">SELECCIONAR INSUMO...</option>
                                                    @foreach($insumos as $ins)
                                                        <option value="{{ $ins->id }}" class="uppercase">
                                                            {{ strtoupper($ins->nombre) }} ({{ strtoupper($ins->unidad_medida) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-span-4">
                                                <input type="number" step="any" name="componentes[1][cantidad_usada]"
                                                    placeholder="CANT."
                                                    class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none uppercase">
                                            </div>
                                            <input type="hidden" name="componentes[1][tipo_componente]" value="insumo">
                                            <input type="hidden" name="componentes[1][preparacion_id]" value="">
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button type="submit"
                                class="w-full py-2 px-4 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-save"></i> GUARDAR VARIANTE
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista Variantes (Col 7) -->
                <div class="lg:col-span-7">
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                            <h2
                                class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-list text-amber-500"></i> VARIANTES DEFINIDAS
                            </h2>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80" x-data="{ editingId: null }">
                            @forelse($variantes as $var)
                                <div class="p-4 space-y-2 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="font-black text-slate-900 dark:text-white text-sm flex items-center gap-2 uppercase">
                                            🥟 {{ strtoupper($var->nombre) }}
                                            @if(!$var->activo)
                                                <span
                                                    class="text-[10px] px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase">INACTIVO</span>
                                            @endif
                                        </span>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-black text-amber-600 dark:text-amber-400 uppercase">BS.
                                                {{ number_format($var->precio_venta, 2) }}</span>
                                            <button type="button"
                                                @click="editingId = (editingId === {{ $var->id }} ? null : {{ $var->id }})"
                                                class="px-2 py-1 rounded bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/80 text-[10px] font-black uppercase transition-all flex items-center gap-1">
                                                <i class="fa-solid fa-pen-to-square"></i> EDITAR
                                            </button>
                                        </div>
                                    </div>

                                    <div
                                        class="bg-slate-50 dark:bg-slate-950/80 p-3 rounded-lg border border-slate-200 dark:border-slate-800 text-xs space-y-1">
                                        <span
                                            class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block">RECETA:</span>
                                        @forelse($var->recetas as $rec)
                                            <div class="flex justify-between text-slate-700 dark:text-slate-300 uppercase">
                                                @if($rec->tipo_componente === 'insumo')
                                                    <span class="font-bold text-amber-600 dark:text-orange-400 uppercase">• [INSUMO]
                                                        {{ strtoupper($rec->insumo->nombre ?? '—') }}</span>
                                                @else
                                                    <span class="font-bold text-rose-600 dark:text-rose-400 uppercase">• [MASA]
                                                        {{ strtoupper($rec->preparacion->nombre ?? '—') }}</span>
                                                @endif
                                                <span class="text-amber-600 dark:text-amber-400 font-bold uppercase">X
                                                    {{ $rec->cantidad_usada }}</span>
                                            </div>
                                        @empty
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic uppercase">SIN
                                                COMPONENTES DE RECETA ASIGNADOS</span>
                                        @endforelse

                                        @if($var->promociones->count())
                                            <div class="mt-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                                                <span
                                                    class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase">PROMOCIONES
                                                    ACTIVAS:</span>
                                                @foreach($var->promociones as $promo)
                                                    <span
                                                        class="text-[10px] font-bold text-slate-600 dark:text-slate-300 block ml-2 uppercase">
                                                        🏷️ {{ strtoupper($promo->nombre) }} — {{ $promo->unidades_por_paquete }} UDS.
                                                        POR BS. {{ number_format($promo->precio_paquete, 2) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Formulario de Edición de Variante & Receta -->
                                    <form action="{{ route('variantes.update', $var->id) }}" method="POST"
                                        x-show="editingId === {{ $var->id }}"
                                        class="mt-3 p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-lg space-y-3"
                                        x-cloak>
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">NOMBRE</label>
                                                <input type="text" name="nombre" value="{{ $var->nombre }}" required
                                                    class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-bold text-slate-900 dark:text-white uppercase">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">PRECIO
                                                    VENTA (BS.)</label>
                                                <input type="number" step="any" name="precio_venta"
                                                    value="{{ $var->precio_venta }}" required
                                                    class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-xs font-bold text-amber-600 dark:text-amber-400 uppercase">
                                            </div>
                                        </div>

                                        <div class="space-y-2 pt-1 border-t border-amber-200/60 dark:border-amber-800/60">
                                            <label
                                                class="block text-[10px] font-bold uppercase text-amber-700 dark:text-amber-400">ASIGNAR
                                                / ACTUALIZAR RECETA</label>

                                            @if($preparaciones->count())
                                                <div class="grid grid-cols-12 gap-2 items-center text-xs">
                                                    <span
                                                        class="col-span-4 text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase">MASA:</span>
                                                    <select name="componentes[0][preparacion_id]"
                                                        class="col-span-5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-[11px] font-bold text-slate-900 dark:text-white uppercase">
                                                        <option value="">NINGUNA</option>
                                                        @foreach($preparaciones as $prep)
                                                            <option value="{{ $prep->id }}" {{ $var->recetas->where('tipo_componente', 'preparacion')->first()?->preparacion_id == $prep->id ? 'selected' : '' }}>{{ strtoupper($prep->nombre) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="number" step="any" name="componentes[0][cantidad_usada]"
                                                        value="{{ $var->recetas->where('tipo_componente', 'preparacion')->first()?->cantidad_usada ?? '' }}"
                                                        placeholder="CANT."
                                                        class="col-span-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-[11px] font-bold text-slate-900 dark:text-white uppercase">
                                                    <input type="hidden" name="componentes[0][tipo_componente]" value="preparacion">
                                                </div>
                                            @endif

                                            @if($insumos->count())
                                                <div class="grid grid-cols-12 gap-2 items-center text-xs">
                                                    <span
                                                        class="col-span-4 text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase">RELLENO
                                                        / INSUMO:</span>
                                                    <select name="componentes[1][insumo_id]"
                                                        class="col-span-5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-[11px] font-bold text-slate-900 dark:text-white uppercase">
                                                        <option value="">NINGUNO</option>
                                                        @foreach($insumos as $ins)
                                                            <option value="{{ $ins->id }}" {{ $var->recetas->where('tipo_componente', 'insumo')->first()?->insumo_id == $ins->id ? 'selected' : '' }}>{{ strtoupper($ins->nombre) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="number" step="any" name="componentes[1][cantidad_usada]"
                                                        value="{{ $var->recetas->where('tipo_componente', 'insumo')->first()?->cantidad_usada ?? '' }}"
                                                        placeholder="CANT."
                                                        class="col-span-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-[11px] font-bold text-slate-900 dark:text-white uppercase">
                                                    <input type="hidden" name="componentes[1][tipo_componente]" value="insumo">
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex justify-end gap-2 pt-1">
                                            <button type="button" @click="editingId = null"
                                                class="px-2.5 py-1 rounded bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-[10px] uppercase">CANCELAR</button>
                                            <button type="submit"
                                                class="px-3 py-1 rounded bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-[10px] uppercase shadow">GUARDAR
                                                CAMBIOS</button>
                                        </div>
                                    </form>
                                </div>
                            @empty
                                <div class="p-8 text-center text-slate-400 font-bold uppercase text-xs">
                                    NO HAY VARIANTES CREADAS AÚN.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <!-- AlpineJS para el cambio dinámico de pestañas y componentes -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                return false;
            }
            form.dataset.submitting = 'true';
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
    </script>
@endsection