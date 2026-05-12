<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];

// Obtener datos actuales de la empresa
$sql = "SELECT * FROM empresa WHERE empresaID = ? AND _estado <> 'X'";
$empresa = $db->obtenerFila($sql, [$empresaID]);

if (!$empresa) {
    echo "Error: Empresa no encontrada.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración de Empresa</title>
</head>
<body>
    <div style="width: 90%; margin: 20px auto; font-family: sans-serif;">
        <h2 style="border-bottom: 2px solid #ccc; padding-bottom: 10px;">CONFIGURACIÓN DE EMPRESA</h2>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div style="padding: 10px; margin-bottom: 20px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;">
                <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
            </div>
        <?php endif; ?>

        <form action="empresa_guardar.php" method="post" enctype="multipart/form-data">
            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                <tr style="background: #f4f4f4;">
                    <th colspan="2" align="left">DATOS GENERALES</th>
                </tr>
                <tr>
                    <td width="30%"><strong>Nombre de la Empresa:</strong></td>
                    <td><input type="text" name="nombre" value="<?php echo $empresa['nombre']; ?>" required style="width: 95%;"></td>
                </tr>
                <tr>
                    <td><strong>RUC / NIT:</strong></td>
                    <td><input type="text" name="ruc" value="<?php echo $empresa['ruc'] ?? ''; ?>" style="width: 95%;"></td>
                </tr>
                <tr>
                    <td><strong>Representante Legal:</strong></td>
                    <td><input type="text" name="representante" value="<?php echo $empresa['representante'] ?? ''; ?>" style="width: 95%;"></td>
                </tr>
                
                <tr style="background: #f4f4f4;">
                    <th colspan="2" align="left">DISEÑO Y LOGO</th>
                </tr>
                <tr>
                    <td><strong>Logo Actual:</strong></td>
                    <td>
                        <?php if ($empresa['logo']): ?>
                            <img src="../../img/<?php echo $empresa['logo']; ?>" style="max-width: 100px;"><br>
                        <?php endif; ?>
                        <input type="file" name="logo" accept="image/*">
                    </td>
                </tr>
                <tr>
                    <td><strong>Colores del Sistema:</strong></td>
                    <td>
                        Primario: <input type="color" name="color_primario" value="<?php echo $empresa['color_primario'] ?? '#4e73df'; ?>">
                        &nbsp;&nbsp;&nbsp;
                        Secundario: <input type="color" name="color_secundario" value="<?php echo $empresa['color_secundario'] ?? '#858796'; ?>">
                    </td>
                </tr>
            </table>

            <div style="margin-top: 20px; text-align: center;">
                <input type="submit" value="GUARDAR CAMBIOS" style="padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer; font-weight: bold;">
                <a href="habitaciones.php" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; display: inline-block; margin-left: 10px;">CANCELAR</a>
            </div>
        </form>
    </div>
</body>
</html>
