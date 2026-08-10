<?php
session_start();
require_once("../../conexion.php");

// ==========================================
// PROCESADOR: ACTUALIZAR
// ==========================================
if (isset($_POST['incidenteID'])) {
    $incidenteID = $_POST['incidenteID'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $estado = $_POST['estado'];
    $solucion = $_POST['solucion'];
    $usuarioID = $_SESSION['sesion_id_usuario'];

    // Si hay solución, marcamos la fecha de atención
    $fecha_atencion_sql = !empty($solucion) ? ", fecha_atencion = NOW()" : "";

    $sql = "UPDATE incidentes SET 
                descripcion = ?, 
                fecha = ?, 
                estado = ?, 
                solucion = ?, 
                _usuario = ?, 
                _fec_modificacion = NOW()
                $fecha_atencion_sql
            WHERE incidenteID = ? AND empresaID = ?";

    $params = [
        $descripcion,
        $fecha,
        $estado,
        $solucion,
        $usuarioID,
        $incidenteID,
        $_SESSION['empresaID']
    ];

    if ($db->ejecutar($sql, $params)) {
        header("Location: incidentes.php?status=updated");
    } else {
        echo "Error al actualizar el incidente.";
    }
    exit();
}

// ==========================================
// PROCESADOR: ELIMINAR (Soft Delete)
// ==========================================
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $usuarioID = $_SESSION['sesion_id_usuario'];

    $sql = "UPDATE incidentes SET 
                _estado = 'X', 
                _usuario = ?, 
                _fec_modificacion = NOW() 
            WHERE incidenteID = ? AND empresaID = ?";

    if ($db->ejecutar($sql, [$usuarioID, $id, $_SESSION['empresaID']])) {
        header("Location: incidentes.php?status=deleted");
    } else {
        echo "Error al eliminar el registro.";
    }
}
?>