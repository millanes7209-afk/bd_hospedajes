<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];

// Obtener roles para el combo
$sql_roles = "SELECT rolID, rol FROM roles WHERE _estado = 'A' AND rolID > 1";
$rs_roles = $db->obtenerTodo($sql_roles);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados</title>
    <style>
        /* ESTILO 'HOSPEDAJES': TEXTO NEGRO FUERTE */
        body, label, input, select, textarea, .form-control, .form-select, h5, h4, h3, strong, span, b {
            color: #000 !important;
        }
        .card-header h4 { text-align: left; font-weight: bold; }
        .fw-bold { font-weight: bold !important; }
        .btn-sm { font-weight: bold; }

        /* Columna bloqueada */
        .col-bloqueada {
            opacity: 0.45;
            pointer-events: none;
            transition: opacity 0.35s ease;
        }
        .col-desbloqueada {
            opacity: 1;
            pointer-events: all;
            transition: opacity 0.35s ease;
        }

        /* Badge OPCIONAL */
        .badge-opcional {
            font-size: 0.7rem;
            background: #6c757d;
            color: #fff !important;
            padding: 2px 8px;
            border-radius: 20px;
            vertical-align: middle;
        }

        /* Separador vertical */
        .border-col {
            border-left: 1px solid #000;
        }

        /* Switch personalizado */
        .switch-custom {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        .switch-custom input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider-custom {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        .slider-custom:before {
            position: absolute;
            content: "";
            height: 18px; width: 18px;
            left: 3px; bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider-custom {
            background-color: #0d6efd;
        }
        input:checked + .slider-custom:before {
            transform: translateX(26px);
        }
    </style>
    <script src="js/empleado_gestion.js" defer></script>
</head>
<body>
    <div class="container-fluid mt-2 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">GESTIÓN DE EMPLEADOS</h4>
                    </div>
                    <div class="card-body">
                        <form id="formFichaEmpleado">
                            <div class="row">

                                <!-- ══════════════════════════════════════════ -->
                                <!-- COLUMNA 1: BUSCAR / REGISTRAR EMPLEADO    -->
                                <!-- ══════════════════════════════════════════ -->
                                <div class="col-md-4 pe-md-4">
                                    <h5 class="border-bottom border-dark pb-2 mb-3 fw-bold">SELECCIONAR EMPLEADO</h5>

                                    <div class="row g-2 mb-2">
                                        <div class="col-md-12">
                                            <label for="ci_busqueda" class="form-label small fw-bold">C.I. / Documento</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control border-dark" id="ci_busqueda"
                                                       placeholder="CI..." autofocus
                                                       onkeydown="if(event.key==='Enter'){event.preventDefault(); realizarBusquedaAjax();}">
                                                <button type="button" class="btn btn-primary" onclick="realizarBusquedaAjax()">BUSCAR</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="resultadoBusqueda" class="mb-2"></div>

                                    <!-- FORMULARIO DE REGISTRO INTEGRADO -->
                                    <div id="formularioRegistroEmpleado" style="display: none;">
                                        <div class="card bg-light border-primary mb-3 shadow-sm">
                                            <div class="card-header py-2">
                                                <h6 class="mb-0 fw-bold">AGREGAR NUEVO EMPLEADO</h6>
                                            </div>
                                            <div class="card-body p-3">
                                                <div id="formEmpleado" class="needs-validation">
                                                    <!-- CI -->
                                                    <div class="row g-2 align-items-center mb-2 px-1">
                                                        <div class="col-auto">
                                                            <span class="small fw-bold">(*) C.I.</span>
                                                        </div>
                                                        <div class="col-auto">
                                                            <strong id="reg_ci_display" class="text-dark" style="font-size: 0.95rem;">-</strong>
                                                            <input type="hidden" id="reg_ci">
                                                        </div>
                                                    </div>

                                                    <div class="row g-2">
                                                        <!-- NOMBRES -->
                                                        <div class="col-12 mb-1">
                                                            <div class="d-flex align-items-center justify-content-between" style="width: 100%;">
                                                                <label class="form-label small mb-0 fw-bold me-2" style="white-space: nowrap;">(*) Nombres</label>
                                                                <div style="width: 70%;">
                                                                    <input type="text" class="form-control form-control-sm border-dark" id="reg_nombres" onkeyup="this.value=this.value.toUpperCase()">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- APELLIDOS -->
                                                        <div class="col-12 mb-1">
                                                            <div class="d-flex align-items-center justify-content-between" style="width: 100%;">
                                                                <label class="form-label small mb-0 fw-bold me-2" style="white-space: nowrap;">(*) Apellidos</label>
                                                                <div style="width: 70%;">
                                                                    <input type="text" class="form-control form-control-sm border-dark" id="reg_apellidos" onkeyup="this.value=this.value.toUpperCase()">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- GENERO Y TELEFONO -->
                                                        <div class="col-6">
                                                            <label class="form-label small mb-1 fw-bold">(*) Género</label>
                                                            <select class="form-control form-control-sm border-dark" id="reg_genero">
                                                                <option value="">Seleccione</option>
                                                                <option value="M">Masculino</option>
                                                                <option value="F">Femenino</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small mb-1 fw-bold">Teléfono</label>
                                                            <input type="text" class="form-control form-control-sm border-dark" id="reg_telefono">
                                                        </div>

                                                        <!-- FECHA NACIMIENTO -->
                                                        <div class="col-12">
                                                            <label class="form-label small mb-1 fw-bold">Fecha Nacimiento</label>
                                                            <input type="date" class="form-control form-control-sm border-dark" id="reg_fecha_nacimiento">
                                                        </div>
                                                    </div>

                                                    <div class="d-grid gap-2 mt-3">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="guardarNuevoEmpleado()">
                                                            GUARDAR EMPLEADO
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('formularioRegistroEmpleado').style.display='none'">
                                                            CANCELAR
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ══════════════════════════════════════════ -->
                                <!-- COLUMNA 2: DATOS DEL CONTRATO             -->
                                <!-- ══════════════════════════════════════════ -->
                                <div class="col-md-4 border-col px-md-4">
                                    <h5 class="border-bottom border-dark pb-2 mb-3 fw-bold text-primary">DATOS DEL CONTRATO</h5>
                                    <div id="seccionContrato" class="col-bloqueada">
                                        <div id="formContrato">
                                            <input type="hidden" name="empleadoID" id="input_empleadoID">
                                            <input type="hidden" name="empresaID" value="<?= $empresaID ?>">

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="rol" class="form-label fw-bold small">(*) Cargo / Rol</label>
                                                <select class="form-control border-dark" name="rolID" id="rol" required>
                                                    <option value="">Seleccione Cargo</option>
                                                    <?php foreach ($rs_roles as $r): ?>
                                                        <option value="<?= (int)$r['rolID'] ?>"><?= htmlspecialchars($r['rol']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="sueldo" class="form-label fw-bold small">(*) Sueldo (Bs)</label>
                                                <input type="text" class="form-control border-dark" name="sueldo" id="sueldo" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="fecha_inicio" class="form-label fw-bold small">(*) Fecha Inicio</label>
                                                <input type="date" class="form-control border-dark" name="fecha_inicio" id="fecha_inicio" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="fecha_fin" class="form-label fw-bold small">Fecha Fin (Opcional)</label>
                                                <input type="date" class="form-control border-dark" name="fecha_fin" id="fecha_fin">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label for="descripcion" class="form-label fw-bold small">Descripción / Notas</label>
                                                <textarea class="form-control border-dark" name="descripcion" id="descripcion" rows="2" onkeyup="this.value=this.value.toUpperCase()"></textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2 bg-light border-dark shadow-sm">
                                                    <div>
                                                        <label class="form-label fw-bold small mb-0 d-block" for="es_titular" style="cursor:pointer;">
                                                            💎 EMPLEADO TITULAR
                                                        </label>
                                                        <div class="text-muted" style="font-size:0.7rem;">Define si este es el cargo principal del empleado.</div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <label class="switch-custom">
                                                            <input type="checkbox" id="es_titular" name="es_titular" value="1" checked>
                                                            <span class="slider-custom"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-grid mt-3">
                                            <button class="btn btn-primary fw-bold" type="button" onclick="guardarContrato()">REGISTRAR CONTRATO</button>
                                        </div>

                                        <!-- Mensaje de éxito tras guardar contrato -->
                                        </div> <!-- Fin formContrato -->

                                        <!-- Mensaje de éxito tras guardar contrato -->
                                        <div id="mensajeContratoOk" class="alert alert-success mt-3 py-2 small" style="display:none;">
                                            ✅ Contrato registrado correctamente.
                                        </div>

                                        <!-- Mensaje de ERROR (Más moderno) -->
                                        <div id="mensajeContratoError" class="alert alert-danger mt-3 py-2 small border-2 border-danger shadow-sm" style="display:none;">
                                            ⚠️ <span id="textoContratoError"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- ══════════════════════════════════════════ -->
                                <!-- COLUMNA 3: CREAR USUARIO (OPCIONAL)       -->
                                <!-- ══════════════════════════════════════════ -->
                                <div class="col-md-4 border-col ps-md-4">
                                    <h5 class="border-bottom border-dark pb-2 mb-3 fw-bold">
                                        CREAR USUARIO DEL SISTEMA
                                        <span class="badge-opcional ms-2">OPCIONAL</span>
                                    </h5>

                                    <div id="seccionUsuario" class="col-bloqueada">

                                        <p class="small text-muted mb-3">
                                            Si este empleado necesita acceder al sistema, complete los campos. De lo contrario, omita este paso.
                                        </p>

                                        <div class="mb-3">
                                            <label for="nuevo_usuario" class="form-label fw-bold small">(*) Nombre de Usuario</label>
                                            <input type="text" class="form-control border-dark" id="nuevo_usuario"
                                                   placeholder="ej: jperez"
                                                   onkeyup="this.value=this.value.toLowerCase().replace(/\s/g,'')">
                                        </div>

                                        <div class="mb-3">
                                            <label for="nueva_clave" class="form-label fw-bold small">(*) Contraseña</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control border-dark" id="nueva_clave">
                                                <button class="btn btn-outline-secondary border-dark" type="button" onclick="togglePassword('nueva_clave', this)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="confirmar_clave" class="form-label fw-bold small">(*) Confirmar Contraseña</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control border-dark" id="confirmar_clave">
                                                <button class="btn btn-outline-secondary border-dark" type="button" onclick="togglePassword('confirmar_clave', this)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div id="mensajeUsuario" class="mb-2"></div>

                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-success fw-bold" onclick="guardarUsuario()">
                                                CREAR USUARIO
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="omitirUsuario()">
                                                OMITIR
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- /row -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
