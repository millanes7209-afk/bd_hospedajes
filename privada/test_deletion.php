<?php
require_once("../conexion.php");
// No active hospedaje found to test.
$h = $db->obtenerFila("SELECT h.hospedajeID, h.ingresoID FROM hospedajes h WHERE h._estado <> 'X' LIMIT 1");
if (!$h)
    die("No record to test");
$id = $h['hospedajeID'];
$ingresoID = $h['ingresoID'];
echo "Testing deletion of Hospedaje $id (Ingreso $ingresoID)\n";
try {
    $db->beginTransaction();
    // Simulate what hospedaje_eliminar.php does
    $db->ejecutar("UPDATE ingresos SET _estado = 'X' WHERE ingresoID = ?", [$ingresoID]);
    $db->ejecutar("UPDATE hospedajes SET _estado = 'X' WHERE hospedajeID = ?", [$id]);
    echo "SUCCESS: No database errors during simulated deletion.\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
$db->rollBack();
?>