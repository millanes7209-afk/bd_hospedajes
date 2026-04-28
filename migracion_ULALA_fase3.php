<?php
/**
 * FASE 3 - ULALA Migration
 * Poblar tabla cuentas con catálogo inicial de ingresos y egresos.
 * Se insertan para empresaID = 1. Si hay más empresas, duplicar con empresaID correspondiente.
 */
require 'conexion.php';

$ahora = date('Y-m-d H:i:s');
$usuarioID = 1;

$cuentas = [
    // ---- INGRESOS ----
    // cod   nombre                          tipo
    ['401', 'Ingreso Hospedaje',             'INGRESO'],
    ['402', 'Ingreso Momentáneo',            'INGRESO'],
    ['403', 'Ingreso Visita',                'INGRESO'],
    ['404', 'Ingreso Baño',                  'INGRESO'],
    ['405', 'Ingreso Ducha',                 'INGRESO'],
    ['406', 'Ingreso Recarga de Dispositivos','INGRESO'],

    // ---- EGRESOS ----
    ['501', 'Gasto Alimentación',            'EGRESO'],   // refrescos, desayunos, almuerzos
    ['502', 'Gasto Insumos de Limpieza',     'EGRESO'],   // detergente, trapos, guantes
    ['503', 'Gasto Servicios de Mantenimiento','EGRESO'], // albañil, plomero (mano de obra)
    ['504', 'Gasto Materiales y Equipos',    'EGRESO'],   // focos, pilas, compras varias
];

// Obtener todas las empresas activas
$empresas = $db->obtenerTodo("SELECT empresaID FROM empresa WHERE _estado = 'A'");

$total = 0;
$db->beginTransaction();
try {
    foreach ($empresas as $emp) {
        foreach ($cuentas as $c) {
            $db->ejecutar(
                "INSERT INTO cuentas (codigo, nombre, tipo, empresaID, _usuario) VALUES (?, ?, ?, ?, ?)",
                [$c[0], $c[1], $c[2], $emp['empresaID'], $usuarioID]
            );
            $total++;
        }
    }
    $db->commit();
    echo "✅ Catálogo de cuentas insertado correctamente.\n";
    echo "   Empresas: " . count($empresas) . "\n";
    echo "   Cuentas por empresa: " . count($cuentas) . "\n";
    echo "   Total filas insertadas: $total\n\n";

    // Mostrar resumen
    $resumen = $db->obtenerTodo(
        "SELECT c.empresaID, c.codigo, c.nombre, c.tipo 
         FROM cuentas c ORDER BY c.empresaID, c.codigo"
    );
    echo "--- Catálogo creado ---\n";
    foreach ($resumen as $r) {
        $emoji = $r['tipo'] === 'INGRESO' ? '💰' : '💸';
        echo "  $emoji [{$r['empresaID']}] {$r['codigo']} - {$r['nombre']}\n";
    }

} catch (Exception $e) {
    $db->rollBack();
    echo "❌ Error: " . $e->getMessage();
}
