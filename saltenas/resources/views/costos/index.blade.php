@extends('layouts.app')

@section('title', 'Costos de Producción — Salteñas')

@section('content')
    <div class="space-y-6">

        <!-- Header Page -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-coins text-amber-500"></i> Costos de Insumos & Márgenes
                </h1>
                <p class="text-xs text-slate-400 mt-1">Calcula el costo real de preparación por salteña y el precio de venta
                    para determinar tu margen neto.</p>
            </div>
        </div>

        <!-- Main Grid: Insumos + Variantes -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Insumos (Col 6) -->
            <div class="lg:col-span-6 space-y-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2
                        class="text-xs font-black text-amber-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-boxes-stacked"></i> Insumos & Materia Prima
                    </h2>

                    <form action="{{ route('costos.insumos.store') }}" method="POST"
                        class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @csrf
                        <div>
                            <input type="text" name="nombre" required placeholder="ej. Harina"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <select name="unidad_medida" required
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-amber-500 focus:outline-none">
                                <option value="kg">Por Kilogramo (kg)</option>
                                <option value="lt">Por Litro (lt)</option>
                                <option value="unidad">Por Unidad</option>
                                <option value="paquete">Por Paquete</option>
                            </select>
                        </div>
                        <div>
                            <input type="number" step="0.10" name="costo_unitario" required placeholder="Bs. Costo"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-amber-400 focus:border-amber-500 focus:outline-none">
                        </div>
                        <div class="sm:col-span-3">
                            <button type="submit"
                                class="w-full py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider transition-all">
                                + Agregar Insumo
                            </button>
                        </div>
                    </form>

                    <div class="overflow-x-auto pt-2">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-[10px] font-black uppercase text-slate-400">
                                    <th class="py-2">Insumo</th>
                                    <th class="py-2 text-center">Unidad</th>
                                    <th class="py-2 text-right">Costo U.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @foreach($insumos as $ins)
                                    <tr>
                                        <td class="py-2.5 font-bold text-white">{{ $ins->nombre }}</td>
                                        <td class="py-2.5 text-center text-slate-400 uppercase font-bold text-[10px]">
                                            {{ $ins->unidad_medida }}</td>
                                        <td class="py-2.5 text-right font-black text-amber-400">Bs.
                                            {{ number_format($ins->costo_unitario, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Variantes de Salteñas & Márgenes (Col 6) -->
            <div class="lg:col-span-6 space-y-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2
                        class="text-xs font-black text-amber-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-cookie"></i> Variantes de Salteña & Márgenes
                    </h2>

                    <form action="{{ route('costos.variantes.store') }}" method="POST"
                        class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @csrf
                        <div>
                            <input type="text" name="nombre_variante" required placeholder="ej. Salteña de Pollo"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <input type="number" step="0.10" name="costo_unidad" required placeholder="Bs. Costo Prep."
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-rose-400 focus:border-rose-400 focus:outline-none">
                        </div>
                        <div>
                            <input type="number" step="0.50" name="precio_venta" required placeholder="Bs. Precio Venta"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-emerald-400 focus:border-emerald-400 focus:outline-none">
                        </div>
                        <div class="sm:col-span-3">
                            <button type="submit"
                                class="w-full py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider transition-all">
                                + Agregar Variante
                            </button>
                        </div>
                    </form>

                    <div class="overflow-x-auto pt-2">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-[10px] font-black uppercase text-slate-400">
                                    <th class="py-2">Variante</th>
                                    <th class="py-2 text-right">Costo Prep.</th>
                                    <th class="py-2 text-right">P. Venta</th>
                                    <th class="py-2 text-right">Margen Neto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @foreach($costos as $c)
                                    <?php    $margen = $c->precio_venta - $c->costo_unidad; ?>
                                    <tr>
                                        <td class="py-2.5 font-bold text-white">{{ $c->nombre_variante }}</td>
                                        <td class="py-2.5 text-right text-rose-400 font-bold">Bs.
                                            {{ number_format($c->costo_unidad, 2) }}</td>
                                        <td class="py-2.5 text-right text-emerald-400 font-bold">Bs.
                                            {{ number_format($c->precio_venta, 2) }}</td>
                                        <td class="py-2.5 text-right font-black text-amber-400">
                                            Bs. {{ number_format($margen, 2) }}
                                            <span
                                                class="block text-[9px] text-slate-400">({{ number_format(($margen / max($c->precio_venta, 1)) * 100, 0) }}%)</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection