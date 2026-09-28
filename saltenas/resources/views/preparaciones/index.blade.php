@extends('layouts.app')

@section('title', 'PREPARACIONES (MASA & RELLENOS) — SALTEÑAS')

@section('content')
    <div class="space-y-6">

        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                <i class="fa-solid fa-bowl-rice text-rose-400"></i> PREPARACIONES INTERMEDIAS (MASA, RELLENOS)
            </h1>
            <p class="text-xs text-slate-400 mt-1 uppercase">
                UNA PREPARACIÓN ES UN LOTE ELABORADO (EJ. "MASA") QUE TIENE UN <strong class="text-white">RINDE</strong> EN
                UNIDADES.
                SU COSTO POR UNIDAD SE CALCULA DIVIDIENDO EL COSTO TOTAL DE INSUMOS DEL LOTE ENTRE LAS UNIDADES QUE RINDE.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Formulario (Col 5) -->
            <div class="lg:col-span-5">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-5">
                    <h2
                        class="text-xs font-black text-rose-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-plus-circle"></i> NUEVA PREPARACIÓN
                    </h2>

                    @if($insumos->isEmpty())
                        <div
                            class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold uppercase">
                            PRIMERO DEBES CREAR LOS INSUMOS ANTES DE DEFINIR PREPARACIONES.
                            <a href="{{ route('insumos.index') }}" class="underline ml-1 uppercase">IR A INSUMOS</a>
                        </div>
                    @else
                        <form action="{{ route('preparaciones.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">NOMBRE</label>
                                    <input type="text" name="nombre" required placeholder="EJ. MASA DE SALTEÑA"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-rose-500 focus:outline-none uppercase">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">RINDE
                                        (UNIDADES)</label>
                                    <input type="number" name="rinde_cantidad" step="0.01" required placeholder="EJ. 100"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-rose-400 focus:border-rose-500 focus:outline-none uppercase">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[11px] font-black uppercase text-rose-400">
                                    INSUMOS QUE COMPONEN ESTA PREPARACIÓN
                                </label>
                                <div id="insumos-prep" class="space-y-2">
                                    <div class="grid grid-cols-12 gap-2 items-center bg-slate-950 p-2 rounded-lg">
                                        <div class="col-span-7">
                                            <select name="insumos[0][insumo_id]" required
                                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-white focus:border-rose-500 focus:outline-none uppercase">
                                                <option value="" class="uppercase">SELECCIONAR...</option>
                                                @foreach($insumos as $ins)
                                                    <option value="{{ $ins->id }}" class="uppercase">{{ strtoupper($ins->nombre) }}
                                                        ({{ strtoupper($ins->unidad_medida) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-5">
                                            <input type="number" step="0.0001" name="insumos[0][cantidad_usada]" required
                                                placeholder="CANTIDAD"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-white focus:border-rose-500 focus:outline-none uppercase">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-rose-500/20 transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-save"></i> GUARDAR PREPARACIÓN
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Lista Preparaciones (Col 7) -->
            <div class="lg:col-span-7">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-800">
                        <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-list text-rose-400"></i> PREPARACIONES REGISTRADAS
                        </h2>
                    </div>

                    <div class="divide-y divide-slate-800/80">
                        @forelse($preparaciones as $prep)
                            <div class="p-4 space-y-2 hover:bg-slate-800/30">
                                <div class="flex items-center justify-between">
                                    <span class="font-black text-white text-sm uppercase">🥣
                                        {{ strtoupper($prep->nombre) }}</span>
                                    <span class="text-[11px] font-extrabold text-rose-400 uppercase">RINDE:
                                        {{ $prep->rinde_cantidad }} UNIDADES / LOTE</span>
                                </div>
                                <div class="bg-slate-950/80 p-3 rounded-xl border border-slate-800 text-xs space-y-1">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">INSUMOS
                                        DEL LOTE:</span>
                                    @foreach($prep->receta as $item)
                                        <div class="flex justify-between text-slate-300 uppercase">
                                            <span class="font-bold">• {{ strtoupper($item->insumo->nombre ?? 'INSUMO') }}</span>
                                            <span class="text-amber-400 font-bold">{{ $item->cantidad_usada }}
                                                {{ strtoupper($item->insumo->unidad_medida ?? '') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-500 font-bold uppercase text-xs">
                                NO HAY PREPARACIONES REGISTRADAS AÚN.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection