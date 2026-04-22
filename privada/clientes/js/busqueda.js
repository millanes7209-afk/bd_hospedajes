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

    // Eliminar la condición y función updateTableHeader
    // Aquí dejamos la cabecera de la tabla estática sin importar la búsqueda
    thead.innerHTML = `
        <tr>
            <th scope="col">C.I.</th>
            <th scope="col">Nombre</th>
            <th scope="col">Apellidos</th>
            <th scope="col">Edad</th>
            <th scope="col">Procedencia</th>
            <th scope="col"><img src='../../imagenes/modificar.gif' alt='Modificar'></th>
            <th scope="col"><img src='../../imagenes/borrar.jpeg' alt='Eliminar'></th>
        </tr>
    `;

    function filtrarDatos() {
        const nombre = buscarNombres.value.trim().toLowerCase();
        const apellido = buscarApellidos.value.trim().toLowerCase();
        const ci = buscarCI.value.trim();

        fetch("buscar_clientes.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ nombre, apellido, ci }),
        })
            .then((response) => response.json())
            .then((data) => {
                // Filtrar resultados antes de agregarlos
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

                // Limpiar la tabla actual
                tbody.innerHTML = "";
// Función para calcular la edad a partir de la fecha de nacimiento
function calcularEdad(fechaNacimiento) {
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);  // Convertir la fecha de nacimiento en objeto Date

    let edad = hoy.getFullYear() - nacimiento.getFullYear(); // Calcular la diferencia de años

    // Verificar si ya cumplió años este año
    const mesActual = hoy.getMonth();
    const diaActual = hoy.getDate();
    const mesNacimiento = nacimiento.getMonth();
    const diaNacimiento = nacimiento.getDate();

    // Si el cumpleaños aún no ha ocurrido este año, restamos un año
    if (mesActual < mesNacimiento || (mesActual === mesNacimiento && diaActual < diaNacimiento)) {
        edad--;
    }

    return edad;
}
resultadosFiltrados.forEach((fila) => {
    // Calcular la edad con la fecha de nacimiento
    const edad = calcularEdad(fila.fecha_nacimiento);

    tbody.innerHTML += `
        <tr>
            <td>${highlight(fila.ci, ci)}</td>
            <td>${highlight(fila.nombres, nombre)}</td>
            <td>${highlight(fila.apellidos, apellido)}</td>
            <td>${edad}</td>  <!-- Mostrar la edad calculada -->
            <td>${fila.lugar_nacimiento}</td>
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

    // Agregar eventos a los campos de búsqueda
    buscarNombres.addEventListener("input", filtrarDatos);
    buscarApellidos.addEventListener("input", filtrarDatos);
    buscarCI.addEventListener("input", filtrarDatos);
});