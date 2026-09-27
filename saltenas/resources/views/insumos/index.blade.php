@extends('layouts.app')

@section('title', 'Catálogo de Insumos — Salteñas')

@section('content')
    <div class="space-y-6">

        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-orange-400"></i> Catálogo de Insumos & Historial de Precios
                </h1>
                <p class="text-xs text-slate-400 mt-1">Los precios no se editan a mano. Se actualizan automáticamente cada
                    vez que registras una Compra.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Formulario Crear Insumo (Col 4) -->
            <div class="lg:col-span-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2
                        class="text-xs font-black text-orange-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-plus-circle"></i> Nuevo Insumo (Materia Prima)
                    </h2>

                    <form action="{{ route('insumos.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Nombre del
                                Insumo</label>
                            <input type="text" name="nombre" required placeholder="ej. Harina 000, Carne Picada, Pollo"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-orange-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Unidad de
                                Medida</label>
                            <select name="unidad_medida" required
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-orange-500 focus:outline-none">
                                <option value="Kg">Kilogramo (Kg)</option>
                                <option value="Gramo">Gramo (g)</option>
                                <option value="Litro">Litro (L)</option>
                                <option value="Unidad">Unidad (ud)</option>
                                <option value="Quintal">Quintal (qq)</option>
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-orange-500/20 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-save"></i> Crear Insumo
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista de Insumos con Histórico (Col 8) -->
            <div class="lg:col-span-8">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-800">
                        <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-clipboard-list text-orange-400"></i> Insumos y sus Precios Históricos
                        </h2>
                    </div>

                    <div class="divide-y divide-slate-800/80">
                        @forelse($insumos as $ins)
                            <div class="p-4 hover:bg-slate-800/30 transition-colors space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-black text-white text-sm flex items-center gap-2">
                                        📦 {{ $ins->nombre }}
                                        <span
                                            class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-800 text-orange-400 uppercase">
                                            Medida: {{ $ins->unidad_medida }}
                                        </span>
                                    </span>

                                    <span class="text-xs font-black text-amber-400">
                                        Precio Actual: Bs. {{ number_format($ins->precioEnFecha(date('Y-m-d')), 2) }} /
                                        {{ $ins->unidad_medida }}
                                    </span>
                                </div>

                                <!-- Histórico de Precios -->
                                <div class="bg-slate-950/80 p-3 rounded-xl border border-slate-800">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">
                                        Historial de Compras / Precios Registrados:
                                    </span>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($ins->preciosHistorial as $ph)
                                            <span
                                                class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-[10px] font-bold text-slate-300">
                                                📅 {{ \Carbon\Carbon::parse($ph->vigente_desde)->format('d/m/Y') }}: <strong
                                                    class="text-emerald-400">Bs. {{ number_format($ph->precio, 2) }}</strong>
                                            </span>
                                        @empty
                                            <span class="text-[10px] text-slate-500 italic">Sin registros de compras aún. El precio
                                                tomará efecto con la primera compra.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-500 font-bold uppercase text-xs">
                                No hay insumos creados aún.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection