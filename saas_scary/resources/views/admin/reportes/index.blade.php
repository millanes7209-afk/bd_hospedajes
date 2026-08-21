<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>REPORTES Y ESTADÍSTICAS - SALTEÑERÍA MONAKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar Base -->
    <header class="glass-card mb-6 border-b rounded-none px-4 py-3"
        style="border-color:var(--color-card-border);background:var(--color-bg-alt)">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-8">
                    <img src="{{ asset('assets/logo.svg') }}" alt="LOGO" class="w-full h-full object-contain">
                </div>
                <span class="text-base font-black text-[#FFE66D] tracking-wider uppercase">SALTEÑERÍA MONAKA</span>
            </div>

            <nav class="hidden md:flex items-center gap-1 text-xs font-bold uppercase tracking-wider">
                <a href="{{ route('admin.mesas') }}"
                    class="px-3 py-2 rounded-lg transition-colors text-gray-300 hover:text-white hover:bg-white/5 flex items-center gap-2">
                    <i class="fa-solid fa-chair text-sm"></i>Mesas / POS
                </a>

                <a href="{{ route('admin.pedidos') }}"
                    class="px-3 py-2 rounded-lg transition-colors text-gray-300 hover:text-white hover:bg-white/5 flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-list text-sm"></i>Pedidos
                </a>

                <a href="{{ route('admin.productos') }}"
                    class="px-3 py-2 rounded-lg transition-colors text-gray-300 hover:text-white hover:bg-white/5 flex items-center gap-2">
                    <i class="fa-solid fa-utensils text-sm"></i>Productos
                </a>

                <a href="{{ route('admin.reportes') }}"
                    class="px-3 py-2 rounded-lg transition-colors bg-[#FFE66D]/15 text-[#FFE66D] font-extrabold flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-sm"></i>Reportes
                </a>

                <a href="{{ route('admin.perfil') }}"
                    class="px-3 py-2 rounded-lg transition-colors text-gray-300 hover:text-white hover:bg-white/5 flex items-center gap-2">
                    <i class="fa-solid fa-user-gear text-sm"></i>Mi Perfil
                </a>

                <div class="h-4 w-px bg-white/10 mx-1"></div>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 rounded-lg text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors flex items-center gap-1.5 font-bold uppercase">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i>Salir
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <h2 class="text-2xl font-black uppercase"><i class="fa-solid fa-chart-pie mr-2 text-[#FFE66D]"></i>REPORTES
                DE VENTAS Y MÉTRICAS</h2>

            <!-- Filtro de Fechas -->
            <form action="{{ route('admin.reportes') }}" method="GET"
                class="flex items-center gap-2 bg-white/5 p-2 rounded-xl border border-white/10">
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                    class="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs font-bold">
                <span class="text-xs font-bold text-gray-400">AL</span>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                    class="bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs font-bold">
                <button type="submit"
                    class="bg-amber-600 hover:bg-amber-500 text-white font-black text-xs p-2.5 rounded-lg uppercase shadow">
                    <i class="fa-solid fa-filter mr-1"></i>FILTRAR
                </button>
            </form>
        </div>

        <!-- Tarjetas de Métricas Clave -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass-card p-6 border border-amber-500/30 rounded-2xl bg-amber-500/5">
                <div class="text-xs font-bold text-gray-400 uppercase mb-1">TOTAL INGRESOS RECAUDADOS</div>
                <div class="text-3xl font-black text-[#FFE66D]">Bs.{{ number_format($totalIngresos, 2) }}</div>
                <div class="text-[10px] text-gray-400 mt-2 uppercase">Período:
                    {{ date('d/m/Y', strtotime($fechaInicio)) }} - {{ date('d/m/Y', strtotime($fechaFin)) }}</div>
            </div>

            <div class="glass-card p-6 border border-emerald-500/30 rounded-2xl bg-emerald-500/5">
                <div class="text-xs font-bold text-gray-400 uppercase mb-1">TOTAL VENTAS COMPLETADAS</div>
                <div class="text-3xl font-black text-emerald-400">{{ $cantidadVentas }} <span
                        class="text-xs font-bold">VENTAS</span></div>
                <div class="text-[10px] text-gray-400 mt-2 uppercase">Cuentas cerradas satisfactoriamente</div>
            </div>

            <div class="glass-card p-6 border border-blue-500/30 rounded-2xl bg-blue-500/5">
                <div class="text-xs font-bold text-gray-400 uppercase mb-1">TICKET PROMEDIO POR VENTA</div>
                <div class="text-3xl font-black text-blue-400">
                    Bs.{{ number_format($cantidadVentas > 0 ? $totalIngresos / $cantidadVentas : 0, 2) }}
                </div>
                <div class="text-[10px] text-gray-400 mt-2 uppercase">Promedio de consumo por orden</div>
            </div>
        </div>

        <!-- Tablas Desglose -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Ventas por Canal (Local vs WhatsApp) -->
            <div class="glass-card p-6 border border-white/10 rounded-2xl">
                <h3 class="text-base font-black uppercase text-[#FFE66D] mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-[#FFE66D] fa-store"></i>VENTAS POR CANAL DE ORIGEN
                </h3>
                <div class="space-y-3">
                    @forelse ($ventasPorOrigen as $origen)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/10">
                            <div>
                                <span
                                    class="font-black uppercase text-sm {{ $origen->origen === 'local' ? 'text-amber-400' : 'text-emerald-400' }}">
                                    {{ $origen->origen === 'local' ? '🏪 VENTA LOCAL (MESAS / LLEVAR)' : '📱 PEDIDOS WHATSAPP' }}
                                </span>
                                <span class="block text-xs text-gray-400 font-semibold">{{ $origen->total_pedidos }} ÓRDENES
                                    REALIZADAS</span>
                            </div>
                            <div class="text-lg font-black text-white">
                                Bs.{{ number_format($origen->total_monto, 2) }}
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 italic text-center py-4">Sin datos de ventas en este rango de
                            fechas.</p>
                    @endforelse
                </div>
            </div>

            <!-- Productos Más Vendidos (Salteñas vs Refrescos) -->
            <div class="glass-card p-6 border border-white/10 rounded-2xl">
                <h3 class="text-base font-black uppercase text-[#FFE66D] mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-utensils"></i>PRODUCTOS MÁS VENDIDOS (SALTEÑAS & REFRESCOS)
                </h3>
                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    @forelse ($productosVendidos as $prod)
                        <div
                            class="flex items-center justify-between text-xs p-2.5 rounded-lg bg-white/5 border border-white/5">
                            <div>
                                <span class="font-extrabold text-white">{{ strtoupper($prod->nombre_producto) }}</span>
                                @if($prod->nombre_variante)
                                    <span class="text-amber-400 font-bold">({{ strtoupper($prod->nombre_variante) }})</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                <span
                                    class="font-black bg-amber-500/20 text-amber-300 px-2 py-0.5 rounded-full border border-amber-500/40">
                                    {{ $prod->total_cantidad }} Unid.
                                </span>
                                <span
                                    class="font-extrabold text-[#FFE66D]">Bs.{{ number_format($prod->total_ingreso, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 italic text-center py-4">Sin datos de productos vendidos.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Ventas por Hora del Día (Horas Pico) -->
        <div class="glass-card p-6 border border-white/10 rounded-2xl">
            <h3 class="text-base font-black uppercase text-[#FFE66D] mb-4 flex items-center gap-2">
                <i class="fa-regular fa-clock"></i>DISTRIBUCIÓN DE VENTAS POR HORAS PICO (00:00 - 23:00)
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                @foreach ($ventasPorHora as $vh)
                    <div class="p-3 rounded-xl bg-white/5 border border-white/10 text-center">
                        <div class="text-xs font-black text-amber-400">{{ sprintf('%02d:00', $vh->hora) }}</div>
                        <div class="text-sm font-black text-white mt-1">Bs.{{ number_format($vh->total_monto, 2) }}</div>
                        <div class="text-[10px] text-gray-400 font-bold mt-0.5">{{ $vh->total_ventas }} ventas</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</body>

</html>