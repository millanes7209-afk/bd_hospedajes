<?php

if (!function_exists('enviarEstadoVivoCentral')) {
    /**
     * Envía el estado completo actual del turno del carrito a Central (saltenas).
     * Si $forceClosedTurnoId viene informado, notifica explícitamente el cierre de ese turno.
     */
    function enviarEstadoVivoCentral(PDO $pdo, int $defaultCarritoId = 1, ?int $forceClosedTurnoId = null): void
    {
        try {
            $url = 'https://saltenas.alloggibolivia.com/api/v1/carritos/estado-vivo';
            $apiKey = 'pos_saltenas_secret_key_2026';

            if ($forceClosedTurnoId !== null) {
                $stTC = $pdo->prepare("SELECT id, carrito_id, fecha FROM turnos WHERE id = ? LIMIT 1");
                $stTC->execute([$forceClosedTurnoId]);
                $tRow = $stTC->fetch();

                $payload = [
                    'carrito_id' => (int) ($tRow['carrito_id'] ?? $defaultCarritoId),
                    'turno_remoto_id' => $forceClosedTurnoId,
                    'estado' => 'cerrado',
                    'stock_aceptado' => false,
                    'fecha' => $tRow['fecha'] ?? date('Y-m-d'),
                    'total_enviadas' => 0,
                    'total_vendidas' => 0,
                    'total_restantes' => 0,
                    'dinero_total' => 0,
                    'dinero_efectivo' => 0,
                    'dinero_qr' => 0,
                    'desglose_variedades' => [],
                ];
            } else {
                $stT = $pdo->query("SELECT * FROM turnos WHERE estado = 'abierto' ORDER BY id DESC LIMIT 1");
                $turno = $stT->fetch();
                if (!$turno) {
                    return;
                }

                $turnoId = (int) $turno['id'];
                $carritoId = (int) ($turno['carrito_id'] ?: $defaultCarritoId);
                $fecha = $turno['fecha'] ?? date('Y-m-d');

                $stmtVentas = $pdo->prepare("
                    SELECT
                        COALESCE(SUM(CASE
                            WHEN metodo_pago = 'efectivo' THEN monto
                            WHEN metodo_pago = 'hibrido' THEN monto_efectivo
                            ELSE COALESCE(monto_efectivo, 0)
                        END), 0) AS dinero_efectivo,
                        COALESCE(SUM(CASE
                            WHEN metodo_pago = 'qr' THEN monto
                            WHEN metodo_pago = 'hibrido' THEN monto_qr
                            ELSE COALESCE(monto_qr, 0)
                        END), 0) AS dinero_qr
                    FROM ventas
                    WHERE turno_id = ?
                ");
                $stmtVentas->execute([$turnoId]);
                $resVentas = $stmtVentas->fetch();
                $dineroEfectivo = round((float) ($resVentas['dinero_efectivo'] ?? 0), 2);
                $dineroQr = round((float) ($resVentas['dinero_qr'] ?? 0), 2);
                $dineroTotal = round($dineroEfectivo + $dineroQr, 2);

                $stmtVendPorProd = $pdo->prepare("
                    SELECT v.producto_id, COALESCE(p.nombre, CONCAT('Variedad #', v.producto_id)) AS prod_nombre, COALESCE(SUM(v.cantidad), 0) AS cant_vendida
                    FROM ventas v
                    LEFT JOIN productos p ON v.producto_id = p.id
                    WHERE v.turno_id = ?
                    GROUP BY v.producto_id, p.nombre
                ");
                $stmtVendPorProd->execute([$turnoId]);
                $ventasPorProd = [];
                foreach ($stmtVendPorProd->fetchAll() as $vp) {
                    if ($vp['producto_id'] !== null) {
                        $ventasPorProd[(int) $vp['producto_id']] = [
                            'nombre' => $vp['prod_nombre'],
                            'vendida' => (int) $vp['cant_vendida'],
                        ];
                    }
                }

                $stmtStock = $pdo->prepare("
                    SELECT s.producto_id, SUM(s.cantidad_enviada) AS cantidad_enviada, p.nombre AS producto_nombre
                    FROM stock_diario s
                    JOIN productos p ON s.producto_id = p.id
                    WHERE s.turno_id = ? AND s.aceptado = 1
                    GROUP BY s.producto_id, p.nombre
                    ORDER BY p.nombre
                ");
                $stmtStock->execute([$turnoId]);
                $stockRows = $stmtStock->fetchAll();
                $stockAceptado = count($stockRows) > 0;

                $desglose = [];
                $idsEnDesglose = [];

                if ($stockAceptado) {
                    foreach ($stockRows as $st) {
                        $pId = (int) $st['producto_id'];
                        $idsEnDesglose[$pId] = true;
                        $enviada = (int) $st['cantidad_enviada'];
                        $vendida = $ventasPorProd[$pId]['vendida'] ?? 0;
                        $restante = max(0, $enviada - $vendida);

                        $desglose[] = [
                            'variante_id' => $pId,
                            'nombre' => $st['producto_nombre'],
                            'enviada' => $enviada,
                            'vendida' => $vendida,
                            'restante' => $restante,
                        ];
                    }

                    foreach ($ventasPorProd as $pId => $vpInfo) {
                        if (!isset($idsEnDesglose[$pId])) {
                            $desglose[] = [
                                'variante_id' => $pId,
                                'nombre' => $vpInfo['nombre'],
                                'enviada' => 0,
                                'vendida' => $vpInfo['vendida'],
                                'restante' => 0,
                            ];
                        }
                    }
                }

                $totalEnviadas = 0;
                $totalVendidas = 0;
                $totalRestantes = 0;
                foreach ($desglose as $d) {
                    $totalEnviadas += (int) $d['enviada'];
                    $totalVendidas += (int) $d['vendida'];
                    $totalRestantes += (int) $d['restante'];
                }

                $payload = [
                    'carrito_id' => $carritoId,
                    'turno_remoto_id' => $turnoId,
                    'estado' => 'abierto',
                    'stock_aceptado' => $stockAceptado,
                    'fecha' => $fecha,
                    'total_enviadas' => $totalEnviadas,
                    'total_vendidas' => $totalVendidas,
                    'total_restantes' => $totalRestantes,
                    'dinero_total' => $dineroTotal,
                    'dinero_efectivo' => $dineroEfectivo,
                    'dinero_qr' => $dineroQr,
                    'desglose_variedades' => $desglose,
                ];
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-POS-Api-Key: ' . $apiKey,
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_exec($ch);
            curl_close($ch);
        } catch (Throwable $e) {
            // No bloquear la operación local del carrito si hay fallo temporal de red
        }
    }
}
