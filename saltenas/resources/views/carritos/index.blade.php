@extends('layouts.app')

@section('title', 'CARRITOS (PUNTOS DE VENTA) — SALTEÑAS')

@section('content')
    <div class="space-y-6">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm">
            <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                <i class="fa-solid fa-store text-purple-600 dark:text-purple-400"></i> CARRITOS (PUNTOS DE VENTA)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase">REGISTRA Y ADMINISTRA LOS CARRITOS O PUNTOS DE VENTA EN LA CIUDAD.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Formulario (Col 5) -->
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                    <h2 class="text-xs font-black text-slate-900 dark:text-purple-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <i class="fa-solid fa-plus-circle text-purple-500"></i> NUEVO CARRITO
                    </h2>

                    <form action="{{ route('carritos.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">NOMBRE / IDENTIFICADOR DEL CARRITO <span class="text-amber-500">*</span></label>
                            <input type="text" name="nombre" required placeholder="EJ. CARRITO 1 - PEREZ VELASCO"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-purple-500 focus:outline-none uppercase">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">ZONA / UBICACIÓN</label>
                            <input type="text" name="zona" placeholder="EJ. CENTRO, SAN FRANCISCO"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-medium text-slate-900 dark:text-slate-200 focus:border-purple-500 focus:outline-none uppercase">
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 px-4 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-save"></i> GUARDAR CARRITO
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabla (Col 7) -->
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-list text-purple-500"></i> CARRITOS REGISTRADOS
                        </h2>
                    </div>

                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                                <th class="py-2.5 px-4">NOMBRE</th>
                                <th class="py-2.5 px-4">ZONA</th>
                                <th class="py-2.5 px-4">ESTADO</th>
                                <th class="py-2.5 px-4 text-right">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($carritos as $car)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                    <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white uppercase">{{ strtoupper($car->nombre) }}</td>
                                    <td class="py-2.5 px-4 text-slate-600 dark:text-slate-400 uppercase">{{ strtoupper($car->zona ?? 'SIN ZONA') }}</td>
                                    <td class="py-2.5 px-4">
                                        @if($car->activo)
                                            <span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-bold text-[10px] uppercase">ACTIVO</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold text-[10px] uppercase">INACTIVO</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-right">
                                        <a href="{{ route('carritos.toggle', $car->id) }}"
                                            class="text-[10px] font-bold text-purple-600 dark:text-purple-400 hover:underline uppercase">
                                            CAMBIAR ESTADO
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400 font-bold uppercase">NO HAY CARRITOS CREADOS AÚN.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection