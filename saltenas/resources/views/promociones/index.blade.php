@extends('layouts.app')

@section('title', 'PROMOCIONES (COMBOS) — SALTEÑAS')

@section('content')
    <div class="space-y-6">

        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                <i class="fa-solid fa-tags text-indigo-400"></i> PROMOCIONES EXPLÍCITAS (COMBOS)
            </h1>
            <p class="text-xs text-slate-400 mt-1 uppercase">
                DEFINE COMBOS COMO REGLAS DEL SISTEMA (EJ. "3 SALTEÑAS POR BS. 10"). SE USARÁN AL REGISTRAR EL CIERRE DIARIO
                DE CADA CARRITO.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Formulario (Col 5) -->
            <div class="lg:col-span-5">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2
                        class="text-xs font-black text-indigo-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-plus-circle"></i> NUEVA PROMOCIÓN / COMBO
                    </h2>

                    @if($variantes->isEmpty())
                        <div
                            class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-bold uppercase">
                            PRIMERO CREA LAS VARIANTES DE SALTEÑA PARA PODER DEFINIR PROMOCIONES.
                            <a href="{{ route('variantes.index') }}" class="underline ml-1 uppercase">IR A VARIANTES</a>
                        </div>
                    @else
                        <form action="{{ route('promociones.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">NOMBRE DE LA
                                    PROMO</label>
                                <input type="text" name="nombre" required placeholder='EJ. "3 X 10BS", "COMBO FAMILIAR"'
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-indigo-500 focus:outline-none uppercase">
                            </div>

                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">VARIANTE DE
                                    SALTEÑA</label>
                                <select name="variante_id" required
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-indigo-500 focus:outline-none uppercase">
                                    <option value="" class="uppercase">SELECCIONAR VARIANTE...</option>
                                    @foreach($variantes as $var)
                                        <option value="{{ $var->id }}" class="uppercase">{{ strtoupper($var->nombre) }} (PRECIO
                                            NORMAL: BS. {{ $var->precio_venta }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">UNIDADES POR
                                        PAQUETE</label>
                                    <input type="number" name="unidades_por_paquete" min="2" required placeholder="EJ. 3"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-indigo-500 focus:outline-none uppercase">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">PRECIO PAQUETE
                                        (BS.)</label>
                                    <input type="number" step="0.50" name="precio_paquete" required placeholder="EJ. 10"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-indigo-400 focus:border-indigo-500 focus:outline-none uppercase">
                                </div>
                            </div>

                            <div
                                class="bg-indigo-500/5 border border-indigo-500/20 p-3 rounded-xl text-[10px] text-slate-400 font-bold uppercase">
                                💡 AL REGISTRAR EL CIERRE DIARIO, EL SISTEMA USARÁ ESTAS UNIDADES Y PRECIO PARA CALCULAR EL
                                INGRESO ESTIMADO Y VERIFICAR SI HAY DESCUADRE.
                            </div>

                            <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-indigo-500/20 transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-save"></i> GUARDAR PROMOCIÓN
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Tabla Promociones (Col 7) -->
            <div class="lg:col-span-7">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-800">
                        <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-list text-indigo-400"></i> PROMOCIONES ACTIVAS
                        </h2>
                    </div>

                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-slate-950/70 border-b border-slate-800 text-[10px] font-black uppercase text-slate-400">
                                <th class="py-3 px-4">NOMBRE</th>
                                <th class="py-3 px-4">VARIANTE</th>
                                <th class="py-3 px-4 text-center">UNIDADES</th>
                                <th class="py-3 px-4 text-right">PRECIO PAQUETE</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($promociones as $promo)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="py-3 px-4 font-black text-white uppercase">🏷️ {{ strtoupper($promo->nombre) }}
                                    </td>
                                    <td class="py-3 px-4 text-amber-400 font-bold uppercase">
                                        {{ strtoupper($promo->variante->nombre ?? '—') }}</td>
                                    <td class="py-3 px-4 text-center font-black text-white uppercase">
                                        {{ $promo->unidades_por_paquete }} UDS.</td>
                                    <td class="py-3 px-4 text-right font-black text-indigo-400 uppercase">BS.
                                        {{ number_format($promo->precio_paquete, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-500 font-bold uppercase">NO HAY
                                        PROMOCIONES CREADAS AÚN.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection