<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MESAS - SAAS SCARY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .mesa-card-theme {
            background-color: var(--color-bg-alt, rgba(255, 255, 255, 0.05));
            color: var(--color-text, #ffffff);
            border: 1px solid var(--color-border, rgba(255, 255, 255, 0.12));
        }

        .form-input-mesa {
            background-color: var(--color-bg, rgba(0, 0, 0, 0.2));
            color: var(--color-text, #ffffff);
            border: 1px solid var(--color-border, rgba(255, 255, 255, 0.15));
        }

        .form-input-mesa:focus {
            border-color: #FFE66D;
            outline: none;
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
                <i class="fa-solid fa-bolt"></i>POS VENTA RÁPIDA
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

        <!-- Grid de Mesas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($mesas as $mesa)
                        <?php
                $isOcupada = $mesa->estado === 'ocupada';
                $cuenta = $mesa->cuentaActiva;
                            ?>
                        <div
                            class="glass-card p-6 border rounded-2xl flex flex-col justify-between {{ $isOcupada ? 'border-amber-500/50 bg-amber-500/5' : 'border-emerald-500/40 bg-emerald-500/5' }}">
                            <!-- Header Mesa -->
                            <div class="flex items-center justify-between border-b pb-4 mb-4"
                                style="border-color:var(--color-border, rgba(255,255,255,0.1))">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl font-black uppercase text-amber-500">{{ strtoupper($mesa->nombre) }}</span>
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $isOcupada ? 'bg-amber-500/20 text-amber-500 border border-amber-500/50 animate-pulse' : 'bg-emerald-500/20 text-emerald-500 border border-emerald-500/50' }}">
                                        {{ $isOcupada ? 'OCUPADA' : 'LIBRE' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if(!$isOcupada)
                                        <form action="{{ route('admin.mesas.abrir', $mesa->mesaID) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs py-2 px-4 rounded-xl shadow-md uppercase transition-all flex items-center gap-1.5">
                                                <i class="fa-solid fa-door-open"></i>ABRIR CUENTA
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.mesas.liberar', $mesa->mesaID) }}" method="POST"
                                            onsubmit="return confirm('¿Deseas liberar y cerrar la cuenta de esta mesa?');">
                                            @csrf
                                            <button type="submit" title="Cerrar y liberar mesa"
                                                class="bg-red-500/20 hover:bg-red-500/40 text-red-400 font-bold text-xs py-2 px-3 rounded-xl border border-red-500/40 uppercase transition-all flex items-center gap-1">
                                                <i class="fa-solid fa-power-off"></i>LIBERAR
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <!-- Contenido Cuenta Activa -->
                            @if($isOcupada && $cuenta)
                                <div class="space-y-4 mb-4">
                                    <div class="text-xs font-semibold" style="color:var(--color-text-muted, #9ca3af);">
                                        <i class="fa-regular fa-clock mr-1"></i>Apertura:
                                        {{ date('H:i - d/m/Y', strtotime($cuenta->fecha_apertura)) }}
                                    </div>

                                    <!-- Lista de Ítems Consumidos adaptada a tema -->
                                    <div class="mesa-card-theme rounded-xl p-4 space-y-2 max-h-48 overflow-y-auto">
                                        @if($cuenta->items->isEmpty())
                                            <p class="text-xs italic text-center py-2" style="color:var(--color-text-muted, #9ca3af);">Sin
                                                consumos agregados aún.</p>
                                        @else
                                            @foreach ($cuenta->items as $item)
                                                <div class="flex items-center justify-between text-xs py-1.5 border-b last:border-0"
                                                    style="border-color:var(--color-border, rgba(255,255,255,0.08))">
                                                    <div>
                                                        <span class="font-black text-amber-500 mr-1">{{ $item->cantidad }}x</span>
                                                        <span class="font-bold uppercase"
                                                            style="color:var(--color-text);">{{ $item->nombre_producto }}
                                                            {{ $item->nombre_variante ? '(' . $item->nombre_variante . ')' : '' }}</span>
                                                        @if($item->nota)
                                                            <span class="block text-[10px] text-amber-500">Nota: {{ $item->nota }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <span class="font-black text-amber-500">Bs.
                                                            {{ number_format($item->precio_total, 2) }}</span>
                                                        <a href="{{ route('admin.mesas.item.remover', $item->ventaItemID) }}"
                                                            class="text-red-400 hover:text-red-300 p-1" title="Eliminar ítem">
                                                            <i class="fa-solid fa-trash text-xs"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <!-- Formulario para Agregar Productos a la Cuenta -->
                                    <form action="{{ route('admin.mesas.item.agregar', $mesa->mesaID) }}" method="POST"
                                        class="grid grid-cols-1 md:grid-cols-4 gap-2 mesa-card-theme p-3 rounded-xl">
                                        @csrf
                                        <div class="md:col-span-2">
                                            <select name="productoID" id="select-prod-{{ $mesa->mesaID }}"
                                                onchange="handleProductChange({{ $mesa->mesaID }})"
                                                class="w-full form-input-mesa rounded-lg p-2 text-xs font-bold uppercase" required>
                                                <option value="">-- SELECCIONAR PRODUCTO --</option>
                                                @foreach ($productos as $p)
                                                    <option value="{{ $p->productoID }}" data-tipo="{{ $p->tipo }}"
                                                        data-precio="{{ $p->precio }}">
                                                        {{ strtoupper($p->nombre) }}
                                                        {{ $p->tipo === 'simple' ? '(Bs. ' . number_format($p->precio, 2) . ')' : '(CON VARIANTES)' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div id="container-variante-{{ $mesa->mesaID }}" class="hidden mt-2">
                                                <select name="varianteID" id="select-var-{{ $mesa->mesaID }}"
                                                    class="w-full form-input-mesa text-amber-500 rounded-lg p-2 text-xs font-bold uppercase">
                                                    <option value="">-- SELECCIONAR TAMAÑO / VARIANTE --</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <input type="number" name="cantidad" value="1" min="1"
                                                class="w-full form-input-mesa rounded-lg p-2 text-xs font-bold text-center"
                                                placeholder="Cant." required>
                                        </div>
                                        <div>
                                            <button type="submit"
                                                class="w-full bg-amber-600 hover:bg-amber-500 text-white font-black text-xs p-2 rounded-lg shadow uppercase">
                                                <i class="fa-solid fa-plus mr-1"></i>AGREGAR
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Totales y Saldo Adaptados a Modo Claro/Oscuro -->
                                    <div class="mesa-card-theme p-4 rounded-xl flex items-center justify-between border-amber-500/30">
                                        <div>
                                            <div class="text-[11px] font-extrabold uppercase"
                                                style="color:var(--color-text-muted, #9ca3af);">TOTAL CONSUMIDO</div>
                                            <div class="text-2xl font-black text-amber-500">Bs.
                                                {{ number_format($cuenta->monto_total, 2) }}</div>
                                            @if($cuenta->pagos->isNotEmpty())
                                                <div class="text-[10px] text-emerald-500 font-bold uppercase mt-1">
                                                    PAGADO: Bs. {{ number_format($cuenta->totalPagado(), 2) }} | PENDIENTE: Bs.
                                                    {{ number_format($cuenta->saldoPendiente(), 2) }}
                                                </div>
                                            @endif
                                        </div>

                                        <button type="button"
                                            onclick="abrirModalPago('{{ $cuenta->ventaID }}', '{{ $mesa->nombre }}', '{{ $cuenta->saldoPendiente() }}')"
                                            class="bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs py-3 px-5 rounded-xl shadow-lg uppercase flex items-center gap-2">
                                            <i class="fa-solid fa-calculator text-base"></i>COBRAR Y CERRAR
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="py-12 text-center text-gray-400">
                                    <i class="fa-solid fa-chair text-4xl mb-3 opacity-40"></i>
                                    <p class="text-xs font-bold uppercase">MESA DISPONIBLE PARA NUEVOS CLIENTES</p>
                                </div>
                            @endif
                        </div>
            @endforeach
        </div>
    </div>

    <!-- MODAL REGISTRO DE PAGO DIVIDIDO -->
    <div id="modal-pago" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="mesa-card-theme rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <h3 class="text-lg font-black uppercase text-amber-500" id="modal-pago-title">COBRAR CUENTA</h3>
            <p class="text-xs uppercase" style="color:var(--color-text-muted, #9ca3af);">REGISTRA EL PAGO (QR /
                EFECTIVO) Y CIERRA LA MESA</p>

            <form id="form-pago" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div class="mesa-card-theme p-4 rounded-xl text-center">
                        <div class="text-xs font-bold uppercase" style="color:var(--color-text-muted, #9ca3af);">SALDO
                            PENDIENTE A COBRAR</div>
                        <div class="text-3xl font-black text-emerald-500" id="modal-saldo-text">Bs. 0.00</div>
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
            selectVar.innerHTML = '<option value="">-- SELECCIONAR TAMAÑO / VARIANTE --</option>';

            if (!prodId) {
                containerVar.classList.add('hidden');
                return;
            }

            const selectedProd = productosData.find(p => p.productoID == prodId);

            if (selectedProd && selectedProd.tipo === 'variantes' && selectedProd.variantes.length > 0) {
                selectedProd.variantes.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.varianteID;
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