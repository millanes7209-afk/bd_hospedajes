// Funcionalidad de búsqueda para hospedajes
document.addEventListener("DOMContentLoaded", function () {
    const buscarNombres = document.getElementById("buscarNombres");
    const buscarApellidos = document.getElementById("buscarApellidos");
    const buscarCI = document.getElementById("buscarCI");
    const tbody = document.querySelector("table tbody");
    const thead = document.querySelector("table thead");

    function highlight(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, "gi");
        return text.replace(regex, "<span class='highlight'>$1</span>");
    }

    function updateTableHeader(isSeparated) {
        if (isSeparated) {
            thead.innerHTML = `
                <tr>
                    <th scope="col">Usuario</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Apellido</th>
                    <th scope="col">Fecha Ingreso</th>
                    <th scope="col">Fecha Salida</th>
                    <th scope="col">Habitación</th>
                    <th scope="col">Monto</th>
                    <th scope="col">Forma Pago</th>
                    <th scope="col"><img src='../../imagenes/modificar.gif' alt='Modificar'></th>
                    <th scope="col"><img src='../../imagenes/borrar.jpeg' alt='Eliminar'></th>
                </tr>
            `;
        } else {
            thead.innerHTML = `
                <tr>
                    <th scope="col">Usuario</th>
                    <th scope="col">Clientes</th>
                    <th scope="col">Fecha Ingreso</th>
                    <th scope="col">Fecha Salida</th>
                    <th scope="col">Habitación</th>
                    <th scope="col">Monto</th>
                    <th scope="col">Forma Pago</th>
                    <th scope="col">Estado</th>
                    <th scope="col"><img src='../../imagenes/modificar.gif' alt='Modificar'></th>
                    <th scope="col"><img src='../../imagenes/borrar.jpeg' alt='Eliminar'></th>
                </tr>
            `;
        }
    }

    function filtrarDatos() {
        const nombre = buscarNombres.value.trim().toLowerCase();
        const apellido = buscarApellidos.value.trim().toLowerCase();
        const ci = buscarCI.value.trim();

        fetch("buscar_hospedajes.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ nombre, apellido, ci }),
        })
            .then((response) => response.json())
            .then((data) => {
                const isSeparated = nombre || apellido || ci;

                updateTableHeader(isSeparated);

                // El filtrado ya viene hecho desde el servidor (SQL) para mayor rendimiento
                const resultadosFiltrados = data;

                tbody.innerHTML = "";

                resultadosFiltrados.forEach((fila) => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${highlight(fila.usuario)}</td>
                            ${isSeparated ? `
                                <td>${highlight(fila.nombres, nombre)}</td>
                                <td>${highlight(fila.apellidos, apellido)}</td>
                            ` : `<td>${highlight(fila.clientes)}</td>`}
                            <td>${fila.checkin}</td>
                            <td>${fila.checkout}</td>
                            <td>${fila.habitacion_numero}</td>
                            <td>${fila.monto}</td>
                            <td>${fila.estado}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-4">
                                    <form name="formModif${fila.hospedajeID}" method="post" action="hospedaje_modificar.php" style="display:inline;">
                                        <input type="hidden" name="hospedajeID" value="${fila.hospedajeID}">
                                        <input type="hidden" name="auth" value="hospedajes.php">
                                        <button type="submit" style="background:none; border:none; color:#0d6efd; padding:0; cursor:pointer;" title="Modificar">
                                            <i class="fas fa-pencil-alt fa-lg"></i>
                                        </button>
                                    </form>
                                    <button class="btn-accion-limpia" style="background:none; border:none; color:#dc3545; padding:0; cursor:pointer;" onclick="eliminarHospedaje(${fila.hospedajeID})" title="Eliminar">
                                        <i class="fas fa-trash-alt fa-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            });
    }

    // Función debounce: espera 500ms después de escribir para ejecutar
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Búsqueda automática con debounce
    const busquedaAutomatica = debounce(filtrarDatos, 500);

    // Búsqueda inmediata al presionar el botón Buscar
    document.getElementById("botonBuscar").addEventListener("click", filtrarDatos);

    // Búsqueda al presionar ENTER en cualquier campo
    [buscarNombres, buscarApellidos, buscarCI].forEach(input => {
        input.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                filtrarDatos();
            }
        });
    });
});
