<!doctype html>
<html lang="es" class="dark-mode">

<head>
    <meta charset="utf-8">
    <script>(function () { var s = localStorage.getItem('rp_theme') || 'dark'; document.documentElement.className = s === 'light' ? 'light-mode' : 'dark-mode'; })();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GESTIÓN DE PEDIDOS - RICO POLLO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#FFE66D', accent: '#E23E1A', dark: '#09090c' } } } }</script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen" style="background-color:var(--color-bg);color:var(--color-text);">

    <!-- Navbar -->
    <header class="glass-card mb-6 p-4 border-b rounded-none"
        style="border-color:var(--color-card-border);background:var(--color-bg-alt)">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center justify-between w-full md:w-auto">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-8">
                        <img src="{{ asset('assets/logo.svg') }}" alt="LOGO" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="text-base md:text-lg font-black text-[#FFE66D] tracking-wider uppercase">RICO POLLO -
                            PANEL ADMIN</h1>
                    </div>
                </div>
                <!-- Botón Menú Hamburguesa para Móvil -->
                <button id="mobile-menu-btn"
                    class="md:hidden text-xl text-gray-300 hover:text-white p-2 focus:outline-none">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <div id="nav-menu" class="hidden md:flex items-center gap-2 flex-wrap w-full md:w-auto">
                <a href="{{ route('admin.pedidos') }}"
                    class="btn-primary text-xs font-black uppercase !py-2 !px-4 shadow-lg">
                    <i class="fa-solid fa-clipboard-list mr-1.5"></i>GESTIÓN DE PEDIDOS
                </a>
                <a href="{{ route('admin.productos') }}"
                    class="btn-outline text-xs font-bold uppercase !py-2 !px-4 hover:text-[#FFE66D]">
                    <i class="fa-solid fa-utensils mr-1.5"></i>GESTIÓN DE PRODUCTOS
                </a>
                @if(Session::get('is_super_admin') || Session::get('rolID') === 'ADMINISTRADOR')
                    <a href="{{ route('admin.usuarios') }}"
                        class="btn-outline text-xs font-bold uppercase !py-2 !px-3 hover:text-[#FFE66D]">
                        <i class="fa-solid fa-users mr-1.5"></i>USUARIOS
                    </a>
                @endif
                <a href="{{ route('admin.perfil') }}"
                    class="btn-outline text-xs font-bold uppercase !py-2 !px-3 hover:text-[#FFE66D]">
                    <i class="fa-solid fa-user-gear mr-1.5"></i>MI PERFIL
                </a>
                <a href="{{ route('menu') }}" class="btn-outline text-xs font-bold uppercase !py-2 !px-3"
                    target="_blank">
                    <i class="fa-solid fa-store mr-1.5"></i>VER TIENDA
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="btn-outline text-xs font-bold uppercase !py-2 !px-3 border-red-500/40 text-red-400 hover:bg-red-950/30">
                        <i class="fa-solid fa-right-from-bracket mr-1.5"></i>SALIR
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-black uppercase"><i
                        class="fa-solid fa-clipboard-list mr-2 text-[#FFE66D]"></i>PEDIDOS Y SOLICITUDES</h2>
                <span
                    class="text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 px-3 py-1 rounded-full animate-pulse flex items-center gap-1.5 ml-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> EN LÍNEA
                </span>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300 font-bold text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (empty($pedidos))
            <div class="glass-card p-12 text-center">
                <i class="fa-solid fa-box-open text-5xl mb-4 text-gray-500"></i>
                <h3 class="text-lg font-bold uppercase mb-2">NO HAY PEDIDOS REGISTRADOS</h3>
                <p class="text-xs text-gray-400">Las nuevas solicitudes realizadas por los clientes aparecerán aquí
                    automáticamente.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($pedidos as $pedido)
                        <?php
                    $est = strtolower($pedido['estado']);
                    $metodoPago = strtolower($pedido['metodo_pago'] ?? 'ninguno');
                    $phoneClean = preg_replace('/[^0-9]/', '', $pedido['cliente_telefono']);
                    $ticketUrl = route('ticket.show', $pedido['pedidoID']);

                    // ── Si el pedido fue aceptado y el cliente eligió QR → mostrar botón de comprobante ──
                    $esAceptadoQR = ($est === 'aceptado' && $metodoPago === 'qr');

                    // MENSAJES PREDETERMINADOS PARA WHATSAPP
                    $wspMsgs = [
                        'pendiente' => "Hola {$pedido['cliente_nombre']}, recibimos tu solicitud #{$pedido['numero_pedido']} en Rico Pollo. La estamos revisando, en breve te confirmamos. ¡Gracias por preferirnos! 🍗",
                        'aceptado' => "¡Hola {$pedido['cliente_nombre']}! 🎉 Tu pedido #{$pedido['numero_pedido']} ha sido *ACEPTADO* por Rico Pollo. 🍗 Estamos procesando tu pedido y en breve comenzaremos la preparación. Puedes hacer seguimiento aquí: {$ticketUrl}",
                        'aceptado_qr' => "¡Hola {$pedido['cliente_nombre']}! 🙌 Tu pedido #{$pedido['numero_pedido']} en *Rico Pollo* ha sido confirmado.\n\n"
                            . "💳 *Método de pago elegido: QR*\n"
                            . "💰 *Total a pagar: Bs. " . number_format($pedido['monto_total'], 2) . "*\n\n"
                            . "Por favor realiza la transferencia al código QR de Rico Pollo y *envíanos la captura o comprobante de pago* a este chat para proceder con la preparación de tu pedido.\n\n"
                            . "¡Muchas gracias! 😊🍗",
                        'preparando' => "¡Hola {$pedido['cliente_nombre']}! 👨‍🍳 Tu pedido #{$pedido['numero_pedido']} ya está siendo preparado en cocina. En breve estará listo. Puedes seguir el estado en vivo aquí: {$ticketUrl}",
                        'listo' => "¡Hola {$pedido['cliente_nombre']}! ✅ Tu pedido #{$pedido['numero_pedido']} está *LISTO* y empaquetado. 🛍️",
                        'en_camino' => "¡Hola {$pedido['cliente_nombre']}! 🛵💨 Tu pedido #{$pedido['numero_pedido']} ya va *EN CAMINO* a tu dirección. Puedes rastrearlo aquí: {$ticketUrl}",
                        'entregado' => "¡Muchas gracias por tu compra, {$pedido['cliente_nombre']}! 😋 Esperamos que disfrutes tu comida de Rico Pollo. Fue un placer atenderte. ¡Hasta pronto!",
                        'cancelado' => "Hola {$pedido['cliente_nombre']}, lamentamos informarte que tu solicitud #{$pedido['numero_pedido']} no pudo ser aceptada en este momento por falta de stock. Te pedimos disculpas y esperamos atenderte pronto. 🙏"
                    ];

                    // Seleccionar mensaje correcto: si es aceptado y QR → mensaje de comprobante
                    $msgKey = $esAceptadoQR ? 'aceptado_qr' : ($est);
                    $currWspMsg = urlencode($wspMsgs[$msgKey] ?? $wspMsgs['pendiente']);
                    $wspLink = "https://wa.me/591{$phoneClean}?text={$currWspMsg}";
                                                                                                         ?>

                        <div
                            class="glass-card p-5 border <?php        echo $est === 'pendiente' ? 'border-amber-500/50 bg-amber-500/5' : 'border-white/10'; ?>">
                            <div class="flex flex-col md:flex-row md:items-center justify-between border-b pb-4 mb-4 gap-4"
                                style="border-color:var(--color-card-border)">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg font-black text-[#FFE66D]">{{ $pedido['numero_pedido'] }}</span>
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                                                                                                                                                         @if($est === 'pendiente') bg-amber-500/20 text-amber-300 border border-amber-500/50 animate-pulse
                                                                                                                                                                         @elseif($est === 'aceptado') bg-blue-500/20 text-blue-300 border border-blue-500/40
                                                                                                                                                                         @elseif($est === 'preparando') bg-yellow-500/20 text-yellow-300 border border-yellow-500/40
                                                                                                                                                                         @elseif($est === 'listo') bg-emerald-500/20 text-emerald-300 border border-emerald-500/40
                                                                                                                                                                         @elseif($est === 'en_camino') bg-purple-500/20 text-purple-300 border border-purple-500/40
                                                                                                                                                                         @elseif($est === 'entregado') bg-green-500/20 text-green-300 border border-green-500/40
                                                                                                                                                                         @else bg-red-500/20 text-red-300 border border-red-500/40
                                                                                                                                                                         @endif">
                                            {{ strtoupper($pedido['estado']) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        <i class="fa-regular fa-clock mr-1"></i>{{ $pedido['fecha_creacion'] }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <!-- BOTÓN WHATSAPP: contextual según estado y método de pago -->
                                    <?php        if ($esAceptadoQR): ?>
                                    {{-- Pedido aceptado con QR: solicitar comprobante --}}
                                    <a href="{{ $wspLink }}" target="_blank"
                                        class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs py-1.5 px-3 rounded-lg flex items-center gap-1.5 shadow-md"
                                        title="Envía mensaje al cliente para que envíe el comprobante QR">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                        <i class="fa-solid fa-receipt text-sm"></i>
                                        SOLICITAR COMPROBANTE
                                    </a>
                                    <?php        else: ?>
                                    {{-- Mensaje estándar según el estado actual --}}
                                    <a href="{{ $wspLink }}" target="_blank"
                                        class="bg-green-600 hover:bg-green-500 text-white font-bold text-xs py-1.5 px-3 rounded-lg flex items-center gap-1.5 shadow-md">
                                        <i class="fa-brands fa-whatsapp text-sm"></i> ENVIAR WHATSAPP
                                    </a>
                                    <?php        endif; ?>

                                    <!-- ACCIONES SEGÚN ESTADO -->
                                    @if($est === 'pendiente')
                                        <!-- ACEPTAR O RECHAZAR SOLICITUD -->
                                        <form action="{{ route('admin.pedidos.aceptar', $pedido['pedidoID']) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs py-1.5 px-3 rounded-lg shadow-md uppercase">
                                                <i class="fa-solid fa-circle-check mr-1"></i>ACEPTAR SOLICITUD
                                            </button>
                                        </form>

                                        {{-- Botón que abre el modal personalizado --}}
                                        <button type="button"
                                            onclick="abrirModalRechazo('{{ route('admin.pedidos.rechazar', $pedido['pedidoID']) }}', '{{ $pedido['numero_pedido'] }}', '{{ addslashes($pedido['cliente_nombre']) }}')"
                                            class="bg-red-600 hover:bg-red-500 text-white font-black text-xs py-1.5 px-3 rounded-lg shadow-md uppercase">
                                            <i class="fa-solid fa-circle-xmark mr-1"></i>RECHAZAR
                                        </button>
                                    @else
                                                    <?php
                                        // Avance secuencial: cada estado tiene solo un siguiente paso
                                        $siguienteEstado = [
                                            'aceptado' => ['estado' => 'preparando', 'label' => 'MARCAR EN COCINA', 'icon' => 'fa-fire-burner', 'color' => 'bg-yellow-500 hover:bg-yellow-400'],
                                            'preparando' => ['estado' => 'listo', 'label' => 'MARCAR LISTO', 'icon' => 'fa-box-open', 'color' => 'bg-emerald-500 hover:bg-emerald-400'],
                                            'listo' => ['estado' => 'en_camino', 'label' => 'MARCAR EN CAMINO', 'icon' => 'fa-motorcycle', 'color' => 'bg-purple-500 hover:bg-purple-400'],
                                            'en_camino' => ['estado' => 'entregado', 'label' => 'MARCAR ENTREGADO', 'icon' => 'fa-circle-check', 'color' => 'bg-green-500 hover:bg-green-400'],
                                            'entregado' => null,
                                            'cancelado' => null,
                                        ];
                                        $next = $siguienteEstado[$est] ?? null;
                                                                                                                                                                                                                                                                        ?>
                                                    @if($next)
                                                        <form action="{{ route('admin.pedidos.estado', $pedido['pedidoID']) }}" method="POST"
                                                            class="inline">
                                                            @csrf
                                                            <input type="hidden" name="estado" value="{{ $next['estado'] }}">
                                                            <button type="submit"
                                                                class="text-white font-black text-xs py-2 px-4 rounded-lg shadow-md uppercase flex items-center gap-1.5 {{ $next['color'] }}">
                                                                <i class="fa-solid {{ $next['icon'] }}"></i>
                                                                {{ $next['label'] }}
                                                            </button>
                                                        </form>
                                                    @elseif($est === 'entregado')
                                                        <span
                                                            class="text-xs font-bold text-green-400 bg-green-500/10 border border-green-500/30 px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                                                            <i class="fa-solid fa-circle-check"></i> COMPLETADO
                                                        </span>
                                                    @endif
                                    @endif

                                    <a href="{{ route('ticket.show', $pedido['pedidoID']) }}" target="_blank"
                                        class="btn-outline text-xs font-bold py-1.5 px-3">
                                        <i class="fa-solid fa-eye mr-1"></i>VER
                                    </a>
                                </div>
                            </div>

                            <!-- Cliente & Entrega -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs mb-4">
                                <div>
                                    <span class="block font-bold text-gray-400">CLIENTE</span>
                                    <span class="font-black text-sm uppercase text-white">{{ $pedido['cliente_nombre'] }}</span>
                                    <span class="block text-gray-300 mt-0.5"><i
                                            class="fa-solid fa-phone mr-1 text-green-400"></i>{{ $pedido['cliente_telefono'] }}</span>
                                </div>
                                <div>
                                    <span class="block font-bold text-gray-400">DIRECCIÓN / MESA</span>
                                    <span
                                        class="font-bold uppercase text-white">{{ $pedido['direccion_entrega'] ?: ($pedido['numero_mesa'] ? 'MESA ' . $pedido['numero_mesa'] : 'LOCAL') }}</span>
                                    @if(!empty($pedido['latitud']) && !empty($pedido['longitud']))
                                        <a href="https://www.google.com/maps?q={{ $pedido['latitud'] }},{{ $pedido['longitud'] }}"
                                            target="_blank" class="block text-blue-400 hover:underline mt-0.5">
                                            <i class="fa-solid fa-map-pin mr-1"></i>VER UBICACIÓN GPS
                                        </a>
                                    @endif
                                </div>
                                <div>
                                    <span class="block font-bold text-gray-400">MÉTODO DE PAGO</span>
                                    <span
                                        class="font-black uppercase text-[#FFE66D]"><?php        echo (!empty($pedido['metodo_pago']) && $pedido['metodo_pago'] !== 'ninguno') ? strtoupper($pedido['metodo_pago']) : 'PENDIENTE DE SELECCIÓN'; ?></span>
                                    <span class="block font-black text-sm mt-1 text-white">TOTAL:
                                        Bs.{{ number_format($pedido['monto_total'], 2) }}</span>
                                </div>
                            </div>

                            <!-- Items -->
                            @if(!empty($pedido['items']))
                                <div class="border-t pt-3" style="border-color:var(--color-card-border)">
                                    <h4 class="text-[11px] font-bold text-gray-400 uppercase mb-2">PRODUCTOS SOLICITADOS</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                        @foreach($pedido['items'] as $item)
                                            <div
                                                class="bg-black/40 p-2 rounded-lg text-xs flex justify-between items-center border border-white/5">
                                                <div>
                                                    <span
                                                        class="font-bold uppercase text-gray-200">{{ $item['nombre_variante'] ?: 'PRODUCTO #' . $item['productoID'] }}</span>
                                                    <span class="text-gray-400 block text-[10px]">Cantidad: {{ $item['cantidad'] }}</span>
                                                </div>
                                                <span
                                                    class="font-bold text-[#FFE66D]">Bs.{{ number_format($item['precio_total'], 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- SCRIPT AUTO-RELOAD EN TIEMPO REAL CON SONIDO -->
    <script>
        // SINTETIZADOR DE SONIDO DE CAMPANILLA (WEB AUDIO API)
        function playChimeSound() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();

                const now = ctx.currentTime;
                // Nota 1
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now); // D5
                gain1.gain.setValueAtTime(0.3, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.6);

                // Nota 2 (Campanilla de aviso)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.15); // A5
                gain2.gain.setValueAtTime(0.4, now + 0.15);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.15);
                osc2.stop(now + 0.8);
            } catch (e) {
                console.log('Audio Context Error:', e);
            }
        }

        // POLLING EN TIEMPO REAL CADA 3.5 SEGUNDOS
        let lastHash = null;
        let lastMaxId = null;

        function checkRealtimeOrders() {
            fetch('{{ route("api.admin.pedidos") }}')
                .then(res => res.json())
                .then(data => {
                    if (data && data.hash_state) {
                        if (lastHash === null) {
                            lastHash = data.hash_state;
                            lastMaxId = data.max_id;
                        } else if (data.hash_state !== lastHash) {
                            // SI LLEGÓ UN NUEVO PEDIDO
                            if (data.max_id > lastMaxId) {
                                playChimeSound();
                            }
                            lastHash = data.hash_state;
                            lastMaxId = data.max_id;
                            // RECARGA DE LA PANTALLA
                            setTimeout(() => {
                                window.location.reload();
                            }, 300);
                        }
                    }
                })
                .catch(err => console.log('Polling error:', err));
        }

        setInterval(checkRealtimeOrders, 3500);

        document.getElementById('mobile-menu-btn')?.addEventListener('click', function () {
            const menu = document.getElementById('nav-menu');
            menu.classList.toggle('hidden');
            menu.classList.toggle('flex');
            menu.classList.toggle('flex-col');
        });
    </script>

    <!-- ═══════════════════════════════════════════════════════════
         MODAL DE CONFIRMACIÓN DE RECHAZO
    ═══════════════════════════════════════════════════════════ -->
    <div id="modal-rechazo" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none!important; background:rgba(0,0,0,0.75); backdrop-filter:blur(6px);">
        <div id="modal-rechazo-box"
            class="glass-card w-full max-w-sm p-6 rounded-2xl shadow-2xl border border-red-500/40"
            style="background:var(--color-bg-alt); transform:scale(0.92); opacity:0; transition: transform 0.25s cubic-bezier(.34,1.56,.64,1), opacity 0.2s ease;">

            <!-- Ícono -->
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 rounded-full flex items-center justify-center"
                    style="background:rgba(239,68,68,0.15); border:2px solid rgba(239,68,68,0.4);">
                    <i class="fa-solid fa-triangle-exclamation text-2xl text-red-400"></i>
                </div>
            </div>

            <!-- Título -->
            <h3 class="text-center text-base font-black uppercase tracking-wider text-white mb-1">
                Rechazar Solicitud
            </h3>
            <p id="modal-rechazo-subtitle" class="text-center text-xs text-gray-400 mb-5"></p>

            <!-- Separador -->
            <div class="border-t mb-5" style="border-color:var(--color-card-border);"></div>

            <!-- Advertencia -->
            <p class="text-xs text-center text-red-300 mb-5 font-semibold">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Esta acción no se puede deshacer. El cliente será notificado del rechazo.
            </p>

            <!-- Botones -->
            <div class="flex gap-3">
                <button onclick="cerrarModalRechazo()"
                    class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold uppercase border border-white/20 text-gray-300 hover:bg-white/10 transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i>CANCELAR
                </button>
                <form id="modal-rechazo-form" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full py-2.5 px-4 rounded-xl text-xs font-black uppercase bg-red-600 hover:bg-red-500 text-white transition-colors shadow-lg">
                        <i class="fa-solid fa-circle-xmark mr-1"></i>SÍ, RECHAZAR
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // ── MODAL DE RECHAZO ──────────────────────────────────────────
        function abrirModalRechazo(actionUrl, numeroPedido, clienteNombre) {
            document.getElementById('modal-rechazo-form').action = actionUrl;
            document.getElementById('modal-rechazo-subtitle').textContent =
                '¿Deseas rechazar el pedido ' + numeroPedido + ' de ' + clienteNombre + '?';

            const modal = document.getElementById('modal-rechazo');
            const box = document.getElementById('modal-rechazo-box');

            modal.style.setProperty('display', 'flex', 'important');
            // Forzar reflow para que la transición funcione
            void box.offsetWidth;
            box.style.transform = 'scale(1)';
            box.style.opacity = '1';
        }

        function cerrarModalRechazo() {
            const modal = document.getElementById('modal-rechazo');
            const box = document.getElementById('modal-rechazo-box');
            box.style.transform = 'scale(0.92)';
            box.style.opacity = '0';
            setTimeout(() => modal.style.setProperty('display', 'none', 'important'), 220);
        }

        // Cerrar al hacer clic fuera del box
        document.getElementById('modal-rechazo').addEventListener('click', function (e) {
            if (e.target === this) cerrarModalRechazo();
        });

        // Cerrar con ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') cerrarModalRechazo();
        });
    </script>

</body>

</html>