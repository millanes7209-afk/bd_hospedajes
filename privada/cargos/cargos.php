<?php
session_start();
require_once("../../conexion.php");
require_once("../../paginacion.inc.php");
require_once("../../libreria_menu.php");

// Paginación y conteo de registros
contarRegistros($db, "cargos");
paginacion("cargos.php?");

// Consulta para obtener la lista de cargos
$sql3 = $db->Prepare("SELECT     *
                     FROM       cargos
                     WHERE      _estado <> 'X' 
                     ORDER BY   cargoID DESC                    
                     LIMIT      ? OFFSET ?");
$rs = $db->GetAll($sql3, array($nElem, $regIni));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Cargos</title>
    <!-- Estilos personalizados -->
    <style>
        thead {
            color: black;
            background: #b5b5b5;
        }
        .card {
            margin: 20px;
        }
        tr {
            color: black;
        }
        .form-control {
            border-color: black;
        }
        .formita {
            padding: 25px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h3>LISTADO DE CARGOS</h3>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="cargo_nuevo.php" class="btn btn-success mb-3" role="button">Añadir Cargo</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Cargo</th>
                            <th scope="col">Descripción</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg'></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs) : ?>
                            <?php $b = $regIni + 1; ?>
                            <?php foreach ($rs as $fila) : ?>
                                <tr>
                                    <td><?php echo $b; ?></td>
                                    <td><?php echo $fila['cargo']; ?></td>
                                    <td><?php echo $fila['descripcion']; ?></td>
                                    <td>
                                        <form name="formModif<?php echo $fila['cargoID']; ?>" method="post" action="cargo_modificar.php" style="display:inline;">
                                            <input type="hidden" name="cargoID" value="<?php echo $fila['cargoID']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Modificar</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form name="formElimi<?php echo $fila['cargoID']; ?>" method="post" action="cargo_eliminar.php" style="display:inline;">
                                            <input type="hidden" name="cargoID" value="<?php echo $fila['cargoID']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Desea realmente eliminar el cargo <?php echo $fila['cargo']; ?>?');">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php $b++; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php mostrar_paginacion(); ?>
        </div>
    </div>
</body>
</html>
