<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];
$ci_busqueda = isset($_GET['ci']) ? trim($_GET['ci']) : '';
$empleado_encontrado = null;
$contrato_existente = null;

// Obtener roles disponibles de la tabla roles
$sql_roles ="SELECT rolID, rol FROM roles WHERE _estado = 'A' and rolID>1";
$rs_roles = $db->obtenerTodo($sql_roles);

// Si hay CI para buscar (solo para mostrar datos)
if (!empty($ci_busqueda)) {
    // Buscar empleado por CI
    $sql_empleado ="SELECT * FROM empleados WHERE ci = ? AND _estado <> 'X'";
    $rs_empleado = $db->obtenerTodo($sql_empleado, array($ci_busqueda));
    
    if (count($rs_empleado) > 0) {
        $empleado_encontrado = $rs_empleado[0];
        
        // Verificar si ya tiene contrato con esta empresa (solo para mostrar)
        $sql_contrato ="SELECT * FROM empleado_empresas WHERE empleadoID = ? AND empresaID = ? AND _estado <> 'X'";
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
                    <h3 class="mb-0">GESTIÓN DE EMPLEADOS</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($ci_busqueda)): ?>
                    <?php endif; ?>

                    <?php if (!empty($ci_busqueda)): ?>
                        <?php if ($empleado_encontrado): ?>
                            <?php if ($contrato_existente): ?>
                                <!-- Ya tiene contrato con esta empresa -->
                                <div class="alert alert-warning">
                                    <strong>⚠️ Este empleado ya tiene un contrato laboral con esta empresa.</strong>
                                </div>
                                
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5>Datos del Contrato Existente</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>CARGO:</strong> <?= htmlspecialchars($contrato_existente['rol']) ?><br>
                                                <strong>Sueldo:</strong> <?= number_format($contrato_existente['sueldo'], 2, ',', '.') ?><br>
                                                <strong>Inicio del Contrato:</strong> <?= htmlspecialchars($contrato_existente['fecha_inicio']) ?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Fin del Contrato:</strong> <?= !empty($contrato_existente['fecha_fin']) ? htmlspecialchars($contrato_existente['fecha_fin']) : 'Indefinido' ?><br>
                                                <strong>Estado Laboral:</strong> <?= htmlspecialchars($contrato_existente['estado_laboral']) ?>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-3">
                                            <button class="btn btn-primary" onclick="mostrarFormularioNuevoContrato()">
                                                📝 Agregar Nuevo Contrato
                                            </button>
                                            <button class="btn btn-secondary" onclick="location.href='empleado_nuevo.php'">
                                                Atrás
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
                                                <h6>Datos Personales</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <strong>Nombre:</strong> <?= htmlspecialchars($empleado_encontrado['nombres']) ?><br>
                                                        <strong>Apellidos:</strong> <?= htmlspecialchars($empleado_encontrado['apellidos']) ?><br>
                                                        <strong>C.I.:</strong> <?= htmlspecialchars($empleado_encontrado['ci']) ?><br>
                                                        <strong>Teléfono:</strong> <?= htmlspecialchars($empleado_encontrado['telefono']) ?><br>
                                                        <strong>Género:</strong> <?= htmlspecialchars($empleado_encontrado['genero']) ?><br>
                                                        <strong>Fecha Nac.:</strong> <?= htmlspecialchars($empleado_encontrado['fecha_nacimiento']) ?>
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
                                                    <input type="hidden" name="empleadoID" value="<?= htmlspecialchars($empleado_encontrado['empleadoID']) ?>">
                                                    <input type="hidden" name="empresaID" value="<?= htmlspecialchars($empresaID) ?>">
                                                    
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
                                                            <input type="number" class="form-control" name="sueldo" id="sueldo" step="0.01" required>
                                                            <div class="invalid-feedback" id="sueldoError"></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="fecha_inicio" class="form-label">(*) Inicio del Contrato</label>
                                                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" required>
                                                            <div class="invalid-feedback" id="fechaInicioError"></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="fecha_fin" class="form-label">Fin del Contrato</label>
                                                            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                                                            <small class="text-muted">Dejar en blanco si es indefinido</small>
                                                        </div>
                                                    </div>
                                                    
                                                                                                    </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Columna 3: Contenedor de usuario (inicialmente oculto) -->
                                    <div class="col-md-4" id="contenedorUsuario" style="display: none;">
                                        <!-- El contenido se inyectará dinámicamente -->
                                    </div>
                                </div>
                                
                                <!-- Botones debajo de las 3 columnas -->
                                <div class="row mt-4">
                                    <div class="col-12 text-center">
                                        <button type="button" class="btn btn-success btn-lg" onclick="guardarContrato()">
                                            <i class="fas fa-save me-2"></i>Guardar Contrato
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-lg" onclick="history.back()">
                                            <i class="fas fa-arrow-left me-2"></i>Atrás
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <!-- Empleado no encontrado -->
                            <div class="alert alert-warning">
                                <strong>❌ No se encontró ningún empleado con C.I. <?= htmlspecialchars($ci_busqueda) ?></strong>
                            </div>
                            
                            <div class="text-center">
                                <button class="btn btn-primary" onclick="mostrarFormularioNuevoEmpleado()">
                                    Crear Nuevo Empleado
                                </button>
                                <button class="btn btn-secondary" onclick="history.back()">
                                    Atrás
                                </button>
                            </div>

                            <!-- Formulario de nuevo empleado (inicialmente oculto) -->
                            <div id="formularioNuevoEmpleado" style="display: none;" class="card mt-3">
                                <div class="card-header">
                                    <h5>👤 Datos Personales del Nuevo Empleado</h5>
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
                                                <input type="text" class="form-control" name="apellidos" id="apellidos" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" name="telefono" id="telefono">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="genero" class="form-label">(*) Género</label>
                                                <select class="form-control" name="genero" id="genero" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="M">Masculino</option>
                                                    <option value="F">Femenino</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="fecha_nacimiento" class="form-label">Fecha Nacimiento</label>
                                                <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento">
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-3">
                                            <button type="button" class="btn btn-success">
                                                💾 Guardar Empleado
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="ocultarFormularioNuevoEmpleado()">
                                                ❌ Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de búsqueda por CI -->
<div class="modal fade" id="modalBusquedaCi" tabindex="-1" aria-labelledby="modalBusquedaCiLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBusquedaCiLabel">Buscar Empleado por C.I.</h5>
                <button type="button" class="btn-close" onclick="history.back()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formBusquedaCi" method="GET">
                    <div class="mb-3">
                        <label for="ci_modal" class="form-label">Ingrese C.I. del Empleado:</label>
                        <input type="text" class="form-control" name="ci" id="ci_modal" 
                               placeholder="Ej: 12345678" 
                               required>
                        <small class="text-muted">Ingrese el número de Cédula de Identidad del empleado</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
                <button type="submit" form="formBusquedaCi" class="btn btn-success">
                    Buscar Empleado
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de validación y mensajes -->
<div class="modal fade" id="modalValidacion" tabindex="-1" aria-labelledby="modalValidacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalValidacionLabel">Mensaje del Sistema</h5>
                <button type="button" class="btn-close" onclick="cerrarModalValidacion()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="mensajeModal"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="cerrarModalValidacion()">Aceptar</button>
            </div>
        </div>
    </div>
</div>


<!-- FontAwesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Auto-abrir modal al cargar la página y limpiar campo CI
document.addEventListener('DOMContentLoaded', function() {
    const modalBusquedaCi = document.getElementById('modalBusquedaCi');
    if (modalBusquedaCi) {
        // Solo abrir modal si no hay búsqueda en curso
        if (window.location.search === '') {
            const modal = new bootstrap.Modal(modalBusquedaCi);
            modal.show();
        }
        
        // Limpiar campo CI cuando se abre el modal
        modalBusquedaCi.addEventListener('show.bs.modal', function () {
            document.getElementById('ci_modal').value = '';
            document.getElementById('ci_modal').focus();
        });
    }
});

function ocultarFormularioContrato() {
    document.getElementById('formularioContrato').style.display = 'none';
}

function mostrarFormularioNuevoEmpleado() {
    document.getElementById('formularioNuevoEmpleado').style.display = 'block';
}

function ocultarFormularioNuevoEmpleado() {
    document.getElementById('formularioNuevoEmpleado').style.display = 'none';
}

function mostrarFormularioUsuario() {
    document.getElementById('formularioUsuario').style.display = 'block';
}

function ocultarFormularioUsuario() {
    document.getElementById('formularioUsuario').style.display = 'none';
}

function guardarContrato() {
    // Limpiar validaciones anteriores
    limpiarValidacionesContrato();
    
    // Validar campos obligatorios
    const rol = document.getElementById('rol').value.trim();
    const sueldo = document.getElementById('sueldo').value.trim();
    const fecha_inicio = document.getElementById('fecha_inicio').value.trim();
    
    let hayErrores = false;
    
    if (!rol) {
        document.getElementById('rol').classList.add('is-invalid');
        document.getElementById('rolError').textContent = 'El cargo es obligatorio';
        hayErrores = true;
    }
    
    if (!sueldo) {
        document.getElementById('sueldo').classList.add('is-invalid');
        document.getElementById('sueldoError').textContent = 'El sueldo es obligatorio';
        hayErrores = true;
    }
    
    if (!fecha_inicio) {
        document.getElementById('fecha_inicio').classList.add('is-invalid');
        document.getElementById('fechaInicioError').textContent = 'La fecha de inicio es obligatoria';
        hayErrores = true;
    }
    
    if (hayErrores) {
        return;
    }
    
    // Depuración: mostrar datos del formulario
    console.log('=== DEPURACIÓN GUARDAR CONTRATO ===');
    console.log('empleadoID:', document.querySelector('input[name="empleadoID"]').value);
    console.log('empresaID:', document.querySelector('input[name="empresaID"]').value);
    console.log('rol:', rol);
    console.log('sueldo:', sueldo);
    console.log('fecha_inicio:', fecha_inicio);
    console.log('fecha_fin:', document.getElementById('fecha_fin').value);
    
    // Limpiar formato de sueldo antes de enviar
    const sueldoInput = document.getElementById('sueldo');
    const sueldoOriginal = sueldoInput.value;
    sueldoInput.value = sueldoInput.value.replace(/\D/g, ''); // Eliminar separadores
    
    // Obtener datos del formulario
    const formData = new FormData(document.getElementById('formContrato'));
    
    // Restaurar formato visual
    sueldoInput.value = sueldoOriginal;
    
    // Enviar datos al servidor
    fetch('empleado_contrato_guardar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log('Respuesta del servidor:', data);
        
        // Si el contrato se guardó correctamente, preguntar si desea crear usuario
        if (data.includes('SUCCESS') || data.includes('ÉXITO')) {
            // Primero mostrar contrato guardado
            mostrarContratoGuardado();
            
            // Luego mostrar pregunta en tercera columna
            mostrarPreguntaUsuario();
        } else {
            document.getElementById('mensajeModal').textContent = 'Error al guardar el contrato. Revise la consola para más detalles.';
            const modal = new bootstrap.Modal(document.getElementById('modalValidacion'));
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error al guardar contrato:', error);
        // Mostrar error con modal de Bootstrap
        document.getElementById('mensajeModal').textContent = 'Error al guardar el contrato. Revise la consola para más detalles.';
        const modal = new bootstrap.Modal(document.getElementById('modalValidacion'));
        modal.show();
    });
}

function mostrarContratoGuardado() {
    // Hacer readonly los campos del contrato
    document.getElementById('rol').setAttribute('readonly', true);
    document.getElementById('sueldo').setAttribute('readonly', true);
    document.getElementById('fecha_inicio').setAttribute('readonly', true);
    document.getElementById('fecha_fin').setAttribute('readonly', true);
    
    // Quitar clases de validación
    document.getElementById('rol').classList.remove('is-invalid', 'is-valid');
    document.getElementById('sueldo').classList.remove('is-invalid', 'is-valid');
    document.getElementById('fecha_inicio').classList.remove('is-invalid', 'is-valid');
    document.getElementById('fecha_fin').classList.remove('is-invalid', 'is-valid');
    
    // Ocultar botones debajo de las 3 columnas
    const botonesInferiores = document.querySelector('.row.mt-4 .col-12');
    if (botonesInferiores) {
        botonesInferiores.style.display = 'none';
    }
    
    // Eliminar botón "Atrás" del card de empleado
    const btnAtras = document.querySelector('#datosEmpleado .btn-secondary');
    if (btnAtras) {
        btnAtras.style.display = 'none';
    }
}

function cerrarModalValidacion() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalValidacion'));
    if (modal) {
        modal.hide();
    }
}

function mostrarPreguntaUsuario() {
    // Mostrar tercera columna con pregunta
    const contenedorUsuario = document.getElementById('contenedorUsuario');
    contenedorUsuario.innerHTML = `
        <div class="card">
            <div class="card-header">
                <h6>Crear Usuario</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                    <h5>¿Desea crear un usuario para este empleado?</h5>
                    <p class="text-muted">Podrá acceder al sistema con sus propias credenciales</p>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success btn-lg" onclick="mostrarFormularioUsuario()">
                        <i class="fas fa-check me-2"></i>Sí, crear usuario
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.href='personas.php'">
                        <i class="fas fa-times me-2"></i>No, gracias
                    </button>
                </div>
            </div>
        </div>
    `;
    contenedorUsuario.style.display = 'block';
}

function mostrarFormularioUsuario() {
    // Mostrar tercera columna con formulario de usuario
    const contenedorUsuario = document.getElementById('contenedorUsuario');
    contenedorUsuario.innerHTML = `
        <div class="card">
            <div class="card-header">
                <h6>Crear Usuario</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Empleado:</strong> <?= htmlspecialchars($empleado_encontrado['nombres'] . ' ' . $empleado_encontrado['apellidos']) ?>
                </div>
                
                <form id="formUsuario">
                    <input type="hidden" name="empleadoID" value="<?= htmlspecialchars($empleado_encontrado['empleadoID']) ?>">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <label for="usuario" class="form-label">(*) Nombre de Usuario</label>
                            <input type="text" class="form-control" name="usuario" id="usuario" required>
                            <div class="invalid-feedback" id="usuarioError"></div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <label for="clave" class="form-label">(*) Contraseña</label>
                            <input type="password" class="form-control" name="clave" id="clave" required>
                            <div class="invalid-feedback" id="claveError"></div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-success" onclick="guardarUsuario()">
                            Crear Usuario
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='personas.php'">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    contenedorUsuario.style.display = 'block';
}

function guardarUsuario() {
    // Limpiar validaciones anteriores
    limpiarValidacionesUsuario();
    
    // Validar campos obligatorios
    const usuario = document.getElementById('usuario').value.trim();
    const clave = document.getElementById('clave').value.trim();
    
    let hayErrores = false;
    
    if (!usuario) {
        document.getElementById('usuario').classList.add('is-invalid');
        document.getElementById('usuarioError').textContent = 'El nombre de usuario es obligatorio';
        hayErrores = true;
    }
    
    if (!clave) {
        document.getElementById('clave').classList.add('is-invalid');
        document.getElementById('claveError').textContent = 'La contraseña es obligatoria';
        hayErrores = true;
    }
    
    if (hayErrores) {
        return;
    }
    
    // Simular guardado del usuario
    document.getElementById('usuario').classList.add('is-valid');
    document.getElementById('clave').classList.add('is-valid');
    
    // Aquí se guardaría el usuario y el trigger insertaría en roles_usuario
    setTimeout(() => {
        // Redirigir a la tabla de empleados
        window.location.href = 'personas.php';
    }, 1500); // Redirigir después de 1.5 segundos
}

function limpiarValidacionesContrato() {
    // Limpiar validaciones del contrato
    const campos = ['rol', 'sueldo', 'fecha_inicio', 'fecha_fin'];
    campos.forEach(campo => {
        const element = document.getElementById(campo);
        if (element) {
            element.classList.remove('is-invalid', 'is-valid');
        }
    });
    
    // Limpiar mensajes de error
    const errores = ['rolError', 'sueldoError', 'fechaInicioError'];
    errores.forEach(error => {
        const element = document.getElementById(error);
        if (element) {
            element.textContent = '';
        }
    });
}

function limpiarValidacionesUsuario() {
    // Limpiar validaciones del usuario
    const campos = ['usuario', 'clave'];
    campos.forEach(campo => {
        const element = document.getElementById(campo);
        if (element) {
            element.classList.remove('is-invalid', 'is-valid');
        }
    });
    
    // Limpiar mensajes de error
    const errores = ['usuarioError', 'claveError'];
    errores.forEach(error => {
        const element = document.getElementById(error);
        if (element) {
            element.textContent = '';
        }
    });
}

function ocultarFormularioUsuario() {
    document.getElementById('contenedorUsuario').style.display = 'none';
    
    // Restaurar campos del contrato
    document.getElementById('rol').removeAttribute('readonly');
    document.getElementById('sueldo').removeAttribute('readonly');
    document.getElementById('fecha_inicio').removeAttribute('readonly');
    document.getElementById('fecha_fin').removeAttribute('readonly');
    
    // Mostrar botón de guardar contrato
    const btnGuardar = document.querySelector('#formContrato button[onclick="guardarContrato()"]');
    if (btnGuardar) {
        btnGuardar.style.display = 'inline-block';
    }
    
    // Limpiar validaciones
    limpiarValidacionesContrato();
    limpiarValidacionesUsuario();
}

// Agregar event listeners para limpiar validaciones y formatear sueldo
document.addEventListener('DOMContentLoaded', function() {
    // Formatear sueldo con separadores de miles
    const sueldoInput = document.getElementById('sueldo');
    if (sueldoInput) {
        sueldoInput.addEventListener('input', function(e) {
            // Limpiar validación
            this.classList.remove('is-invalid');
            document.getElementById('sueldoError').textContent = '';
            
            // Formatear con separadores de miles
            let value = e.target.value.replace(/\D/g, ''); // Eliminar todo excepto números
            if (value) {
                // Formatear con separadores de miles
                value = parseInt(value).toLocaleString('es-ES');
                e.target.value = value;
            }
        });
        
        // Limpiar formato al enviar formulario
        document.getElementById('formContrato').addEventListener('submit', function(e) {
            e.preventDefault();
            const sueldoValue = sueldoInput.value.replace(/\D/g, ''); // Eliminar separadores para enviar
            sueldoInput.value = sueldoValue;
            guardarContrato();
        });
    }
    
    // Campos del contrato
    const camposContrato = ['rol', 'fecha_inicio', 'fecha_fin'];
    camposContrato.forEach(campo => {
        const element = document.getElementById(campo);
        if (element) {
            element.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const errorId = campo + 'Error';
                if (campo === 'fecha_inicio') {
                    document.getElementById('fechaInicioError').textContent = '';
                } else {
                    document.getElementById(errorId).textContent = '';
                }
            });
        }
    });
    
    // Campos del usuario
    const camposUsuario = ['usuario', 'clave'];
    camposUsuario.forEach(campo => {
        const element = document.getElementById(campo);
        if (element) {
            element.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                document.getElementById(campo + 'Error').textContent = '';
            });
        }
    });
});
</script>

</body>
</html>
