<?php
session_start();
require_once("../../conexion.php");

// 1. Recibir los datos por POST (enviados desde el AJAX)
$ci = isset($_POST['ci']) ? $_POST['ci'] : '';
$paisID = isset($_POST['paisID']) ? $_POST['paisID'] : '';

if (!empty($ci) && !empty($paisID)) {

    // 2. Consulta avanzada: Datos del cliente + Visitas + Detalle de Incidentes
    $empresaID = $_SESSION['empresaID'];
    $sql = "SELECT c.*, p.nombre AS nombre_pais,
            (SELECT COUNT(*) FROM hospedajes_clientes hc WHERE hc.clienteID = c.clienteID AND hc._estado <> 'X') as total_visitas,
            (SELECT GROUP_CONCAT(CONCAT('• ', descripcion, ' (', estado, ')') SEPARATOR '<br>') 
             FROM incidentes 
             WHERE clienteID = c.clienteID AND estado <> 'RECHAZADO' AND _estado <> 'X') as incidentes_detalle,
            (SELECT hab.numero FROM hospedajes_clientes hc 
             JOIN hospedajes h ON hc.hospedajeID = h.hospedajeID 
             JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
             WHERE hc.clienteID = c.clienteID 
             AND h.empresaID = ? 
             AND h.estado = 'ACTIVO' 
             AND h._estado <> 'X' 
             AND hc._estado <> 'X' LIMIT 1) AS habitacion_activa
            FROM clientes c
            INNER JOIN paises p ON c.paisID = p.paisID
            WHERE c.ci = ? 
            AND c.paisID = ? 
            AND c._estado <> 'X'";
    $fila = $db->obtenerFila($sql, [$empresaID, $ci, $paisID]);



    if ($fila) {
        if (!empty($fila['habitacion_activa'])) {
            $nombre_completo = trim($fila['nombres'] . ' ' . $fila['apellido1'] . ' ' . $fila['apellido2']);
            echo "<div class='alert alert-danger d-flex align-items-center py-2 mb-0' style='gap: 8px;'>";
            echo "  <i class='fas fa-ban'></i>";
            echo "  <span class='small fw-bold'>El cliente {$nombre_completo} ya se encuentra hospedado en la habitación " . $fila['habitacion_activa'] . "</span>";
            echo "</div>";
        } else {
            // --- CASO A: CLIENTE ENCONTRADO ---
            echo "<div class='alert alert-info py-2 px-3 mb-0' style='border-left: 5px solid #0d6efd;'>";

            echo "  <div class='d-flex justify-content-between align-items-center mb-1'>";
            echo "      <p class='mb-0'><strong>Cliente:</strong> " . $fila['ci'] . " - " . $fila['nombres'] . " " . $fila['apellido1'] . "</p>";
            echo "      <button type='button' class='btn btn-primary btn-sm fw-bold' onclick='seleccionarCliente(" . $fila['clienteID'] . ")'>";
            echo "          <i class='fas fa-plus-circle'></i> SELECCIONAR";
            echo "      </button>";
            echo "  </div>";

            // Mostrar Conteo de Visitas
            $visitas = (int) $fila['total_visitas'];
            echo "  <div class='small text-dark'>";
            echo "      <i class='fas fa-history me-1'></i> Visitas registradas: <span class='fw-bold'>$visitas</span>";

            // Mostrar Detalle de Incidentes si existen
            $detalle = $fila['incidentes_detalle'];
            if (!empty($detalle)) {
                echo "      <div class='mt-2 p-2 border border-danger bg-white rounded'>";
                echo "          <span class='text-danger fw-bold d-block'><i class='fas fa-exclamation-triangle'></i> ANTECEDENTES DEL CLIENTE:</span>";
                echo "          <div class='small text-danger' style='line-height: 1.2;'>$detalle</div>";
                echo "      </div>";
            } else {
                echo "      <span class='ms-3 text-success small'><i class='fas fa-check-circle'></i> Cliente ejemplar (Sin incidentes)</span>";
            }
            echo "  </div>";

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