/**
 * Lógica de Gestión de Empleados
 * Separada del archivo principal para depuración y mantenimiento
 */

function realizarBusquedaAjax() {
    const ci = document.getElementById('ci_modal').value.trim();
    if (!ci) return;

    console.log("LOG: Iniciando búsqueda AJAX para CI:", ci);
    
    fetch(`ajax_buscar_empleado.php?ci=${ci}`)
    .then(response => response.json())
    .then(data => {
        console.log("Respuesta AJAX:", data);
        
        // Cerrar modal de búsqueda
        const modalElement = document.getElementById('modalBusquedaCi');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) modalInstance.hide();

        if (data.status === 'FOUND_EMPLOYEE') {
            window.location.href = `empleado_nuevo.php?ci=${ci}`;
        } else if (data.status === 'FOUND_PERSON') {
            mostrarFormularioNuevoEmpleado();
            const campos = {
                'ci_nuevo': data.Empleado.ci,
                'nombres': data.Empleado.nombres,
                'apellidos': data.Empleado.apellidos,
                'telefono': data.Empleado.telefono,
                'genero': data.Empleado.genero,
                'fecha_nacimiento': data.Empleado.fecha_nacimiento
            };
            for (let id in campos) {
                let el = document.getElementById(id);
                if (el) el.value = campos[id];
            }
            // SIN ALERT: El usuario ya ve el formulario desplegado
        } else {
            mostrarFormularioNuevoEmpleado();
            let elCi = document.getElementById('ci_nuevo');
            if (elCi) elCi.value = ci;
            // SIN ALERT: Apertura directa
        }
    })
    .catch(error => {
        console.error("Error en búsqueda AJAX:", error);
    });
}

/** 
 * Función para mostrar el formulario de contrato cuando el empleado ya existe 
 * pero no tiene contrato laboral con la empresa actual.
 */
function mostrarFormularioNuevoContrato() {
    const formContrato = document.getElementById('formularioContrato');
    if (formContrato) {
        formContrato.style.display = 'block';
        // Scroll suave hasta el formulario
        formContrato.scrollIntoView({ behavior: 'smooth' });
    } else {
        // Si no existe el div específico, recargamos con el CI para forzar la vista de contrato
        const ci = document.getElementById('ci_modal')?.value;
        if(ci) window.location.href = `empleado_nuevo.php?ci=${ci}`;
    }
}


document.addEventListener('DOMContentLoaded', function() {
    console.log("LOG: Script de gestión de empleados cargado.");
    
    // Auto-abrir modal de búsqueda si no hay parámetros en la URL
    var modalElement = document.getElementById('modalBusquedaCi');
    if (modalElement && window.location.search === '') {
        try {
            var modal = new bootstrap.Modal(modalElement);
            modal.show();
        } catch(e) {
            console.error("Error al iniciar modal de búsqueda:", e);
        }
    }

    // Configurar tecla ENTER en el modal de búsqueda
    const inputCiModal = document.getElementById('ci_modal');
    if (inputCiModal) {
        inputCiModal.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                realizarBusquedaAjax();
            }
        });
    }

    // Formatear sueldo con separadores de miles
    const sueldoInput = document.getElementById('sueldo');
    if (sueldoInput) {
        sueldoInput.addEventListener('input', function(e) {
            this.classList.remove('is-invalid');
            var errorLabel = document.getElementById('sueldoError');
            if (errorLabel) errorLabel.textContent = '';
            
            let value = e.target.value.replace(/[^0-9]/g, ''); 
            if (value) {
                value = parseInt(value).toLocaleString('es-ES');
                e.target.value = value;
            }
        });
        
        const formContrato = document.getElementById('formContrato');
        if (formContrato) {
            formContrato.addEventListener('submit', function(e) {
                e.preventDefault();
                const sueldoValue = sueldoInput.value.replace(/[^0-9]/g, ''); 
                sueldoInput.value = sueldoValue;
                guardarContrato();
            });
        }
    }
    
    // Gestión de validaciones genéricas al escribir
    document.querySelectorAll('.form-control').forEach(element => {
        element.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
});

function ocultarFormularioContrato() {
    var container = document.getElementById('formularioContrato');
    if (container) container.style.display = 'none';
}

function mostrarFormularioNuevoEmpleado() {
    var container = document.getElementById('formularioNuevoEmpleado');
    if (container) container.style.display = 'block';
}

function ocultarFormularioNuevoEmpleado() {
    var container = document.getElementById('formularioNuevoEmpleado');
    if (container) container.style.display = 'none';
}

function guardarContrato() {
    limpiarValidacionesContrato();
    
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
    
    if (hayErrores) return;
    
    const sueldoInput = document.getElementById('sueldo');
    const sueldoOriginal = sueldoInput.value;
    sueldoInput.value = sueldoInput.value.replace(/[^0-9]/g, ''); 
    
    const formData = new FormData(document.getElementById('formContrato'));
    sueldoInput.value = sueldoOriginal;
    
    fetch('empleado_contrato_guardar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('SUCCESS') || data.includes('ÉXITO')) {
            mostrarContratoGuardado();
            mostrarPreguntaUsuario();
        } else {
            alert('Error al guardar el contrato. Revise la consola.');
        }
    })
    .catch(error => {
        console.error('Error al guardar contrato:', error);
    });
}

function mostrarContratoGuardado() {
    ['rol', 'sueldo', 'fecha_inicio', 'fecha_fin'].forEach(id => {
        var el = document.getElementById(id);
        if (el) el.setAttribute('readonly', true);
    });
    
    const botonesInferiores = document.querySelector('.row.mt-4 .col-12');
    if (botonesInferiores) botonesInferiores.style.display = 'none';
}

function mostrarPreguntaUsuario() {
    const contenedorUsuario = document.getElementById('contenedorUsuario');
    if (!contenedorUsuario) return;
    
    contenedorUsuario.innerHTML = `
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">Próximo Paso</h6>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                <h5>¿Desea crear un usuario?</h5>
                <p class="text-muted small">Esto le permitirá al empleado acceder al sistema.</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" onclick="mostrarFormularioUsuario()">
                        <i class="fas fa-check me-1"></i>Sí, crear usuario
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='EMPLEADOS.php'">
                        No por ahora
                    </button>
                </div>
            </div>
        </div>
    `;
    contenedorUsuario.style.display = 'block';
}

function mostrarFormularioUsuario() {
    const contenedorUsuario = document.getElementById('contenedorUsuario');
    // Los datos dinámicos como empleadoID se obtienen de los campos ocultos ya presentes en el HTML
    const empNombre = document.getElementById('nombre_completo_empleado')?.value || "el empleado";
    const empID = document.getElementById('empleadoID_oculto')?.value || "";

    contenedorUsuario.innerHTML = `
        <div class="card shadow-sm border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">Crear Acceso de Usuario</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">Asignar credenciales para: <strong>${empNombre}</strong></p>
                <form id="formUsuario">
                    <input type="hidden" name="empleadoID" value="${empID}">
                    <div class="mb-3">
                        <label class="form-label small">Usuario</label>
                        <input type="text" class="form-control" name="usuario" id="usuario" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Contraseña</label>
                        <input type="password" class="form-control" name="clave" id="clave" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" onclick="guardarUsuario()">
                            Confirmar Usuario
                        </button>
                        <button type="button" class="btn btn-link btn-sm text-secondary" onclick="window.location.href='EMPLEADOS.php'">
                            Saltar este paso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

function guardarUsuario() {
    const usuario = document.getElementById('usuario').value.trim();
    const clave = document.getElementById('clave').value.trim();
    
    if (!usuario || !clave) {
        alert("Por favor complete todos los campos.");
        return;
    }
    
    const formData = new FormData(document.getElementById('formUsuario'));
    fetch('usuario_guardar.php', {
        method: 'POST',
        body: formData
    })
    .then(() => {
        window.location.href = 'EMPLEADOS.php';
    })
    .catch(err => console.error(err));
}

function limpiarValidacionesContrato() {
    ['rol', 'sueldo', 'fecha_inicio'].forEach(id => {
        var el = document.getElementById(id);
        if (el) el.classList.remove('is-invalid');
    });
}
