<?php
session_start();
require_once("../../conexion.php");

// 1. Recibir los datos por POST (enviados desde el AJAX)
$ci = isset($_POST['ci']) ? $_POST['ci'] : '';
$paisID = isset($_POST['paisID']) ? $_POST['paisID'] : '';

if (!empty($ci) && !empty($paisID)) {
    
    // 2. Consulta buscando la combinación exacta de CI y País, verificando si tiene hospedaje activo
    $sql = "SELECT c.*, p.nombre AS nombre_pais,
            (SELECT hab.numero FROM hospedajes_clientes hc 
             JOIN hospedajes h ON hc.hospedajeID = h.hospedajeID 
             JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
             WHERE hc.clienteID = c.clienteID AND h.estado = 'ACTIVO' AND h._estado <> 'X' AND hc._estado <> 'X' LIMIT 1) AS habitacion_activa
            FROM clientes c
            INNER JOIN paises p ON c.paisID = p.paisID
            WHERE c.ci = ? 
            AND c.paisID = ? 
            AND c._estado <> 'X'";
    $fila =$db->obtenerFila($sql, [$ci, $paisID]);

    if ($fila) {
        if (!empty($fila['habitacion_activa'])) {
            $nombre_completo = trim($fila['nombres'] . ' ' . $fila['apellido1'] . ' ' . $fila['apellido2']);
            echo "<div class='alert alert-danger d-flex align-items-center py-2 mb-0' style='gap: 8px;'>";
            echo "  <i class='fas fa-ban'></i>";
            echo "  <span class='small fw-bold'>El cliente {$nombre_completo} ya se encuentra hospedado en la habitación " . $fila['habitacion_activa'] . "</span>";
            echo "</div>";
        } else {
        // --- CASO A: CLIENTE ENCONTRADO ---
        // 'd-flex' alinea en horizontal, 'justify-content-between' separa los extremos
        echo "<div class='alert alert-info d-flex justify-content-between align-items-center' style='padding: 10px 15px;'>";
        
        echo "  <p class='mb-0'><strong>Cliente:</strong> ".$fila['ci'] ." - ". $fila['nombres'] . " " . $fila['apellido1'] . " " . $fila['apellido2'] . "</p>";
        
        // Botón con el check pequeño y cuadrado
        echo "  <button type='button' class='btn btn-success btn-sm' onclick='seleccionarCliente(" . $fila['clienteID'] . ")' style='display: flex; align-items: center; gap: 8px;'>
                    <i class='fas fa-check' style='font-size: 9px; padding: 3px; border: 1px solid white; border-radius: 2px; line-height: 1;'></i> 
                </button>";
        
        echo "</div>";
        }

    } else {
        // --- CASO B: NO EXISTE EL CLIENTE ---
        echo "<div class='alert alert-warning py-2 mb-0'>";
        echo "  <i class='fas fa-exclamation-triangle'></i> Cliente no encontrado, registra uno nuevo.";
        echo "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Faltan datos para realizar la búsqueda.</div>";
}
?>