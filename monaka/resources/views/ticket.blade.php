<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ESTADO DE PEDIDO - {{ $pedido['numero_pedido'] ?? '' }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap"
    rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Outfit', sans-serif;
      background: #0f1115;
      color: #f3f4f6;
      padding: 20px 15px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .container {
      width: 100%;
      max-width: 480px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* CARD DE ESTADO PRINCIPAL */
    .status-card {
      background: #181b21;
      border: 1px solid #2a2e39;
      border-radius: 16px;
      padding: 24px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .badge-status {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 50px;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 15px;
    }

    .badge-pendiente {
      background: rgba(255, 179, 0, 0.15);
      color: #ffb300;
      border: 1px solid #ffb300;
    }

    .badge-aceptado {
      background: rgba(59, 130, 246, 0.15);
      color: #60a5fa;
      border: 1px solid #3b82f6;
    }

    .badge-preparando {
      background: rgba(245, 158, 11, 0.15);
      color: #fbbf24;
      border: 1px solid #f59e0b;
    }

    .badge-listo {
      background: rgba(16, 185, 129, 0.15);
      color: #34d399;
      border: 1px solid #10b981;
    }

    .badge-en_camino {
      background: rgba(139, 92, 246, 0.15);
      color: #a78bfa;
      border: 1px solid #8b5cf6;
    }

    .badge-entregado {
      background: rgba(34, 197, 94, 0.2);
      color: #4ade80;
      border: 1px solid #22c55e;
    }

    .badge-cancelado {
      background: rgba(239, 68, 68, 0.15);
      color: #f87171;
      border: 1px solid #ef4444;
    }

    .status-title {
      font-size: 20px;
      font-weight: 800;
      margin-bottom: 8px;
      color: #ffffff;
    }

    .status-desc {
      font-size: 14px;
      color: #9ca3af;
      line-height: 1.5;
    }

    /* TIMELINE DE PROGRESO */
    .timeline {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 25px;
      position: relative;
      padding: 0 10px;
    }

    .timeline::before {
      content: '';
      position: absolute;
      top: 18px;
      left: 30px;
      right: 30px;
      height: 3px;
      background: #2a2e39;
      z-index: 1;
    }

    .timeline-progress {
      position: absolute;
      top: 18px;
      left: 30px;
      height: 3px;
      background: #ff5722;
      z-index: 2;
      transition: width 0.4s ease;
      width: 0%;
    }

    .timeline-step {
      position: relative;
      z-index: 3;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
    }

    .step-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #181b21;
      border: 2px solid #2a2e39;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      color: #6b7280;
      transition: all 0.3s ease;
    }

    .timeline-step.active .step-icon {
      border-color: #ff5722;
      background: #ff5722;
      color: white;
      box-shadow: 0 0 12px rgba(255, 87, 34, 0.5);
    }

    .timeline-step.completed .step-icon {
      border-color: #22c55e;
      background: #22c55e;
      color: white;
    }

    .step-label {
      font-size: 10px;
      font-weight: 600;
      color: #6b7280;
    }

    .timeline-step.active .step-label,
    .timeline-step.completed .step-label {
      color: #f3f4f6;
    }

    /* MÓDULO DE SELECCIÓN DE PAGO (CUANDO ES ACEPTADO) */
    .payment-module {
      background: #181b21;
      border: 1px solid #ff9800;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 10px 25px rgba(255, 152, 0, 0.15);
    }

    .payment-title {
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 12px;
      color: #ffb74d;
      text-align: center;
    }

    .payment-options {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 15px;
    }

    .payment-btn {
      background: #222630;
      border: 2px solid #2a2e39;
      border-radius: 12px;
      padding: 14px 10px;
      color: white;
      font-weight: 700;
      font-size: 14px;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      transition: all 0.2s ease;
    }

    .payment-btn:hover,
    .payment-btn.selected {
      border-color: #ff5722;
      background: rgba(255, 87, 34, 0.15);
      color: #ff7043;
    }

    .payment-box {
      display: none;
      background: #222630;
      border-radius: 12px;
      padding: 15px;
      margin-top: 10px;
      text-align: center;
    }

    .payment-box.active {
      display: block;
    }

    .qr-img {
      width: 180px;
      height: 180px;
      border-radius: 8px;
      background: white;
      padding: 8px;
      margin: 10px auto;
      display: block;
    }

    .input-cash {
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #374151;
      background: #111827;
      color: white;
      font-size: 14px;
      text-align: center;
      margin-top: 8px;
    }

    .btn-confirm-pay {
      width: 100%;
      background: linear-gradient(135deg, #ff5722, #e64a19);
      color: white;
      border: none;
      padding: 14px;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      margin-top: 15px;
      box-shadow: 0 4px 15px rgba(255, 87, 34, 0.4);
    }

    .btn-confirm-pay:hover {
      opacity: 0.9;
    }

    /* TICKET COMPROBANTE ESTÁTICO DE COMPRA */
    .ticket-paper {
      background: #ffffff;
      color: #111827;
      font-family: 'Space Mono', monospace;
      padding: 24px 20px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      position: relative;
    }

    .ticket-paper::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      right: 0;
      height: 8px;
      background: radial-gradient(circle, transparent, transparent 50%, #ffffff 50%, #ffffff 100%);
      background-size: 16px 16px;
    }

    .paper-header {
      text-align: center;
      border-bottom: 2px dashed #9ca3af;
      padding-bottom: 12px;
      margin-bottom: 12px;
    }

    .paper-header h2 {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .paper-header p {
      font-size: 11px;
      color: #4b5563;
    }

    .paper-details {
      font-size: 12px;
      line-height: 1.6;
      margin-bottom: 12px;
    }

    .paper-items {
      border-top: 1px dashed #9ca3af;
      border-bottom: 1px dashed #9ca3af;
      padding: 10px 0;
      margin-bottom: 12px;
    }

    .paper-item {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      margin-bottom: 4px;
    }

    .paper-total {
      text-align: right;
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .paper-footer {
      text-align: center;
      font-size: 10px;
      color: #6b7280;
      border-top: 1px dashed #9ca3af;
      padding-top: 10px;
    }

    /* BOTONES DE ACCIÓN (RECLAMO & WHATSAPP) */
    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .btn-action {
      padding: 12px 18px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 14px;
      text-decoration: none;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border: none;
      cursor: pointer;
    }

    .btn-whatsapp-support {
      background: #25D366;
      color: white;
    }

    .btn-whatsapp-support:hover {
      background: #1ebd59;
    }

    .btn-menu {
      background: #2a2e39;
      color: white;
    }

    .btn-menu:hover {
      background: #374151;
    }

    .pulse {
      animation: pulse-animation 2s infinite;
    }

    @keyframes pulse-animation {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.03);
      }

      100% {
        transform: scale(1);
      }
    }

    @media print {
      @page {
        size: 58mm auto;
        margin: 0mm;
      }

      body {
        background: #ffffff !important;
        color: #000000 !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 58mm !important;
        font-family: 'Courier New', Courier, monospace !important;
        font-size: 11px !important;
      }

      .status-card,
      .payment-module,
      .action-buttons,
      .timeline,
      header,
      footer {
        display: none !important;
      }

      .container {
        max-width: 58mm !important;
        width: 58mm !important;
        padding: 0 !important;
        margin: 0 !important;
      }

      .ticket-paper {
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 2mm !important;
        width: 58mm !important;
        color: #000000 !important;
      }

      .ticket-paper::after {
        display: none !important;
      }

      .paper-header h2 {
        font-size: 15px !important;
        font-weight: 900 !important;
      }

      .paper-header p,
      .paper-details,
      .paper-item,
      .paper-footer {
        font-size: 10px !important;
        line-height: 1.3 !important;
      }

      .paper-total {
        font-size: 13px !important;
        font-weight: 900 !important;
      }
    }
    }
  </style>
</head>

<body>
  <div class="container">

    <!-- BANNER: MANTENER VENTANA ABIERTA -->
    <div
      style="background:rgba(255,230,109,0.08);border:1px solid rgba(255,230,109,0.35);border-radius:14px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:14px;">
      <div style="font-size:22px;flex-shrink:0;">🔔</div>
      <div>
        <p style="font-size:13px;font-weight:800;color:#FFE66D;text-transform:uppercase;margin:0 0 3px;">Mantenga esta
          ventana abierta</p>
        <p style="font-size:12px;color:#d1d5db;margin:0;">El estado de su pedido se actualizará aquí en tiempo real.
          Cerrar esta ventana puede ocasionar la pérdida del seguimiento de su orden.</p>
      </div>
    </div>

    <!-- 1. TARJETA PRINCIPAL DE ESTADO -->
    <div class="status-card" id="statusCard">
      <?php
$estado = strtolower($pedido['estado'] ?? 'pendiente');
$badges = [
  'pendiente' => ['text' => 'SOLICITUD RECIBIDA', 'class' => 'badge-pendiente', 'icon' => '🕒', 'title' => 'Esperando Confirmación del Local', 'desc' => 'Tu solicitud fue enviada a la cocina de Rico Pollo. Estamos verificando la disponibilidad de stock.'],
  'aceptado' => ['text' => 'SOLICITUD ACEPTADA', 'class' => 'badge-aceptado', 'icon' => '✅', 'title' => '¡Solicitud Aprobada!', 'desc' => 'Tu pedido fue aceptado por la pollería. Selecciona tu forma de pago a continuación para comenzar la preparación.'],
  'preparando' => ['text' => 'EN PREPARACIÓN', 'class' => 'badge-preparando', 'icon' => '🍳', 'title' => 'Cocinando tu Pedido', 'desc' => '¡Tu pedido se está preparando en cocina con todo el sabor de Rico Pollo!'],
  'listo' => ['text' => 'PEDIDO LISTO', 'class' => 'badge-listo', 'icon' => '🛍️', 'title' => 'Empaquetado y Listo', 'desc' => 'Tu pedido está empacado en el local listo para la entrega.'],
  'en_camino' => ['text' => 'EN CAMINO', 'class' => 'badge-en_camino', 'icon' => '🛵', 'title' => 'Delivery en Ruta', 'desc' => 'El repartidor lleva tu pedido hacia tu dirección.'],
  'entregado' => ['text' => 'ENTREGADO', 'class' => 'badge-entregado', 'icon' => '🎉', 'title' => '¡Pedido Entregado!', 'desc' => '¡Gracias por tu compra! Esperamos que disfrutes tu comida.'],
  'cancelado' => ['text' => 'RECHAZADO / CANCELADO', 'class' => 'badge-cancelado', 'icon' => '❌', 'title' => 'Solicitud No Aceptada', 'desc' => 'Lamentablemente la pollería no pudo aceptar tu solicitud por falta de stock o alta demanda. Te pedimos disculpas.']
];
$curr = $badges[$estado] ?? $badges['pendiente'];
      ?>

      <div class="badge-status <?php echo $curr['class']; ?>">
        <?php echo $curr['icon'] . ' ' . $curr['text']; ?>
      </div>
      <h1 class="status-title"><?php echo $curr['title']; ?></h1>
      <p class="status-desc"><?php echo $curr['desc']; ?></p>

      <!-- TIMELINE DE SEGUIMIENTO (PARA ESTADOS ACTIVOS) -->
      <?php if (!in_array($estado, ['cancelado', 'pendiente'])): ?>
      <div class="timeline">
        <?php
  $steps = ['aceptado' => 1, 'preparando' => 2, 'listo' => 3, 'en_camino' => 4, 'entregado' => 5];
  $currentStep = $steps[$estado] ?? 1;
  $progressWidth = (($currentStep - 1) / 4) * 100;
        ?>
        <div class="timeline-progress" style="width: <?php  echo $progressWidth; ?>%;"></div>

        <div
          class="timeline-step <?php  echo $currentStep >= 1 ? ($currentStep == 1 ? 'active' : 'completed') : ''; ?>">
          <div class="step-icon">✔</div>
          <span class="step-label">Aceptado</span>
        </div>
        <div
          class="timeline-step <?php  echo $currentStep >= 2 ? ($currentStep == 2 ? 'active' : 'completed') : ''; ?>">
          <div class="step-icon">🍳</div>
          <span class="step-label">Cocina</span>
        </div>
        <div
          class="timeline-step <?php  echo $currentStep >= 3 ? ($currentStep == 3 ? 'active' : 'completed') : ''; ?>">
          <div class="step-icon">📦</div>
          <span class="step-label">Listo</span>
        </div>
        <div
          class="timeline-step <?php  echo $currentStep >= 4 ? ($currentStep == 4 ? 'active' : 'completed') : ''; ?>">
          <div class="step-icon">🛵</div>
          <span class="step-label">Ruta</span>
        </div>
        <div
          class="timeline-step <?php  echo $currentStep >= 5 ? ($currentStep == 5 ? 'active' : 'completed') : ''; ?>">
          <div class="step-icon">🎉</div>
          <span class="step-label">Entrega</span>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- 2. MÓDULO DE SELECCIÓN DE PAGO (SOLO CUANDO EL ESTADO ES 'ACEPTADO') -->
    <?php if ($estado === 'aceptado'): ?>
    <div class="payment-module pulse" id="paymentModule">
      <h3 class="payment-title">💳 SELECCIONA TU MÉTODO DE PAGO</h3>
      <p style="font-size:12px; color:#cbd5e1; text-align:center; margin-bottom:12px;">Tu solicitud fue aprobada. Elige
        cómo deseas pagar para emitir tu ticket oficial:</p>

      <div class="payment-options">
        <button class="payment-btn" onclick="selectPayment('qr')">
          <span style="font-size:24px;">📱</span>
          <span>PAGO QR</span>
        </button>
        <button class="payment-btn" onclick="selectPayment('efectivo')">
          <span style="font-size:24px;">💵</span>
          <span>EFECTIVO</span>
        </button>
      </div>

      <!-- OPCION QR -->
      <div class="payment-box" id="boxQr">
        <?php
  // =====================================================
  // NÚMERO WHATSAPP DE RICO POLLO (CON CÓDIGO DE PAÍS)
  // Cambia este número por el de tu empresa
  $empresaWspNum = '59176543210'; // REEMPLAZA ESTE NÚMERO
  $wspQrData = urlencode("https://wa.me/{$empresaWspNum}");
  $qrImgUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={$wspQrData}";
        ?>
        <p style="font-size:13px; font-weight:bold; color:#ffb74d; text-align:center;">
          📱 Escanea el QR para pagar por WhatsApp a Rico Pollo:
        </p>
        <p style="font-size:11px; color:#9ca3af; text-align:center; margin-bottom:8px;">
          Al escanear, se abrirá WhatsApp de Rico Pollo. Envía una imagen o captura de tu transferencia como
          confirmación.
        </p>
        <div
          style="background:white; padding:10px; display:inline-block; border-radius:10px; margin:10px auto; display:block; text-align:center;">
          <img src="<?php  echo $qrImgUrl; ?>" alt="QR WhatsApp Rico Pollo" width="180" height="180"
            style="border-radius:6px;">
        </div>
        <p style="font-size:13px; font-weight:800; color:#FFE66D; text-align:center; margin-top:10px;">
          💰 Monto a pagar: <span
            style="font-size:18px;">Bs.<?php  echo number_format($pedido['monto_total'], 2); ?></span>
        </p>
        <p style="font-size:11px; color:#9ca3af; text-align:center; margin-bottom:4px;">
          Una vez realizado el pago, presiona el botón de abajo para notificar al local.
        </p>
        <button class="btn-confirm-pay" onclick="submitPayment('qr')">✅ YA REALICÉ EL PAGO POR QR</button>

      </div>

      <!-- OPCION EFECTIVO -->
      <div class="payment-box" id="boxEfectivo">
        <p style="font-size:13px; font-weight:bold; color:#ffb74d;">Pago al momento de la entrega:</p>
        <p style="font-size:12px; color:#9ca3af; margin-top:5px;">¿Con cuánto dinero vas a pagar? (Para que el
          repartidor lleve cambio):</p>
        <input type="number" id="inputMontoEfectivo" class="input-cash" placeholder="Ejemplo: 100 Bs"
          min="<?php  echo $pedido['monto_total']; ?>">
        <button class="btn-confirm-pay" onclick="submitPayment('efectivo')">CONFIRMAR PAGO EN EFECTIVO</button>
      </div>
    </div>
    <?php endif; ?>

    <!-- 3. TICKET ESTÁTICO DE COMPRA (SOLO VISIBLE CUANDO EL PEDIDO NO ESTÁ CANCELADO NI EN PENDIENTE SIN APROBAR) -->
    <?php if ($estado !== 'cancelado'): ?>
    <div class="ticket-paper">
      <div class="paper-header">
        <h2>RICO POLLO</h2>
        <p><?php  echo ($estado === 'pendiente') ? 'SOLICITUD DE PEDIDO' : 'COMPROBANTE OFICIAL DE PEDIDO'; ?></p>
        <p style="font-weight:bold; margin-top:4px; font-size:13px;"><?php  echo $pedido['numero_pedido']; ?></p>
        <p><?php  echo date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])); ?></p>
      </div>

      <div class="paper-details">
        <div><strong>CLIENTE:</strong> <?php  echo htmlspecialchars($pedido['cliente_nombre']); ?></div>
        <div><strong>TELÉFONO:</strong> <?php  echo htmlspecialchars($pedido['cliente_telefono']); ?></div>
        <div><strong>TIPO:</strong> <?php  echo strtoupper($pedido['tipo_pedido']); ?></div>
        <?php  if (!empty($pedido['direccion_entrega'])): ?>
        <div><strong>DIRECCIÓN:</strong> <?php    echo htmlspecialchars($pedido['direccion_entrega']); ?></div>
        <?php  endif; ?>
        <?php  if (!empty($pedido['metodo_pago']) && $pedido['metodo_pago'] !== 'ninguno'): ?>
        <div><strong>PAGO:</strong> <?php    echo strtoupper($pedido['metodo_pago']); ?></div>
        <?php  endif; ?>
        <?php  if (!empty($pedido['nota'])): ?>
        <div><strong>NOTA:</strong> <?php    echo htmlspecialchars($pedido['nota']); ?></div>
        <?php  endif; ?>
      </div>

      <div class="paper-items">
        <?php  foreach ($items as $item): ?>
        <div class="paper-item">
          <span><?php    echo $item['cantidad']; ?>x
            <?php    echo htmlspecialchars($item['nombre_variante'] ?: 'Producto'); ?></span>
          <span>Bs.<?php    echo number_format($item['precio_total'], 2); ?></span>
        </div>
        <?php  endforeach; ?>
      </div>

      <div class="paper-total">
        TOTAL: Bs.<?php  echo number_format($pedido['monto_total'], 2); ?>
      </div>

      <div class="paper-footer">
        <p>¡GRACIAS POR ELEGIR RICO POLLO!</p>
        <p>CONSERVA ESTE COMPROBANTE PARA CUALQUIER CONSULTA</p>
      </div>
    </div>
    <?php endif; ?>

    <!-- 4. BOTONES DE ACCIÓN Y CONTACTO POR WHATSAPP -->
    <div class="action-buttons">
      <?php
$msgWsp = urlencode("Hola Rico Pollo, tengo una consulta/reclamo sobre mi pedido comprobante #" . $pedido['numero_pedido']);
$wspUrl = "https://wa.me/591" . preg_replace('/[^0-9]/', '', $pedido['cliente_telefono']) . "?text=" . $msgWsp;
      ?>
      <a href="<?php echo $wspUrl; ?>" target="_blank" class="btn-action btn-whatsapp-support">
        📱 ¿DUDAS O RECLAMOS? CONTACTAR AL LOCAL VIA WHATSAPP
      </a>

      <?php if (!in_array($estado, ['pendiente', 'cancelado'])): ?>
      <button onclick="window.print()" class="btn-action btn-menu" style="background:#4b5563;">
        🖨️ IMPRIMIR COMPROBANTE
      </button>
      <?php endif; ?>

      <a href="{{ route('menu') }}" class="btn-action btn-menu">
        🍔 VOLVER AL MENÚ
      </a>
    </div>

  </div>

  <script>
    // SELECCIÓN Y ENVÍO DE MÉTODO DE PAGO
    function selectPayment(type) {
      document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('selected'));
      document.querySelectorAll('.payment-box').forEach(b => b.classList.remove('active'));

      if (type === 'qr') {
        event.currentTarget.classList.add('selected');
        document.getElementById('boxQr').classList.add('active');
      } else {
        event.currentTarget.classList.add('selected');
        document.getElementById('boxEfectivo').classList.add('active');
      }
    }

    function submitPayment(metodo) {
      let data = new FormData();
      data.append('metodo_pago', metodo);
      if (metodo === 'efectivo') {
        let val = document.getElementById('inputMontoEfectivo').value;
        data.append('monto_pago', val);
      }

      fetch('{{ route("pedidos.confirmarPago", ["id" => $pedido["id"]]) }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: data
      })
        .then(res => res.json())
        .then(d => {
          if (d.success) {
            window.location.reload();
          }
        })
        .catch(e => console.error(e));
    }

    // AUTO-POLLING AJAX PARA ACTUALIZAR ESTADO EN TIEMPO REAL
    let currentEstado = '<?php echo strtolower($pedido['estado']); ?>';
    setInterval(() => {
      fetch('{{ route("api.pedidos.estado", ["id" => $pedido["id"]]) }}')
        .then(res => res.json())
        .then(data => {
          if (data && data.estado && data.estado !== currentEstado) {
            window.location.reload();
          }
        })
        .catch(err => console.log('Polling error:', err));
    }, 4000);

    // Auto impresión para Impresora Térmica 58mm (IMP006)
    <?php if (request()->has('print') || request()->has('autoprint')): ?>
    window.addEventListener('load', () => {
      setTimeout(() => {
        window.print();
      }, 500);
    });
    <?php endif; ?>
  </script>
</body>

</html>