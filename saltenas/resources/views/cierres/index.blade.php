@extends('layouts.app')

@section('title', 'CIERRE DIARIO — SALTEÑAS')

@section('content')
    <div class="space-y-6" x-data="cierreDiarioModal()" @keydown.escape.window="closeModal()">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-emerald-600 dark:text-emerald-400"></i> CIERRES DIARIOS POR CARRITO
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase">
                    EL SISTEMA VERIFICA AUTOMÁTICAMENTE SI LA CANTIDAD ENTREGADA CUADRA CON LO VENDIDO + SOBRANTE, Y GENERA EL INGRESO EN BÓVEDA.
                </p>
            </div>
            @if(!$carritos->isEmpty() && !$variantes->isEmpty())
                <button @click="showModal = true" type="button"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition-all flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus-circle"></i> + REGISTRAR NUEVO CIERRE
                </button>
            @endif
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
            </div>
        @else
            <!-- MODAL FORMULARIO DE CIERRE DIARIO -->
            <div x-show="showModal" x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Backdrop -->
                    <div x-show="showModal"
                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        @click="closeModal()"
                        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                    <!-- Centering trick -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Modal Body -->
                    <div x-show="showModal"
                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-200 dark:border-slate-800">
                        
                        <!-- Modal Header -->
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <h2 class="text-xs font-black text-slate-900 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-plus-circle text-emerald-500"></i> REGISTRAR NUEVO CIERRE DIARIO
                            </h2>
                            <button @click="closeModal()" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <!-- Formulario Inside Modal -->
                        <form id="formCierreDiario" action="{{ route('cierres.store') }}" method="POST" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                            @csrf

                            <!-- Carrito + Fecha + Temperaturas -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div class="col-span-2">
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">CARRITO / PUNTO DE VENTA <span class="text-amber-500">*</span></label>
                                    <select name="carrito_id" required
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase">
                                        <option value="" class="uppercase">SELECCIONAR CARRITO...</option>
                                        @foreach($carritos as $car)
                                            <option value="{{ $car->id }}" class="uppercase">{{ strtoupper($car->nombre) }} @if($car->zona)({{ strtoupper($car->zona) }})@endif</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">FECHA DEL CIERRE <span class="text-amber-500">*</span></label>
                                    <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 focus:border-emerald-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">TEMP. MIN (°C)</label>
                                    <input type="number" step="any" name="temp_min" placeholder="EJ. 12.5"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-cyan-600 dark:text-cyan-400 focus:border-emerald-500 focus:outline-none uppercase">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">TEMP. MÁX (°C)</label>
                                    <input type="number" step="any" name="temp_max" placeholder="EJ. 24.0"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-bold text-rose-600 dark:text-rose-400 focus:border-emerald-500 focus:outline-none uppercase">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">MONTO REAL ENTREGADO (BS.) <span class="text-rose-500">*</span></label>
                                    <input type="number" step="any" name="monto_real" required placeholder="EJ. 350.50"
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
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">PRECIO NORMAL: <strong class="text-slate-900 dark:text-white">BS. {{ $var->precio_venta }}</strong></span>
                                    </div>
                                    <input type="hidden" name="detalles[{{ $i }}][variante_id]" value="{{ $var->id }}">

                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">SALTEÑAS ENTREGADAS 🥟</label>
                                            <input type="number" name="detalles[{{ $i }}][cantidad_entregada]" min="0" placeholder="EJ. 100"
                                                @input="recalcular()"
                                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-black text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase"
                                                required>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">VENDIDAS NORMAL</label>
                                            <input type="number" name="detalles[{{ $i }}][cantidad_vendida_normal]" min="0" placeholder="EJ. 10"
                                                @input="recalcular()"
                                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-black text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none uppercase"
                                                required>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">SOBRANTES</label>
                                            <input type="number" name="detalles[{{ $i }}][cantidad_sobrante]" min="0" placeholder="EJ. 15"
                                                @input="recalcular()"
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

                            <!-- Panel de estado en tiempo real -->
                            <div x-show="resumen.length > 0" x-cloak class="space-y-2">
                                <p class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">📊 VERIFICACIÓN EN TIEMPO REAL</p>

                                <!-- Filas por variante -->
                                <template x-for="fila in resumen" :key="fila.nombre">
                                    <div :class="fila.ok ? 'border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10' : 'border-rose-300 dark:border-rose-500/40 bg-rose-50 dark:bg-rose-500/10'"
                                        class="rounded-lg border px-3 py-2 flex items-center justify-between gap-2 flex-wrap">
                                        <span class="text-[11px] font-black uppercase"
                                            :class="fila.ok ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300'"
                                            x-text="'🥟 ' + fila.nombre"></span>
                                        <div class="flex items-center gap-3 text-[11px] font-bold">
                                            <span class="text-slate-600 dark:text-slate-400"
                                                x-text="'Entregadas: ' + fila.entregada"></span>
                                            <span class="text-slate-500">=</span>
                                            <span class="text-slate-600 dark:text-slate-400"
                                                x-text="'Vendidas: ' + fila.vendida"></span>
                                            <span class="text-slate-500">+</span>
                                            <span class="text-slate-600 dark:text-slate-400"
                                                x-text="'Sobrantes: ' + fila.sobrante"></span>
                                            <span x-show="!fila.ok" class="font-black text-rose-700 dark:text-rose-400"
                                                x-text="'⚠ DIFF: ' + (fila.entregada - fila.vendida - fila.sobrante)"></span>
                                            <span x-show="fila.ok" class="text-emerald-700 dark:text-emerald-400 font-black">✓ OK</span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Fila de monto -->
                                <div x-show="montoResumen.visible"
                                    :class="montoResumen.ok ? 'border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10' : 'border-rose-300 dark:border-rose-500/40 bg-rose-50 dark:bg-rose-500/10'"
                                    class="rounded-lg border px-3 py-2 flex items-center justify-between gap-2 flex-wrap">
                                    <span class="text-[11px] font-black uppercase"
                                        :class="montoResumen.ok ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300'">💰 MONTO</span>
                                    <div class="flex items-center gap-3 text-[11px] font-bold text-slate-600 dark:text-slate-400">
                                        <span x-text="'Ingresado: BS. ' + montoResumen.real"></span>
                                        <span class="text-slate-500">|</span>
                                        <span x-text="'Estimado: BS. ' + montoResumen.estimado"></span>
                                        <span x-show="!montoResumen.ok" class="font-black text-rose-700 dark:text-rose-400"
                                            x-text="'⚠ DIFF: BS. ' + montoResumen.diff"></span>
                                        <span x-show="montoResumen.ok" class="text-emerald-700 dark:text-emerald-400 font-black">✓ OK</span>
                                    </div>
                                </div>

                                <!-- Aviso si hay inconsistencias -->
                                <div x-show="hayInconsistencias" class="rounded-lg bg-amber-100 dark:bg-amber-500/15 border border-amber-300 dark:border-amber-500/40 px-3 py-2 text-[11px] font-bold text-amber-800 dark:text-amber-300 flex items-center gap-2">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    HAY INCONSISTENCIAS. PUEDE GUARDAR DE TODAS FORMAS, PERO QUEDARÁ MARCADO COMO INCONSISTENTE EN EL HISTORIAL.
                                </div>
                            </div>

                            <div class="pt-2 flex items-center justify-end gap-2">
                                <button type="button" @click="closeModal()"
                                    class="py-2.5 px-4 rounded-lg bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs uppercase transition-all">
                                    CANCELAR
                                </button>
                                <button type="submit"
                                    :class="hayInconsistencias ? 'bg-amber-500 hover:bg-amber-400' : 'bg-emerald-600 hover:bg-emerald-500'"
                                    class="py-2.5 px-5 rounded-lg text-white font-black text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-2">
                                    <i class="fa-solid" :class="hayInconsistencias ? 'fa-triangle-exclamation' : 'fa-calendar-check'"></i>
                                    <span x-text="hayInconsistencias ? 'GUARDAR CON INCONSISTENCIA' : 'GUARDAR CIERRE'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Historial de Cierres -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> HISTORIAL DE CIERRES
                    </h2>
                    @if($carritoId || $fechaInicio || $fechaFin || request('per_page'))
                        <a href="{{ route('cierres.index') }}" class="text-[10px] font-bold text-rose-600 dark:text-rose-400 hover:underline uppercase flex items-center gap-1">
                            <i class="fa-solid fa-rotate-left"></i> LIMPIAR FILTROS
                        </a>
                    @endif
                </div>

                <!-- Barra de Filtros Completa -->
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
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 FILAS</option>
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
                            <th class="py-2.5 px-4 text-center">TEMP. MIN / MÁX</th>
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
                                <td class="py-2.5 px-4 text-center font-bold text-slate-600 dark:text-slate-300 uppercase">
                                    @if($cierre->temp_min !== null || $cierre->temp_max !== null)
                                        <span class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-[11px]">
                                            🌡️ <span class="text-cyan-600 dark:text-cyan-400">{{ $cierre->temp_min !== null ? number_format($cierre->temp_min, 1) . '°C' : '—' }}</span>
                                            /
                                            <span class="text-rose-600 dark:text-rose-400">{{ $cierre->temp_max !== null ? number_format($cierre->temp_max, 1) . '°C' : '—' }}</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-normal text-[11px]">—</span>
                                    @endif
                                </td>
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

                // Solo mostramos la fila si al menos hay un valor distinto de 0
                if (entregada > 0 || vendida > 0 || sobrante > 0) {
                    const ok = entregada === (vendida + sobrante);
                    if (!ok) inconsistente = true;
                    nuevasFila.push({ nombre, entregada, vendida, sobrante, ok });
                    montoEstimado += vendida * precio;
                }
            });

            this.resumen = nuevasFila;

            // Chequeo de monto
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
