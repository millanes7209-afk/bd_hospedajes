function verificarNotificaciones() {
    fetch('verificar_notificaciones.php')
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                data.forEach(notificacion => {
                    mostrarNotificacion(notificacion);
                });
            }
        })
        .catch(error => console.error('Error al verificar notificaciones:', error));
}

function mostrarNotificacion(notificacion) {
    var modal = document.getElementById("miModal");
    var mensaje = document.getElementById("modalMensaje");
    
    mensaje.textContent = notificacion.mensaje;

    var notificacionIDElemento = document.getElementById("notificacionID");
    if (!notificacionIDElemento) {
        notificacionIDElemento = document.createElement('p');
        notificacionIDElemento.id = 'notificacionID';
        notificacionIDElemento.style.fontSize = "0.8rem";
        notificacionIDElemento.style.color = "#888";
        modal.querySelector(".modal-body-notif").appendChild(notificacionIDElemento);
    }
    notificacionIDElemento.textContent = 'ID: ' + notificacion.notificacionID;

    modal.style.display = "block";

    document.getElementById("facturaEmitidaBtn").onclick = function() {
        console.log("Factura Emitida para Notificación ID:", notificacion.notificacionID);
        marcarNotificacionAtendida(notificacion.notificacionID);
        cerrarModal();
    };
    
    document.getElementById("posponerBtn").onclick = function() {
        console.log("Posponer para Notificación ID:", notificacion.notificacionID);
        posponerNotificacion(notificacion.notificacionID);
        cerrarModal();
    };
}

function cerrarModal() {
    var modal = document.getElementById("miModal");
    modal.style.display = "none";
}

function marcarNotificacionAtendida(notificacionID) {
    fetch('marcar_notificacion_atendida.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ notificacionID: notificacionID })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Notificación atendida');
        } else {
            console.error('Error:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function posponerNotificacion(notificacionID) {
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
            console.log('Notificación pospuesta');
        } else {
            console.error('Error:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

setInterval(verificarNotificaciones, 60000);
document.addEventListener('DOMContentLoaded', verificarNotificaciones);
