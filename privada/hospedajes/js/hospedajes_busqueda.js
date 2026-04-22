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
                const isSeparated = nombre || apellido;

                updateTableHeader(isSeparated);

                const resultadosFiltrados = data.filter((fila) => {
                    const nombreCoincide = nombre
                        ? fila.nombres.toLowerCase().includes(nombre)
                        : true;
                    const apellidoCoincide = apellido
                        ? fila.apellidos.toLowerCase().includes(apellido)
                        : true;
                    const ciCoincide = ci ? fila.ci.includes(ci) : true;

                    return nombreCoincide && apellidoCoincide && ciCoincide;
                });

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
                            <td>
                                <form name="formModif${fila.hospedajeID}" method="post" action="hospedaje_modificar.php" style="display:inline;">
                                    <input type="hidden" name="hospedajeID" value="${fila.hospedajeID}">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger btn-accion eliminar-hospedaje" data-hospedajeid="${fila.hospedajeID}" data-clientes="${fila.clientes}">Eliminar</button>
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

    // Agregar eventos a los campos de búsqueda
    buscarNombres.addEventListener("input", () => {
        if (buscarNombres.value.length >= 3 || buscarApellidos.value.length >= 3 || buscarCI.value.length >= 1) {
            busquedaAutomatica();
        }
    });

    buscarApellidos.addEventListener("input", () => {
        if (buscarNombres.value.length >= 3 || buscarApellidos.value.length >= 3 || buscarCI.value.length >= 1) {
            busquedaAutomatica();
        }
    });

    buscarCI.addEventListener("input", () => {
        if (buscarNombres.value.length >= 3 || buscarApellidos.value.length >= 3 || buscarCI.value.length >= 1) {
            busquedaAutomatica();
        }
    });

    // Búsqueda inmediata al presionar el botón Buscar
    document.getElementById("botonBuscar").addEventListener("click", filtrarDatos);
});
