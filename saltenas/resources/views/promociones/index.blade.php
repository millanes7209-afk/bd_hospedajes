@extends('layouts.app')

@section('title', 'Promociones (Combos) — Salteñas')

@section('content')
    <div class="space-y-6">

        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                <i class="fa-solid fa-tags text-indigo-400"></i> Promociones Explícitas (Combos)
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Define combos como reglas del sistema (ej. "3 Salteñas por Bs. 10"). Se usarán al registrar el cierre diario
                de cada carrito.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Formulario (Col 5) -->
            <div class="lg:col-span-5">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2
                        class="text-xs font-black text-indigo-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-plus-circle"></i> Nueva Promoción / Combo
                    </h2>

                    @if($variantes->isEmpty())
                        <div
                            class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-bold">
                            Primero crea las variantes de salteña para poder definir promociones.
                            <a href="{{ route('variantes.index') }}" class="underline ml-1">Ir a Variantes</a>
                        </div>
                    @else
                        <form action="{{ route('promociones.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Nombre de la
                                    Promo</label>
                                <input type="text" name="nombre" required placeholder='ej. "3 x 10Bs", "Combo Familiar"'
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-indigo-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Variante de
                                    Salteña</label>
                                <select name="variante_id" required
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-indigo-500 focus:outline-none">
                                    <option value="">Seleccionar Variante...</option>
                                    @foreach($variantes as $var)
                                        <option value="{{ $var->id }}">{{ $var->nombre }} (Precio normal: Bs.
                                            {{ $var->precio_venta }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Unidades por
                                        Paquete</label>
                                    <input type="number" name="unidades_por_paquete" min="2" required placeholder="ej. 3"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-indigo-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Precio Paquete
                                        (Bs.)</label>
                                    <input type="number" step="0.50" name="precio_paquete" required placeholder="ej. 10"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-indigo-400 focus:border-indigo-500 focus:outline-none">
                                </div>
                            </div>

                            <div
                                class="bg-indigo-500/5 border border-indigo-500/20 p-3 rounded-xl text-[10px] text-slate-400 font-bold">
                                💡 Al registrar el cierre diario, el sistema usará estas unidades y precio para calcular el
                                ingreso estimado y verificar si hay descuadre.
                            </div>

                            <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-indigo-500/20 transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-save"></i> Guardar Promoción
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
                            <i class="fa-solid fa-list text-indigo-400"></i> Promociones Activas
                        </h2>
                    </div>

                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-slate-950/70 border-b border-slate-800 text-[10px] font-black uppercase text-slate-400">
                                <th class="py-3 px-4">Nombre</th>
                                <th class="py-3 px-4">Variante</th>
                                <th class="py-3 px-4 text-center">Unidades</th>
                                <th class="py-3 px-4 text-right">Precio Paquete</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($promociones as $promo)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="py-3 px-4 font-black text-white">🏷️ {{ $promo->nombre }}</td>
                                    <td class="py-3 px-4 text-amber-400 font-bold">{{ $promo->variante->nombre ?? '—' }}</td>
                                    <td class="py-3 px-4 text-center font-black text-white">{{ $promo->unidades_por_paquete }}
                                        uds.</td>
                                    <td class="py-3 px-4 text-right font-black text-indigo-400">Bs.
                                        {{ number_format($promo->precio_paquete, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-500 font-bold uppercase">No hay
                                        promociones creadas aún.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection