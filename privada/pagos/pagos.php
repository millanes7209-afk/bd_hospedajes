<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta para obtener los datos de los pagos
$sql = $db->Prepare("   SELECT pag.pagoID, CONCAT_WS(' ', cli.apellidos, cli.nombres) AS cliente, 
                            pag.monto, fp.tipo AS formaPago, pag.fecha_pago, pag.reservaID, pag.hospedajeID
                        FROM pagos pag
                        JOIN clientes cli ON cli.clienteID = pag.clienteID
                        JOIN formas_pago fp ON fp.formaPagoID = pag.formaPagoID
                        WHERE pag._estado <> 'X'
                        ORDER BY pag.pagoID DESC
");


$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Pagos</title>
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
    </style>
</head>

<body>
    <div class="card">
        <div class="card-header">
            <h3>GESTIÓN DE PAGOS</h3>
        </div>
        
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                
            </div>
            <p></p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Cliente ID</th>
                            <th scope="col">Monto</th>
                            <th scope="col">Forma de Pago</th>
                            <th scope="col">Fecha de Pago</th>
                            <th scope="col">Reserva ID</th>
                            <th scope="col">Hospedaje ID</th>
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
                            <td><?php echo $fila['cliente']; ?></td>
                            <td><?php echo $fila['monto']; ?></td>
                            <td><?php echo $fila['formaPago']; ?></td>
                            <td><?php echo date('d m Y', strtotime($fila['fecha_pago'])); ?></td>
                            <td><?php echo $fila['reservaID'] ? 'SI' : 'NO'; ?>
                            <td><?php echo $fila['hospedajeID'] ? 'SI' : 'NO'; ?>
                            <td>
                                <form name="formModif<?php echo $fila['pagoID']; ?>" method="post" action="pago_modificar.php" style="display:inline;">
                                    <input type="hidden" name="pagoID" value="<?php echo $fila['pagoID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo $fila['pagoID']; ?>" method="post" action="pago_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="pagoID" value="<?php echo $fila['pagoID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-accion" onclick="return confirm('¿Desea eliminar este pago?');">Eliminar</button>
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
