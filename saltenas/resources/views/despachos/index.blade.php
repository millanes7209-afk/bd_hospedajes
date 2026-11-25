@extends('layouts.app')

@section('title', 'Despachos Diarios — Salteñas')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast text-orange-500"></i> Despachos Diarios
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Control de stock enviado a cada carrito. El POS acepta el despacho al abrir el turno.
                </p>
            </div>
            <a href="{{ route('despachos.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase shadow-md shadow-amber-500/20 transition-all">
                <i class="fa-solid fa-plus"></i> Nuevo Despacho
            </a>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('despachos.index') }}"
            class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[140px]">
                <label
                    class="block text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mb-1">Carrito</label>
                <select name="carrito_id"
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
                    <option value="">Todos los carritos</option>
                    @foreach ($carritos as $c)
                        <option value="{{ $c->id }}" {{ $carritoId == $c->id ? 'selected' : '' }}>
                            {{ $c->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[130px]">
                <label class="block text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mb-1">Desde</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
            </div>
            <div class="min-w-[130px]">
                <label class="block text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mb-1">Hasta</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
            </div>
            <button type="submit"
                class="px-4 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-black uppercase transition flex items-center gap-1.5">
                <i class="fa-solid fa-magnifying-glass"></i> Filtrar
            </button>
            @if($carritoId || $fechaInicio || $fechaFin)
                <a href="{{ route('despachos.index') }}"
                    class="px-3 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold uppercase transition flex items-center gap-1.5">
                    <i class="fa-solid fa-xmark"></i> Limpiar
                </a>
            @endif
        </form>

        {{-- Tabla de despachos --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            @if($despachos->isEmpty())
                <div class="py-16 text-center text-slate-400 dark:text-slate-600">
                    <i class="fa-solid fa-truck-fast text-4xl mb-3 opacity-30"></i>
                    <p class="text-sm font-bold">No hay despachos registrados</p>
                    <p class="text-xs mt-1">Crea el primer despacho del día para un carrito.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/50">
                                <th class="px-4 py-3 text-left font-black uppercase text-slate-500 dark:text-slate-400">Fecha
                                </th>
                                <th class="px-4 py-3 text-left font-black uppercase text-slate-500 dark:text-slate-400">Carrito
                                </th>
                                <th class="px-4 py-3 text-left font-black uppercase text-slate-500 dark:text-slate-400">
                                    Variantes</th>
                                <th class="px-4 py-3 text-center font-black uppercase text-slate-500 dark:text-slate-400">Total
                                    Enviado</th>
                                <th class="px-4 py-3 text-center font-black uppercase text-slate-500 dark:text-slate-400">Total
                                    Aceptado</th>
                                <th class="px-4 py-3 text-center font-black uppercase text-slate-500 dark:text-slate-400">Estado
                                </th>
                                <th class="px-4 py-3 text-left font-black uppercase text-slate-500 dark:text-slate-400">
                                    Registrado por</th>
                                <th class="px-4 py-3 text-center font-black uppercase text-slate-500 dark:text-slate-400">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($despachos as $d)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($d->fecha)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="font-black text-purple-700 dark:text-purple-400">{{ $d->carrito?->nombre ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($d->detalles as $det)
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-800 dark:text-amber-400 font-bold text-[10px]">
                                                    {{ $det->variante?->nombre ?? '—' }}: {{ $det->cantidad_enviada }}
                                                    @if($det->cantidad_aceptada > 0 && $det->cantidad_aceptada != $det->cantidad_enviada)
                                                        <span class="text-rose-600 dark:text-rose-400">(recibió
                                                            {{ $det->cantidad_aceptada }})</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-black text-slate-800 dark:text-slate-200">
                                        {{ $d->detalles->sum('cantidad_enviada') }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-black">
                                        @if($d->estado === 'aceptado')
                                            <span
                                                class="text-emerald-600 dark:text-emerald-400">{{ $d->detalles->sum('cantidad_aceptada') }}</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($d->estado === 'pendiente')
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 text-[10px] font-black uppercase">
                                                <i class="fa-solid fa-clock"></i> Pendiente
                                            </span>
                                        @elseif($d->estado === 'aceptado')
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-[10px] font-black uppercase">
                                                <i class="fa-solid fa-circle-check"></i> Aceptado
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 text-[10px] font-black uppercase">
                                                {{ ucfirst($d->estado) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                        {{ $d->user?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($d->estado !== 'aceptado')
                                            <form method="POST" action="{{ route('despachos.destroy', $d->id) }}"
                                                onsubmit="return confirm('¿Eliminar este despacho?')" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition text-xs">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span
                                                class="text-slate-300 dark:text-slate-700 text-[10px] font-bold italic">Aceptado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($despachos->hasPages())
                    <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
                        {{ $despachos->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
@endsection