// Función para manejar el clic en una habitación disponible
function handleHabitacionClick(estado, numero, tipo, precio, habitacionID) {
    var modal = new bootstrap.Modal(document.getElementById('menu-opciones'));
    const modalFooter = document.getElementById('modal-footer');
    modalFooter.innerHTML = ''; // Limpiar botones existentes

    if (estado === 'DISPONIBLE') {
        // Mostrar opciones de "Hospedar" y "Reservar"
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-primary" onclick="redirectToOcupar('hospedar', '${numero}', '${tipo}', '${precio}', '${habitacionID}')">Hospedar</button>
            <button type="button" class="btn btn-info" onclick="redirectToOcupar('reservar', '${numero}', '${tipo}', '${precio}', '${habitacionID}')">Reservar</button>
        `;
        modal.show();
    }
}

// Función para redirigir a ocupar.php con la acción seleccionada
function redirectToOcupar(action, numero, tipo, precio, habitacionID) {
    window.location.href = 'acciones/ocupar.php?numero=' + numero + 
                           '&tipo=' + tipo + 
                           '&precio=' + precio + 
                           '&habitacionID=' + habitacionID + 
                           '&accion=' + action;
}
