<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$accion = $_REQUEST['accion'] ?? '';
$hoy = date('Y-m-d');

if ($accion === 'aceptar_stock') {
    try {
        $stmt = $pdo->prepare("UPDATE stock_diario SET aceptado = 1 WHERE fecha = ?");
        $stmt->execute([$hoy]);
        echo json_encode(['success' => true, 'message' => '¡Stock aceptado con éxito!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($accion === 'registrar_venta') {
    $input = json_decode(file_get_contents('php://input'), true);
    $metodoPago = in_array(($input['metodo_pago'] ?? 'efectivo'), ['efectivo', 'qr']) ? $input['metodo_pago'] : 'efectivo';

    $items = [];
    if (isset($input['items']) && is_array($input['items'])) {
        $items = $input['items'];
    } else {
        $items[] = [
            'tipo' => $input['tipo'] ?? 'individual',
            'producto_id' => $input['producto_id'] ?? null,
            'promocion_id' => $input['promocion_id'] ?? null,
            'cantidad' => (int) ($input['cantidad'] ?? 1),
            'monto' => (float) ($input['monto'] ?? 0.00)
        ];
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO ventas (fecha_hora, producto_id, promocion_id, cantidad, monto, metodo_pago) VALUES (NOW(), ?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $pId = $item['producto_id'] ?? null;
            $prId = $item['promocion_id'] ?? null;
            $cant = (int) ($item['cantidad'] ?? 1);
            $monto = (float) ($item['monto'] ?? 0.00);

            $stmt->execute([$pId, $prId, $cant, $monto, $metodoPago]);
        }
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Venta registrada con éxito']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($accion === 'cerrar_turno') {
    $input = json_decode(file_get_contents('php://input'), true);
    $totalVendidas = (int) ($input['total_vendidas'] ?? 0);
    $totalSobrantes = (int) ($input['total_sobrantes'] ?? 0);
    $dineroCobrado = (float) ($input['dinero_cobrado'] ?? 0.00);
    $dineroEfectivo = (float) ($input['dinero_efectivo'] ?? 0.00);
    $dineroQr = (float) ($input['dinero_qr'] ?? 0.00);
    $observaciones = trim($input['observaciones'] ?? '');

    try {
        // 1. Guardado Local
        $stmt = $pdo->prepare("INSERT INTO cierres (fecha, total_vendidas, total_sobrantes, dinero_cobrado, dinero_efectivo, dinero_qr, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$hoy, $totalVendidas, $totalSobrantes, $dineroCobrado, $dineroEfectivo, $dineroQr, $observaciones]);

        // 2. Sincronización Automática con Sistema Central (saltenas.alloggibolivia.com)
        $centralApiUrl = 'https://saltenas.alloggibolivia.com/api/v1/cierres/sincronizar';
        $apiKey = 'pos_saltenas_secret_key_2026';

        $payloadCentral = [
            'fecha' => $hoy,
            'monto_real' => $dineroCobrado,
            'observaciones' => "Efectivo: Bs. $dineroEfectivo | QR: Bs. $dineroQr | " . $observaciones,
            'detalles' => [
                [
                    'variante_id' => 1,
                    'cantidad_entregada' => $totalVendidas + $totalSobrantes,
                    'cantidad_vendida_normal' => $totalVendidas,
                    'cantidad_sobrante' => $totalSobrantes
                ]
            ]
        ];

        $ch = curl_init($centralApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadCentral));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-POS-Api-Key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $resp = curl_exec($ch);
        curl_close($ch);

        echo json_encode(['success' => true, 'message' => 'Cierre de turno completado y sincronizado con Central']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);
