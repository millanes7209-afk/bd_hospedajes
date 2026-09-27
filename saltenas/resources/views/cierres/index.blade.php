@extends('layouts.app')

@section('title', 'Cierre Diario — Salteñas')

@section('content')
    <div class="space-y-6">

        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
            <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-emerald-400"></i> Registrar Cierre Diario por Carrito
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                El sistema verifica automáticamente si la cantidad entregada cuadra con lo vendido + sobrante,
                y si el monto estimado coincide con el monto real entregado. Los errores se marcan en rojo.
            </p>
        </div>

        @if($carritos->isEmpty() || $variantes->isEmpty())
            <div
                class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-6 text-amber-400 font-bold text-sm flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-2xl mt-0.5"></i>
                <div>
                    <p class="font-black uppercase">Configuración Incompleta</p>
                    <p class="text-xs mt-1">Para registrar un cierre necesitas tener al menos un <strong>carrito</strong> y una
                        <strong>variante de salteña</strong> creados.</p>
                    <div class="flex gap-3 mt-3">
                        <a href="{{ route('carritos.index') }}"
                            class="px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-black text-xs uppercase">+ Crear
                            Carrito</a>
                        <a href="{{ route('variantes.index') }}"
                            class="px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-black text-xs uppercase">+ Crear
                            Variante</a>
                    </div>
                </div>
            </div>
        @else
            <!-- Formulario Cierre Diario -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-5">
                <h2
                    class="text-xs font-black text-emerald-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-plus-circle"></i> Nuevo Cierre Diario
                </h2>

                <form action="{{ route('cierres.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Carrito + Fecha + Temperatura -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Carrito / Punto de Venta
                                <span class="text-amber-500">*</span></label>
                            <select name="carrito_id" required
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white focus:border-emerald-500 focus:outline-none">
                                <option value="">Seleccionar Carrito...</option>
                                @foreach($carritos as $car)
                                    <option value="{{ $car->id }}">{{ $car->nombre }} @if($car->zona)({{ $car->zona }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Fecha del Cierre <span
                                    class="text-amber-500">*</span></label>
                            <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-amber-400 focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Monto Real Entregado (Bs.)
                                <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.50" name="monto_real" required placeholder="ej. 350"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-black text-rose-400 focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Detalle por Variante -->
                    @foreach($variantes as $i => $var)
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="font-black text-amber-400">🥟 {{ $var->nombre }}</span>
                                <span class="text-[10px] font-bold text-slate-400">Precio Normal: <strong class="text-white">Bs.
                                        {{ $var->precio_venta }}</strong></span>
                            </div>
                            <input type="hidden" name="detalles[{{ $i }}][variante_id]" value="{{ $var->id }}">

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Cantidad Entregada
                                        ☝</label>
                                    <input type="number" name="detalles[{{ $i }}][cantidad_entregada]" min="0" placeholder="ej. 100"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs font-black text-white focus:border-emerald-500 focus:outline-none"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Vendidas a Precio
                                        Normal</label>
                                    <input type="number" name="detalles[{{ $i }}][cantidad_vendida_normal]" min="0"
                                        placeholder="ej. 10"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs font-black text-white focus:border-emerald-500 focus:outline-none"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Sobrantes</label>
                                    <input type="number" name="detalles[{{ $i }}][cantidad_sobrante]" min="0" placeholder="ej. 15"
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs font-black text-white focus:border-emerald-500 focus:outline-none"
                                        required>
                                </div>
                            </div>

                            <!-- Sección de Promociones si tiene -->
                            @if($var->promociones->count())
                                <div class="bg-indigo-500/5 border border-indigo-500/20 rounded-xl p-3 space-y-2">
                                    <span class="text-[10px] font-black text-indigo-400 uppercase block">🏷️ Paquetes / Combos
                                        Vendidos</span>
                                    @foreach($var->promociones as $j => $promo)
                                        <div class="flex items-center gap-3">
                                            <label class="text-xs font-bold text-slate-300 flex-1">{{ $promo->nombre }}
                                                ({{ $promo->unidades_por_paquete }} uds. x Bs. {{ $promo->precio_paquete }})</label>
                                            <input type="hidden" name="detalles[{{ $i }}][promociones][{{ $j }}][promocion_id]"
                                                value="{{ $promo->id }}">
                                            <div class="w-28">
                                                <input type="number" min="0" value="0"
                                                    name="detalles[{{ $i }}][promociones][{{ $j }}][paquetes_vendidos]"
                                                    placeholder="# Paquetes"
                                                    class="w-full bg-slate-900 border border-indigo-500/30 rounded-lg px-3 py-1.5 text-xs font-black text-indigo-400 focus:border-indigo-500 focus:outline-none text-center">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Observaciones / Nota</label>
                        <textarea name="observaciones" rows="2" placeholder="ej. Día lluvioso, se vendió menos..."
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-200 focus:border-emerald-500 focus:outline-none"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-calendar-check"></i> Registrar Cierre & Generar Ingreso en Bóveda
                    </button>
                </form>
            </div>
        @endif

        <!-- Historial de Cierres -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between flex-wrap gap-3">
                <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-400"></i> Historial de Cierres
                </h2>
                <form action="{{ route('cierres.index') }}" method="GET" class="flex items-center gap-2">
                    <select name="carrito_id" onchange="this.form.submit()"
                        class="bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-xs font-bold text-white focus:border-emerald-500 focus:outline-none">
                        <option value="">Todos los Carritos</option>
                        @foreach($carritos as $car)
                            <option value="{{ $car->id }}" {{ $carritoId == $car->id ? 'selected' : '' }}>{{ $car->nombre }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="bg-slate-950/70 border-b border-slate-800 text-[10px] font-black uppercase text-slate-400">
                            <th class="py-3 px-4">Fecha</th>
                            <th class="py-3 px-4">Carrito</th>
                            <th class="py-3 px-4 text-right">Monto Estimado</th>
                            <th class="py-3 px-4 text-right">Monto Real</th>
                            <th class="py-3 px-4 text-right">Diferencia</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($cierres as $cierre)
                            <tr
                                class="{{ $cierre->inconsistente ? 'bg-rose-500/5' : '' }} hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4 font-bold text-white">
                                    {{ \Carbon\Carbon::parse($cierre->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-purple-400">{{ $cierre->carrito->nombre ?? '—' }}</td>
                                <td class="py-3 px-4 text-right font-bold text-slate-300">Bs.
                                    {{ number_format($cierre->monto_estimado, 2) }}</td>
                                <td class="py-3 px-4 text-right font-bold text-white">Bs.
                                    {{ number_format($cierre->monto_real, 2) }}</td>
                                <td
                                    class="py-3 px-4 text-right font-black {{ abs($cierre->diferencia) < 0.01 ? 'text-emerald-400' : ($cierre->diferencia > 0 ? 'text-rose-400' : 'text-cyan-400') }}">
                                    {{ $cierre->diferencia > 0 ? '-' : '+' }} Bs.
                                    {{ number_format(abs($cierre->diferencia), 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if(!$cierre->inconsistente)
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-black text-[10px]">✓
                                            CUADRA</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/30 text-rose-400 font-black text-[10px] animate-pulse">⚠
                                            INCONSISTENTE</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('cierres.destroy', $cierre->id) }}"
                                        class="text-[10px] font-bold text-rose-500 hover:underline"
                                        onclick="return confirm('¿Eliminar este cierre? También se eliminará el movimiento de Bóveda asociado.')">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500 font-bold uppercase">No hay cierres
                                    registrados aún.</td>
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
@endsection