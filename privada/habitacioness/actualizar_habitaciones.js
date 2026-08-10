function actualizarEstados() {
    fetch('get_habitaciones.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(habitacion => {
                const habitacionButton = document.getElementById('habitacion-' + habitacion.habitacionID);
                const infoDiv = document.getElementById('info-' + habitacion.habitacionID);

                let btnClass = 'btn-habitacion';
                switch (habitacion.estado) {
                    case 'DISPONIBLE':
                        btnClass += ' btn btn-success';
                        break;
                    case 'OCUPADA':
                        btnClass += ' btn btn-info';
                        break;
                    case 'DEUDA':
                        btnClass += ' btn btn-danger';
                        break;
                    case 'LIMPIEZA':
                        btnClass += ' btn btn-secondary';
                        break;
                    case 'RESERVADA':
                        btnClass += ' btn btn-warning';
                        break;
                    default:
                        btnClass += ' btn btn-secondary';
                }

                habitacionButton.className = btnClass;

                if (habitacion.estado === 'OCUPADA') {
                    loadInfo(habitacion.habitacionID, habitacion.estado);
                } else {
                    infoDiv.innerHTML = '';
                }
            });
        })
        .catch(error => console.error('Error al actualizar el estado de las habitaciones:', error));
}

setInterval(actualizarEstados, 10000); // Llamar cada 10 segundos
