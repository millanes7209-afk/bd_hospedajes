@extends('layouts.app')

@section('title', 'CATÁLOGO DE INSUMOS — SALTEÑAS')

@section('content')
    <div class="space-y-6">

        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-orange-400"></i> CATÁLOGO DE INSUMOS & HISTORIAL DE PRECIOS
                </h1>
                <p class="text-xs text-slate-400 mt-1 uppercase">LOS PRECIOS NO SE EDITAN A MANO. SE ACTUALIZAN
                    AUTOMÁTICAMENTE CADA
                    VEZ QUE REGISTRAS UNA COMPRA.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Formulario Crear Insumo (Col 4) -->
            <div class="lg:col-span-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2
                        class="text-xs font-black text-orange-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-plus-circle"></i> NUEVO INSUMO (MATERIA PRIMA)
                    </h2>

                    <form action="{{ route('insumos.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">NOMBRE DEL
                                INSUMO</label>
                            <input type="text" name="nombre" required placeholder="EJ. HARINA 000, CARNE PICADA, POLLO"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-orange-500 focus:outline-none uppercase">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">UNIDAD DE
                                MEDIDA</label>
                            <select name="unidad_medida" required
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-orange-500 focus:outline-none uppercase">
                                <option value="">SELECCIONAR UNIDAD...</option>
                                <optgroup label="── PESO ──">
                                    <option value="kg">KILOGRAMO (KG)</option>
                                    <option value="g">GRAMO (G)</option>
                                    <option value="quintal">QUINTAL (QQ)</option>
                                    <option value="libra">LIBRA (LB)</option>
                                </optgroup>
                                <optgroup label="── VOLUMEN ──">
                                    <option value="litro">LITRO (L)</option>
                                    <option value="ml">MILILITRO (ML)</option>
                                    <option value="taza">TAZA (250 ML)</option>
                                    <option value="cucharada">CUCHARADA (15 ML)</option>
                                    <option value="cucharadita">CUCHARADITA (5 ML)</option>
                                </optgroup>
                                <optgroup label="── CANTIDAD ──">
                                    <option value="unidad">UNIDAD (UD)</option>
                                    <option value="docena">DOCENA (12 UDS)</option>
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
                            class="w-full py-2.5 px-4 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-orange-500/20 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-save"></i> CREAR INSUMO
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista de Insumos con Histórico (Col 8) -->
            <div class="lg:col-span-8">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-800">
                        <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-clipboard-list text-orange-400"></i> INSUMOS Y SUS PRECIOS HISTÓRICOS
                        </h2>
                    </div>

                    <div class="divide-y divide-slate-800/80">
                        @forelse($insumos as $ins)
                            <div class="p-4 hover:bg-slate-800/30 transition-colors space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-black text-white text-sm flex items-center gap-2 uppercase">
                                        📦 {{ strtoupper($ins->nombre) }}
                                        <span
                                            class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-800 text-orange-400 uppercase">
                                            MEDIDA: {{ strtoupper($ins->unidad_medida) }}
                                        </span>
                                    </span>

                                    <span class="text-xs font-black text-amber-400 uppercase">
                                        PRECIO ACTUAL: BS. {{ number_format($ins->precioEnFecha(date('Y-m-d')), 2) }} /
                                        {{ strtoupper($ins->unidad_medida) }}
                                    </span>
                                </div>

                                <!-- Histórico de Precios -->
                                <div class="bg-slate-950/80 p-3 rounded-xl border border-slate-800">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">
                                        HISTORIAL DE COMPRAS / PRECIOS REGISTRADOS:
                                    </span>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($ins->preciosHistorial as $ph)
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-[10px] font-bold text-slate-300 uppercase">
                                                📅 {{ \Carbon\Carbon::parse($ph->vigente_desde)->format('d/m/Y') }}: <strong
                                                    class="text-emerald-400 uppercase">BS.
                                                    {{ number_format($ph->precio, 2) }}</strong>
                                            </span>
                                        @empty
                                            <span class="text-[10px] text-slate-500 italic uppercase">SIN REGISTROS DE COMPRAS AÚN.
                                                EL PRECIO
                                                TOMARÁ EFECTO CON LA PRIMERA COMPRA.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-500 font-bold uppercase text-xs">
                                NO HAY INSUMOS CREADOS AÚN.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection