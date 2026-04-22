/**
 * Cálculo automático de checkout y autocompletado
 */
function autocompletarCheckout() {
    var hoy = new Date();
    // Sumar 1 día para el checkout por defecto
    hoy.setDate(hoy.getDate() + 1);
    hoy.setHours(13, 0, 0, 0); // Check-out estándar a las 12:00 PM

    actualizarSalida(hoy);
}

function actualizarSalida() {
    var tipo = document.getElementById('tipo').value;
    var divDuracion = document.getElementById('contenedorDuracion');
    var checkoutInput = document.getElementById('checkout');
    var inputPrecio = document.getElementById('monto_total');
    var hoy = new Date();
    var checkoutDate = new Date();

    if (tipo === 'MOMENTANEO') {
        if (divDuracion) divDuracion.style.display = 'block';
        
        var duracion = parseInt(document.getElementById('duracion').value) || 1;
        var minutosGracia = duracion * 10;
        
        // Asignar precio dinámico según duración
        if (duracion == 1) inputPrecio.value = 30;
        else if (duracion == 2) inputPrecio.value = 50;
        else if (duracion == 3) inputPrecio.value = 60;

        checkoutDate.setHours(hoy.getHours() + duracion);
        checkoutDate.setMinutes(hoy.getMinutes() + minutosGracia);

    } else {
        if (divDuracion) divDuracion.style.display = 'none';
        
        // Restaurar precio original
        if (inputPrecio) {
            inputPrecio.value = inputPrecio.getAttribute('data-original');
        }
        
        var horaActual = hoy.getHours();
        if (horaActual >= 6) {
            checkoutDate.setDate(hoy.getDate() + 1);
        } else {
            checkoutDate.setDate(hoy.getDate());
        }
        checkoutDate.setHours(13, 0, 0, 0);
    }

    if (checkoutInput) {
        checkoutInput.value = formatearFechaLocal(checkoutDate);
    }

    // DISPARAR RECÁLCULO DE SALDO PARA QUE EL BOTÓN SE ACTUALICE
    if (typeof actualizarResumenPagos === 'function') {
        actualizarResumenPagos();
    }
}

function formatearFechaLocal(date) {
    var year = date.getFullYear();
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    var hh = String(date.getHours()).padStart(2, '0');
    var mm = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hh}:${mm}`;
}

/**
 * REGISTRAR CLIENTE - LIMPIO SIN DEPURADORES
 */
function registrarCliente(event) {
    if (event) event.preventDefault();

    var formCliente = document.getElementById('formCliente');
    if (!formCliente) return;

    // VALIDACIÓN MANUAL DE CAMPOS OBLIGATORIOS
    var inputs = formCliente.querySelectorAll('input[required], select[required]');
    var esValido = true;
    inputs.forEach(input => {
        if (!input.value || input.value.trim() === "") {
            esValido = false;
        }
    });

    if (!esValido) {
        formCliente.classList.add('was-validated');
        return;
    }

    // RECOLECTAR DATOS
    var data = {
        ci: document.getElementById('ci1').value,
        nombres: document.getElementById('nombres1').value,
        apellido1: document.getElementById('apellido1').value,
        apellido2: document.getElementById('apellido2').value,
        fecha_nacimiento: document.getElementById('fecha_nacimiento1').value,
        lugar_nacimiento: document.getElementById('lugar_nacimiento1').value,
        estado_civil: document.getElementById('estado_civil1').value,
        profesion: document.getElementById('profesion1').value,
        paisID: document.getElementById('paisID1').value
    };

    var ajax = nuevoAjax();
    var url = 'registrar_cliente.php';
    var params = new URLSearchParams(data);

    ajax.open('POST', url, true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    ajax.onreadystatechange = function () {
        if (ajax.readyState == 4) {
            if (ajax.status == 200) {
                var res = ajax.responseText;

                if (res.startsWith('success:')) {
                    var nuevoID = res.split(':')[1];

                    // Guardar profesión en historial (datalist)
                    guardarProfesionEnMemoria(data.profesion);

                    // Pequeño feedback visual antes de cerrar
                    var btn = event.target || document.querySelector('#formCliente button');
                    var originalText = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i> ¡REGISTRADO!';
                    btn.classList.replace('btn-primary', 'btn-success');

                    setTimeout(() => {
                        // Cerrar formulario y limpiar
                        document.getElementById('formularioRegistro').style.display = 'none';

                        // Limpiar campos
                        var campos = formCliente.querySelectorAll('input, select');
                        campos.forEach(c => {
                            if (c.type !== 'hidden') c.value = '';
                        });
                        formCliente.classList.remove('was-validated');
                        if (document.getElementById('edadDisplay')) document.getElementById('edadDisplay').style.display = 'none';
                        if (document.getElementById('mensajeAlertas')) document.getElementById('mensajeAlertas').style.display = 'none';

                        // Restaurar botón
                        btn.innerHTML = originalText;
                        btn.classList.replace('btn-success', 'btn-primary');

                        // Seleccionar al nuevo cliente en el buscador de hospedaje
                        if (typeof seleccionarCliente === 'function') {
                            seleccionarCliente(nuevoID);
                        }
                    }, 1000);

                } else if (res === 'error_ci_duplicado') {
                    alert('Este C.I. ya está registrado para este país.');
                } else {
                    alert('Error en el registro: ' + res);
                }
            } else {
                alert('Error de conexión con el servidor (HTTP ' + ajax.status + ').');
            }
        }
    };
    ajax.send(params.toString());
}

/**
 * LÓGICA DE MEMORIA PARA PROFESIONES (Sugerencias dinámicas)
 */
function guardarProfesionEnMemoria(profesion) {
    if (!profesion || profesion.trim().length === 0) return;

    var lista = JSON.parse(localStorage.getItem('historialProfesiones')) || [];
    profesion = profesion.toUpperCase().trim();

    if (!lista.includes(profesion)) {
        lista.push(profesion);
        localStorage.setItem('historialProfesiones', JSON.stringify(lista));
        cargarProfesionesDeMemoria();
    }
}

function cargarProfesionesDeMemoria() {
    var lista = JSON.parse(localStorage.getItem('historialProfesiones')) || [];
    var datalist = document.getElementById('listaProfesiones');
    if (datalist) {
        datalist.innerHTML = "";
        lista.forEach(p => {
            var option = document.createElement('option');
            option.value = p;
            datalist.appendChild(option);
        });
    }
}

document.addEventListener('DOMContentLoaded', cargarProfesionesDeMemoria);

/**
 * VALIDACIÓN DE EDAD Y ALERTAS DE MENORES
 */
function validarEdad() {
    const fechaNacimientoInput = document.getElementById('fecha_nacimiento1');
    const edadDisplay = document.getElementById('edadDisplay');
    const mensajeAlertas = document.getElementById('mensajeAlertas');

    if (!fechaNacimientoInput.value) {
        edadDisplay.style.display = 'none';
        mensajeAlertas.style.display = 'none';
        return;
    }

    const fechaNacimiento = new Date(fechaNacimientoInput.value);
    const hoy = new Date();
    fechaNacimiento.setMinutes(fechaNacimiento.getMinutes() + fechaNacimiento.getTimezoneOffset());

    if (fechaNacimiento > hoy) {
        mensajeAlertas.className = 'alert alert-danger py-1 small mt-2';
        mensajeAlertas.innerHTML = '<i class="fas fa-times-circle"></i> Fecha inválida.';
        mensajeAlertas.style.display = 'block';
        edadDisplay.style.display = 'none';
        fechaNacimientoInput.value = '';
        return;
    }

    let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
    const m = hoy.getMonth() - fechaNacimiento.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
        edad--;
    }

    edadDisplay.innerText = 'Edad: ' + edad + ' años';
    if (edadDisplay) edadDisplay.style.display = 'block';

    if (edad < 18) {
        mensajeAlertas.className = 'alert alert-danger py-2 px-3 small mt-0 mb-3 fw-bold';
        mensajeAlertas.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> CLIENTE MENOR DE EDAD (${edad} años)`;
        mensajeAlertas.style.display = 'block';
    } else {
        mensajeAlertas.style.display = 'none';
    }
}
