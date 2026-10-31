@extends('layouts.app')

@section('title', 'Cierres Diarios & Liquidación de Carritos — Salteñas')

@section('content')
    <div class="space-y-6" x-data="cierreDiarioModal()" @keydown.escape.window="closeModal()">

        <!-- Header Principal -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-emerald-600 dark:text-emerald-400"></i> CIERRES DIARIOS & LIQUIDACIÓN DE CARRITOS
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase">
                    AUDITORÍA DE SALTEÑAS VENDIDAS/SOBRANTES Y LIQUIDACIÓN DE INGRESOS (EFECTIVO + QR) HACIA LA BÓVEDA CENTRAL.
                </p>
            </div>
            @if(!$carritos->isEmpty() && !$variantes->isEmpty())
                <button @click="showModal = true" type="button"
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-2 shrink-0 border border-slate-700">
                    <i class="fa-solid fa-plus-circle text-emerald-400"></i> + REGISTRO MANUAL DE EMERGENCIA
                </button>
            @endif
        </div>

        {{-- =====================================================================
             SECCIÓN 1: BANDEJA DE LIQUIDACIÓN (CIERRES REMOTOS PENDIENTES)
             Aparece destacada para que el dueño reciba el dinero y lo asiente en Bóveda
             ===================================================================== --}}
        <div class="bg-white dark:bg-slate-900 border-2 {{ $cierresPendientes->isNotEmpty() ? 'border-amber-400 dark:border-amber-500/50' : 'border-slate-200 dark:border-slate-800' }} rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-3.5 {{ $cierresPendientes->isNotEmpty() ? 'bg-amber-50 dark:bg-amber-500/10 border-b border-amber-200 dark:border-amber-500/30' : 'bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800' }} flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-inbox text-amber-600 dark:text-amber-400 text-base {{ $cierresPendientes->isNotEmpty() ? 'animate-bounce' : '' }}"></i>
                    <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">
                        BANDEJA DE LIQUIDACIÓN: CIERRES ENVIADOS POR LOS CARRITOS ({{ $cierresPendientes->count() }} PENDIENTE{{ $cierresPendientes->count() === 1 ? '' : 'S' }})
                    </h2>
                </div>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">
                    Audita las salteñas sobrantes y cuenta el efectivo antes de aprobar el ingreso a Bóveda
                </span>
            </div>

            @if($cierresPendientes->isNotEmpty())
                <div class="p-5 space-y-4">
                    @foreach($cierresPendientes as $cp)
                        <div class="bg-slate-50 dark:bg-slate-950/70 border-2 border-amber-300 dark:border-amber-500/30 rounded-xl p-5 shadow-sm space-y-4" id="cierre-card-{{ $cp->id }}">
                            <!-- Fila Superior: Datos del Carrito y Botones de Aprobación -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-slate-200 dark:border-slate-800">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-300 font-black text-xs uppercase tracking-wider">
                                            🛒 {{ strtoupper($cp->carrito->nombre ?? 'CARRITO') }}
                                        </span>
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                            Turno del {{ \Carbon\Carbon::parse($cp->fecha)->format('d/m/Y') }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 font-extrabold text-[10px] uppercase animate-pulse">
                                            ⏳ ESPERANDO LIQUIDACIÓN
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">
                                        Recibido: {{ $cp->created_at ? $cp->created_at->format('H:i') . ' hrs' : 'Hoy' }} | Origen: POS Móvil
                                    </span>
                                </div>

                                <!-- Botones de Acción (Formularios POST a rutas web oficiales) -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <form action="{{ route('cierres.aprobar', $cp->id) }}" method="POST" onsubmit="return confirm('¿Aprobar este cierre e ingresar Bs. {{ number_format($cp->monto_real, 2) }} a la Bóveda Central?')">
                                        @csrf
                                        <button type="submit"
                                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition-all flex items-center gap-1.5">
                                            <i class="fa-solid fa-check-circle"></i> APROBAR E INGRESAR A BÓVEDA
                                        </button>
                                    </form>

                                    <form action="{{ route('cierres.rechazar', $cp->id) }}" method="POST" onsubmit="return confirm('¿Rechazar u observar este cierre? No ingresará a Bóveda.')">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider transition-all flex items-center gap-1.5">
                                            <i class="fa-solid fa-ban"></i> OBSERVAR
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Fila Media: Arqueo Financiero vs Auditoría de Piezas -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <!-- Columna Dinero (5 cols) -->
                                <div class="md:col-span-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 space-y-3">
                                    <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider block">
                                        💰 ARQUEO FINANCIERO A RECIBIR
                                    </span>

                                    <!-- Efectivo a Entregar -->
                                    <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl flex items-center justify-between">
                                        <div>
                                            <span class="text-[10px] font-black text-emerald-800 dark:text-emerald-400 uppercase block">
                                                💵 EFECTIVO EN MANO
                                            </span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold">El vendedor debe entregarte:</span>
                                        </div>
                                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                                            Bs. {{ number_format($cp->dinero_efectivo, 2) }}
                                        </span>
                                    </div>

                                    <!-- Pagos QR -->
                                    <div class="p-3 bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/30 rounded-xl flex items-center justify-between">
                                        <div>
                                            <span class="text-[10px] font-black text-purple-800 dark:text-purple-400 uppercase block">
                                                📱 COBROS POR QR
                                            </span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold">Directo a cuenta bancaria:</span>
                                        </div>
                                        <span class="text-xl font-black text-purple-600 dark:text-purple-400">
                                            Bs. {{ number_format($cp->dinero_qr, 2) }}
                                        </span>
                                    </div>

                                    <!-- Total Declarado -->
                                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                        <span class="text-xs font-black uppercase text-slate-900 dark:text-white">TOTAL COBRADO:</span>
                                        <span class="text-lg font-black text-slate-900 dark:text-white">
                                            Bs. {{ number_format($cp->monto_real, 2) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Columna Salteñas (7 cols) -->
                                <div class="md:col-span-7 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 space-y-2">
                                    <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider block">
                                        🥟 AUDITORÍA DE SALTEÑAS (DESPACHADAS VS RENDIDAS)
                                    </span>

                                    @if($cp->detalles && $cp->detalles->isNotEmpty())
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left text-xs border-collapse">
                                                <thead>
                                                    <tr class="border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-400">
                                                        <th class="py-1.5">Variedad</th>
                                                        <th class="py-1.5 text-center">Entregadas</th>
                                                        <th class="py-1.5 text-center text-emerald-600 dark:text-emerald-400">Vendidas</th>
                                                        <th class="py-1.5 text-center text-amber-600 dark:text-amber-400">Sobrantes</th>
                                                        <th class="py-1.5 text-right">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                                                    @php $cuadraTodo = true; @endphp
                                                    @foreach($cp->detalles as $det)
                                                        @php
                                                            $justificado = (int)$det->cantidad_vendida_normal + (int)$det->cantidad_sobrante;
                                                            $cuadra = ((int)$det->cantidad_entregada === $justificado);
                                                            if (!$cuadra) $cuadraTodo = false;
                                                        @endphp
                                                        <tr>
                                                            <td class="py-2 font-bold text-slate-900 dark:text-white">
                                                                {{ $det->variante->nombre ?? 'Salteña' }}
                                                            </td>
                                                            <td class="py-2 text-center text-slate-600 dark:text-slate-300 font-bold">
                                                                {{ $det->cantidad_entregada }}
                                                            </td>
                                                            <td class="py-2 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                                                {{ $det->cantidad_vendida_normal }}
                                                            </td>
                                                            <td class="py-2 text-center font-bold text-amber-600 dark:text-amber-400">
                                                                {{ $det->cantidad_sobrante }}
                                                            </td>
                                                            <td class="py-2 text-right">
                                                                @if($cuadra)
                                                                    <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400">✓ CUADRA</span>
                                                                @else
                                                                    <span class="text-[10px] font-black text-rose-600 dark:text-rose-400">⚠ DIF: {{ (int)$det->cantidad_entregada - $justificado }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-xs text-slate-400 py-3 text-center">Sin desglose de variantes disponible</div>
                                    @endif

                                    <!-- Observaciones del Vendedor -->
                                    @if($cp->observaciones)
                                        <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-300">
                                            <span class="font-bold text-slate-400 uppercase text-[10px]">Nota del vendedor:</span>
                                            <span class="italic">{{ $cp->observaciones }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-slate-400 space-y-2">
                    <i class="fa-solid fa-circle-check text-3xl text-emerald-500/40"></i>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        No hay cierres pendientes por liquidar.
                    </p>
                    <p class="text-[11px] text-slate-400 max-w-md mx-auto">
                        Cuando un carrito finalice su jornada en el POS móvil, aparecerá aquí automáticamente para que audites las salteñas y confirmes el efectivo antes de mandarlo a Bóveda.
                    </p>
                </div>
            @endif
        </div>

        {{-- =====================================================================
             SECCIÓN 2: HISTORIAL CONSOLIDADO DE CIERRES
             ===================================================================== --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> HISTORIAL CONSOLIDADO DE CIERRES
                    </h2>
                    @if($carritoId || $fechaInicio || $fechaFin || request('per_page'))
                        <a href="{{ route('cierres.index') }}" class="text-[10px] font-bold text-rose-600 dark:text-rose-400 hover:underline uppercase flex items-center gap-1">
                            <i class="fa-solid fa-rotate-left"></i> LIMPIAR FILTROS
                        </a>
                    @endif
                </div>

                <!-- Barra de Filtros -->
                <form action="{{ route('cierres.index') }}" method="GET" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 pt-1">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-0.5">CARRITO</label>
                        <select name="carrito_id"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase">
                            <option value="" class="uppercase">TODOS</option>
                            @foreach($carritos as $car)
                                <option value="{{ $car->id }}" {{ $carritoId == $car->id ? 'selected' : '' }} class="uppercase">{{ strtoupper($car->nombre) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-0.5">DESDE FECHA</label>
                        <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-0.5">HASTA FECHA</label>
                        <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-0.5">FILAS</label>
                        <select name="per_page"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 FILAS</option>
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 FILAS</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 FILAS</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 FILAS</option>
                            <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>TODOS</option>
                        </select>
                    </div>

                    <div class="col-span-2 sm:col-span-1 lg:col-span-2 flex items-end gap-2">
                        <button type="submit"
                            class="w-full py-1.5 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase shadow-sm transition-all flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-filter"></i> FILTRAR
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                            <th class="py-2.5 px-4">FECHA</th>
                            <th class="py-2.5 px-4">CARRITO</th>
                            <th class="py-2.5 px-4 text-center">DESGLOSE (EF. / QR)</th>
                            <th class="py-2.5 px-4 text-right">MONTO REAL</th>
                            <th class="py-2.5 px-4 text-right">MONTO ESTIMADO</th>
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
                                <td class="py-2.5 px-4 font-bold text-purple-600 dark:text-purple-400 uppercase">
                                    {{ strtoupper($cierre->carrito->nombre ?? '—') }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold">
                                        <span class="text-emerald-700 dark:text-emerald-400">Ef: Bs. {{ number_format($cierre->dinero_efectivo, 2) }}</span>
                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                        <span class="text-purple-700 dark:text-purple-400">QR: Bs. {{ number_format($cierre->dinero_qr, 2) }}</span>
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-black text-slate-900 dark:text-white uppercase">
                                    BS. {{ number_format($cierre->monto_real, 2) }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-600 dark:text-slate-300 uppercase">
                                    BS. {{ number_format($cierre->monto_estimado, 2) }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-black {{ abs($cierre->diferencia) < 0.01 ? 'text-emerald-600 dark:text-emerald-400' : ($cierre->diferencia > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-cyan-600 dark:text-cyan-400') }}">
                                    {{ $cierre->diferencia > 0 ? '-' : '+' }} BS. {{ number_format(abs($cierre->diferencia), 2) }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    @if($cierre->estado === 'aprobado')
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-400 font-black text-[10px] uppercase">
                                            ✓ EN BÓVEDA
                                        </span>
                                    @elseif($cierre->estado === 'pendiente')
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-400 font-black text-[10px] uppercase animate-pulse">
                                            ⏳ PENDIENTE
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-800 dark:text-rose-400 font-black text-[10px] uppercase">
                                            ❌ RECHAZADO
                                        </span>
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
                                <td colspan="8" class="py-8 text-center text-slate-400 font-bold uppercase">NO HAY CIERRES REGISTRADOS CON LOS FILTROS SELECCIONADOS.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
                @if($cierres instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $cierres->links() }}
                @else
                    <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center py-1">
                        MOSTRANDO TODOS LOS REGISTROS ({{ count($cierres) }})
                    </div>
                @endif
            </div>
        </div>

        {{-- =====================================================================
             MODAL: REGISTRO MANUAL DE EMERGENCIA
             ===================================================================== --}}
        @if(!$carritos->isEmpty() && !$variantes->isEmpty())
            <div x-show="showModal" x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showModal"
                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        @click="closeModal()"
                        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showModal"
                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-200 dark:border-slate-800">

                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <h2 class="text-xs font-black text-slate-900 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-plus-circle text-emerald-500"></i> REGISTRAR CIERRE MANUAL DE EMERGENCIA
                            </h2>
                            <button @click="closeModal()" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <form id="formCierreDiario" action="{{ route('cierres.store') }}" method="POST" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto"
                            @submit="if (hayInconsistencias) { $event.preventDefault(); alert('No se permite guardar un cierre con inconsistencias. Corrija las cantidades o el monto.'); }">
                            @csrf

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div class="col-span-2">
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">CARRITO <span class="text-amber-500">*</span></label>
                                    <select name="carrito_id" required
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase">
                                        <option value="" class="uppercase">SELECCIONAR CARRITO...</option>
                                        @foreach($carritos as $car)
                                            <option value="{{ $car->id }}" class="uppercase">{{ strtoupper($car->nombre) }} @if($car->zona)({{ strtoupper($car->zona) }})@endif</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">FECHA <span class="text-amber-500">*</span></label>
                                    <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 focus:border-emerald-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">EFECTIVO (BS.)</label>
                                    <input type="number" step="any" name="dinero_efectivo" placeholder="0.00"
                                        @input="recalcularMontoReal()"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 focus:border-emerald-500 focus:outline-none uppercase">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">QR (BS.)</label>
                                    <input type="number" step="any" name="dinero_qr" placeholder="0.00"
                                        @input="recalcularMontoReal()"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-purple-600 dark:text-purple-400 focus:border-emerald-500 focus:outline-none uppercase">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">MONTO TOTAL COBRADO (BS.) <span class="text-rose-500">*</span></label>
                                    <input type="number" step="any" name="monto_real" id="monto_real_input" required placeholder="0.00"
                                        @input="recalcular()"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-black text-rose-600 dark:text-rose-400 focus:border-emerald-500 focus:outline-none uppercase">
                                </div>
                            </div>

                            <!-- Detalle por Variante -->
                            @foreach($variantes as $i => $var)
                                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg p-4 space-y-3"
                                    data-variante-nombre="{{ strtoupper($var->nombre) }}"
                                    data-variante-precio="{{ $var->precio_venta }}">
                                    <div class="flex items-center justify-between">
                                        <span class="font-black text-slate-900 dark:text-amber-400 uppercase">🥟 {{ strtoupper($var->nombre) }}</span>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">PRECIO: BS. {{ $var->precio_venta }}</span>
                                    </div>
                                    <input type="hidden" name="detalles[{{ $i }}][variante_id]" value="{{ $var->id }}">

                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">ENTREGADAS</label>
                                            <input type="number" name="detalles[{{ $i }}][cantidad_entregada]" min="0" placeholder="0"
                                                @input="recalcular()"
                                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-black text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none"
                                                required>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">VENDIDAS</label>
                                            <input type="number" name="detalles[{{ $i }}][cantidad_vendida_normal]" min="0" placeholder="0"
                                                @input="recalcular()"
                                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-black text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none"
                                                required>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">SOBRANTES</label>
                                            <input type="number" name="detalles[{{ $i }}][cantidad_sobrante]" min="0" placeholder="0"
                                                @input="recalcular()"
                                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-black text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none"
                                                required>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">OBSERVACIONES</label>
                                <textarea name="observaciones" rows="2" placeholder="Nota o justificación de inconsistencias..."
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-medium text-slate-900 dark:text-slate-200 focus:border-emerald-500 focus:outline-none uppercase"></textarea>
                            </div>

                            <!-- Panel de verificación interactiva -->
                            <div x-show="resumen.length > 0" x-cloak class="space-y-2">
                                <p class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">📊 VERIFICACIÓN EN TIEMPO REAL</p>

                                <template x-for="fila in resumen" :key="fila.nombre">
                                    <div :class="fila.ok ? 'border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10' : 'border-rose-300 dark:border-rose-500/40 bg-rose-50 dark:bg-rose-500/10'"
                                        class="rounded-lg border px-3 py-2 flex items-center justify-between gap-2 flex-wrap text-xs">
                                        <span class="font-black uppercase" :class="fila.ok ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300'" x-text="'🥟 ' + fila.nombre"></span>
                                        <div class="flex items-center gap-2 font-bold text-slate-600 dark:text-slate-400">
                                            <span x-text="'Entregadas: ' + fila.entregada"></span>
                                            <span>=</span>
                                            <span x-text="'Vendidas: ' + fila.vendida"></span>
                                            <span>+</span>
                                            <span x-text="'Sobrantes: ' + fila.sobrante"></span>
                                            <span x-show="!fila.ok" class="font-black text-rose-700 dark:text-rose-400" x-text="'⚠ DIFF: ' + (fila.entregada - fila.vendida - fila.sobrante)"></span>
                                            <span x-show="fila.ok" class="text-emerald-700 dark:text-emerald-400 font-black">✓ OK</span>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="montoResumen.visible"
                                    :class="montoResumen.ok ? 'border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10' : 'border-rose-300 dark:border-rose-500/40 bg-rose-50 dark:bg-rose-500/10'"
                                    class="rounded-lg border px-3 py-2 flex items-center justify-between gap-2 flex-wrap text-xs">
                                    <span class="font-black uppercase" :class="montoResumen.ok ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300'">💰 MONTO TOTAL</span>
                                    <div class="flex items-center gap-2 font-bold text-slate-600 dark:text-slate-400">
                                        <span x-text="'Ingresado: BS. ' + montoResumen.real"></span>
                                        <span>|</span>
                                        <span x-text="'Estimado: BS. ' + montoResumen.estimado"></span>
                                        <span x-show="!montoResumen.ok" class="font-black text-rose-700 dark:text-rose-400" x-text="'⚠ DIFF: BS. ' + montoResumen.diff"></span>
                                        <span x-show="montoResumen.ok" class="text-emerald-700 dark:text-emerald-400 font-black">✓ OK</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 flex items-center justify-end gap-2">
                                <button type="button" @click="closeModal()"
                                    class="py-2.5 px-4 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs uppercase">
                                    CANCELAR
                                </button>
                                <button type="submit"
                                    :disabled="hayInconsistencias"
                                    :class="hayInconsistencias ? 'bg-slate-300 dark:bg-slate-800 text-slate-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-md'"
                                    class="py-2.5 px-5 rounded-lg font-black text-xs uppercase tracking-wider transition-all">
                                    GUARDAR CIERRE MANUAL
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection

@push('scripts')
<script>
function cierreDiarioModal() {
    return {
        showModal: false,
        resumen: [],
        montoResumen: { visible: false, ok: true, real: '0.00', estimado: '0.00', diff: '0.00' },
        hayInconsistencias: false,

        closeModal() {
            this.showModal = false;
            this.resumen = [];
            this.montoResumen = { visible: false, ok: true, real: '0.00', estimado: '0.00', diff: '0.00' };
            this.hayInconsistencias = false;
        },

        recalcularMontoReal() {
            const form = document.getElementById('formCierreDiario');
            if (!form) return;
            const ef = parseFloat(form.querySelector('[name="dinero_efectivo"]')?.value) || 0;
            const qr = parseFloat(form.querySelector('[name="dinero_qr"]')?.value) || 0;
            const inputReal = document.getElementById('monto_real_input');
            if (inputReal && (ef > 0 || qr > 0)) {
                inputReal.value = (ef + qr).toFixed(2);
            }
            this.recalcular();
        },

        recalcular() {
            const form = document.getElementById('formCierreDiario');
            if (!form) return;

            const bloques = form.querySelectorAll('[data-variante-nombre]');
            let montoEstimado = 0;
            let inconsistente = false;
            const nuevasFila = [];

            bloques.forEach(bloque => {
                const nombre    = bloque.dataset.varianteNombre;
                const precio    = parseFloat(bloque.dataset.variantePrecio) || 0;
                const entregada = parseInt(bloque.querySelector('[name$="[cantidad_entregada]"]')?.value) || 0;
                const vendida   = parseInt(bloque.querySelector('[name$="[cantidad_vendida_normal]"]')?.value) || 0;
                const sobrante  = parseInt(bloque.querySelector('[name$="[cantidad_sobrante]"]')?.value) || 0;

                const totalJustificado = vendida + sobrante;

                if (entregada > 0 || vendida > 0 || sobrante > 0) {
                    const ok = (entregada === totalJustificado);
                    if (!ok) inconsistente = true;
                    nuevasFila.push({
                        nombre,
                        entregada,
                        vendida,
                        sobrante,
                        ok
                    });
                    montoEstimado += (vendida * precio);
                }
            });

            this.resumen = nuevasFila;

            const montoReal = parseFloat(form.querySelector('[name="monto_real"]')?.value) || 0;
            if (montoReal > 0 || montoEstimado > 0) {
                const diff = Math.abs(montoReal - montoEstimado);
                const montoOk = diff <= 0.01;
                if (!montoOk) inconsistente = true;
                this.montoResumen = {
                    visible: true,
                    ok: montoOk,
                    real: montoReal.toFixed(2),
                    estimado: montoEstimado.toFixed(2),
                    diff: (montoReal - montoEstimado).toFixed(2)
                };
            } else {
                this.montoResumen = { visible: false, ok: true, real: '0.00', estimado: '0.00', diff: '0.00' };
            }

            this.hayInconsistencias = inconsistente;
        }
    };
}
</script>
@endpush
