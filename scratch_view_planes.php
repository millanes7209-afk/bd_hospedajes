<?php
require_once("conexion.php");
try {
    $res = $db->obtenerTodo("SELECT planID, tarea, plazo, estado FROM planes");
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
