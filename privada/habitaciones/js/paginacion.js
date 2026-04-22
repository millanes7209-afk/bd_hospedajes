function cargarRegistros(pagina = 1) {
    const cantidad = document.getElementById('cantidad').value;
    fetch(`habitaciones.php?cantidad=${cantidad}&pagina=${pagina}`)
        .then(response => response.text())
        .then(data => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');
            const nuevaTabla = doc.querySelector('.table-responsive').innerHTML;
            const nuevaPaginacion = doc.querySelector('.paginacion').innerHTML;

            document.querySelector('.table-responsive').innerHTML = nuevaTabla;
            document.querySelector('.paginacion').innerHTML = nuevaPaginacion;

            asignarEventosEliminacion();
        })
        .catch(error => console.error('Error:', error));
}

function cambiarPagina(pagina) {
    cargarRegistros(pagina);
}

    function asignarEventosEliminacion() {
        document.querySelectorAll('.eliminar-habitacion').forEach(boton => {
            boton.addEventListener('click', function(event) {
                event.preventDefault();
                const habitacionID = this.getAttribute('data-habitacionid');
                const numero = this.getAttribute('data-numero');
                document.getElementById('habitacionNumero').textContent = numero;
                showModal();

                document.getElementById('confirmDeleteBtn').onclick = function() {
                    fetch('habitacion_eliminar.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ habitacionID: habitacionID })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const mensajeDiv = document.getElementById('mensaje');
                        mensajeDiv.innerHTML = `<div class="alert alert-${data.tipo}">${data.mensaje}</div>`;
                        if (data.tipo === 'success') {
                            setTimeout(() => { cargarRegistros(); }, 2000);
                        }
                        hideModal();
                    })
                    .catch(error => console.error('Error:', error));
                };
            });
        });
    }

    // Asignar eventos al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        asignarEventosEliminacion();
    });
