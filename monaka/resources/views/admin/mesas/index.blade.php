<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MESAS - SALTEÑERÍA MONAKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-input-mesa {
            background-color: var(--color-bg, rgba(0, 0, 0, 0.25));
            color: var(--color-text, #ffffff);
            border: 1px solid var(--color-border, rgba(255, 255, 255, 0.15));
        }

        .form-input-mesa:focus {
            border-color: #FFE66D;
            outline: none;
        }

        /* Contraste adaptativo para texto destacado */
        .light-mode .text-amber-500,
        .light-mode .text-amber-400 {
            color: #b45309 !important;
        }
    </style>
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar Unificada -->
    @include('layouts.admin_navbar')

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl md:text-2xl font-black uppercase flex items-center gap-2">
                <i class="fa-solid fa-chair text-amber-500"></i>MESAS
            </h2>
            <a href="{{ route('admin.pos') }}"
                class="btn-primary text-xs font-black uppercase !py-2 !px-4 shadow flex items-center gap-2">
                <i class="fa-solid fa-bolt"></i>VENTAS
            </a>
        </div>

        @if (session('success'))
            <div
                class="mb-4 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300 font-bold text-xs uppercase">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-4 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 font-bold text-xs uppercase">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Grid de Mesas Compacto e Independiente -->
        @if($mesas->isEmpty())
            <div
                class="py-12 flex flex-col items-center justify-center text-center text-gray-400 glass-card rounded-2xl border border-amber-500/10">
                <i class="fa-solid fa-chair text-5xl mb-4 opacity-40"></i>
                <p class="text-sm font-bold uppercase tracking-widest text-amber-500/80">NO HAY MESAS REGISTRADAS EN LA BASE
                    DE DATOS</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach ($mesas as $mesa)
                        <?php
                    $isOcupada = $mesa->estado === 'ocupada';
                    $cuenta = $mesa->cuentaActiva;
                            ?>
                        <div
                            class="glass-card rounded-2xl p-4 flex flex-col justify-between transition-all hover:border-amber-500/40 {{ $isOcupada ? 'border-amber-500/40 bg-amber-500/5 shadow-lg shadow-amber-500/5' : 'border-emerald-500/30 bg-emerald-500/5' }}">

                            <!-- Header Tarjeta -->
                            <div>
                                <div class="flex items-center justify-between pb-3 mb-3 border-b"
                                    style="border-color:var(--color-border, rgba(255,255,255,0.1))">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-sm {{ $isOcupada ? 'bg-amber-500/20 text-amber-400' : 'bg-emerald-500/20 text-emerald-400' }}">
                                            <i class="fa-solid fa-chair text-xs"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-black uppercase text-sm tracking-wide text-amber-500">
                                                {{ strtoupper($mesa->nombre) }}</h3>
                                            <span class="text-[10px] font-semibold block"
                                                style="color:var(--color-text-muted, #9ca3af);">
                                                {{ $isOcupada && $cuenta ? 'Abierta ' . date('H:i', strtotime($cuenta->fecha_apertura)) : 'Disponible' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $isOcupada ? 'bg-amber-500/20 text-amber-400 border border-amber-500/40 animate-pulse' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' }}">
                                            {{ $isOcupada ? 'OCUPADA' : 'LIBRE' }}
                                        </span>

                                        @if($isOcupada)
                                            <form action="{{ route('admin.mesas.liberar', $mesa->id) }}" method="POST"
                                                onsubmit="return confirm('¿Deseas liberar la mesa?');">
                                                @csrf
                                                <button type="submit" title="Liberar Mesa"
                                                    class="p-1.5 rounded-lg text-red-400 hover:bg-red-500/20 text-xs transition-colors">
                                                    <i class="fa-solid fa-power-off"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                <!-- Contenido -->
                                @if(!$isOcupada)
                                    <!-- Estado LIBRE -->
                                    <div class="py-6 flex flex-col items-center justify-center text-center">
                                        <i class="fa-solid fa-utensils text-3xl mb-2 opacity-20 text-emerald-400"></i>
                                        <p class="text-[11px] font-bold uppercase mb-4 opacity-60">Mesa Disponible</p>
                                        <form action="{{ route('admin.mesas.abrir', $mesa->id) }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit"
                                                class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs py-2.5 px-4 rounded-xl shadow uppercase transition-all flex items-center justify-center gap-2">
                                                <i class="fa-solid fa-door-open"></i>ABRIR CUENTA
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <!-- Estado OCUPADA -->
                                    <div class="space-y-3">
                                        <!-- Total Consumido Header -->
                                        <div
                                            class="flex items-center justify-between bg-black/20 p-2.5 rounded-xl border border-white/5">
                                            <span class="text-[10px] font-extrabold uppercase text-gray-400">TOTAL</span>
                                            <span class="text-base font-black text-amber-400">Bs.
                                                {{ number_format($cuenta->monto_total, 2) }}</span>
                                        </div>

                                        <!-- Lista de Ítems Consumidos -->
                                        <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                                            @if($cuenta->items->isEmpty())
                                                <p class="text-[11px] italic text-center py-3 text-gray-400">Sin consumos agregados</p>
                                            @else
                                                @foreach ($cuenta->items as $item)
                                                    <div
                                                        class="flex items-center justify-between text-xs p-2 rounded-lg bg-white/5 hover:bg-white/10 transition-colors">
                                                        <div class="truncate mr-2">
                                                            <span class="font-black text-amber-400 text-[11px]">{{ $item->cantidad }}x</span>
                                                            <span class="font-bold text-[11px] uppercase">{{ $item->nombre_producto }}
                                                                {{ $item->nombre_variante ? '(' . $item->nombre_variante . ')' : '' }}</span>
                                                        </div>
                                                        <div class="flex items-center gap-2 shrink-0">
                                                            <span class="font-black text-[11px] text-amber-400">Bs.
                                                                {{ number_format($item->precio_total, 2) }}</span>
                                                            <a href="{{ route('admin.mesas.item.remover', $item->id) }}"
                                                                class="text-red-400 hover:text-red-300" title="Eliminar">
                                                                <i class="fa-solid fa-times text-xs"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>

                                        <!-- Formulario Agregar Producto -->
                                        <form action="{{ route('admin.mesas.item.agregar', $mesa->id) }}" method="POST"
                                            class="space-y-1.5 pt-1">
                                            @csrf
                                            <div class="flex gap-1.5">
                                                <select name="producto_id" id="select-prod-{{ $mesa->id }}"
                                                    onchange="handleProductChange({{ $mesa->id }})"
                                                    class="flex-1 form-input-mesa rounded-lg p-1.5 text-[11px] font-bold uppercase truncate"
                                                    required>
                                                    <option value="">+ AGREGAR PRODUCTO</option>
                                                    @foreach ($productos as $p)
                                                        <option value="{{ $p->id }}"
                                                            data-tiene-variantes="{{ $p->tiene_variantes ? 'true' : 'false' }}"
                                                            data-precio="{{ $p->precio_virtual }}">
                                                            {{ strtoupper($p->nombre) }}
                                                            {{ !$p->tiene_variantes ? '(Bs. ' . number_format($p->precio_virtual, 2) . ')' : '(VAR)' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="number" name="cantidad" value="1" min="1"
                                                    class="w-12 form-input-mesa rounded-lg p-1.5 text-[11px] font-bold text-center"
                                                    placeholder="Cant." required>
                                                <button type="submit"
                                                    class="bg-amber-600 hover:bg-amber-500 text-white font-black text-[11px] px-3 py-1.5 rounded-lg uppercase transition-all shadow">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                            </div>
                                            <div id="container-variante-{{ $mesa->id }}" class="hidden">
                                                <select name="variante_id" id="select-var-{{ $mesa->id }}"
                                                    class="w-full form-input-mesa text-amber-400 rounded-lg p-1.5 text-[11px] font-bold uppercase">
                                                    <option value="">-- TAMAÑO / VARIANTE --</option>
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <!-- Botón Cobrar al Pie -->
                            @if($isOcupada && $cuenta)
                                <div class="pt-3 mt-3 border-t" style="border-color:var(--color-border, rgba(255,255,255,0.1))">
                                    <button type="button"
                                        onclick="abrirModalPago('{{ $cuenta->id }}', '{{ $mesa->nombre }}', '{{ $cuenta->saldoPendiente() }}')"
                                        class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs py-2 px-3 rounded-xl shadow uppercase flex items-center justify-center gap-2 transition-all">
                                        <i class="fa-solid fa-calculator"></i>COBRAR Y CERRAR
                                    </button>
                                </div>
                            @endif
                        </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- MODAL REGISTRO DE PAGO DIVIDIDO -->
    <div id="modal-pago" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="glass-card rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4 border border-amber-500/30"
            style="background-color: var(--color-bg-alt, #111827);">
            <h3 class="text-lg font-black uppercase text-amber-500" id="modal-pago-title">COBRAR CUENTA</h3>
            <p class="text-xs uppercase" style="color:var(--color-text-muted, #9ca3af);">REGISTRA EL PAGO (QR /
                EFECTIVO) Y CIERRA LA MESA</p>

            <form id="form-pago" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div class="bg-black/30 p-4 rounded-xl text-center border border-white/5">
                        <div class="text-xs font-bold uppercase" style="color:var(--color-text-muted, #9ca3af);">SALDO
                            PENDIENTE A COBRAR</div>
                        <div class="text-3xl font-black text-emerald-400" id="modal-saldo-text">Bs. 0.00</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase mb-1"
                            style="color:var(--color-text-muted, #9ca3af);">MÉTODO DE PAGO</label>
                        <select name="metodo_pago"
                            class="w-full form-input-mesa rounded-xl p-3 text-xs font-bold uppercase" required>
                            <option value="efectivo">💵 EFECTIVO</option>
                            <option value="qr">📱 PAGO QR</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase mb-1"
                            style="color:var(--color-text-muted, #9ca3af);">MONTO A ABONAR (Bs.)</label>
                        <input type="number" step="0.01" name="monto" id="input-monto-pago"
                            class="w-full form-input-mesa rounded-xl p-3 text-sm font-bold text-center" required>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" onclick="cerrarModalPago()"
                        class="flex-1 py-3 px-4 rounded-xl text-xs font-bold uppercase border border-white/20 hover:bg-white/10 transition-colors">
                        CANCELAR
                    </button>
                    <button type="submit"
                        class="flex-1 py-3 px-4 rounded-xl text-xs font-black uppercase bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg transition-all">
                        CONFIRMAR PAGO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const productosData = @json($productos);

        function handleProductChange(mesaId) {
            const selectProd = document.getElementById('select-prod-' + mesaId);
            const containerVar = document.getElementById('container-variante-' + mesaId);
            const selectVar = document.getElementById('select-var-' + mesaId);

            const prodId = selectProd.value;
            selectVar.innerHTML = '<option value="">-- TAMAÑO / VARIANTE --</option>';

            if (!prodId) {
                containerVar.classList.add('hidden');
                return;
            }

            const selectedProd = productosData.find(p => p.id == prodId);

            if (selectedProd && selectedProd.tiene_variantes && selectedProd.variantes.length > 0) {
                selectedProd.variantes.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = `${v.nombre_variante.toUpperCase()} - (Bs. ${parseFloat(v.precio).toFixed(2)})`;
                    selectVar.appendChild(opt);
                });
                containerVar.classList.remove('hidden');
                selectVar.required = true;
            } else {
                containerVar.classList.add('hidden');
                selectVar.required = false;
            }
        }

        function abrirModalPago(ventaId, mesaNombre, saldo) {
            document.getElementById('form-pago').action = '/admin/mesas/pago/' + ventaId;
            document.getElementById('modal-pago-title').textContent = 'COBRAR CUENTA - ' + mesaNombre.toUpperCase();
            document.getElementById('modal-saldo-text').textContent = 'Bs. ' + parseFloat(saldo).toFixed(2);
            document.getElementById('input-monto-pago').value = parseFloat(saldo).toFixed(2);
            document.getElementById('modal-pago').style.display = 'flex';
        }

        function cerrarModalPago() {
            document.getElementById('modal-pago').style.display = 'none';
        }
    </script>
</body>

</html>