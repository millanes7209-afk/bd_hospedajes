// Función para buscar cliente por CI
function buscarCliente() {
    const ci = document.getElementById('ci').value;
    if (ci.trim() === '') return;

    fetch('../ajax/buscar_cliente.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ci=' + encodeURIComponent(ci)
    })
    .then(response => response.json())
    .then(data => mostrarResultadosBusqueda(data))
    .catch(error => console.error('Error al buscar cliente:', error));
}

// Función para mostrar resultados de búsqueda
// Función para mostrar resultados de búsqueda
function mostrarResultadosBusqueda(clientes) {
    const resultadosDiv = document.getElementById('resultados-busqueda');
    const formNuevoClienteDiv = document.getElementById('form-nuevo-cliente'); // Div del formulario de nuevo cliente
    resultadosDiv.innerHTML = '';

    if (clientes.length > 0) {
        // Si se encuentran clientes, muestra los resultados y oculta el formulario de nuevo cliente
        clientes.forEach(cliente => {
            const div = document.createElement('div');
            div.innerHTML = `
                <input type="radio" name="cliente" onclick="seleccionarCliente(${cliente.clienteID})">
                ${cliente.nombre_completo} - CI: ${cliente.ci}
            `;
            resultadosDiv.appendChild(div);
        });
        formNuevoClienteDiv.style.display = 'none';
    } else {
        // Si no se encuentran clientes, muestra el mensaje y el formulario de nuevo cliente
        resultadosDiv.innerHTML = 'No se encontraron resultados. Puedes agregar uno nuevo.';
        formNuevoClienteDiv.style.display = 'block';
    }
}


// Función para seleccionar un cliente de los resultados
function seleccionarCliente(clienteID) {
    fetch('../ajax/seleccionar_cliente.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'clienteID=' + encodeURIComponent(clienteID)
    })
    .then(response => response.json())
    .then(data => mostrarClienteSeleccionado(data))
    .catch(error => console.error('Error al seleccionar cliente:', error));
}

// Función para mostrar información del cliente seleccionado
function mostrarClienteSeleccionado(cliente) {
    const clienteSeleccionadoDiv = document.getElementById('cliente-seleccionado');
    clienteSeleccionadoDiv.innerHTML = `
        <p><b>Nombre:</b> ${cliente.nombre_completo}</p>
        <p><b>CI:</b> ${cliente.ci}</p>
        <input type="hidden" name="clienteID" value="${cliente.clienteID}">
    `;
}

// Función para registrar un nuevo cliente
function registrarCliente() {
    const formData = new FormData(document.getElementById('form-nuevo-cliente'));

    fetch('../ajax/inserta_cliente.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cliente registrado correctamente.');
            buscarCliente(); // Actualiza la búsqueda para incluir el nuevo cliente
        } else {
            alert('Error al registrar el cliente.');
        }
    })
    .catch(error => console.error('Error al registrar cliente:', error));
}

// Validar CI único
function validarCI() {
    const ci = document.getElementById('ci').value;

    fetch('../ajax/valida_ci.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ci=' + encodeURIComponent(ci)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            alert('El CI ingresado ya existe.');
            document.getElementById('ci').classList.add('is-invalid');
        } else {
            document.getElementById('ci').classList.remove('is-invalid');
        }
    })
    .catch(error => console.error('Error al validar CI:', error));
}
