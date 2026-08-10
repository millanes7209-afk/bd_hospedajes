<?php
session_start();
require_once("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresaID = $_SESSION['empresaID'];
    $nombre = strtoupper($_POST['nombre'] ?? '');
    $ruc_nit = $_POST['ruc_nit'] ?? '';

    // Lógica para representante: Si es 'OTRO', tomamos el valor del input manual
    $rep_val = $_POST['representante_legal'] ?? '';
    if ($rep_val === 'OTRO') {
        $representante_legal = strtoupper($_POST['representante_manual'] ?? '');
    } else {
        $representante_legal = strtoupper($rep_val);
    }

    $direccion = strtoupper($_POST['direccion'] ?? '');
    $color_p = $_POST['color_primario'] ?? '#4e73df';
    $color_s = $_POST['color_secundario'] ?? '#858796';

    try {
        // 1. Manejo del Logo (logo_agencia)
        $logo_sql = "";
        $params_extra = [];

        if (isset($_FILES['logo_agencia']) && $_FILES['logo_agencia']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['logo_agencia']['name'], PATHINFO_EXTENSION);
            $nombre_logo = "logo_" . $empresaID . "_" . time() . "." . $ext;
            $ruta_destino = "../../img/" . $nombre_logo;

            if (move_uploaded_file($_FILES['logo_agencia']['tmp_name'], $ruta_destino)) {
                $logo_sql = ", logo_agencia = ?";
                $params_extra[] = $nombre_logo;
            }
        }

        // 2. Preparar consulta de actualización con nombres reales de columnas
        $sql = "UPDATE empresa 
                SET nombre = ?, 
                    ruc_nit = ?, 
                    representante_legal = ?, 
                    direccion = ?,
                    color_primario = ?, 
                    color_secundario = ? 
                    $logo_sql 
                WHERE empresaID = ?";

        $params = [$nombre, $ruc_nit, $representante_legal, $direccion, $color_p, $color_s];

        // Añadir logo si se subió
        foreach ($params_extra as $p)
            $params[] = $p;

        // Añadir ID de empresa al final
        $params[] = $empresaID;

        $db->ejecutar($sql, $params);

        $_SESSION['mensaje'] = "Los cambios han sido guardados exitosamente.";

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "ERROR AL GUARDAR: " . $e->getMessage();
    }

    header("Location: empresa_modificar.php");
    exit();
}
?>