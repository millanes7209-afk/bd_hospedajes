<?php
require_once("../../conexion.php");
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Consulta para obtener detalles de la habitación
    $query = "SELECT tipo, tiene_bano, tiene_tv FROM habitaciones WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $habitacion = mysqli_fetch_assoc($result);
        echo json_encode($habitacion);
    }
}
?>
