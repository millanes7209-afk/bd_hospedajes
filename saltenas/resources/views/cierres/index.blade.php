@extends('layouts.app')

@section('title', 'CIERRE DIARIO — SALTEÑAS')

@section('content')
    <div class="space-y-6">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm">
            <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-emerald-600 dark:text-emerald-400"></i> REGISTRAR CIERRE DIARIO POR CARRITO
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase">
                EL SISTEMA VERIFICA AUTOMÁTICAMENTE SI LA CANTIDAD ENTREGADA CUADRA CON LO VENDIDO + SOBRANTE, Y SI EL MONTO ESTIMADO COINCIDE CON EL MONTO REAL ENTREGADO.
            </p>
        </div>

        @if($carritos->isEmpty() || $variantes->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-500/30 rounded-xl p-5 space-y-4 shadow-sm">
                <div class="flex items-center gap-3 text-amber-600 dark:text-amber-400">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    <h2 class="text-xs font-black uppercase tracking-wider">CONFIGURACIÓN PREVIA REQUERIDA PARA REGISTRAR CIERRES</h2>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 uppercase">
                    PARA PODER HABILITAR EL FORMULARIO DE CIERRE DIARIO, EL SISTEMA NECESITA TENER REGISTRADO AL MENOS UN <strong class="text-slate-900 dark:text-white">CARRITO</strong> Y UNA <strong class="text-slate-900 dark:text-white">VARIANTE DE SALTEÑA</strong>.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="p-4 rounded-xl border {{ $carritos->count() ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-400' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-xs uppercase flex items-center gap-2">
                                <i class="fa-solid {{ $carritos->count() ? 'fa-circle-check text-emerald-600 dark:text-emerald-400' : 'fa-circle-xmark text-rose-600 dark:text-rose-400' }}"></i>
                                1. CARRITOS (PUNTOS DE VENTA)
                            </span>
                            <span class="text-xs font-extrabold uppercase px-2 py-0.5 rounded {{ $carritos->count() ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-500/20 text-rose-800 dark:text-rose-300' }}">
                                {{ $carritos->count() }} REGISTRADOS
                            </span>
                        </div>
                        @if(!$carritos->count())
                            <a href="{{ route('carritos.index') }}" class="mt-3 inline-block px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-black text-[11px] uppercase shadow-sm">+ CREAR CARRITO</a>
                        @endif
                    </div>

                    <div class="p-4 rounded-xl border {{ $variantes->count() ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-400' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-xs uppercase flex items-center gap-2">
                                <i class="fa-solid {{ $variantes->count() ? 'fa-circle-check text-emerald-600 dark:text-emerald-400' : 'fa-circle-xmark text-rose-600 dark:text-rose-400' }}"></i>
                                2. VARIANTES DE SALTEÑA
                            </span>
                            <span class="text-xs font-extrabold uppercase px-2 py-0.5 rounded {{ $variantes->count() ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-500/20 text-rose-800 dark:text-rose-300' }}">
                                {{ $variantes->count() }} REGISTRADAS
                            </span>
                        </div>
                        @if(!$variantes->count())
                            <a href="{{ route('productos.index', ['tab' => 'variantes']) }}" class="mt-3 inline-block px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-black text-[11px] uppercase shadow-sm">+ CREAR VARIANTE DE SALTEÑA</a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- Formulario Cierre Diario -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-4 shadow-sm">
                <h2 class="text-xs font-black text-slate-900 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <i class="fa-solid fa-plus-circle text-emerald-500"></i> NUEVO CIERRE DIARIO
                </h2>

                <form action="{{ route('cierres.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Carrito + Fecha + Temperatura -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">CARRITO / PUNTO DE VENTA <span class="text-amber-500">*</span></label>
                            <select name="carrito_id" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase">
                                <option value="" class="uppercase">SELECCIONAR CARRITO...</option>
                                @foreach($carritos as $car)
                                    <option value="{{ $car->id }}" class="uppercase">{{ strtoupper($car->nombre) }} @if($car->zona)({{ strtoupper($car->zona) }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">FECHA DEL CIERRE <span class="text-amber-500">*</span></label>
                            <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">MONTO REAL ENTREGADO (BS.) <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="monto_real" required placeholder="EJ. 350.50"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-black text-rose-600 dark:text-rose-400 focus:border-emerald-500 focus:outline-none uppercase">
                        </div>
                    </div>

                    <!-- Detalle por Variante -->
                    @foreach($variantes as $i => $var)
                        <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-black text-slate-900 dark:text-amber-400 uppercase">🥟 {{ strtoupper($var->nombre) }}</span>
                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">PRECIO NORMAL: <strong class="text-slate-900 dark:text-white">BS. {{ $var->precio_venta }}</strong></span>
                            </div>
                            <input type="hidden" name="detalles[{{ $i }}][variante_id]" value="{{ $var->id }}">

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">CANTIDAD ENTREGADA ☝</label>
                                    <input type="number" name="detalles[{{ $i }}][cantidad_entregada]" min="0" placeholder="EJ. 100"
                                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-black text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">VENDIDAS NORMAL</label>
                                    <input type="number" name="detalles[{{ $i }}][cantidad_vendida_normal]" min="0" placeholder="EJ. 10"
                                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-black text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">SOBRANTES</label>
                                    <input type="number" name="detalles[{{ $i }}][cantidad_sobrante]" min="0" placeholder="EJ. 15"
                                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-black text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase"
                                        required>
                                </div>
                            </div>

                            <!-- Sección de Promociones -->
                            @if($var->promociones->count())
                                <div class="bg-indigo-50 dark:bg-indigo-500/5 border border-indigo-200 dark:border-indigo-500/20 rounded-lg p-3 space-y-2">
                                    <span class="text-[10px] font-bold text-indigo-700 dark:text-indigo-400 uppercase block">🏷️ PAQUETES / COMBOS VENDIDOS</span>
                                    @foreach($var->promociones as $j => $promo)
                                        <div class="flex items-center gap-3">
                                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 flex-1 uppercase">{{ strtoupper($promo->nombre) }} ({{ $promo->unidades_por_paquete }} UDS. X BS. {{ $promo->precio_paquete }})</label>
                                            <input type="hidden" name="detalles[{{ $i }}][promociones][{{ $j }}][promocion_id]" value="{{ $promo->id }}">
                                            <div class="w-28">
                                                <input type="number" min="0" value="0"
                                                    name="detalles[{{ $i }}][promociones][{{ $j }}][paquetes_vendidos]"
                                                    placeholder="# PAQUETES"
                                                    class="w-full bg-white dark:bg-slate-900 border border-indigo-300 dark:border-indigo-500/30 rounded-lg px-3 py-1.5 text-xs font-black text-indigo-700 dark:text-indigo-400 focus:border-indigo-500 focus:outline-none text-center uppercase">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">OBSERVACIONES / NOTA</label>
                        <textarea name="observaciones" rows="2" placeholder="EJ. DÍA LLUVIOSO, SE VENDIÓ MENOS..."
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-medium text-slate-900 dark:text-slate-200 focus:border-emerald-500 focus:outline-none uppercase"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-calendar-check"></i> REGISTRAR CIERRE & GENERAR INGRESO EN BÓVEDA
                    </button>
                </form>
            </div>
        @endif

        <!-- Historial de Cierres -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
                <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> HISTORIAL DE CIERRES
                </h2>
                <form action="{{ route('cierres.index') }}" method="GET" class="flex items-center gap-2">
                    <select name="carrito_id" onchange="this.form.submit()"
                        class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase">
                        <option value="" class="uppercase">TODOS LOS CARRITOS</option>
                        @foreach($carritos as $car)
                            <option value="{{ $car->id }}" {{ $carritoId == $car->id ? 'selected' : '' }} class="uppercase">{{ strtoupper($car->nombre) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                            <th class="py-2.5 px-4">FECHA</th>
                            <th class="py-2.5 px-4">CARRITO</th>
                            <th class="py-2.5 px-4 text-right">MONTO ESTIMADO</th>
                            <th class="py-2.5 px-4 text-right">MONTO REAL</th>
                            <th class="py-2.5 px-4 text-right">DIFERENCIA</th>
                            <th class="py-2.5 px-4 text-center">ESTADO</th>
                            <th class="py-2.5 px-4 text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($cierres as $cierre)
                            <tr class="{{ $cierre->inconsistente ? 'bg-rose-50 dark:bg-rose-500/5' : '' }} hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white uppercase">
                                    {{ \Carbon\Carbon::parse($cierre->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-4 font-bold text-purple-600 dark:text-purple-400 uppercase">{{ strtoupper($cierre->carrito->nombre ?? '—') }}</td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-600 dark:text-slate-300 uppercase">BS. {{ number_format($cierre->monto_estimado, 2) }}</td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-900 dark:text-white uppercase">BS. {{ number_format($cierre->monto_real, 2) }}</td>
                                <td class="py-2.5 px-4 text-right font-black {{ abs($cierre->diferencia) < 0.01 ? 'text-emerald-600 dark:text-emerald-400' : ($cierre->diferencia > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-cyan-600 dark:text-cyan-400') }}">
                                    {{ $cierre->diferencia > 0 ? '-' : '+' }} BS. {{ number_format(abs($cierre->diferencia), 2) }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    @if(!$cierre->inconsistente)
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-400 font-black text-[10px] uppercase">✓ CUADRA</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-800 dark:text-rose-400 font-black text-[10px] animate-pulse uppercase">⚠ INCONSISTENTE</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 text-right">
                                    <a href="{{ route('cierres.destroy', $cierre->id) }}"
                                        class="text-[10px] font-bold text-rose-600 dark:text-rose-500 hover:underline uppercase"
                                        onclick="return confirm('¿Eliminar este cierre? También se eliminará el movimiento de Bóveda asociado.')">
                                        ELIMINAR
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 font-bold uppercase">NO HAY CIERRES REGISTRADOS AÚN.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
                {{ $cierres->links() }}
            </div>
        </div>
    </div>
@endsection