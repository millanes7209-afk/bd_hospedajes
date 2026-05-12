<?php
session_start();
require_once("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresaID = $_SESSION['empresaID'];
    $nombre = strtoupper($_POST['nombre']);
    $ruc = $_POST['ruc'];
    $representante = strtoupper($_POST['representante']);
    $color_p = $_POST['color_primario'];
    $color_s = $_POST['color_secundario'];

    try {
        $db->beginTransaction();

        // 1. Manejo del Logo
        $logo_sql = "";
        $params = [$nombre, $ruc, $representante, $color_p, $color_s];

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $nombre_logo = "logo_" . $empresaID . "_" . time() . "." . $ext;
            $ruta_destino = "../../img/" . $nombre_logo;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $ruta_destino)) {
                $logo_sql = ", logo = ?";
                $params[] = $nombre_logo;
            }
        }

        // 2. Actualizar Tabla
        $sql = "UPDATE empresa SET nombre = ?, ruc = ?, representante = ?, color_primario = ?, color_secundario = ? $logo_sql 
                WHERE empresaID = ?";
        $params[] = $empresaID;

        $db->ejecutar($sql, $params);
        $db->commit();

        $_SESSION['mensaje'] = "Datos de la empresa actualizados correctamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: empresa_modificar.php");
    exit();
}
?>
