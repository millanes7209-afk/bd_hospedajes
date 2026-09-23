@extends('layouts.app')

@section('title', 'Sucursales — Salteñas')

@section('content')
    <div class="space-y-6">

        <!-- Header Page -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-store text-amber-500"></i> Gestión de Sucursales
                </h1>
                <p class="text-xs text-slate-400 mt-1">Administra tus puntos de venta actuales y futuros (Monaka, Dulces
                    Sueños, Tía Coca, etc.).</p>
            </div>
        </div>

        <!-- Main Grid: Form + Branch Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Formulario (Col 4) -->
            <div class="lg:col-span-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 sticky top-24">
                    <h2
                        class="text-xs font-black text-amber-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-plus-circle"></i> Registrar Nueva Sucursal
                    </h2>

                    <form action="{{ route('sucursales.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">
                                Nombre de la Sucursal <span class="text-amber-500">*</span>
                            </label>
                            <input type="text" name="nombre" required placeholder="ej. Dulces Sueños"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs font-bold text-white focus:border-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">
                                Dirección / Ubicación
                            </label>
                            <input type="text" name="direccion" placeholder="ej. Puesto 2 / Av. Camacho"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-200 focus:border-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">
                                Encargado / Responsable
                            </label>
                            <input type="text" name="encargado" placeholder="ej. Doña María"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-200 focus:border-amber-500 focus:outline-none">
                        </div>

                        <button type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check"></i> Guardar Sucursal
                        </button>
                    </form>
                </div>
            </div>

            <!-- Cards / Table (Col 8) -->
            <div class="lg:col-span-8 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($sucursales as $suc)
                        <div
                            class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3 relative overflow-hidden group">
                            <div class="flex items-center justify-between">
                                <h3 class="font-black text-base text-white uppercase">{{ $suc->nombre }}</h3>
                                <a href="{{ route('sucursales.toggle', $suc->id) }}"
                                    class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase transition-all {{ $suc->activa ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $suc->activa ? '● Activa' : 'Inactiva' }}
                                </a>
                            </div>

                            <div class="text-xs text-slate-400 space-y-1">
                                <div>📍 <strong class="text-slate-300">Ubicación:</strong>
                                    {{ $suc->direccion ?: 'Sin especificar' }}</div>
                                <div>👤 <strong class="text-slate-300">Encargado:</strong>
                                    {{ $suc->encargado ?: 'Sin especificar' }}</div>
                            </div>

                            <div
                                class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs font-bold text-slate-400">
                                <span>Total cierres: <strong
                                        class="text-amber-400">{{ $suc->cierres()->count() }}</strong></span>
                                <a href="{{ route('cierres.index', ['sucursal_id' => $suc->id]) }}"
                                    class="text-amber-400 hover:text-amber-300 transition-colors flex items-center gap-1 text-[11px] uppercase font-black">
                                    Ver Cierres <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection