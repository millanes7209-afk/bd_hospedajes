@extends('layouts.app')

@section('title', 'Cierre Diario — Salteñas')

@section('content')
    <div class="space-y-6">

        <!-- Header Page -->
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <div>
                <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-amber-500"></i> Cierre Diario por Sucursal
                </h1>
                <p class="text-xs text-slate-400 mt-1">Registra las ventas, sobrantes, dinero recaudado y factores
                    climáticos del día.</p>
            </div>
            <div class="flex items-center gap-2">
                <span
                    class="px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 font-extrabold text-xs">
                    📅 HOY: {{ now()->locale('es')->isoFormat('dddd D [de] MMMM YYYY') }}
                </span>
            </div>
        </div>

        <!-- Main Grid: Form + History -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Formulario de Registro (Col 5) -->
            <div class="lg:col-span-5">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-5 sticky top-24">
                    <h2
                        class="text-sm font-black text-amber-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-pen-to-square"></i> Registrar / Editar Cierre
                    </h2>

                    @if($sucursales->isEmpty())
                        <div
                            class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold space-y-2">
                            <div class="flex items-center gap-2 text-sm font-black">
                                <i class="fa-solid fa-triangle-exclamation"></i> No hay sucursales registradas
                            </div>
                            <p class="text-[11px] font-medium text-slate-300">
                                Para poder registrar un cierre diario, primero debes registrar al menos una sucursal (punto de
                                venta).
                            </p>
                            <a href="{{ route('sucursales.index') }}"
                                class="inline-block mt-1 px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-black text-[11px] uppercase tracking-wider">
                                + Crear Primera Sucursal
                            </a>
                        </div>
                    @else
                        <form action="{{ route('cierres.store') }}" method="POST" class="space-y-4">
                            @csrf

                            <!-- 1. Sucursal & Fecha (Editable) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">
                                        Sucursal <span class="text-amber-500">*</span>
                                    </label>
                                    <select name="sucursal_id" required
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs font-bold text-white focus:border-amber-500 focus:outline-none">
                                        @foreach($sucursales as $suc)
                                            <option value="{{ $suc->id }}" {{ (old('sucursal_id', $sucursal_id) == $suc->id) ? 'selected' : '' }}>
                                                {{ $suc->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">
                                        Fecha del Cierre <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="date" name="fecha" required value="{{ old('fecha', date('Y-m-d')) }}"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs font-bold text-amber-400 focus:border-amber-500 focus:outline-none">
                                </div>
                            </div>

                            <!-- 2. Clima del Día -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-400 mb-2">
                                    Clima Predominante <span class="text-amber-500">*</span>
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="clima" value="frio_lluvia" class="peer hidden" {{ old('clima') == 'frio_lluvia' ? 'checked' : '' }}>
                                        <div
                                            class="p-2.5 rounded-xl border border-slate-800 bg-slate-950 text-center peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 peer-checked:text-cyan-400 hover:border-slate-700 transition-all">
                                            <div class="text-lg">❄️ 🌧️</div>
                                            <span class="text-[10px] font-black uppercase block mt-1">Frío / Lluvia</span>
                                        </div>
                                    </label>

                                    <label class="cursor-pointer">
                                        <input type="radio" name="clima" value="templado_nublado" class="peer hidden" {{ old('clima', 'templado_nublado') == 'templado_nublado' ? 'checked' : '' }}>
                                        <div
                                            class="p-2.5 rounded-xl border border-slate-800 bg-slate-950 text-center peer-checked:border-amber-500 peer-checked:bg-amber-500/10 peer-checked:text-amber-400 hover:border-slate-700 transition-all">
                                            <div class="text-lg">⛅ 🌤️</div>
                                            <span class="text-[10px] font-black uppercase block mt-1">Templado</span>
                                        </div>
                                    </label>

                                    <label class="cursor-pointer">
                                        <input type="radio" name="clima" value="caluroso_soleado" class="peer hidden" {{ old('clima') == 'caluroso_soleado' ? 'checked' : '' }}>
                                        <div
                                            class="p-2.5 rounded-xl border border-slate-800 bg-slate-950 text-center peer-checked:border-rose-500 peer-checked:bg-rose-500/10 peer-checked:text-rose-400 hover:border-slate-700 transition-all">
                                            <div class="text-lg">☀️ 🌞</div>
                                            <span class="text-[10px] font-black uppercase block mt-1">Caluroso</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- 3. Rango de Temperatura (°C) -->
                            <div class="grid grid-cols-2 gap-3 bg-slate-950/60 p-3 rounded-xl border border-slate-800/80">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">
                                        Temp. Mínima (°C)
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="temp_min" placeholder="ej. 8" value="{{ old('temp_min') }}"
                                            class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-3 pr-7 py-2 text-xs font-bold text-cyan-400 focus:border-cyan-500 focus:outline-none">
                                        <span class="absolute right-2.5 top-2 text-xs font-bold text-slate-500">°C</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">
                                        Temp. Máxima (°C)
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="temp_max" placeholder="ej. 22" value="{{ old('temp_max') }}"
                                            class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-3 pr-7 py-2 text-xs font-bold text-rose-400 focus:border-rose-500 focus:outline-none">
                                        <span class="absolute right-2.5 top-2 text-xs font-bold text-slate-500">°C</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Salteñas Vendidas vs Sobrantes -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-black uppercase text-emerald-400 mb-1">
                                        Salteñas Vendidas <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="number" name="saltenas_vendidas" min="0" required
                                        value="{{ old('saltenas_vendidas', 0) }}"
                                        class="w-full bg-slate-950 border border-emerald-500/50 rounded-xl px-3 py-2.5 text-sm font-black text-emerald-400 focus:border-emerald-400 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-black uppercase text-rose-400 mb-1">
                                        Sobrantes / Mermas <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="number" name="saltenas_sobrantes" min="0" required
                                        value="{{ old('saltenas_sobrantes', 0) }}"
                                        class="w-full bg-slate-950 border border-rose-500/50 rounded-xl px-3 py-2.5 text-sm font-black text-rose-400 focus:border-rose-400 focus:outline-none">
                                </div>
                            </div>

                            <!-- 5. Totales Recaudados (Efectivo & QR) -->
                            <div class="grid grid-cols-2 gap-3 bg-slate-950/60 p-3 rounded-xl border border-slate-800/80">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">
                                        💵 Efectivo (Bs.) <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="number" step="0.50" min="0" name="total_efectivo" required
                                        value="{{ old('total_efectivo', 0.00) }}"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-white focus:border-amber-500 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">
                                        📱 QR / Transfer. (Bs.) <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="number" step="0.50" min="0" name="total_qr" required
                                        value="{{ old('total_qr', 0.00) }}"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-white focus:border-amber-500 focus:outline-none">
                                </div>
                            </div>

                            <!-- 6. Observaciones -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">
                                    Observaciones del Día
                                </label>
                                <textarea name="observaciones" rows="2"
                                    placeholder="ej. Día festivo, marcha universitaria, falta de insumos, etc."
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-200 focus:border-amber-500 focus:outline-none">{{ old('observaciones') }}</textarea>
                            </div>

                            <button type="submit"
                                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Guardar Cierre Diario
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Tabla Historial de Cierres (Col 7) -->
            <div class="lg:col-span-7 space-y-4">

                <!-- Filtro por Sucursal -->
                <div class="flex items-center justify-between bg-slate-900 border border-slate-800 p-4 rounded-2xl">
                    <span class="text-xs font-bold text-slate-400 uppercase">Filtrar por Sucursal:</span>
                    <form action="{{ route('cierres.index') }}" method="GET" class="flex gap-2">
                        <select name="sucursal_id" onchange="this.form.submit()"
                            class="bg-slate-950 border border-slate-700 rounded-xl px-3 py-1.5 text-xs font-bold text-white">
                            <option value="">TODAS LAS SUCURSALES</option>
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}" {{ $sucursal_id == $suc->id ? 'selected' : '' }}>
                                    {{ $suc->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <!-- Listado de Cierres -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                        <h3 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-amber-500"></i> Historial de Cierres Diarios
                        </h3>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase">Total: {{ $cierres->total() }}
                            registros</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-slate-950/70 border-b border-slate-800 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                                    <th class="py-3 px-4">Fecha / Sucursal</th>
                                    <th class="py-3 px-4">Clima & Temp</th>
                                    <th class="py-3 px-4 text-center">Salteñas</th>
                                    <th class="py-3 px-4 text-right">Recaudación</th>
                                    <th class="py-3 px-4 text-right">Ganancia Est.</th>
                                    <th class="py-3 px-4 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 text-xs font-medium text-slate-300">
                                @forelse($cierres as $c)
                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4">
                                            <div class="font-black text-white text-xs">
                                                {{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}
                                            </div>
                                            <div class="text-[10px] font-bold text-amber-400 uppercase">
                                                {{ $c->sucursal->nombre ?? 'N/A' }}
                                            </div>
                                        </td>

                                        <td class="py-3.5 px-4">
                                            @if($c->clima === 'frio_lluvia')
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-[10px] font-bold uppercase inline-flex items-center gap-1">
                                                    ❄️ Frío / Lluvia
                                                </span>
                                            @elseif($c->clima === 'caluroso_soleado')
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/30 text-rose-400 text-[10px] font-bold uppercase inline-flex items-center gap-1">
                                                    ☀️ Caluroso
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-[10px] font-bold uppercase inline-flex items-center gap-1">
                                                    ⛅ Templado
                                                </span>
                                            @endif

                                            @if($c->temp_min !== null || $c->temp_max !== null)
                                                <div class="text-[10px] text-slate-400 font-bold mt-1">
                                                    🌡️ {{ $c->temp_min ?? '--' }}°C - {{ $c->temp_max ?? '--' }}°C
                                                </div>
                                            @endif
                                        </td>

                                        <td class="py-3.5 px-4 text-center">
                                            <div class="font-extrabold text-emerald-400">
                                                {{ $c->saltenas_vendidas }} <span class="text-[9px] text-slate-400">vend.</span>
                                            </div>
                                            @if($c->saltenas_sobrantes > 0)
                                                <div class="text-[10px] font-bold text-rose-400">
                                                    {{ $c->saltenas_sobrantes }} <span
                                                        class="text-[9px] opacity-70">sobrantes</span>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="py-3.5 px-4 text-right">
                                            <div class="font-black text-amber-400 text-xs">
                                                Bs. {{ number_format($c->total_recaudado, 2) }}
                                            </div>
                                            <div class="text-[9px] text-slate-400">
                                                💵 {{ number_format($c->total_efectivo, 0) }} | 📱
                                                {{ number_format($c->total_qr, 0) }}
                                            </div>
                                        </td>

                                        <td class="py-3.5 px-4 text-right">
                                            <span
                                                class="font-black text-xs {{ $c->ganancia_neta >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                                Bs. {{ number_format($c->ganancia_neta, 2) }}
                                            </span>
                                        </td>

                                        <td class="py-3.5 px-4 text-center">
                                            <a href="{{ route('cierres.destroy', $c->id) }}"
                                                onclick="return confirm('¿Eliminar registro de este cierre?');"
                                                class="p-1.5 text-slate-500 hover:text-rose-400 transition-colors">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @if($c->observaciones)
                                        <tr class="bg-slate-950/40">
                                            <td colspan="6" class="py-1.5 px-4 text-[10px] text-slate-400 italic">
                                                📝 <strong>Nota:</strong> {{ $c->observaciones }}
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-500 text-xs font-bold uppercase">
                                            No hay registros de cierre diario aún.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-3 border-t border-slate-800">
                        {{ $cierres->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection