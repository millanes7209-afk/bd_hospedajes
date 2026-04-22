<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
require_once("../../paginacion.inc.php");

// Consulta para obtener los grupos
$sql = $db->Prepare(" SELECT * 
                      FROM grupos
                      WHERE _estado = 'A'
                    ");
$rs = $db->GetAll($sql);

// Contar registros y configurar la paginación
contarRegistros($db, "opciones");
paginacion("opciones.php?");

// Consulta para obtener las opciones con los grupos correspondientes
$sql3 = $db->Prepare(" SELECT gru.*, op.*
                       FROM grupos gru, opciones op
                       WHERE op.id_grupo = gru.id_grupo
                       AND gru._estado <> 'X'
                       AND op._estado <> 'X'
                       ORDER BY op.id_opcion ASC
                       LIMIT ? OFFSET ?
                    ");
$rsOpciones = $db->GetAll($sql3, array($nElem, $regIni));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Opciones</title>
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
            <h3>GESTIÓN OPCIONES</h3>
        </div>
        <div class="card-body">
            <!-- INICIO BUSCADOR -->
            <div class="formita">
                <form action="#" method="post" name="formu">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="grupo" class="form-label">Grupo</label>
                            <input type="text" class="form-control" name="grupo" id="grupo" size="10" onKeyUp="buscar_opciones()">
                        </div>
                        <div class="col-md-6">
                            <label for="opcion" class="form-label">Opción</label>
                            <input type="text" class="form-control" name="opcion" id="opcion" size="10" onKeyUp="buscar_opciones()">
                        </div>
                    </div>
                </form>
            </div>
            <!-- FIN BUSCADOR -->

            <!-- INICIO BUSCADOR 2 -->
            <div class="formita">
                <form action="#" method="post" name="formu2">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="grupo1" class="form-label">Grupo</label>
                            <select class="form-control" name="grupo1" id="grupo1" onchange="buscar_opciones1()">
                                <option value="">--Seleccione--</option>
                                <?php foreach ($rs as $fila): ?>
                                    <option value="<?php echo $fila['grupo']; ?>"><?php echo $fila['grupo']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="opcion1" class="form-label">Opción</label>
                            <input type="text" class="form-control" name="opcion1" id="opcion1" size="10" onKeyUp="buscar_opciones1()">
                        </div>
                    </div>
                </form>
            </div>
            <!-- FIN BUSCADOR 2 -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="opcion_nuevo.php" class="btn btn-success" role="button">Nueva Opción</a>
            </div>
            <p></p>
            <div id="opciones1">
                <?php if ($rsOpciones): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">N°</th>
                                <th scope="col">GRUPO</th>
                                <th scope="col">OPCIÓN</th>
                                <th scope="col">CONTENIDO</th>
                                <th scope="col">ORDEN</th>
                                <th scope="col"><img src='../../imagenes/modificar.gif'></th>
                                <th scope="col"><img src='../../imagenes/borrar.jpeg'></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $b = 0;
                            $total = $pag - 1;
                            $a = $nElem * $total;
                            $b = $b + 1 + $a;
                            foreach ($rsOpciones as $fila): ?>
                                <tr>
                                    <td><?php echo $b; ?></td>
                                    <td><?php echo $fila['grupo']; ?></td>
                                    <td><?php echo $fila['opcion']; ?></td>
                                    <td><?php echo $fila['contenido']; ?></td>
                                    <td><?php echo $fila['orden']; ?></td>
                                    <td>
                                        <form name="formModif<?php echo $fila['id_opcion']; ?>" method="post" action="opcion_modificar.php" style="display:inline;">
                                            <input type="hidden" name="id_opcion" value="<?php echo $fila['id_opcion']; ?>">
                                            <input type="hidden" name="id_grupo" value="<?php echo $fila['id_grupo']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form name="formElimi<?php echo $fila['id_opcion']; ?>" method="post" action="opcion_eliminar.php" style="display:inline;">
                                            <input type="hidden" name="id_opcion" value="<?php echo $fila['id_opcion']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger btn-accion" onclick="return confirm('¿Desea realmente eliminar la opción <?php echo $fila['opcion']; ?>?');">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php $b++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php mostrar_paginacion(); ?>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="../../ajax.js"></script>
    <script type="text/javascript" src="js/buscar_opciones1.js"></script>
    <script type="text/javascript" src="js/buscar_opciones.js"></script>
</body>
</html>
