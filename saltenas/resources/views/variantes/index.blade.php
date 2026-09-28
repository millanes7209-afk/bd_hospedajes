@extends('layouts.app')

@section('title', 'VARIANTES DE SALTEÑA — SALTEÑAS')

@section('content')
    <div class="space-y-6">

        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                <i class="fa-solid fa-cookie text-amber-400"></i> VARIANTES DE SALTEÑA & RECETAS
            </h1>
            <p class="text-xs text-slate-400 mt-1 uppercase">
                DEFINE CADA TIPO DE SALTEÑA (EJ. "SALTEÑA DE POLLO") CON SU RECETA COMPUESTA DE
                <strong class="text-white">INSUMOS DIRECTOS</strong> Y/O <strong class="text-white">PREPARACIONES
                    INTERMEDIAS</strong> (MASA).
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Formulario (Col 5) -->
            <div class="lg:col-span-5">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2
                        class="text-xs font-black text-amber-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-plus-circle"></i> NUEVA VARIANTE
                    </h2>

                    <form action="{{ route('variantes.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">NOMBRE</label>
                                <input type="text" name="nombre" required placeholder="EJ. SALTEÑA DE POLLO"
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-amber-500 focus:outline-none uppercase">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">PRECIO VENTA
                                    (BS.)</label>
                                <input type="number" step="0.50" name="precio_venta" required placeholder="EJ. 8"
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-amber-400 focus:border-amber-500 focus:outline-none uppercase">
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-[11px] font-black uppercase text-amber-400">COMPONENTES DE
                                RECETA</label>

                            <!-- Fila 0: Masa/Preparación -->
                            @if($preparaciones->count())
                                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 space-y-2">
                                    <span class="text-[10px] font-black text-rose-400 uppercase">→ PREPARACIÓN INTERMEDIA
                                        (MASA)</span>
                                    <div class="grid grid-cols-12 gap-2">
                                        <div class="col-span-8">
                                            <select name="componentes[0][preparacion_id]"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-white focus:border-amber-500 focus:outline-none uppercase">
                                                <option value="" class="uppercase">NINGUNA</option>
                                                @foreach($preparaciones as $prep)
                                                    <option value="{{ $prep->id }}" class="uppercase">
                                                        {{ strtoupper($prep->nombre) }} (RINDE {{ $prep->rinde_cantidad }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-4">
                                            <input type="number" step="0.0001" name="componentes[0][cantidad_usada]"
                                                placeholder="CANT."
                                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-white focus:border-amber-500 focus:outline-none uppercase">
                                        </div>
                                        <input type="hidden" name="componentes[0][tipo_componente]" value="preparacion">
                                        <input type="hidden" name="componentes[0][insumo_id]" value="">
                                    </div>
                                </div>
                            @endif

                            <!-- Fila 1: Insumo Directo (ej. Carne/Pollo) -->
                            @if($insumos->count())
                                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 space-y-2">
                                    <span class="text-[10px] font-black text-orange-400 uppercase">→ INSUMO DIRECTO (EJ.
                                        RELLENO)</span>
                                    <div class="grid grid-cols-12 gap-2">
                                        <div class="col-span-8">
                                            <select name="componentes[1][insumo_id]"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-white focus:border-amber-500 focus:outline-none uppercase">
                                                <option value="" class="uppercase">SELECCIONAR INSUMO...</option>
                                                @foreach($insumos as $ins)
                                                    <option value="{{ $ins->id }}" class="uppercase">{{ strtoupper($ins->nombre) }}
                                                        ({{ strtoupper($ins->unidad_medida) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-4">
                                            <input type="number" step="0.0001" name="componentes[1][cantidad_usada]"
                                                placeholder="CANT."
                                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-white focus:border-amber-500 focus:outline-none uppercase">
                                        </div>
                                        <input type="hidden" name="componentes[1][tipo_componente]" value="insumo">
                                        <input type="hidden" name="componentes[1][preparacion_id]" value="">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-save"></i> GUARDAR VARIANTE
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista Variantes (Col 7) -->
            <div class="lg:col-span-7">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-800">
                        <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-list text-amber-400"></i> VARIANTES DEFINIDAS
                        </h2>
                    </div>

                    <div class="divide-y divide-slate-800/80">
                        @forelse($variantes as $var)
                            <div class="p-4 space-y-2 hover:bg-slate-800/30">
                                <div class="flex items-center justify-between">
                                    <span class="font-black text-white text-sm flex items-center gap-2 uppercase">
                                        🥟 {{ strtoupper($var->nombre) }}
                                        @if(!$var->activo)
                                            <span
                                                class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400 font-bold uppercase">INACTIVO</span>
                                        @endif
                                    </span>
                                    <span class="text-sm font-black text-amber-400 uppercase">BS.
                                        {{ number_format($var->precio_venta, 2) }}</span>
                                </div>

                                <div class="bg-slate-950/80 p-3 rounded-xl border border-slate-800 text-xs space-y-1">
                                    <span
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">RECETA:</span>
                                    @foreach($var->recetas as $rec)
                                        <div class="flex justify-between text-slate-300 uppercase">
                                            @if($rec->tipo_componente === 'insumo')
                                                <span class="font-bold text-orange-400 uppercase">• [INSUMO]
                                                    {{ strtoupper($rec->insumo->nombre ?? '—') }}</span>
                                            @else
                                                <span class="font-bold text-rose-400 uppercase">• [MASA]
                                                    {{ strtoupper($rec->preparacion->nombre ?? '—') }}</span>
                                            @endif
                                            <span class="text-amber-400 font-bold uppercase">X {{ $rec->cantidad_usada }}</span>
                                        </div>
                                    @endforeach

                                    @if($var->promociones->count())
                                        <div class="mt-2 pt-2 border-t border-slate-800">
                                            <span class="text-[10px] font-black text-indigo-400 uppercase">PROMOCIONES
                                                ACTIVAS:</span>
                                            @foreach($var->promociones as $promo)
                                                <span class="text-[10px] font-bold text-slate-300 block ml-2 uppercase">
                                                    🏷️ {{ strtoupper($promo->nombre) }} — {{ $promo->unidades_por_paquete }} UDS. POR
                                                    BS.
                                                    {{ number_format($promo->precio_paquete, 2) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-500 font-bold uppercase text-xs">
                                NO HAY VARIANTES CREADAS AÚN.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection