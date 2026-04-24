function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// ─────────────────────────────────────────────
// COLUMNA 1: BUSCAR EMPLEADO
// ─────────────────────────────────────────────
function realizarBusquedaAjax() {
    const ci = document.getElementById('ci_busqueda').value.trim();
    if (!ci) return;

    // Resetear vistas
    const resDiv = document.getElementById('resultadoBusqueda');
    resDiv.innerHTML = "";
    document.getElementById('formularioRegistroEmpleado').style.display = 'none';

    bloquearSeccion('seccionContrato');
    bloquearSeccion('seccionUsuario');
    document.getElementById('mensajeContratoOk').style.display = 'none';

    fetch(`ajax_buscar_empleado.php?ci=${ci}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'YA_TIENE_CONTRATO') {
            tieneUsuarioGlobal = data.tiene_usuario;
            const infoUsuario = data.tiene_usuario
                ? `<br><b>Usuario:</b> ${data.Usuario.usuario}`
                : `<br><span class="text-danger">⚠️ Sin usuario del sistema</span>`;

            resDiv.innerHTML = `
                <div class="card border-dark bg-light mb-2 shadow-sm">
                    <div class="card-header bg-info text-white py-1"><b>EMPLEADO CON CONTRATO</b></div>
                    <div class="card-body p-2 small">
                        <b>Nombre:</b> ${data.Empleado.nombres} ${data.Empleado.apellidos}<br>
                        <hr class="my-1">
                        <b>Cargo:</b> ${data.Contrato.rol_texto}<br>
                        <b>Sueldo:</b> Bs. ${data.Contrato.sueldo}<br>
                        <b>Estado Laboral:</b> ${data.Contrato.estado_laboral}
                        ${infoUsuario}
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold w-100 mt-1"
                        onclick="habilitarNuevoContrato(${data.Empleado.empleadoID})">
                    + AGREGAR NUEVO CONTRATO
                </button>`;

            // Si NO tiene usuario → habilitar columna 3 directamente
            if (!data.tiene_usuario) {
                document.getElementById('input_empleadoID').value = data.Empleado.empleadoID;
                desbloquearSeccion('seccionUsuario');
            }
            // Col 2 (contrato) permanece bloqueada hasta que se presione el botón


        } else if (data.status === 'EXISTE_SIN_CONTRATO') {
            tieneUsuarioGlobal = data.tiene_usuario;
            resDiv.innerHTML = `
                <div class="alert alert-success py-2 small border-dark">
                    <b>ID CONFIRMADA:</b> ${data.Empleado.nombres} ${data.Empleado.apellidos}
                </div>`;

            document.getElementById('input_empleadoID').value = data.Empleado.empleadoID;
            desbloquearSeccion('seccionContrato');
            document.getElementById('rol').focus();

            // Si ya tiene usuario: bloquear col 3 permanentemente con aviso
            if (data.tiene_usuario) {
                const seccionU = document.getElementById('seccionUsuario');
                if (seccionU) {
                    seccionU.innerHTML = `
                        <div class="alert alert-secondary py-2 small mt-2">
                            🔒 Este empleado ya tiene usuario asignado:
                            <b>${data.Usuario.usuario}</b><br>
                            No es posible crear un segundo usuario.
                        </div>`;
                    seccionU.classList.remove('col-bloqueada');
                    seccionU.classList.add('col-desbloqueada');
                    seccionU.style.pointerEvents = 'none';
                }
            }
            // Si no tiene usuario: col 3 se habilitará después de guardar el contrato

        } else if (data.status === 'NO_EXISTE') {
            resDiv.innerHTML = `
                <div class="alert alert-warning py-1 small border-dark">Empleado no encontrado. Regístrelo abajo.</div>`;

            document.getElementById('formularioRegistroEmpleado').style.display = 'block';
            document.getElementById('reg_ci_display').innerText = ci;
            document.getElementById('reg_ci').value = ci;
            document.getElementById('reg_nombres').focus();
        }
    })
    .catch(err => console.error("Error crítico:", err));
}

// ─────────────────────────────────────────────
// COLUMNA 1: HABILITAR NUEVO CONTRATO ADICIONAL
// ─────────────────────────────────────────────
function habilitarNuevoContrato(empleadoID) {
    document.getElementById('input_empleadoID').value = empleadoID;

    // Cambiar label del botón para indicar que es un contrato adicional
    const btnRegistrar = document.querySelector('#seccionContrato .btn-primary');
    if (btnRegistrar) {
        btnRegistrar.textContent = 'REGISTRAR NUEVO CONTRATO';
    }

    // Mostrar banner informativo en col 2
    const seccion = document.getElementById('seccionContrato');
    const bannerExistente = seccion.querySelector('.banner-nuevo-contrato');
    if (!bannerExistente) {
        const banner = document.createElement('div');
        banner.className = 'alert alert-info py-1 small mb-3 banner-nuevo-contrato';
        banner.innerHTML = 'ℹ️ <b>CONTRATO ADICIONAL</b> — Se agregará un nuevo contrato al empleado.';
        seccion.insertBefore(banner, seccion.firstChild);
    }

    desbloquearSeccion('seccionContrato');
    document.getElementById('rol').focus();
}

// ─────────────────────────────────────────────
// COLUMNA 1: GUARDAR NUEVO EMPLEADO (si no existe)
// ─────────────────────────────────────────────
function guardarNuevoEmpleado() {
    const nombres  = document.getElementById('reg_nombres').value.trim();
    const apellidos = document.getElementById('reg_apellidos').value.trim();
    const genero   = document.getElementById('reg_genero').value;
    const ci       = document.getElementById('reg_ci').value;
    const telefono = document.getElementById('reg_telefono').value;
    const fecha_nacimiento = document.getElementById('reg_fecha_nacimiento').value;

    if (!nombres || !apellidos || !genero) {
        alert("Complete los campos obligatorios del empleado");
        return;
    }

    const formData = new FormData();
    formData.append('nombres', nombres);
    formData.append('apellidos', apellidos);
    formData.append('genero', genero);
    formData.append('ci', ci);
    formData.append('telefono', telefono);
    formData.append('fecha_nacimiento', fecha_nacimiento);

    fetch('ajax_inserta_empleado.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'SUCCESS') {
            tieneUsuarioGlobal = false;
            const resBusqueda = document.getElementById('resultadoBusqueda');
            resBusqueda.innerHTML = `
                <div class="card border-success bg-light mb-2 shadow-sm">
                    <div class="card-header bg-success text-white py-1"><b>EMPLEADO REGISTRADO</b></div>
                    <div class="card-body p-2 small">
                        <b>Nombre:</b> ${nombres} ${apellidos}<br>
                        <b>C.I.:</b> ${ci}<br>
                        <b>Género:</b> ${genero === 'M' ? 'Masculino' : 'Femenino'}
                    </div>
                </div>`;

            document.getElementById('input_empleadoID').value = data.empleadoID;
            document.getElementById('formularioRegistroEmpleado').style.display = 'none';
            desbloquearSeccion('seccionContrato');
        } else {
            alert("Error: " + data.message);
        }
    });
}

// ─────────────────────────────────────────────
// COLUMNA 2: GUARDAR CONTRATO
// ─────────────────────────────────────────────
function guardarContrato() {
    const rol = document.getElementById('rol').value;
    const sueldoInput = document.getElementById('sueldo');
    const sueldoValueOriginal = sueldoInput.value.trim();
    const fecha_inicio = document.getElementById('fecha_inicio').value;

    if (!rol || !sueldoValueOriginal || !fecha_inicio) {
        const debugDiv = document.getElementById('debugContrato');
        if (debugDiv) debugDiv.innerHTML = '<div class="alert alert-warning mt-2 p-2 small">⚠️ Complete Cargo, Sueldo y Fecha de Inicio.</div>';
        return;
    }

    // Limpiar separadores de miles antes de enviar
    const sueldoLimpio = sueldoValueOriginal.replace(/\./g, '').replace(/,/g, '.');

    const formData = new FormData(document.getElementById('formFichaEmpleado'));
    formData.set('sueldo', sueldoLimpio);

    fetch('empleado_contrato_guardar.php', { method: 'POST', body: formData })
    .then(response => response.text())
    .then(data => {
        if (data.includes('SUCCESS')) {
            // Obtener datos del form para el resumen antes de ocultarlo
            const cargoText = document.getElementById('rol').options[document.getElementById('rol').selectedIndex].text;
            const sueldoText = document.getElementById('sueldo').value;

            // Ocultar formulario y mostrar resumen
            document.getElementById('formContrato').innerHTML = `
                <div class="card border-success bg-light mb-2 shadow-sm">
                    <div class="card-header bg-success text-white py-1"><b>CONTRATO REGISTRADO</b></div>
                    <div class="card-body p-2 small">
                        <b>Cargo:</b> ${cargoText}<br>
                        <b>Sueldo:</b> Bs. ${sueldoText}<br>
                        <span class="text-success">✅ El contrato se guardó correctamente.</span>
                    </div>
                </div>`;

            const msgOk = document.getElementById('mensajeContratoOk');
            if (msgOk) msgOk.style.display = 'block';

            // No bloqueamos la sección completa (gris) para que el resumen se lea bien.
            // Al haber reemplazado el formContrato con el resumen, ya no es editable.

            if (tieneUsuarioGlobal) {
                // Si ya tiene usuario, no hay nada más que hacer aquí. Volver al listado.
                const msgOk = document.getElementById('mensajeContratoOk');
                msgOk.innerHTML += "<br>ℹ️ <b>El empleado ya tiene usuario. Redirigiendo al listado...</b>";
                setTimeout(() => {
                    window.location.href = 'empleados.php';
                }, 1800);
            } else {
                // Si NO tiene usuario, ir a la columna 3
                desbloquearSeccion('seccionUsuario');
                const inputU = document.getElementById('nuevo_usuario');
                if (inputU) inputU.focus();
            }
        } else {
            // MOSTRAR ERROR MODERNO EN LA UI
            const divError = document.getElementById('mensajeContratoError');
            const spanError = document.getElementById('textoContratoError');
            if (divError && spanError) {
                spanError.innerText = data.replace('ERROR:', '').trim();
                divError.style.display = 'block';
                // Hacer scroll suave hacia el error
                divError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            console.error('Error contrato:', data);
        }
    })
    .catch(err => {
        console.error("Error contrato:", err);
        alert("Ocurrió un error al guardar el contrato. Verifique la consola.");
    });
}

// ─────────────────────────────────────────────
// COLUMNA 3: CREAR USUARIO (OPCIONAL)
// ─────────────────────────────────────────────
function guardarUsuario() {
    const empleadoID    = document.getElementById('input_empleadoID').value;
    const usuario       = document.getElementById('nuevo_usuario').value.trim();
    const clave         = document.getElementById('nueva_clave').value;
    const confirmar     = document.getElementById('confirmar_clave').value;
    const msgDiv        = document.getElementById('mensajeUsuario');

    msgDiv.innerHTML = '';

    if (!usuario || !clave || !confirmar) {
        msgDiv.innerHTML = `<div class="alert alert-warning py-1 small">Complete todos los campos de usuario.</div>`;
        return;
    }

    if (clave !== confirmar) {
        msgDiv.innerHTML = `<div class="alert alert-danger py-1 small">Las contraseñas no coinciden.</div>`;
        return;
    }

    if (clave.length < 4) {
        msgDiv.innerHTML = `<div class="alert alert-warning py-1 small">La contraseña debe tener al menos 4 caracteres.</div>`;
        return;
    }

    const formData = new FormData();
    formData.append('empleadoID', empleadoID);
    formData.append('usuario', usuario);
    formData.append('clave', clave);

    fetch('ajax_crear_usuario.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'SUCCESS') {
            msgDiv.innerHTML = `<div class="alert alert-success py-1 small">✅ Usuario <b>${usuario}</b> creado correctamente.</div>`;

            // Deshabilitar campos y botón de crear para evitar doble envío
            document.getElementById('nuevo_usuario').disabled = true;
            document.getElementById('nueva_clave').disabled = true;
            document.getElementById('confirmar_clave').disabled = true;

            // Después de 2s redirigir al listado
            setTimeout(() => { window.location.href = 'empleados.php'; }, 2000);
        } else {
            msgDiv.innerHTML = `<div class="alert alert-danger py-1 small">❌ ${data.message}</div>`;
        }
    })
    .catch(err => {
        msgDiv.innerHTML = `<div class="alert alert-danger py-1 small">Error de conexión.</div>`;
        console.error(err);
    });
}

// ─────────────────────────────────────────────
// COLUMNA 3: OMITIR USUARIO — ir al listado
// ─────────────────────────────────────────────
function omitirUsuario() {
    window.location.href = 'empleados.php';
}

// ─────────────────────────────────────────────
// HELPERS: bloquear / desbloquear secciones
// ─────────────────────────────────────────────
function bloquearSeccion(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('col-desbloqueada');
    el.classList.add('col-bloqueada');
}

function desbloquearSeccion(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('col-bloqueada');
    el.classList.add('col-desbloqueada');
}

// Escapar HTML para mostrar respuesta del servidor sin XSS
function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// ─────────────────────────────────────────────
// FORMATEADOR DE SUELDO con separador de miles
// ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const sueldoInput = document.getElementById('sueldo');
    if (sueldoInput) {
        sueldoInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value) {
                value = new Intl.NumberFormat('de-DE').format(value);
            }
            e.target.value = value;
        });
    }
});
