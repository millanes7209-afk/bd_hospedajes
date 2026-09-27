@extends('layouts.app')

@section('title', 'Carritos (Puntos de Venta) — Salteñas')

@section('content')
    <div class="space-y-6">

        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-store text-purple-400"></i> Carritos (Puntos de Venta)
                </h1>
                <p class="text-xs text-slate-400 mt-1">Registra y administra los carritos o puntos de venta en la ciudad.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Formulario (Col 5) -->
            <div class="lg:col-span-5">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2
                        class="text-xs font-black text-purple-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-plus-circle"></i> Nuevo Carrito
                    </h2>

                    <form action="{{ route('carritos.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Nombre / Identificador
                                del Carrito</label>
                            <input type="text" name="nombre" required placeholder="ej. Carrito 1 - Perez Velasco"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-purple-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Zona /
                                Ubicación</label>
                            <input type="text" name="zona" placeholder="ej. Centro, San Francisco"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-200 focus:border-purple-500 focus:outline-none">
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-purple-500/20 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-save"></i> Guardar Carrito
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabla (Col 7) -->
            <div class="lg:col-span-7">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-800">
                        <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-list text-purple-400"></i> Carritos Registrados
                        </h2>
                    </div>

                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-slate-950/70 border-b border-slate-800 text-[10px] font-black uppercase text-slate-400">
                                <th class="py-3 px-4">Nombre</th>
                                <th class="py-3 px-4">Zona</th>
                                <th class="py-3 px-4">Estado</th>
                                <th class="py-3 px-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($carritos as $car)
                                <tr>
                                    <td class="py-3 px-4 font-bold text-white">{{ $car->nombre }}</td>
                                    <td class="py-3 px-4 text-slate-400">{{ $car->zona ?? 'Sin zona' }}</td>
                                    <td class="py-3 px-4">
                                        @if($car->activo)
                                            <span
                                                class="px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold text-[10px]">ACTIVO</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 rounded bg-slate-800 text-slate-400 font-bold text-[10px]">INACTIVO</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('carritos.toggle', $car->id) }}"
                                            class="text-[10px] font-bold text-purple-400 hover:underline">
                                            Cambiar Estado
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-500 font-bold uppercase">No hay carritos
                                        creados aún.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection