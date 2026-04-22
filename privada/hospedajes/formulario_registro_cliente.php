<!-- Formulario para registrar un nuevo cliente -->
<div id="formularioRegistro" style="display: none;">
    <div class="card bg-light border-primary mb-3 shadow-sm">
        <div class="card-header py-2">
            <h6 class="mb-0">AGREGAR NUEVO CLIENTE</h6>
        </div>
        <div class="card-body p-3">
            <!-- SE CAMBIÓ FORM POR DIV PARA EVITAR ANIDAMIENTO INVÁLIDO EN HTML -->
            <div id="formCliente" class="needs-validation">
                <!-- Alerta de menor arriba -->
                <div id="mensajeAlertas" class="alert alert-danger py-2 px-3 small mt-0 mb-3 fw-bold text-center" style="display:none; border-width: 2px;"></div>

                <div class="row g-2 align-items-center mb-2 px-1">
                    <div class="col-auto">
                        <span class="small fw-bold">(*) C.I.</span>
                    </div>
                    <div class="col-auto">
                        <strong id="ci1_display" class="text-dark" style="font-size: 0.95rem;">-</strong>
                        <input type="hidden" name="ci1" id="ci1">
                    </div>
                    <div class="col-auto ms-3">
                        <span class="small fw-bold">(*) País</span>
                    </div>
                    <div class="col-auto">
                        <strong id="paisID1_text" class="text-dark" style="font-size: 0.95rem;">-</strong>
                        <input type="hidden" name="paisID1" id="paisID1">
                    </div>
                </div>
                
                <!-- INICIO DE FILA DE FORMULARIO -->
                <div class="row g-2">
                    <!-- NOMBRES EN UNA SOLA LÍNEA (Usando d-flex para control total) -->
                    <div class="col-12 mb-1">
                        <div class="d-flex align-items-center justify-content-between" style="width: 100%;">
                        <label for="nombres1" class="form-label small mb-0 fw-bold me-2" style="white-space: nowrap;">(*) Nombres</label>
                        
                        <div style="width: 70%;">
                            <input type="text" class="form-control form-control-sm" name="nombres1" id="nombres1" required onkeyup="this.value=this.value.toUpperCase()">
                            <div class="invalid-feedback">Nombre obligatorio.</div>
                        </div>
                    </div>
                    </div>
                    
                    <!-- APELLIDOS -->
                    <div class="col-6">
                        <label for="apellido1" class="form-label small mb-1 fw-bold">(*) 1er Apellido</label>
                        <input type="text" class="form-control form-control-sm" name="apellido1" id="apellido1" required onkeyup="this.value=this.value.toUpperCase()">
                        <div class="invalid-feedback">Apellido obligatorio.</div>
                    </div>
                    <div class="col-6">
                        <label for="apellido2" class="form-label small mb-1 fw-bold">2do Apellido</label>
                        <input type="text" class="form-control form-control-sm" name="apellido2" id="apellido2" onkeyup="this.value=this.value.toUpperCase()">
                    </div>

                    <!-- FECHA Y LUGAR -->
                    <div class="col-12 col-sm-6">
                        <label for="fecha_nacimiento1" class="form-label small mb-1 fw-bold">(*) Fecha Nacimiento</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_nacimiento1" id="fecha_nacimiento1" onchange="validarEdad()" required>
                        <div class="invalid-feedback">Fecha obligatoria.</div>
                        <div id="edadDisplay" class="text-dark fw-bold mt-1" style="display:none; font-size: 0.85rem;"></div>
                    </div>
                    
                    <div class="col-12 col-sm-6" id="wrapperLugarNacimiento">
                        <label for="lugar_nacimiento1" class="form-label small mb-1 fw-bold">(*) Lugar Nac.</label>
                        <div id="contenedorLugarNacimiento">
                            <!-- JS inyectará el input o el select con ID lugar_nacimiento1 -->
                        </div>
                    </div>

                    <!-- ESTADO CIVIL Y PROFESIÓN -->
                    <div class="col-12 col-sm-6">
                        <label for="estado_civil1" class="form-label small mb-1 fw-bold">Estado Civil</label>
                        <select class="form-control form-control-sm" name="estado_civil1" id="estado_civil1">
                            <option value="" selected disabled>Seleccione</option>
                            <option value="SOLTERO">SOLTERO</option>
                            <option value="CASADO">CASADO</option>
                            <option value="DIVORCIADO">DIVORCIADO</option>
                            <option value="VIUDO">VIUDO</option>
                            <option value="UNION LIBRE">UNIÓN LIBRE</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="profesion1" class="form-label small mb-1 fw-bold">Profesión</label>
                        <input type="text" class="form-control form-control-sm" name="profesion1" id="profesion1" list="listaProfesiones" onkeyup="this.value=this.value.toUpperCase()">
                        <datalist id="listaProfesiones"></datalist>
                    </div>
                </div>

                <div id="mensajeAlerta" class="alert alert-danger p-1 mt-2 small" style="display:none;"></div>

                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-primary btn-sm" onclick="registrarCliente(event)">
                        <i class="fas fa-save"></i> GUARDAR
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('formularioRegistro').style.display='none'">
                        CANCELAR
                    </button>
                </div>
                <span id="debugContent"></span>
            </div>
        </div>
    </div>
</div>
