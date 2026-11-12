@extends('layouts.app')

@section('title', 'CARRITOS (PUNTOS DE VENTA) — SALTEÑAS')

@section('content')
    <div class="space-y-6">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-store text-purple-600 dark:text-purple-400"></i> CARRITOS (PUNTOS DE VENTA)
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase">MONITOR EN TIEMPO REAL DE TURNOS ABIERTOS Y ADMINISTRACIÓN DE PUNTOS DE VENTA.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-300 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-black text-[10px] uppercase flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    EN VIVO (C/ 10S)
                </span>
                <button type="button" onclick="refrescarMonitorVivo(true)" id="btn-refresh-vivo"
                    class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[10px] uppercase transition-all flex items-center gap-1.5 border border-slate-200 dark:border-slate-700">
                    <i class="fa-solid fa-arrows-rotate" id="icon-refresh-vivo"></i> ACTUALIZAR
                </button>
            </div>
        </div>

        {{-- =====================================================================
             SECCIÓN EN VIVO: SOLO CARRITOS CON TURNO ABIERTO EN ESTE MOMENTO
             ===================================================================== --}}
        <div class="bg-white dark:bg-slate-900 border-2 border-purple-300 dark:border-purple-500/40 rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-3 bg-purple-50/70 dark:bg-purple-950/30 border-b border-purple-200 dark:border-purple-500/30 flex items-center justify-between flex-wrap gap-2">
                <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-tower-broadcast text-purple-600 dark:text-purple-400"></i>
                    TURNOS ABIERTOS AHORA (<span id="vivo-count">{{ count($turnosVivos) }}</span>)
                </h2>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase" id="vivo-last-sync">
                    Solo se muestran carritos con turno abierto
                </span>
            </div>

            <div id="monitor-vivo-container" class="p-4 space-y-4">
                @forelse($turnosVivos as $tv)
                    <div class="bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 rounded-xl p-4 space-y-3">
                        <!-- Cabecera del Carrito en Turno -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-200 dark:border-slate-800">
                            <div class="flex items-center flex-wrap gap-2">
                                <span class="px-2.5 py-1 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-300 font-black text-xs uppercase tracking-wider">
                                    🛒 {{ $tv['carrito_nombre'] }}
                                </span>
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">
                                    {{ $tv['carrito_zona'] }}
                                </span>
                                @if($tv['stock_aceptado'])
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-black text-[10px] uppercase inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> EN TURNO
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 font-black text-[10px] uppercase inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> TURNO ABIERTO (SIN STOCK ACEPTADO)
                                    </span>
                                @endif
                            </div>
                            <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase flex items-center gap-3">
                                <span><i class="fa-regular fa-clock mr-1 text-purple-500"></i> Abierto: {{ $tv['abierto_hora'] }} hrs</span>
                                <span><i class="fa-solid fa-signal mr-1 {{ $tv['segundos_sin_senal'] > 120 ? 'text-amber-500' : 'text-emerald-500' }}"></i> {{ $tv['hace_texto'] }}</span>
                            </div>
                        </div>

                        <!-- 2 Cards Compactos: (1) Salteñas Restantes + Variedad | (2) Total Vendido Bs. + Desglose -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <!-- Card 1: Stock actual de salteñas + desglose por variedad -->
                            <div class="bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-500/30 rounded-xl p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-black uppercase text-blue-700 dark:text-blue-400 tracking-wider">
                                        🥟 SALTEÑAS EN CARRITO AHORA
                                    </span>
                                    <span class="text-xl font-black {{ $tv['total_restantes'] <= 5 && $tv['stock_aceptado'] ? 'text-rose-600 dark:text-rose-400' : 'text-blue-600 dark:text-blue-400' }}">
                                        {{ $tv['total_restantes'] }} u.
                                    </span>
                                </div>

                                @if(!empty($tv['desglose_variedades']))
                                    <div class="flex flex-wrap gap-1.5 pt-1 border-t border-slate-100 dark:border-slate-800">
                                        @foreach($tv['desglose_variedades'] as $var)
                                            <span class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5">
                                                <span>{{ $var['nombre'] }}:</span>
                                                <span class="font-black {{ (int)$var['restante'] <= 3 ? 'text-rose-600 dark:text-rose-400' : 'text-blue-600 dark:text-blue-400' }}">
                                                    {{ (int)$var['restante'] }} u.
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-[11px] text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800">
                                        Esperando recepción de stock inicial...
                                    </div>
                                @endif
                            </div>

                            <!-- Card 2: Total vendido en Bs. + desglose Efectivo / QR -->
                            <div class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-500/30 rounded-xl p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-black uppercase text-emerald-700 dark:text-emerald-400 tracking-wider">
                                        💰 TOTAL VENDIDO EN EL TURNO
                                    </span>
                                    <div class="text-right">
                                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                                            Bs. {{ number_format($tv['dinero_total'], 2) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-slate-100 dark:border-slate-800 text-[11px] font-bold">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="px-2 py-1 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300">
                                            💵 Efectivo: Bs. {{ number_format($tv['dinero_efectivo'], 2) }}
                                        </span>
                                        <span class="px-2 py-1 rounded-lg bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/30 text-purple-800 dark:text-purple-300">
                                            📱 QR: Bs. {{ number_format($tv['dinero_qr'], 2) }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                                        {{ $tv['total_vendidas'] }} u. vendidas
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400 space-y-1.5">
                        <i class="fa-solid fa-moon text-2xl opacity-50"></i>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Ningún carrito tiene turno abierto en este momento.
                        </p>
                        <p class="text-[11px] text-slate-400">
                            Apenas un vendedor abra su turno en el POS, verás aquí cuántas salteñas tiene y lo vendido en tiempo real.
                        </p>
                    </div>
                @endforelse
            </div>
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

@push('scripts')
<script>
    function escHtmlVivo(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function fmtBsVivo(n) {
        return Number(n || 0).toFixed(2);
    }

    function renderizarMonitorVivo(turnos) {
        const container = document.getElementById('monitor-vivo-container');
        const countEl = document.getElementById('vivo-count');
        if (!container) return;
        if (countEl) countEl.innerText = turnos.length;

        if (!turnos || turnos.length === 0) {
            container.innerHTML = `
                <div class="py-6 text-center text-slate-400 space-y-1.5">
                    <i class="fa-solid fa-moon text-2xl opacity-50"></i>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Ningún carrito tiene turno abierto en este momento.
                    </p>
                    <p class="text-[11px] text-slate-400">
                        Apenas un vendedor abra su turno en el POS, verás aquí cuántas salteñas tiene y lo vendido en tiempo real.
                    </p>
                </div>
            `;
            return;
        }

        let html = '';
        turnos.forEach(tv => {
            const estadoBadge = tv.stock_aceptado
                ? `<span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-black text-[10px] uppercase inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> EN TURNO
                   </span>`
                : `<span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 font-black text-[10px] uppercase inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> TURNO ABIERTO (SIN STOCK ACEPTADO)
                   </span>`;

            const senalColor = (tv.segundos_sin_senal || 0) > 120 ? 'text-amber-500' : 'text-emerald-500';
            const totalRestColor = (tv.total_restantes <= 5 && tv.stock_aceptado)
                ? 'text-rose-600 dark:text-rose-400'
                : 'text-blue-600 dark:text-blue-400';

            let varsHtml = '';
            if (Array.isArray(tv.desglose_variedades) && tv.desglose_variedades.length > 0) {
                const pills = tv.desglose_variedades.map(v => {
                    const rest = parseInt(v.restante || 0);
                    const restCol = rest <= 3 ? 'text-rose-600 dark:text-rose-400' : 'text-blue-600 dark:text-blue-400';
                    return `
                        <span class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5">
                            <span>${escHtmlVivo(v.nombre)}:</span>
                            <span class="font-black ${restCol}">${rest} u.</span>
                        </span>
                    `;
                }).join('');
                varsHtml = `<div class="flex flex-wrap gap-1.5 pt-1 border-t border-slate-100 dark:border-slate-800">${pills}</div>`;
            } else {
                varsHtml = `<div class="text-[11px] text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800">Esperando recepción de stock inicial...</div>`;
            }

            html += `
                <div class="bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 rounded-xl p-4 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center flex-wrap gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-300 font-black text-xs uppercase tracking-wider">
                                🛒 ${escHtmlVivo(tv.carrito_nombre)}
                            </span>
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">
                                ${escHtmlVivo(tv.carrito_zona)}
                            </span>
                            ${estadoBadge}
                        </div>
                        <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase flex items-center gap-3">
                            <span><i class="fa-regular fa-clock mr-1 text-purple-500"></i> Abierto: ${escHtmlVivo(tv.abierto_hora)} hrs</span>
                            <span><i class="fa-solid fa-signal mr-1 ${senalColor}"></i> ${escHtmlVivo(tv.hace_texto)}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-500/30 rounded-xl p-3.5 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-black uppercase text-blue-700 dark:text-blue-400 tracking-wider">
                                    🥟 SALTEÑAS EN CARRITO AHORA
                                </span>
                                <span class="text-xl font-black ${totalRestColor}">
                                    ${parseInt(tv.total_restantes || 0)} u.
                                </span>
                            </div>
                            ${varsHtml}
                        </div>

                        <div class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-500/30 rounded-xl p-3.5 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-black uppercase text-emerald-700 dark:text-emerald-400 tracking-wider">
                                    💰 TOTAL VENDIDO EN EL TURNO
                                </span>
                                <div class="text-right">
                                    <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                                        Bs. ${fmtBsVivo(tv.dinero_total)}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-slate-100 dark:border-slate-800 text-[11px] font-bold">
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="px-2 py-1 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300">
                                        💵 Efectivo: Bs. ${fmtBsVivo(tv.dinero_efectivo)}
                                    </span>
                                    <span class="px-2 py-1 rounded-lg bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/30 text-purple-800 dark:text-purple-300">
                                        📱 QR: Bs. ${fmtBsVivo(tv.dinero_qr)}
                                    </span>
                                </div>
                                <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">
                                    ${parseInt(tv.total_vendidas || 0)} u. vendidas
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function refrescarMonitorVivo(manual = false) {
        const icon = document.getElementById('icon-refresh-vivo');
        const lastSync = document.getElementById('vivo-last-sync');
        if (manual && icon) icon.classList.add('fa-spin');

        fetch('{{ route("carritos.estado_vivo") }}')
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    renderizarMonitorVivo(data.turnos_vivos || []);
                    if (lastSync && data.actualizado_servidor) {
                        lastSync.innerText = 'Última lectura: ' + data.actualizado_servidor;
                    }
                }
            })
            .catch(() => {})
            .finally(() => {
                if (manual && icon) setTimeout(() => icon.classList.remove('fa-spin'), 350);
            });
    }

    setInterval(() => refrescarMonitorVivo(false), 10000);
</script>
@endpush
