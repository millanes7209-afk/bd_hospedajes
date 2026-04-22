<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];
$ci_busqueda = isset($_GET['ci']) ? trim($_GET['ci']) : '';
$empleado_encontrado = null;
$contrato_existente = null;

// Obtener roles disponibles de la tabla roles
$sql_roles = "SELECT rolID, rol FROM roles WHERE _estado = 'A' and rolID>1";
$rs_roles = $db->obtenerTodo($sql_roles);

// Si hay CI para buscar (solo para mostrar datos)
if (!empty($ci_busqueda)) {
    // Buscar empleado por CI
    $sql_empleado = "SELECT * FROM empleados WHERE ci = ? AND _estado <> 'X'";
    $rs_empleado = $db->obtenerTodo($sql_empleado, array($ci_busqueda));

    if (count($rs_empleado) > 0) {
        $empleado_encontrado = $rs_empleado[0];

        // Verificar si ya tiene contrato con esta empresa (solo para mostrar)
        $sql_contrato = "SELECT * FROM empleado_empresas WHERE empleadoID = ? AND empresaID = ? AND _estado <> 'X'";
        $rs_contrato = $db->obtenerTodo($sql_contrato, array($empleado_encontrado['empleadoID'], $empresaID));

        if (count($rs_contrato) > 0) {
            $contrato_existente = $rs_contrato[0];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Empleado</title>
    <!-- Bootstrap CSS -->
</head>

<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">

                        <h3 class="mb-0">GESTIóN DE EMPLEADOS</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($ci_busqueda)): ?>
                        <?php endif; ?>

                        <?php if (!empty($ci_busqueda)): ?>
                            <?php if ($empleado_encontrado): ?>
                                <?php if ($contrato_existente): ?>
                                    <!-- Ya tiene contrato con esta empresa -->
                                    <div class="alert alert-warning">
                                        <div class='alert alert-info'><strong>?? Este empleado ya tiene un contrato activo.</strong>
                                        </div>
                                    </div>

                                    <div class="card mt-3">
                                        <div class="card-header">
                                            <h5>Datos del Contrato Existente</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>CARGO:</strong> <?= htmlspecialchars($contrato_existente['rol']) ?><br>
                                                    <strong>Sueldo:</strong>
                                                    <?= number_format($contrato_existente['sueldo'], 2, ',', '.') ?><br>
                                                    <strong>Inicio del Contrato:</strong>
                                                    <?= htmlspecialchars($contrato_existente['fecha_inicio']) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Fin del Contrato:</strong>
                                                    <?= !empty($contrato_existente['fecha_fin']) ? htmlspecialchars($contrato_existente['fecha_fin']) : 'Indefinido' ?><br>
                                                    <strong>Estado Laboral:</strong>
                                                    <?= htmlspecialchars($contrato_existente['estado_laboral']) ?>
                                                </div>
                                            </div>

                                            <div class="text-center mt-3">
                                                <button class="btn btn-primary" onclick="mostrarFormularioNuevoContrato()">
                                                    Agregar Nuevo Contrato
                                                </button>
                                                <button class="btn btn-secondary" onclick="location.href='empleado_nuevo.php'">
                                                    Atras
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Empleado encontrado sin contrato en esta empresa -->
                                    <div class="row mt-3">
                                        <!-- Columna 1: Datos del empleado -->
                                        <div class="col-md-3">
                                            <div class="card" id="datosEmpleado">
                                                <div class="card-header">
                                                    <h6>Datos Empleadoles</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <strong>Nombre:</strong>
                                                            <?= htmlspecialchars($empleado_encontrado['nombres']) ?><br>
                                                            <strong>Apellidos:</strong>
                                                            <?= htmlspecialchars($empleado_encontrado['apellidos']) ?><br>
                                                            <strong>C.I.:</strong>
                                                            <?= htmlspecialchars($empleado_encontrado['ci']) ?><br>
                                                            <strong>TelÃ©fono:</strong>
                                                            <?= htmlspecialchars($empleado_encontrado['telefono']) ?><br>
                                                            <strong>GÃ©nero:</strong>
                                                            <?= htmlspecialchars($empleado_encontrado['genero']) ?><br>
                                                            <strong>Fecha Nac.:</strong>
                                                            <?= htmlspecialchars($empleado_encontrado['fecha_nacimiento']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columna 2: Formulario de contrato -->
                                        <div class="col-md-5">
                                            <div id="formularioContrato" class="card">
                                                <div class="card-header">
                                                    <h5> Datos del Contrato Laboral</h5>
                                                </div>
                                                <div class="card-body">
                                                    <form id="formContrato">
                                                        <input type="hidden" name="empleadoID"
                                                            value="<?= htmlspecialchars($empleado_encontrado['empleadoID']) ?>">
                                                        <input type="hidden" name="empresaID"
                                                            value="<?= htmlspecialchars($empresaID) ?>">

                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label for="rol" class="form-label">(*) CARGO</label>
                                                                <select class="form-control" name="rol" id="rol" required>
                                                                    <option value="">Seleccione...</option>
                                                                    <?php foreach ($rs_roles as $rol): ?>
                                                                        <option value="<?= htmlspecialchars($rol['rol']) ?>">
                                                                            <?= htmlspecialchars($rol['rol']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                <div class="invalid-feedback" id="rolError"></div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="sueldo" class="form-label">(*) Sueldo</label>
                                                                <input type="number" class="form-control" name="sueldo" id="sueldo"
                                                                    step="0.01" required>
                                                                <div class="invalid-feedback" id="sueldoError"></div>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label for="fecha_inicio" class="form-label">(*) Inicio del
                                                                    Contrato</label>
                                                                <input type="date" class="form-control" name="fecha_inicio"
                                                                    id="fecha_inicio" required>
                                                                <div class="invalid-feedback" id="fechaInicioError"></div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="fecha_fin" class="form-label">Fin del Contrato</label>
                                                                <input type="date" class="form-control" name="fecha_fin"
                                                                    id="fecha_fin">
                                                                <small class="text-muted">Dejar en blanco si es indefinido</small>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columna 3: Contenedor de usuario (inicialmente oculto) -->
                                        <div class="col-md-4" id="contenedorUsuario" style="display: none;">
                                            <!-- El contenido se inyectarÃ¡ dinÃ¡micamente -->
                                        </div>
                                    </div>

                                    <!-- Botones debajo de las 3 columnas -->
                                    <div class="row mt-4">
                                        <div class="col-12 text-center">
                                            <button type="button" class="btn btn-success btn-lg" onclick="guardarContrato()">
                                                <i class="fas fa-save me-2"></i>Guardar Contrato
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-lg" onclick="history.back()">
                                                <i class="fas fa-arrow-left me-2"></i>AtrÃ¡s
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class='alert alert-warning shadow-sm border-warning'>
                                <h5 class='alert-heading'><i class='fas fa-user-slash me-2'></i> Empleado No Encontrado</h5>
                                <p class='mb-0'>No se ha encontrado ninguna Empleado con el C.I.
                                    <strong><?= htmlspecialchars($ci_busqueda) ?></strong> en nuestra base de datos.
                                </p>
                            </div>

                            <div class="text-center">
                                <button class="btn btn-primary" onclick="mostrarFormularioNuevoEmpleado()">
                                    Crear Nuevo Empleado
                                </button>
                                <button class="btn btn-secondary" onclick="history.back()">
                                    At
                                    ras
                                </button>
                            </div>

                            <!-- Formulario de nuevo empleado (inicialmente oculto) -->
                            <div id="formularioNuevoEmpleado" style="display: none;" class="card mt-3">
                                <div class="card-header">
                                    <h5>Datos Empleadoles del Nuevo Empleado</h5>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="ci" class="form-label">(*) C.I.</label>
                                                <input type="text" class="form-control" name="ci" id="ci_nuevo"
                                                    value="<?= htmlspecialchars($ci_busqueda) ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="nombres" class="form-label">(*) Nombres</label>
                                                <input type="text" class="form-control" name="nombres" id="nombres" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="apellidos" class="form-label">(*) Apellidos</label>
                                                <input type="text" class="form-control" name="apellidos" id="apellidos"
                                                    required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="telefono" class="form-label">Tel�fono</label>
                                                <input type="text" class="form-control" name="telefono" id="telefono">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="genero" class="form-label">(*) Sexo</label>
                                                <select class="form-control" name="genero" id="genero" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="M">Masculino</option>
                                                    <option value="F">Femenino</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="fecha_nacimiento" class="form-label">Fecha Nacimiento</label>
                                                <input type="date" class="form-control" name="fecha_nacimiento"
                                                    id="fecha_nacimiento">
                                            </div>
                                        </div>

                                        <div class="text-center mt-3">
                                            <button type="button" class="btn btn-success">
                                                Guardar Empleado
                                            </button>
                                            <button type="button" class="btn btn-secondary"
                                                onclick="ocultarFormularioNuevoEmpleado()">
                                                Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php
                // Pasar datos a JS de forma segura mediante inputs ocultos si el empleado existe
                if (isset($empleado_encontrado)): ?>
                    <input type="hidden" id="nombre_completo_empleado"
                        value="<?= htmlspecialchars($empleado_encontrado['nombres'] . ' ' . $empleado_encontrado['apellidos']) ?>">
                    <input type="hidden" id="empleadoID_oculto"
                        value="<?= htmlspecialchars($empleado_encontrado['empleadoID']) ?>">
                <?php endif; ?>

                <!-- Inclusión de Componentes (Separados por orden) -->
                <?php include_once("modales_empleado.php"); ?>

                <!-- Lógica de Negocio (LOCAL) -->
                <script src="js/empleado_gestion.js"></script>

</body>

</html>
