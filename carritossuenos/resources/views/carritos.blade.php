{{--
 Vista pública para gestionar ventas del carrito.
 Utiliza Axios para llamar a los endpoints API y muestra:
  - Tabla de ventas de hoy
  - Totales por tipo de pago
  - Stock diario
  - Botones para aceptar stock y cerrar turno
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carritos - Registro de Ventas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Registro de Ventas - Carrito</h1>

    <!-- Formulario para registrar nueva venta -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Nueva Venta</h5>
            <form id="ventaForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Producto ID</label>
                    <input type="number" class="form-control" name="producto_id" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Promoción ID (opcional)</label>
                    <input type="number" class="form-control" name="promocion_id">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" class="form-control" name="cantidad" min="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Monto</label>
                    <input type="number" step="0.01" class="form-control" name="monto" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo Pago</label>
                    <select class="form-select" name="tipo_pago" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="qr">QR</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de ventas del día -->
    <h4>Ventas de Hoy</h4>
    <table class="table table-sm table-hover" id="ventasTable">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Monto</th>
            <th>Tipo Pago</th>
            <th>Hora</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Totales por tipo de pago -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="alert alert-success" id="totalEfectivo">Efectivo: 0</div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info" id="totalQr">QR: 0</div>
        </div>
    </div>

    <!-- Stock Diario -->
    <h4>Stock Diario</h4>
    <table class="table table-sm" id="stockTable">
        <thead class="table-light">
        <tr>
            <th>Producto</th>
            <th>Cantidad Enviada</th>
            <th>Aceptado</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
    <button class="btn btn-primary mb-4" id="abrirTurnoBtn"><i class="fa fa-bell"></i> Abrir Turno</button>
    <!-- Modal para despacho -->
    <div class="modal fade" id="despachoModal" tabindex="-1" aria-labelledby="despachoModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="despachoModalLabel">Despacho del Día</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="despachoContent">
            <!-- contenido dinámico -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="aceptarDespachoBtn">Aceptar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Cierre de turno -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Cerrar Turno</h5>
            <form id="cierreForm" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Total Efectivo</label>
                    <input type="number" step="0.01" class="form-control" name="total_efectivo" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Total QR</label>
                    <input type="number" step="0.01" class="form-control" name="total_qr" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Observaciones</label>
                    <input type="text" class="form-control" name="observaciones">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-danger">Cerrar Turno</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const api = axios.create({ baseURL: '/api/carrito' });

    // Cargar datos iniciales
    async function cargarDatos() {
        await cargarVentas();
        await cargarStock();
        await actualizarTotales();
    }

    // Ventas de hoy
    async function cargarVentas() {
        const response = await api.get('/ventas');
        const ventas = response.data.ventas || [];
        const tbody = document.querySelector('#ventasTable tbody');
        tbody.innerHTML = '';
        ventas.forEach(v => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${v.id}</td><td>${v.producto_id}</td><td>${v.cantidad}</td><td>${v.monto}</td><td>${v.tipo_pago}</td><td>${v.fecha_hora}</td>`;
            tbody.appendChild(tr);
        });
    }

    // Stock diario
    async function cargarStock() {
        const resp = await api.get('/stock');
        const stock = resp.data.stock || [];
        const tbody = document.querySelector('#stockTable tbody');
        tbody.innerHTML = '';
        stock.forEach(s => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${s.producto_nombre}</td><td>${s.cantidad_enviada}</td><td>${s.aceptado ? 'Sí' : 'No'}</td>`;
            tbody.appendChild(tr);
        });
    }

    async function actualizarTotales() {
        const resp = await api.get('/totales-diarios'); // endpoint a crear en controlador
        document.getElementById('totalEfectivo').textContent = 'Efectivo: ' + (resp.data.total_efectivo || 0);
        document.getElementById('totalQr').textContent = 'QR: ' + (resp.data.total_qr || 0);
    }

    // Registrar venta
    document.getElementById('ventaForm').addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const payload = Object.fromEntries(formData.entries());
        await api.post('/registrar-venta', payload);
        e.target.reset();
        await cargarDatos();
    });

    // (Removed accept stock button; using abrir turno flow)
    // Cerrar turno
    document.getElementById('cierreForm').addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const payload = Object.fromEntries(formData.entries());
        const resp = await api.post('/cerrar-turno', payload);
        alert('Turno cerrado');
        console.log(resp.data);
        e.target.reset();
    });

    // Abrir turno (consultar despacho)
    document.getElementById('abrirTurnoBtn').addEventListener('click', async () => {
        const resp = await api.get('/consultar-despacho');
        if (!resp.data.hay_despacho) {
            alert('No hay despacho para hoy');
            return;
        }
        // Build modal content
        const despacho = resp.data.despacho;
        let html = `<p>ID Despacho: ${despacho.id}</p>`;
        html += '<ul>';
        despacho.detalles.forEach(det => {
            html += `<li>${det.variante_nombre}: ${det.cantidad_enviada}</li>`;
        });
        html += '</ul>';
        document.getElementById('despachoContent').innerHTML = html;
        // Show modal (Bootstrap 5)
        const modal = new bootstrap.Modal(document.getElementById('despachoModal'));
        modal.show();
    });

    // Aceptar despacho (abrir turno)
    document.getElementById('aceptarDespachoBtn').addEventListener('click', async () => {
        const resp = await api.post('/aceptar-despacho');
        if (resp.data.hay_despacho) {
            alert('Turno abierto y stock actualizado');
            // Refresh data
            await cargarDatos();
        } else {
            alert('No se pudo abrir el turno');
        }
        // Hide modal
        const modalEl = document.getElementById('despachoModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    });

    // Inicializar
    cargarDatos();
</script>
</body>
</html>
