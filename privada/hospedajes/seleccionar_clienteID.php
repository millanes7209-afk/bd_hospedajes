<?php
session_start();
require_once("../../conexion.php");

$clienteID = isset($_POST['clienteID']) ? $_POST['clienteID'] : '';

if (!empty($clienteID)) {
    $sql = "SELECT c.*, p.nombre AS nombre_pais 
            FROM clientes c
            INNER JOIN paises p ON c.paisID = p.paisID
            WHERE c.clienteID = ? 
            AND c._estado <> 'X'";
    $fila = $db->obtenerFila($sql, [$clienteID]);

    if ($fila) {
        $nombreCompleto = $fila['nombres'] . " " . $fila['apellido1'] . " " . $fila['apellido2'];
        ?>
        <div class="d-flex align-items-center justify-content-between py-2 px-3 border-bottom">
            <div>
                <strong class="text-uppercase" style="font-size: 0.9rem;"><?php echo $nombreCompleto; ?></strong>
                <br>
                <small class="text-muted">C.I.: <?php echo $fila['ci']; ?> (<?php echo $fila['nombre_pais']; ?>)</small>
            </div>
            <button type="button" class="btn btn-link text-danger p-0 text-decoration-none" 
                    onclick="deseleccionarCliente(<?php echo $clienteID; ?>)">
                <i class="fas fa-times-circle fa-lg"></i>
            </button>
        </div>
        <?php
    } else {
        echo "<div class='text-danger p-2'>No encontrado</div>";
    }
}
?>
