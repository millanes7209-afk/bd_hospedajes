@extends('layouts.app')

@section('title', 'Dashboard — Sistema Salteñas')

@section('content')
    <div class="space-y-6">

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Bóveda Saldo -->
            <a href="{{ route('boveda.index') }}"
                class="bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-500/30 hover:border-amber-500 dark:hover:border-amber-500/60 rounded-xl p-5 flex items-center gap-4 group transition-all shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-200 dark:group-hover:bg-amber-500/20 transition-all">
                    <i class="fa-solid fa-vault text-amber-600 dark:text-amber-400 text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block">SALDO BÓVEDA</span>
                    <span class="text-2xl font-black {{ $saldoBoveda >= 0 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400' }} tracking-tight">
                        Bs. {{ number_format($saldoBoveda, 2) }}
                    </span>
                </div>
            </a>

            <!-- Carritos Activos -->
            <a href="{{ route('carritos.index') }}"
                class="bg-white dark:bg-slate-900 border border-purple-300 dark:border-purple-500/30 hover:border-purple-500 dark:hover:border-purple-500/60 rounded-xl p-5 flex items-center gap-4 group transition-all shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-500/10 flex items-center justify-center group-hover:bg-purple-200 dark:group-hover:bg-purple-500/20 transition-all">
                    <i class="fa-solid fa-store text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block">CARRITOS ACTIVOS</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $totalCarritos }}</span>
                </div>
            </a>

            <!-- Cierres con Inconsistencias -->
            <a href="{{ route('cierres.index') }}"
                class="bg-white dark:bg-slate-900 border border-rose-300 dark:border-rose-500/30 hover:border-rose-500 dark:hover:border-rose-500/60 rounded-xl p-5 flex items-center gap-4 group transition-all shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-500/10 flex items-center justify-center group-hover:bg-rose-200 dark:group-hover:bg-rose-500/20 transition-all">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block">CIERRES INCONSISTENTES</span>
                    <span class="text-2xl font-black {{ $totalCierresInconsistentes > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} tracking-tight">
                        {{ $totalCierresInconsistentes }}
                    </span>
                </div>
            </a>

            <!-- Compras Registradas -->
            <a href="{{ route('compras.index') }}"
                class="bg-white dark:bg-slate-900 border border-cyan-300 dark:border-cyan-500/30 hover:border-cyan-500 dark:hover:border-cyan-500/60 rounded-xl p-5 flex items-center gap-4 group transition-all shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-500/10 flex items-center justify-center group-hover:bg-cyan-200 dark:group-hover:bg-cyan-500/20 transition-all">
                    <i class="fa-solid fa-cart-shopping text-cyan-600 dark:text-cyan-400 text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest block">COMPRAS TOTALES</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $totalUltimasCompras }}</span>
                </div>
            </a>
        </div>

        <!-- WIDGET DE PROYECCIÓN DE VENTAS Y PRODUCCIÓN ESTIMADA -->
        <div class="bg-white dark:bg-slate-900 border border-indigo-200 dark:border-indigo-500/30 rounded-xl p-5 shadow-sm space-y-4"
            x-data="proyeccionWidget({{ json_encode($proyeccion) }})">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h2 class="text-xs font-black text-indigo-900 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-indigo-500 text-sm"></i>
                        PROYECCIÓN DE VENTAS Y PRODUCCIÓN SUGERIDA
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 uppercase">
                        ESTIMACIÓN DE UNIDADES A PREPARAR Y DINERO A RECAUDAR SEGÚN EL DÍA DE LA SEMANA Y EL CLIMA.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full bg-indigo-100 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 text-indigo-800 dark:text-indigo-300 font-black text-[10px] uppercase flex items-center gap-1">
                        <i class="fa-solid fa-calendar-day"></i>
                        <span x-text="data.nombre_dia + ' (' + formatDate(data.fecha_target) + ')'"></span>
                    </span>
                </div>
            </div>

            <!-- Filtros de Proyección -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3 bg-slate-50 dark:bg-slate-950/60 p-3 rounded-lg border border-slate-200 dark:border-slate-800">
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">FECHA A PROYECTAR</label>
                    <input type="date" x-model="fecha" @change="consultar()"
                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">CARRITO</label>
                    <select x-model="carritoId" @change="consultar()"
                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none uppercase">
                        <option value="">TODOS LOS CARRITOS</option>
                        <template x-for="c in data.carritos_lista" :key="c.id">
                            <option :value="c.id" x-text="c.nombre.toUpperCase()"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">TEMP. MIN (°C)</label>
                    <input type="number" step="any" x-model="tempMin" @change.debounce.500ms="consultar()" placeholder="PROMEDIO HIST."
                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-cyan-600 dark:text-cyan-400 focus:border-indigo-500 focus:outline-none uppercase">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">TEMP. MÁX (°C)</label>
                    <input type="number" step="any" x-model="tempMax" @change.debounce.500ms="consultar()" placeholder="PROMEDIO HIST."
                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-rose-600 dark:text-rose-400 focus:border-indigo-500 focus:outline-none uppercase">
                </div>
                <div class="col-span-2 sm:col-span-4 lg:col-span-1 flex items-end">
                    <button type="button" @click="consultar()" :disabled="cargando"
                        class="w-full py-1.5 px-3 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs uppercase shadow-sm transition-all flex items-center justify-center gap-1.5 disabled:opacity-50">
                        <i class="fa-solid" :class="cargando ? 'fa-spinner fa-spin' : 'fa-arrows-rotate'"></i>
                        <span x-text="cargando ? 'CALCULANDO...' : 'RECALCULAR'"></span>
                    </button>
                </div>
            </div>

            <!-- Resumen de Proyección Totales -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl p-3 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-lg">
                        🥟
                    </div>
                    <div>
                        <span class="text-[10px] font-black text-emerald-800 dark:text-emerald-300 uppercase block">UNIDADES ESTIMADAS A PREPARAR</span>
                        <span class="text-xl font-black text-emerald-700 dark:text-emerald-400" x-text="data.total_unidades + ' UNIDADES'"></span>
                    </div>
                </div>

                <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-xl p-3 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-lg">
                        💰
                    </div>
                    <div>
                        <span class="text-[10px] font-black text-amber-800 dark:text-amber-300 uppercase block">RECAUDACIÓN ESTIMADA</span>
                        <span class="text-xl font-black text-amber-700 dark:text-amber-400" x-text="'BS. ' + formatNumber(data.monto_total)"></span>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center font-black text-lg">
                        📊
                    </div>
                    <div>
                        <span class="text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase block">BASE DE DATOS HISTÓRICA</span>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase"
                            x-text="data.dias_analizados + ' CIERRES DE (' + data.nombre_dia + ') ANALIZADOS'"></span>
                    </div>
                </div>
            </div>

            <!-- Desglose por Variante -->
            <div class="space-y-2">
                <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider block">🥟 DETALLE SUGERIDO DE PRODUCCIÓN POR VARIANTE</span>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <template x-for="v in data.variantes" :key="v.variante_id">
                        <div class="bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-xl p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-black text-xs text-slate-900 dark:text-white uppercase flex items-center gap-1.5">
                                    <span>🥟</span> <span x-text="v.nombre"></span>
                                </span>
                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase" x-text="'BS. ' + formatNumber(v.precio_venta) + ' / U'"></span>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-200/60 dark:border-slate-800 pt-2">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase block">SUGERIDO A ENTREGAR</span>
                                    <span class="text-lg font-black text-indigo-600 dark:text-indigo-400" x-text="v.unidades_sugeridas + ' UDS.'"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase block">ESTIMADO BS.</span>
                                    <span class="text-sm font-black text-amber-600 dark:text-amber-400" x-text="'BS. ' + formatNumber(v.monto_estimado)"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Últimos Cierres (Col 7) -->
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> ÚLTIMOS 5 CIERRES DIARIOS
                        </h2>
                        <a href="{{ route('cierres.index') }}"
                            class="text-[10px] font-black text-amber-600 dark:text-amber-400 hover:underline uppercase">VER TODOS</a>
                    </div>

                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                                <th class="py-2.5 px-4">FECHA</th>
                                <th class="py-2.5 px-4">CARRITO</th>
                                <th class="py-2.5 px-4 text-right">ESTIMADO</th>
                                <th class="py-2.5 px-4 text-right">REAL</th>
                                <th class="py-2.5 px-4 text-center">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($ultimosCierres as $c)
                                <tr class="{{ $c->inconsistente ? 'bg-rose-50 dark:bg-rose-500/5' : '' }} hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
                                    <td class="py-2.5 px-4 text-purple-600 dark:text-purple-400 font-bold uppercase">{{ strtoupper($c->carrito->nombre ?? '—') }}</td>
                                    <td class="py-2.5 px-4 text-right text-slate-600 dark:text-slate-300 font-bold">BS. {{ number_format($c->monto_estimado, 2) }}</td>
                                    <td class="py-2.5 px-4 text-right text-slate-900 dark:text-white font-black">BS. {{ number_format($c->monto_real, 2) }}</td>
                                    <td class="py-2.5 px-4 text-center">
                                        @if(!$c->inconsistente)
                                            <span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-black text-[10px]">✓</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded bg-rose-100 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 font-black text-[10px] animate-pulse">⚠</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 font-bold uppercase">SIN CIERRES REGISTRADOS AÚN.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel de Alertas / Inconsistencias (Col 5) -->
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-900 border {{ $totalCierresInconsistentes > 0 ? 'border-rose-300 dark:border-rose-500/40' : 'border-slate-200 dark:border-slate-800' }} rounded-xl overflow-hidden shadow-sm h-full">
                    <div class="px-5 py-3 border-b {{ $totalCierresInconsistentes > 0 ? 'border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/5' : 'border-slate-100 dark:border-slate-800' }} flex items-center justify-between">
                        <h2 class="text-xs font-black {{ $totalCierresInconsistentes > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-slate-900 dark:text-white' }} uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved"></i>
                            {{ $totalCierresInconsistentes > 0 ? '🚨 ALERTAS DE INCONSISTENCIA' : '✅ SIN ALERTAS' }}
                        </h2>
                    </div>

                    @if($totalCierresInconsistentes == 0)
                        <div class="p-8 text-center">
                            <i class="fa-solid fa-shield-check text-emerald-500 text-5xl mb-3"></i>
                            <p class="font-black text-emerald-600 dark:text-emerald-400 text-sm uppercase">TODO CUADRA</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase">TODOS LOS CIERRES REGISTRADOS TIENEN CONSISTENCIA MATEMÁTICA.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach($cierresInconsistentesList as $ci)
                                <div class="p-4 hover:bg-rose-50 dark:hover:bg-rose-500/5 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <span class="font-black text-slate-900 dark:text-white text-xs uppercase">{{ strtoupper($ci->carrito->nombre ?? '—') }}</span>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($ci->fecha)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">DIFERENCIA:</span>
                                        <span class="text-xs font-black text-rose-600 dark:text-rose-400 uppercase">
                                            BS. {{ number_format(abs($ci->diferencia), 2) }}
                                            {{ $ci->diferencia > 0 ? '(FALTANTE)' : '(SOBRANTE)' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('cierres.index') }}"
                                class="text-[10px] font-black text-rose-600 dark:text-rose-400 hover:underline uppercase flex items-center gap-1">
                                <i class="fa-solid fa-arrow-right"></i> VER TODOS LOS CIERRES INCONSISTENTES
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function proyeccionWidget(initialData) {
    return {
        data: initialData,
        fecha: initialData.fecha_target,
        carritoId: initialData.carrito_id || '',
        tempMin: initialData.temp_min || '',
        tempMax: initialData.temp_max || '',
        cargando: false,

        formatDate(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        },

        formatNumber(val) {
            const num = parseFloat(val) || 0;
            return num.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        async consultar() {
            this.cargando = true;
            try {
                const params = new URLSearchParams({
                    fecha_proyeccion: this.fecha,
                    carrito_id: this.carritoId,
                    temp_min: this.tempMin,
                    temp_max: this.tempMax
                });
                const res = await fetch(`{{ route('dashboard') }}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    this.data = await res.json();
                }
            } catch (err) {
                console.error('Error al consultar proyecciones:', err);
            } finally {
                this.cargando = false;
            }
        }
    };
}
</script>
@endpush
