async function handleResponse(response) {
    const contentType = response.headers.get("content-type");
    if (!response.ok) {
        const text = await response.text();
        console.error('Error del servidor (HTTP ' + response.status + '):', text);
        throw new Error('El servidor devolvió un error HTTP ' + response.status);
    }
    if (!contentType || !contentType.includes("application/json")) {
        const text = await response.text();
        console.error('El servidor no devolvió JSON. Respuesta recibida:', text);
        throw new Error('El servidor devolvió HTML o texto en lugar de JSON. Revisa la consola para más detalles.');
    }
    return response.json();
}

function verificarNotificaciones() {
    fetch('verificar_notificaciones.php')
        .then(handleResponse)
        .then(data => {
            if (data && data.length > 0) {
                // Mostrar la notificación
                data.forEach(notificacion => {
                    mostrarNotificacion(notificacion);
                });
            }
        })
        .catch(error => {
            console.error('Error al verificar notificaciones:', error.message);
        });
}

// Función para mostrar el modal con el mensaje
function mostrarNotificacion(notificacion) {
    // Obtener el modal y su contenido
    var modal = document.getElementById("miModal");
    var mensaje = document.getElementById("modalMensaje");
    
    // Mostrar el mensaje de la notificación
    mensaje.textContent = notificacion.mensaje;

    // Mostrar el notificacionID en el modal
    var notificacionIDElemento = document.getElementById("notificacionID");
    if (!notificacionIDElemento) {
        notificacionIDElemento = document.createElement('p');
        notificacionIDElemento.id = 'notificacionID';
        var modalBody = modal.querySelector(".modal-body");
        if (modalBody) modalBody.appendChild(notificacionIDElemento);
    }
    if (notificacionIDElemento) {
        notificacionIDElemento.textContent = 'Notificación ID: ' + notificacion.notificacionID;
    }

    // Mostrar el modal
    modal.classList.add("show");

    // Asignar las acciones de los botones
    const facturaBtn = document.getElementById("facturaEmitidaBtn");
    if (facturaBtn) {
        facturaBtn.onclick = function() {
            console.log("Factura Emitida para Notificación ID:", notificacion.notificacionID);
            marcarNotificacionAtendida(notificacion.notificacionID);
            cerrarModal();
        };
    }
    
    const posponerBtn = document.getElementById("posponerBtn");
    if (posponerBtn) {
        posponerBtn.onclick = function() {
            console.log("Posponer para Notificación ID:", notificacion.notificacionID);
            posponerNotificacion(notificacion.notificacionID);
            cerrarModal();
        };
    }

    // Cerrar el modal cuando se hace clic en la "X" (opcional)
    const cerrarBtn = document.querySelector(".cerrar");
    if (cerrarBtn) {
        cerrarBtn.onclick = function() {
            // Nada aquí porque el modal no debe cerrarse con la "X"
        };
    }

    // Eliminar la lógica para cerrar el modal al hacer clic fuera de la ventana
    window.onclick = function(event) {
        if (event.target == modal) {
            // Nada aquí porque no queremos que se cierre al hacer clic fuera
        }
    };
}

// Función para cerrar el modal
function cerrarModal() {
    var modal = document.getElementById("miModal");
    if (modal) modal.classList.remove("show");
}

function marcarNotificacionAtendida(notificacionID) {
    console.log("Enviando Notificación ID para marcar como atendida:", notificacionID);
    
    fetch('marcar_notificacion_atendida.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ notificacionID: notificacionID })
    })
    .then(handleResponse)
    .then(data => {
        console.log('Respuesta del servidor:', data);
        if (data.success) {
            console.log('Notificación marcada como atendida:', data);
        } else {
            console.error('Error al marcar la notificación como atendida:', data.message);
        }
    })
    .catch(error => {
        console.error('Error al marcar notificación como atendida:', error.message);
    });
}

function posponerNotificacion(notificacionID) {
    console.log("Notificación ID para posponer:", notificacionID);
    fetch('posponer_notificacion.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ notificacionID: notificacionID })
    })
    .then(handleResponse)
    .then(data => {
        if (data.success) {
            console.log('Notificación pospuesta:', data);
        } else {
            console.error('Error al posponer la notificación:', data.message);
        }
    })
    .catch(error => {
        console.error('Error al posponer notificación:', error.message);
    });
}

// Verificar notificaciones cada 1 minuto
setInterval(verificarNotificaciones, 60000);