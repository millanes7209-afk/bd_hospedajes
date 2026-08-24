<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS MESAS & CUENTAS - SALTEÑERÍA MONAKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar Unificada -->
    @include('layouts.admin_navbar')

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-black uppercase"><i class="fa-solid fa-chair mr-2 text-[#FFE66D]"></i>CONTROL DE
                MESAS Y CUENTAS ABIERTAS</h2>
        </div>

        @if (session('success'))
            <div
                class="mb-4 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300 font-bold text-sm uppercase">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-4 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 font-bold text-sm uppercase">
                {{ session('error') }}
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
                                style="border-color:var(--color-card-border)">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl font-black uppercase text-[#FFE66D]">{{ strtoupper($mesa->nombre) }}</span>
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $isOcupada ? 'bg-amber-500/20 text-amber-300 border border-amber-500/50 animate-pulse' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/50' }}">
                                        {{ $isOcupada ? 'OCUPADA' : 'LIBRE' }}
                                    </span>
                                </div>

                                @if(!$isOcupada)
                                    <form action="{{ route('admin.mesas.abrir', $mesa->mesaID) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs py-2 px-4 rounded-xl shadow-md uppercase transition-all">
                                            <i class="fa-solid fa-door-open mr-1.5"></i>ABRIR CUENTA
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Contenido Cuenta Activa -->
                            @if($isOcupada && $cuenta)
                                <div class="space-y-4 mb-6">
                                    <div class="text-xs text-gray-400">
                                        <i class="fa-regular fa-clock mr-1"></i>Apertura:
                                        {{ date('H:i - d/m/Y', strtotime($cuenta->fecha_apertura)) }}
                                    </div>

                                    <!-- Lista de Ítems Consumidos -->
                                    <div class="bg-black/30 rounded-xl p-4 border border-white/5 space-y-2 max-h-48 overflow-y-auto">
                                        @if($cuenta->items->isEmpty())
                                            <p class="text-xs text-gray-400 italic text-center py-2">Sin consumos agregados aún.</p>
                                        @else
                                            @foreach ($cuenta->items as $item)
                                                <div
                                                    class="flex items-center justify-between text-xs py-1 border-b border-white/5 last:border-0">
                                                    <div>
                                                        <span class="font-bold text-white">{{ $item->cantidad }}x</span>
                                                        <span class="font-semibold text-gray-200">{{ $item->nombre_producto }}
                                                            {{ $item->nombre_variante ? '(' . $item->nombre_variante . ')' : '' }}</span>
                                                        @if($item->nota)
                                                            <span class="block text-[10px] text-amber-400">Nota: {{ $item->nota }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <span
                                                            class="font-extrabold text-[#FFE66D]">Bs.{{ number_format($item->precio_total, 2) }}</span>
                                                        <a href="{{ route('admin.mesas.item.remover', $item->ventaItemID) }}"
                                                            class="text-red-400 hover:text-red-300 p-1" title="Eliminar ítem">
                                                            <i class="fa-solid fa-trash text-xs"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <!-- Botón para Agregar Productos a la Cuenta -->
                                    <form action="{{ route('admin.mesas.item.agregar', $mesa->mesaID) }}" method="POST"
                                        class="grid grid-cols-1 md:grid-cols-4 gap-2 bg-white/5 p-3 rounded-xl border border-white/10">
                                        @csrf
                                        <div class="md:col-span-2">
                                            <select name="productoID" id="select-prod-{{ $mesa->mesaID }}"
                                                onchange="handleProductChange({{ $mesa->mesaID }})"
                                                class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs font-bold uppercase"
                                                required>
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
                                                    class="w-full bg-slate-900 border border-slate-700 text-amber-300 rounded-lg p-2 text-xs font-bold uppercase">
                                                    <option value="">-- SELECCIONAR TAMAÑO / VARIANTE --</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <input type="number" name="cantidad" value="1" min="1"
                                                class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-xs font-bold text-center"
                                                placeholder="Cant." required>
                                        </div>
                                        <div>
                                            <button type="submit"
                                                class="w-full bg-amber-600 hover:bg-amber-500 text-white font-black text-xs p-2 rounded-lg shadow uppercase">
                                                <i class="fa-solid fa-plus mr-1"></i>AGREGAR
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Totales y Saldo -->
                                    <div
                                        class="bg-black/40 p-4 rounded-xl border border-amber-500/30 flex items-center justify-between">
                                        <div>
                                            <div class="text-xs text-gray-400 font-bold uppercase">TOTAL CONSUMIDO</div>
                                            <div class="text-2xl font-black text-[#FFE66D]">
                                                Bs.{{ number_format($cuenta->monto_total, 2) }}</div>
                                            @if($cuenta->pagos->isNotEmpty())
                                                <div class="text-[10px] text-emerald-400 font-bold uppercase mt-1">
                                                    PAGADO: Bs.{{ number_format($cuenta->totalPagado(), 2) }} | PENDIENTE:
                                                    Bs.{{ number_format($cuenta->saldoPendiente(), 2) }}
                                                </div>
                                            @endif
                                        </div>

                                        <button type="button"
                                            onclick="abrirModalPago('{{ $cuenta->ventaID }}', '{{ $mesa->nombre }}', '{{ $cuenta->saldoPendiente() }}')"
                                            class="bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500 text-white font-black text-xs py-3 px-5 rounded-xl shadow-lg uppercase flex items-center gap-2">
                                            <i class="fa-solid fa-calculator text-base"></i>COBRAR Y CERRAR
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="py-12 text-center text-gray-500">
                                    <i class="fa-solid fa-chair text-4xl mb-3"></i>
                                    <p class="text-xs font-bold uppercase">MESA DISPONIBLE PARA NUEVOS CLIENTES</p>
                                </div>
                            @endif
                        </div>
            @endforeach
        </div>
    </div>

    <!-- MODAL REGISTRO DE PAGO DIVIDIDO -->
    <div id="modal-pago" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-[#181b21] border border-amber-500/40 rounded-2xl p-6 max-w-md w-full shadow-2xl">
            <h3 class="text-lg font-black uppercase text-[#FFE66D] mb-1" id="modal-pago-title">COBRAR CUENTA</h3>
            <p class="text-xs text-gray-400 mb-4 uppercase">REGISTRA EL PAGO (QR / EFECTIVO) Y CIERRA LA MESA</p>

            <form id="form-pago" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div class="bg-black/40 p-4 rounded-xl text-center border border-white/10">
                        <div class="text-xs text-gray-400 font-bold uppercase">SALDO PENDIENTE A COBRAR</div>
                        <div class="text-3xl font-black text-emerald-400" id="modal-saldo-text">Bs.0.00</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase mb-1">MÉTODO DE PAGO</label>
                        <select name="metodo_pago"
                            class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl p-3 text-xs font-bold uppercase"
                            required>
                            <option value="efectivo">💵 EFECTIVO</option>
                            <option value="qr">📱 PAGO QR</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase mb-1">MONTO A ABONAR (Bs.)</label>
                        <input type="number" step="0.01" name="monto" id="input-monto-pago"
                            class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl p-3 text-sm font-bold text-center"
                            required>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" onclick="cerrarModalPago()"
                        class="flex-1 py-3 px-4 rounded-xl text-xs font-bold uppercase border border-white/20 text-gray-300 hover:bg-white/10 transition-colors">
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
            document.getElementById('modal-saldo-text').textContent = 'Bs.' + parseFloat(saldo).toFixed(2);
            document.getElementById('input-monto-pago').value = parseFloat(saldo).toFixed(2);
            document.getElementById('modal-pago').style.display = 'flex';
        }

        function cerrarModalPago() {
            document.getElementById('modal-pago').style.display = 'none';
        }
    </script>
</body>

</html>