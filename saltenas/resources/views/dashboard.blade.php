@extends('layouts.app')

@section('title', 'Dashboard — Salteñas Analítica')

@section('content')
    <div class="space-y-6">

        <!-- Hero Banner & KPIs -->
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-900 to-amber-950/40 border border-slate-800 p-6 rounded-2xl shadow-xl">
            <div>
                <h1 class="text-2xl font-black text-white uppercase flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-amber-500"></i> Dashboard & Proyecciones
                </h1>
                <p class="text-xs text-slate-400 mt-1">Análisis de impacto del clima, márgenes de ganancia y rendimiento por
                    sucursal.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cierres.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus text-sm"></i> Nuevo Cierre Diario
                </a>
            </div>
        </div>

        <!-- KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[10px] font-black uppercase tracking-wider">Sucursales Activas</span>
                    <i class="fa-solid fa-store text-amber-500 text-lg"></i>
                </div>
                <div class="text-3xl font-black text-white">{{ $totalSucursales }}</div>
                <div class="text-[10px] font-bold text-slate-500 mt-1">Puntos de venta registrados</div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[10px] font-black uppercase tracking-wider">Salteñas Vendidas</span>
                    <i class="fa-solid fa-cookie-bite text-emerald-500 text-lg"></i>
                </div>
                <div class="text-3xl font-black text-emerald-400">{{ number_format($totalVendidas) }}</div>
                <div class="text-[10px] font-bold text-slate-500 mt-1">Total unidades en cierres</div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[10px] font-black uppercase tracking-wider">Recaudación Total</span>
                    <i class="fa-solid fa-cash-register text-amber-400 text-lg"></i>
                </div>
                <div class="text-3xl font-black text-amber-400">Bs. {{ number_format($totalRecaudado, 2) }}</div>
                <div class="text-[10px] font-bold text-slate-500 mt-1">Efectivo + QR cobrados</div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[10px] font-black uppercase tracking-wider">Ganancia Neta Est.</span>
                    <i class="fa-solid fa-coins text-cyan-400 text-lg"></i>
                </div>
                <div class="text-3xl font-black text-cyan-400">Bs. {{ number_format($gananciaTotal, 2) }}</div>
                <div class="text-[10px] font-bold text-slate-500 mt-1">Ingresos menos costo insumos</div>
            </div>
        </div>

        <!-- Analítica Clima vs Ventas Cards -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
            <h2 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-temperature-half text-cyan-400"></i> Promedio de Ventas según el Clima
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="p-4 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase text-cyan-400 block">❄️ Frío / Lluvia</span>
                        <span class="text-2xl font-black text-white">
                            {{ number_format($promedioClima['frio_lluvia']->avg_vendidas ?? 0, 1) }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 block">salteñas / día</span>
                    </div>
                    <div class="text-right text-[10px] font-bold text-slate-500">
                        {{ $promedioClima['frio_lluvia']->total_dias ?? 0 }} días reg.
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase text-amber-400 block">⛅ Templado</span>
                        <span class="text-2xl font-black text-white">
                            {{ number_format($promedioClima['templado_nublado']->avg_vendidas ?? 0, 1) }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 block">salteñas / día</span>
                    </div>
                    <div class="text-right text-[10px] font-bold text-slate-500">
                        {{ $promedioClima['templado_nublado']->total_dias ?? 0 }} días reg.
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase text-rose-400 block">☀️ Caluroso</span>
                        <span class="text-2xl font-black text-white">
                            {{ number_format($promedioClima['caluroso_soleado']->avg_vendidas ?? 0, 1) }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 block">salteñas / día</span>
                    </div>
                    <div class="text-right text-[10px] font-bold text-slate-500">
                        {{ $promedioClima['caluroso_soleado']->total_dias ?? 0 }} días reg.
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Chart 1: Tendencia de Cierres Recientes -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
                <h3 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-chart-area text-amber-500"></i> Tendencia de Ventas (Últimos Cierres)
                </h3>
                <div class="h-64">
                    <canvas id="chartTendencia"></canvas>
                </div>
            </div>

            <!-- Chart 2: Comparativo por Sucursal -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
                <h3 class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-chart-bar text-emerald-500"></i> Ventas por Sucursal
                </h3>
                <div class="h-64">
                    <canvas id="chartSucursales"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Chart 1: Tendencia de Cierres
            const ctx1 = document.getElementById('chartTendencia').getContext('2d');
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: {!! json_encode($ultimosCierres->map(fn($c) => \Carbon\Carbon::parse($c->fecha)->format('d/m'))->values()) !!},
                    datasets: [{
                        label: 'Salteñas Vendidas',
                        data: {!! json_encode($ultimosCierres->pluck('saltenas_vendidas')->values()) !!},
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#f59e0b',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#94a3b8', font: { weight: 'bold', size: 11 } } }
                    },
                    scales: {
                        x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                    }
                }
            });

            // Chart 2: Ventas por Sucursal
            const ctx2 = document.getElementById('chartSucursales').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($ventasPorSucursal->pluck('nombre')->values()) !!},
                    datasets: [{
                        label: 'Unidades Vendidas',
                        data: {!! json_encode($ventasPorSucursal->pluck('cierres_sum_saltenas_vendidas')->map(fn($v) => $v ?? 0)->values()) !!},
                        backgroundColor: ['#f59e0b', '#10b981', '#06b6d4', '#ec4899'],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { ticks: { color: '#64748b' }, grid: { display: false } },
                        y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                    }
                }
            });
        });
    </script>
@endsection