<?php
session_start();
require_once("../../conexion.php");
require_once("../../resaltarBusqueda.inc.php");

$ci = $_POST["ci"] ?? '';

$personas = [];

if ($ci) {
    // Realizar la consulta a la base de datos
    $sql3 = $db->Prepare("SELECT apellidos, nombres, ci,clienteID
                          FROM clientes
                          WHERE ci LIKE ? 
                          AND _estado <> 'X'");
    $personas = $db->GetAll($sql3, array($ci."%"));
}
?>

<?php if ($personas): ?>
    <!-- Si existen resultados, se muestra la tabla -->
    <div class='table-responsive'>
        <table class='table table-striped'>
            <thead>
                <tr>
                    <th scope='col'>C.I.</th>
                    <th scope='col'>Apellidos</th>
                    <th scope='col'>Nombres</th>
                    <th scope='col'>Seleccionar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($personas as $fila): ?>
                    <tr>
                        <td><?= resaltar($ci, $fila["ci"]) ?></td>
                        <td><?= resaltar($ci, $fila["apellidos"]) ?></td>
                        <td><?= resaltar($ci, $fila["nombres"]) ?></td>
                        <td>
                            <input type='radio' name='opcion' value='' onClick='buscar_persona(<?= $fila["clienteID"] ?>)'>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <!-- Si no hay resultados, se muestra un mensaje y el formulario para agregar una nueva persona -->
    <b>EL CLIENTE NO EXISTE!!</b>
    <div class='container my-5'>
        <div class='row justify-content-center'>
            <div class='col-md-12'>
                <div class='form-container'>
                    <div class='card'>
                        <div class='card-header'>
                            <h4>Agregar Nuevo Cliente</h4>
                        </div>
                        <div class='card-body'>
                            <form method="post" name="formu">
                                <div class='row mb3'>
                                    <div class='col-md-6'>
                                        <label for='ci1' class='form-label'>(*) CI</label>
                                        <input type='text' class='form-control' name='ci1' placeholder='CARNET DE IDENTIDAD' size='30' required>
                                        <div class='invalid-feedback'>
                                            Cédula de identidad obligatoria.
                                        </div>
                                    </div>
                                    <div class='col-md-6'>
                                        <label for='apellidos1' class='form-label'>Apellidos</label>
                                        <input type='text' class='form-control' name='apellidos1' placeholder='APELLIDOS' size='30' onkeyup='this.value=this.value.toUpperCase()' pattern="[A-Za-z\s]+" required>
                                        <div class='invalid-feedback'>
                                            Ingrese solo texto.
                                        </div>
                                    </div>
                                </div>
                                <div class='row mb-3'>                                    
                                    <div class='col-md-6'>
                                        <label for='nombres1' class='form-label'>Nombres</label>
                                        <input type='text' class='form-control' name='nombres1' placeholder='NOMBRES' size='30' onkeyup='this.value=this.value.toUpperCase()' pattern="[A-Za-z\s]+" required>
                                        <div class='invalid-feedback'>
                                            Ingrese solo texto.
                                        </div>
                                    </div>
                                    <div class='col-md-6'>
                                        <label for='lugar_nacimiento1' class='form-label'>Lugar de Nacimiento</label>
                                        <input type='text' class='form-control' name='lugar_nacimiento1' size='30' 
                                        placeholder="LUGAR NACIMIENTO" onkeyup='this.value=this.value.toUpperCase()' required>
                                        <div class='invalid-feedback'>
                                            Campo obligatorio.
                                        </div>
                                    </div> 
                                </div>
                                <div class='row mb-3'>
                                    <div class='col-md-6'>
                                        <label for='fecha_nacimiento1' class='form-label'>Fecha Nacimiento</label>
                                        <input type='date' class='form-control' name='fecha_nacimiento1' size='30'>
                                        <div class='invalid-feedback'>
                                            Campo obligatorio.
                                        </div>
                                    </div> 
                                </div>
                                <div class='row mb-3'>
                                    <div class='col-md-6'>
                                        <label for='estado_civil1' class='form-label'>Estado Civil</label>
                                        <input type='text' class='form-control' name='estado_civil1'
                                        onkeyup='this.value=this.value.toUpperCase()' size='30'>
                                    </div>
                                    <div class='col-md-6'>
                                        <label for='profesion1' class='form-label'>Profesión</label>
                                        <input type='text' class='form-control' name='profesion1' 
                                        onkeyup='this.value=this.value.toUpperCase()' size='30'>
                                    </div>
                                </div>
                                <div class='row mb-3'>
                                    <div class='col-md-12 text-center'>
                                        <button class='btn btn-primary' type='button' onclick="inserta_persona()">AGREGAR CLIENTE</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<script src="../js/validacion_obligatorios.js"></script>