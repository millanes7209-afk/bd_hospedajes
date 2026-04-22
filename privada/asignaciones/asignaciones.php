<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta para obtener los datos de las asignaciones, incluyendo nombres y tipos de turno
$sql = $db->Prepare("
    SELECT 
        asi.*, 
        per.nombres AS persona_nombre, 
        per.ap AS persona_apellido, 
        turno.tipo AS turno_tipo
    FROM 
        asignaciones asi
        JOIN personas per ON asi.id_persona = per.id_persona
        JOIN turnos turno ON asi.turnoID = turno.turnoID
    WHERE 
        asi._estado <> 'X'
    ORDER BY 
        asi.asignacionID DESC
");

$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Asignaciones</title>
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
        .btn-accion {
            margin-right: 5px;
        }
        ul.dias-lista {
            list-style-type: none;
            padding-left: 0;
            margin: 0;
        }
        ul.dias-lista li {
            padding-left: 1em;
            position: relative;
        }
        ul.dias-lista li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: black;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="card-header">
            <h3>GESTIÓN ASIGNACIONES</h3>
        </div>
       
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="asignacion_nuevo.php" class="btn btn-success" role="button">Añadir Asignación</a>
            </div>
            <p></p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Persona</th>
                            <th scope="col">Turno</th>
                            <th scope="col">Fecha Inicio</th>
                            <th scope="col">Fecha Fin</th>
                            <th scope="col">Días</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif' alt='Modificar'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg' alt='Eliminar'></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($rs) : ?>
                    <?php $b = 1; ?>
                    <?php foreach ($rs as $fila) : ?>
                        <tr>
                            <td><?php echo $b; ?></td>
                            <td><?php echo htmlspecialchars($fila['persona_nombre']) . ' ' . htmlspecialchars($fila['persona_apellido']); ?></td>
                            <td><?php echo htmlspecialchars($fila['turno_tipo']); ?></td>
                            <td><?php echo htmlspecialchars($fila['fecha_inicio']); ?></td>
                            <td><?php echo htmlspecialchars($fila['fecha_fin']); ?></td>
                            <td>
                                <ul class="dias-lista">
                                    <?php
                                    // Asumimos que los días están separados por comas
                                    $dias_array = explode(',', $fila['dias']); 
                                    foreach ($dias_array as $dia) {
                                        echo '<li>' . htmlspecialchars($dia) . '</li>';
                                    }
                                    ?>
                                </ul>
                            </td>
                            <td>
                                <form name="formModif<?php echo htmlspecialchars($fila['asignacionID']); ?>" method="post" action="asignacion_modificar.php" style="display:inline;">
                                    <input type="hidden" name="asignacionID" value="<?php echo htmlspecialchars($fila['asignacionID']); ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo htmlspecialchars($fila['asignacionID']); ?>" method="post" action="asignacion_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="asignacionID" value="<?php echo htmlspecialchars($fila['asignacionID']); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-accion" onclick="return confirm('Desea realmente eliminar la asignación?');">Eliminar</button>
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
</body>
</html>
