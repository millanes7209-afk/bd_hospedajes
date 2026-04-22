function verificarNotificaciones() {
    fetch('verificar_notificaciones.php')
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                // Mostrar la notificación
                data.forEach(notificacion => {
                    mostrarNotificacion(notificacion);
                });
            }
        })
        .catch(error => console.error('Error al verificar notificaciones:', error));
}
// Función para mostrar el modal con el mensajefunction mostrarNotificacion(notificacion) {
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
            modal.querySelector(".modal-body").appendChild(notificacionIDElemento);
        }
        notificacionIDElemento.textContent = 'Notificación ID: ' + notificacion.notificacionID;
    
        // Mostrar el modal
        modal.classList.add("show");
    
        // Asignar las acciones de los botones
        document.getElementById("facturaEmitidaBtn").onclick = function() {
            console.log("Factura Emitida para Notificación ID:", notificacion.notificacionID);
            marcarNotificacionAtendida(notificacion.notificacionID);  // Llamamos a la función pasando el notificacionID
            cerrarModal();
        };
        
        document.getElementById("posponerBtn").onclick = function() {
            console.log("Posponer para Notificación ID:", notificacion.notificacionID);
            posponerNotificacion(notificacion.notificacionID);  // Llamamos a la función pasando el notificacionID
            cerrarModal();
        };
    
        // Cerrar el modal cuando se hace clic en la "X" (opcional)
        document.querySelector(".cerrar").onclick = function() {
            // Nada aquí porque el modal no debe cerrarse con la "X"
        };
    
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
    modal.classList.remove("show");
}
function marcarNotificacionAtendida(notificacionID) {
    console.log("Enviando Notificación ID para marcar como atendida:", notificacionID);  // Verificar el valor
    
    fetch('marcar_notificacion_atendida.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ notificacionID: notificacionID })
    })
    .then(response => {
        return response.json(); // Asegurarnos de que estamos recibiendo JSON
    })
    .then(data => {
        console.log('Respuesta del servidor:', data); // Ver la respuesta completa
        if (data.success) {
            console.log('Notificación marcada como atendida:', data);
        } else {
            console.error('Error al marcar la notificación como atendida:', data.message);
        }
    })
    .catch(error => {
        console.error('Error al marcar notificación como atendida:', error);
        // Aquí podemos agregar más detalles sobre el error
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Notificación pospuesta:', data);
        } else {
            console.error('Error al posponer la notificación:', data.message);
        }
    })
    .catch(error => console.error('Error al posponer notificación:', error));
}


// Verificar notificaciones cada 1 minuto
setInterval(verificarNotificaciones, 60000);