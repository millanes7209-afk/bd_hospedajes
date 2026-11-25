@extends('layouts.app')

@section('title', 'Nuevo Despacho — Salteñas')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('despachos.index') }}"
                class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition text-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast text-orange-500"></i> Nuevo Despacho
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Asigna el stock diario a un carrito. El POS lo verá al abrir el turno.
                </p>
            </div>
        </div>

        {{-- Formulario con cálculo dinámico en tiempo real --}}
        <form method="POST" action="{{ route('despachos.store') }}"
            x-data="{
                cantidades: [ {{ $variantes->map(fn($v, $i) => (int)old("detalles.$i.cantidad_enviada", 0))->implode(', ') }} ],
                get total() {
                    return this.cantidades.reduce((a, b) => (parseInt(a) || 0) + (parseInt(b) || 0), 0);
                }
            }"
            class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
            @csrf

            {{-- Carrito y Fecha --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                        Carrito (Punto de Venta) <span class="text-rose-500">*</span>
                    </label>
                    <select name="carrito_id" required
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm font-bold px-3 py-2.5 focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
                        <option value="">— Seleccionar carrito —</option>
                        @foreach($carritos as $c)
                            <option value="{{ $c->id }}" {{ old('carrito_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                        Fecha del Despacho <span class="text-emerald-500 font-bold">(Automática)</span>
                    </label>
                    <input type="hidden" name="fecha" value="{{ now()->toDateString() }}">
                    <div class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 text-slate-800 dark:text-slate-200 text-sm font-bold px-3 py-2.5 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check text-emerald-500"></i>
                            Hoy, {{ now()->format('d/m/Y') }}
                        </span>
                        <span class="text-[10px] uppercase font-black px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                            Auto
                        </span>
                    </div>
                </div>
            </div>

            {{-- Variantes / Cantidades --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                        Variantes a Despachar <span class="text-rose-500">*</span>
                    </label>
                    @if($variantes->isEmpty())
                        <span class="text-[10px] text-rose-500 font-bold">⚠ No hay variantes activas</span>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-700">
                                <th class="px-4 py-2.5 text-left font-black uppercase text-slate-500 dark:text-slate-400">
                                    Variante</th>
                                <th
                                    class="px-4 py-2.5 text-center font-black uppercase text-slate-500 dark:text-slate-400 w-36">
                                    Cantidad a Enviar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($variantes as $i => $v)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-300">
                                        <input type="hidden" name="detalles[{{ $i }}][variante_id]" value="{{ $v->id }}">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $v->nombre }}</span>
                                        @if($v->precio_venta)
                                            <span class="text-slate-400 font-normal ml-1">— Bs
                                                {{ number_format($v->precio_venta, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="detalles[{{ $i }}][cantidad_enviada]" min="0"
                                            x-model.number="cantidades[{{ $i }}]"
                                            value="{{ old("detalles.$i.cantidad_enviada", 0) }}"
                                            onfocus="this.select()"
                                            onclick="this.select()"
                                            placeholder="0"
                                            class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm font-black text-center px-2 py-1.5 focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-8 text-center text-slate-400 dark:text-slate-600 text-xs">
                                        No hay variantes activas registradas.
                                        <a href="{{ route('productos.index', ['tab' => 'variantes']) }}"
                                            class="text-amber-500 hover:underline ml-1">Crear variante</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(!$variantes->isEmpty())
                            <tfoot>
                                <tr class="bg-amber-50 dark:bg-amber-950/40 border-t-2 border-amber-300 dark:border-amber-700/60">
                                    <td class="px-4 py-3.5 text-left font-black uppercase text-amber-900 dark:text-amber-300 text-xs">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-calculator text-amber-500 text-sm"></i>
                                            <span>TOTAL SALTEÑAS A DESPACHAR:</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <div class="inline-flex items-baseline justify-center gap-1 px-3 py-1.5 rounded-xl bg-amber-500 text-slate-950 shadow-sm font-black">
                                            <span class="text-base" x-text="total">0</span>
                                            <span class="text-[10px] uppercase font-bold">uds</span>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                <p class="text-[10px] text-slate-400 dark:text-slate-600 mt-1.5 flex items-center gap-1">
                    <i class="fa-solid fa-circle-info"></i>
                    Las variantes con cantidad 0 no se guardarán. Debes enviar al menos una con cantidad ≥ 1.
                </p>
            </div>

            {{-- Observaciones --}}
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                    Observaciones (opcional)
                </label>
                <textarea name="observaciones" rows="2" maxlength="1000"
                    placeholder="Ej: Incluye 10 unidades especiales para evento..."
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm px-3 py-2.5 focus:ring-2 focus:ring-amber-500 focus:outline-none transition resize-none uppercase">{{ old('observaciones') }}</textarea>
            </div>

            {{-- Acciones --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2 text-xs font-black uppercase">
                    <span class="text-slate-400">Total Despachado:</span>
                    <span class="px-2.5 py-1 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 font-black"
                        x-text="total + ' Salteñas'">0 Salteñas</span>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <a href="{{ route('despachos.index') }}"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-black uppercase transition">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black uppercase shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-truck-fast"></i> Registrar Despacho
                    </button>
                </div>
            </div>
        </form>

    </div>
@endsection
