<?php
session_start();
require_once("../../conexion.php");
require_once("../../paginacion.inc.php");
require_once("../../libreria_menu.php");

// Contar registros para la paginación
contarRegistros($db,"grupos");

// Configurar la paginación
paginacion("grupos.php?");

// Consulta SQL para obtener los grupos
$sql = $db->Prepare("SELECT *
                     FROM grupos
                     WHERE _estado <> 'X' 
                     /*AND id_grupo > 1*/
                     ORDER BY id_grupo ASC                    
                     LIMIT ? OFFSET ?");
$rs = $db->GetAll($sql, array($nElem, $regIni));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Grupos</title>
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
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
           <h3>GESTIÓN GRUPOS</h3>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="grupo_nuevo.php" class="btn btn-success mb-3" role="button">Añadir Grupo</a>
          </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Grupo</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg'></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($rs) : ?>
                    <?php 
                    $b = 0;
                    $total = $pag - 1;
                    $a = $nElem * $total;
                    $b = $b + 1 + $a;
                    ?>
                    <?php foreach ($rs as $fila) : ?>
                        <tr>
                            <td><?php echo $b; ?></td>
                            <td><?php echo $fila['grupo']; ?></td>
                            <td>
                                <form name="formModif<?php echo $fila['id_grupo']; ?>" method="post" action="grupo_modificar.php" style="display:inline;">
                                    <input type="hidden" name="id_grupo" value="<?php echo $fila['id_grupo']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo $fila['id_grupo']; ?>" method="post" action="grupo_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="id_grupo" value="<?php echo $fila['id_grupo']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Desea realmente eliminar el grupo <?php echo $fila['grupo']; ?>?');">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php $b++; ?>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php mostrar_paginacion(); ?>
</body>
</html>
