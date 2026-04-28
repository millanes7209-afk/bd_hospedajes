<?php
require 'conexion.php';

$empresas = $db->obtenerTodo("SELECT empresaID FROM empresa WHERE _estado = 'A'");

$db->beginTransaction();
try {
    foreach ($empresas as $emp) {
        $db->ejecutar(
            "INSERT INTO cuentas (codigo, nombre, tipo, empresaID, _usuario) VALUES (?, ?, ?, ?, ?)",
            ['407', 'Ingreso Alquiler de Local', 'INGRESO', $emp['empresaID'], 1]
        );
        echo "✅ Cuenta 407 agregada para empresa {$emp['empresaID']}\n";
    }
    $db->commit();
    echo "\n✅ Listo.\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "❌ Error: " . $e->getMessage();
}
