@extends('layouts.app')

@section('title', 'COMPRAS DE INSUMOS — SALTEÑAS')

@section('content')
    <div class="space-y-6">

        <!-- Header Page -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-cart-shopping text-cyan-400"></i> COMPRAS & HISTORIAL DE PRECIOS
                </h1>
                <p class="text-xs text-slate-400 mt-1 uppercase">REGISTRA COMPRAS DE MATERIA PRIMA PARA ABASTECER A TODOS
                    LOS
                    CARRITOS. AL COMPRAR, EL PRECIO DEL INSUMO Y EL EGRESO DE BÓVEDA SE ACTUALIZAN AUTOMÁTICAMENTE.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Formulario Registrar Compra (Col 5) -->
            <div class="lg:col-span-5">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2
                        class="text-xs font-black text-cyan-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-cart-plus"></i> REGISTRAR NUEVA COMPRA
                    </h2>

                    @if($insumos->isEmpty())
                        <div
                            class="p-4 rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-xs font-bold space-y-2">
                            <p class="uppercase">NO TIENES INSUMOS EN EL CATÁLOGO. POR FAVOR CREA PRIMERO LOS INSUMOS (EJ.
                                HARINA, CARNE, POLLO).</p>
                            <a href="{{ route('insumos.index') }}"
                                class="inline-block px-3 py-1.5 rounded-lg bg-cyan-500 text-slate-950 font-black text-xs uppercase">
                                + CREAR INSUMOS
                            </a>
                        </div>
                    @else
                        <form action="{{ route('compras.store') }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">
                                    FECHA DE COMPRA <span class="text-amber-500">*</span>
                                </label>
                                <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-amber-400 focus:border-amber-500 focus:outline-none">
                            </div>

                            <!-- Item de Insumo -->
                            <div class="space-y-3 bg-slate-950/60 p-3.5 rounded-xl border border-slate-800">
                                <label class="block text-[11px] font-black uppercase text-cyan-400">
                                    DETALLE DE INSUMO COMPRADO
                                </label>
                                <div>
                                    <select name="items[0][insumo_id]" required
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-cyan-500 focus:outline-none uppercase">
                                        <option value="" class="uppercase">SELECCIONAR INSUMO...</option>
                                        @foreach($insumos as $ins)
                                            <option value="{{ $ins->id }}" class="uppercase">{{ strtoupper($ins->nombre) }}
                                                ({{ strtoupper($ins->unidad_medida) }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[10px] font-bold text-slate-400 uppercase mb-1">CANTIDAD</label>
                                        <input type="number" step="0.01" name="items[0][cantidad]" required placeholder="EJ. 50"
                                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-white focus:border-cyan-500 focus:outline-none uppercase">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">PRECIO UNITARIO
                                            (BS.)</label>
                                        <input type="number" step="0.10" name="items[0][precio_unitario]" required
                                            placeholder="EJ. 6.50"
                                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-cyan-400 focus:border-cyan-500 focus:outline-none uppercase">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">
                                    OBSERVACIONES / NOTA
                                </label>
                                <textarea name="observaciones" rows="2" placeholder="EJ. COMPRA SEMANAL DE HARINA DE QUINTAL..."
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-200 focus:border-cyan-500 focus:outline-none uppercase"></textarea>
                            </div>

                            <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-cyan-500/20 transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cart-check"></i> REGISTRAR COMPRA & EGRESO
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Tabla Historial de Compras (Col 7) -->
            <div class="lg:col-span-7">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-boxes-packing text-cyan-400"></i> HISTORIAL DE COMPRAS
                        </h2>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase">TOTAL:
                            {{ $compras->total() }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr
                                    class="bg-slate-950/70 border-b border-slate-800 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                                    <th class="py-3 px-4">FECHA</th>
                                    <th class="py-3 px-4">INSUMOS COMPRADOS</th>
                                    <th class="py-3 px-4 text-right">MONTO TOTAL</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @forelse($compras as $c)
                                    <tr>
                                        <td class="py-3 px-4 font-bold text-white uppercase">
                                            {{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            @foreach($c->detalles as $det)
                                                <div class="font-bold text-slate-200 uppercase">
                                                    • {{ strtoupper($det->insumo->nombre ?? 'INSUMO') }}:
                                                    {{ number_format($det->cantidad, 2) }}
                                                    {{ strtoupper($det->insumo->unidad_medida ?? '') }} X BS.
                                                    {{ number_format($det->precio_unitario, 2) }}
                                                </div>
                                            @endforeach
                                        </td>
                                        <td class="py-3 px-4 text-right font-black text-rose-400 uppercase">
                                            BS. {{ number_format($c->monto_total, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-slate-500 font-bold uppercase">
                                            NO HAY COMPRAS REGISTRADAS AÚN.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-3 border-t border-slate-800">
                        {{ $compras->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection