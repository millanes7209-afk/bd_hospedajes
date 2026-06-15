// Funcionalidad de búsqueda para hospedajes
document.addEventListener("DOMContentLoaded", function () {
    const buscarNombres = document.getElementById("buscarNombres");
    const buscarApellidos = document.getElementById("buscarApellidos");
    const buscarCI = document.getElementById("buscarCI");
    const botonBuscar = document.getElementById("botonBuscar");
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
                    <th scope="col">Estado</th>
                    <th colspan="2" class="text-center">Acciones</th>
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
                    <th scope="col">Estado</th>
                    <th colspan="2" class="text-center">Acciones</th>
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
            body: new URLSearchParams({ nombre, apellido, ci, auth: 'hospedajes.php' }),
        })
            .then((response) => response.json())
            .then((data) => {
                const resultados = data || [];
                const isSeparated = nombre || apellido || ci;
                updateTableHeader(isSeparated);

                tbody.innerHTML = "";

                if (resultados.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="11" class="text-center text-muted">No se encontraron resultados</td></tr>`;
                    return;
                }

                resultados.forEach((fila) => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${highlight(fila.usuario)}</td>
                            ${isSeparated ? `
                                <td>${highlight(fila.nombres, nombre)}</td>
                                <td>${highlight(fila.apellidos, apellido)}</td>
                            ` : `<td>${highlight(fila.clientes)}</td>`}
                            <td>${fila.checkin}</td>
                            <td>${fila.checkout}</td>
                            <td class="text-center">${fila.habitacion_numero}</td>
                            <td class="text-center">Bs. ${fila.monto}</td>
                            <td class="text-center">${fila.estado}</td>
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
            })
            .catch(error => {
                console.error("Error en la búsqueda:", error);
            });
    }

    if (botonBuscar) {
        botonBuscar.addEventListener("click", filtrarDatos);
    }

    const inputs = [buscarNombres, buscarApellidos, buscarCI];
    inputs.forEach(input => {
        if (input) {
            input.addEventListener("keypress", function (e) {
                if (e.key === "Enter") {
                    filtrarDatos();
                }
            });
        }
    });

});
