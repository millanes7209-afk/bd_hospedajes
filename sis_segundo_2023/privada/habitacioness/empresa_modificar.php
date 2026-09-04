<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];

// 1. Obtener datos actuales de la empresa
$sql = "SELECT * FROM empresa WHERE empresaID = ? AND _estado <> 'X'";
$empresa = $db->obtenerFila($sql, [$empresaID]);

if (!$empresa) {
    echo "Error: Empresa no encontrada.";
    exit();
}

// 2. Obtener lista de PROPIETARIOS asociados a esta empresa
// Se busca a través de empleados vinculado a esta empresa que tengan rol de Propietario (rolID=3)
$sql_props = "SELECT DISTINCT e.nombres, e.apellidos
              FROM empleados e
              INNER JOIN empleado_empresas ee ON e.empleadoID = ee.empleadoID
              INNER JOIN usuarios u ON e.empleadoID = u.empleadoID
              INNER JOIN usuarios_roles ur ON u.usuarioID = ur.usuarioID
              WHERE ee.empresaID = ? 
              AND ur.rolID = 3
              AND e._estado <> 'X'
              AND u._estado <> 'X'";
$propietarios = $db->obtenerTodo($sql_props, [$empresaID]);

$rep_actual = $empresa['representante_legal'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración de Empresa</title>
</head>
<body>
    <div style="width: 95%; margin: 20px auto; font-family: sans-serif; color: #000;">
        <h2 style="border-bottom: 2px solid #ccc; padding-bottom: 10px;">CONFIGURACIÓN DE EMPRESA</h2>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div style="padding: 10px; margin-bottom: 20px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;">
                <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
            </div>
        <?php endif; ?>

        <form action="empresa_guardar.php" method="post" enctype="multipart/form-data">
            <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                <tr style="background: #f4f4f4;">
                    <th colspan="4" align="left">DATOS GENERALES</th>
                </tr>
                <tr>
                    <td width="15%"><strong>Nombre:</strong></td>
                    <td width="35%"><input type="text" name="nombre" value="<?php echo htmlspecialchars($empresa['nombre'] ?? ''); ?>" required style="width: 95%;"></td>
                    <td width="15%"><strong>RUC / NIT:</strong></td>
                    <td width="35%"><input type="text" name="ruc_nit" value="<?php echo htmlspecialchars($empresa['ruc_nit'] ?? ''); ?>" style="width: 95%;"></td>
                </tr>
                <tr>
                    <td><strong>Representante:</strong></td>
                    <td>
                        <select name="representante_legal" id="rep_legal" style="width: 95%; padding: 4px;" onchange="verificarOtro(this.value)">
                            <option value="">-- Seleccionar Propietario --</option>
                            <?php 
                            $encontrado = false;
                            foreach ($propietarios as $p): 
                                $nombre_completo = strtoupper($p['nombres'] . " " . $p['apellidos']);
                                $selected = "";
                                if ($rep_actual == $nombre_completo) {
                                    $selected = "selected";
                                    $encontrado = true;
                                }
                            ?>
                                <option value="<?php echo $nombre_completo; ?>" <?php echo $selected; ?>>
                                    <?php echo $nombre_completo; ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="OTRO" <?php echo (!$encontrado && $rep_actual != "") ? 'selected' : ''; ?>>OTRO (Escribir manualmente...)</option>
                        </select>
                        <input type="text" name="representante_manual" id="rep_manual" 
                               value="<?php echo (!$encontrado) ? $rep_actual : ''; ?>" 
                               style="width: 95%; margin-top: 5px; <?php echo ($encontrado || $rep_actual == "") ? 'display:none;' : ''; ?>" 
                               placeholder="Escriba el nombre del representante">
                    </td>
                    <td><strong>Dirección:</strong></td>
                    <td><input type="text" name="direccion" value="<?php echo htmlspecialchars($empresa['direccion'] ?? ''); ?>" style="width: 95%;"></td>
                </tr>
                
                <tr style="background: #f4f4f4;">
                    <th colspan="4" align="left">DISEÑO Y LOGO</th>
                </tr>
                <tr>
                    <td><strong>Logo Actual:</strong></td>
                    <td colspan="3">
                        <?php if (!empty($empresa['logo_agencia'])): ?>
                            <img src="../../img/<?php echo $empresa['logo_agencia']; ?>" style="max-width: 100px; vertical-align: middle; margin-right: 20px;">
                        <?php endif; ?>
                        <input type="file" name="logo_agencia" accept="image/*">
                    </td>
                </tr>
                <tr>
                    <td><strong>Colores:</strong></td>
                    <td colspan="3" style="padding: 15px;">
                        <b>Primario:</b> <input type="color" name="color_primario" value="<?php echo $empresa['color_primario'] ?? '#4e73df'; ?>">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <b>Secundario:</b> <input type="color" name="color_secundario" value="<?php echo $empresa['color_secundario'] ?? '#858796'; ?>">
                    </td>
                </tr>
            </table>

            <div style="margin-top: 25px; text-align: center;">
                <input type="submit" value="GUARDAR CAMBIOS" style="padding: 12px 30px; background: #28a745; color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 4px;">
                <a href="habitaciones.php" style="padding: 12px 30px; background: #6c757d; color: white; text-decoration: none; display: inline-block; margin-left: 15px; border-radius: 4px; font-weight: bold;">CANCELAR</a>
            </div>
        </form>
    </div>

    <script>
        function verificarOtro(valor) {
            const inputManual = document.getElementById('rep_manual');
            if (valor === 'OTRO') {
                inputManual.style.display = 'block';
                inputManual.required = true;
                inputManual.focus();
            } else {
                inputManual.style.display = 'none';
                inputManual.required = false;
            }
        }
    </script>
</body>
</html>