// ============================================================
// tienda.js — lógica completa de tienda.php
// ============================================================

let productos = [];
let carrito = [];
let modoCarrito = 'venta'; // 'venta' o 'compra'
let modalNuevoProducto, modalRetiro, toastNotificacion;

// ── Notificación Toast ──────────────────────────────────────
function mostrarNotificacion(titulo, mensaje, tipo = 'success') {
    const icono = tipo === 'success' ? 'fa-check-circle text-success' :
        tipo === 'error' ? 'fa-exclamation-circle text-danger' :
            'fa-info-circle text-info';

    document.getElementById('toastIcon').className = `fas ${icono} me-2`;
    document.getElementById('toastTitulo').textContent = titulo;
    document.getElementById('toastMensaje').textContent = mensaje;

    toastNotificacion = new bootstrap.Toast(document.getElementById('toastNotificacion'));
    toastNotificacion.show();
}

// ── Inicialización ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    modalNuevoProducto = new bootstrap.Modal(document.getElementById('modalNuevoProducto'));
    modalRetiro = new bootstrap.Modal(document.getElementById('modalRetiro'));
    cargarProductos();
    cargarCaja();
    agregarFilaPago();
});

// ── Productos ───────────────────────────────────────────────
function cargarProductos() {
    fetch('ajax_producto.php?accion=listar')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'ok') {
                productos = res.data;
                renderizarProductos();
            }
        });
}

function cargarCaja() {
    fetch('ajax_caja_tienda.php?accion=obtener')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'ok') {
                document.getElementById('cajaEfectivo').textContent = parseFloat(res.data.saldo_efectivo || 0).toFixed(2);
                document.getElementById('cajaQR').textContent = parseFloat(res.data.saldo_qr || 0).toFixed(2);
                document.getElementById('cajaTotal').textContent = parseFloat(res.data.saldo_total || 0).toFixed(2);
            }
        })
        .catch(() => { });
}

function renderizarProductos() {
    const enStock = productos.filter(p => p.stock > 0);
    const sinStock = productos.filter(p => p.stock <= 0);
    document.getElementById('productosGrid').innerHTML = enStock.map(p => crearTarjetaProducto(p)).join('');
    document.getElementById('sinStockGrid').innerHTML = sinStock.map(p => crearTarjetaProducto(p)).join('');
}

function crearTarjetaProducto(p) {
    const nombreCompleto = p.medida ? `${p.nombre} (${p.medida}L)` : p.nombre;
    const imagenHtml = p.imagen
        ? `<img src="../../${p.imagen}" alt="${nombreCompleto}" style="width:100%;height:100%;object-fit:cover;">`
        : `<i class="fas fa-box fa-4x text-muted"></i>`;

    return `
        <div class="col-6 col-md-3 col-lg-3">
            <div class="product-card">
                <div class="product-image position-relative">
                    ${imagenHtml}
                    <span class="price-badge">Bs. ${parseFloat(p.precio_venta || 0).toFixed(2)}</span>
                </div>
                <div class="p-3 flex-1 d-flex flex-column">
                    <h6 class="mb-1 fw-bold text-truncate" title="${nombreCompleto}" style="font-size:0.9rem;">${nombreCompleto}</h6>
                    <div class="stock-badge mb-3">
                        <div class="label">Stock</div>
                        <div class="value">${p.stock}</div>
                    </div>
                    <div class="mt-auto d-flex gap-2">
                        <button class="btn btn-sm btn-venta py-1 flex-fill" onclick="agregarAlCarritoVenta(${p.productoID})">
                            <i class="fas fa-shopping-cart"></i> Vender
                        </button>
                        <button class="btn btn-sm btn-stock py-1 flex-fill" onclick="agregarAlCarritoCompra(${p.productoID})">
                            <i class="fas fa-box"></i> Comprar
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
}

// ── Modo Carrito ────────────────────────────────────────────
function cambiarModo(modo) {
    modoCarrito = modo;
    carrito = [];

    document.getElementById('btnModoVenta').className = modo === 'venta' ? 'btn btn-sm btn-venta' : 'btn btn-sm btn-outline-secondary';
    document.getElementById('btnModoCompra').className = modo === 'compra' ? 'btn btn-sm btn-stock' : 'btn btn-sm btn-outline-secondary';
    document.getElementById('btnConfirmar').innerHTML = modo === 'venta'
        ? '<i class="fas fa-check-circle"></i> CONFIRMAR VENTA'
        : '<i class="fas fa-check-circle"></i> CONFIRMAR COMPRA';

    const mpSection = document.getElementById('metodoPagoSection');
    if (mpSection) mpSection.style.display = modo === 'venta' ? '' : 'none';

    renderizarCarrito();
}

// ── Filas de Pago ───────────────────────────────────────────
function agregarFilaPago() {
    const contenedor = document.getElementById('contenedorPagos');
    if (!contenedor) return;
    const index = contenedor.children.length;
    const selectTemplate = document.getElementById('templateFormaPago').innerHTML;

    const div = document.createElement('div');
    div.className = 'row g-1 mb-1 align-items-center fila-pago';
    div.innerHTML = `
        <div class="col-7">
            <select class="form-control form-control-sm select-fp" name="pagos[${index}][formapagoID]"
                onchange="actualizarResumenPagosTienda()">
                ${selectTemplate}
            </select>
        </div>
        <div class="col-4">
            <input type="number" class="form-control form-control-sm input-monto-pago"
                placeholder="Monto" step="0.5" min="0"
                oninput="actualizarResumenPagosTienda()">
        </div>
        <div class="col-1 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger border-0 p-0"
                onclick="eliminarFilaPago(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>`;
    contenedor.appendChild(div);

    if (contenedor.children.length === 1) {
        const total = parseFloat(document.getElementById('totalCarrito').value) || 0;
        if (total > 0) div.querySelector('.input-monto-pago').value = total.toFixed(2);
    }
    actualizarResumenPagosTienda();
}

function eliminarFilaPago(btn) {
    const contenedor = document.getElementById('contenedorPagos');
    if (contenedor.children.length > 1) {
        btn.closest('.fila-pago').remove();
        actualizarResumenPagosTienda();
    }
}

function actualizarResumenPagosTienda() {
    const totalVenta = parseFloat(document.getElementById('totalCarrito').value) || 0;
    let totalPagado = 0;
    document.querySelectorAll('.input-monto-pago').forEach(inp => { totalPagado += parseFloat(inp.value) || 0; });

    const saldo = totalVenta - totalPagado;
    const displayPagado = document.getElementById('displayTotalPagado');
    const displaySaldo = document.getElementById('displaySaldoPendiente');

    if (displayPagado) displayPagado.innerText = totalPagado.toFixed(2);
    if (displaySaldo) {
        displaySaldo.innerText = saldo.toFixed(2);
        const contenedorSaldo = document.querySelector('.saldo-valor');
        if (contenedorSaldo) {
            contenedorSaldo.className = 'fw-bold saldo-valor ' + (Math.abs(saldo) < 0.01 ? 'text-success' : 'text-danger');
        }
    }
}

// ── Carrito ─────────────────────────────────────────────────
function agregarAlCarritoVenta(productoID) {
    if (modoCarrito !== 'venta') cambiarModo('venta');
    agregarAlCarrito(productoID, 'venta');
}

function agregarAlCarritoCompra(productoID) {
    if (modoCarrito !== 'compra') cambiarModo('compra');
    agregarAlCarrito(productoID, 'compra');
}

function agregarAlCarrito(productoID, tipo) {
    const producto = productos.find(p => p.productoID == productoID);
    if (!producto) return;

    if (tipo === 'venta') {
        const stockDisponible = parseInt(producto.stock) || 0;
        if (stockDisponible <= 0) {
            mostrarNotificacion('Error', `Stock agotado para '${producto.nombre}'.`, 'error');
            return;
        }
        const existe = carrito.find(item => item.productoID == productoID);
        if (existe && existe.cantidad >= stockDisponible) {
            mostrarNotificacion('Advertencia', `Solo hay ${stockDisponible} unidad(es) disponible(s) de '${producto.nombre}'.`, 'warning');
            return;
        }
    }

    const existe = carrito.find(item => item.productoID == productoID);
    if (existe) {
        existe.cantidad++;
        existe.subtotal = existe.cantidad * existe.precio;
    } else {
        const nombreCompleto = producto.medida ? `${producto.nombre} (${producto.medida}L)` : producto.nombre;
        carrito.push({
            productoID,
            nombre: nombreCompleto,
            cantidad: 1,
            precio: tipo === 'venta' ? parseFloat(producto.precio_venta || 0) : parseFloat(producto.precio_costo || 0),
            subtotal: tipo === 'venta' ? parseFloat(producto.precio_venta || 0) : parseFloat(producto.precio_costo || 0)
        });
    }
    renderizarCarrito();
}

function renderizarCarrito() {
    const container = document.getElementById('carritoItems');
    if (carrito.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="text-muted">El carrito está vacío</p>
                <small class="text-muted">Haz clic en "${modoCarrito === 'venta' ? 'Vender' : 'Comprar'}" para agregar productos</small>
            </div>`;
        document.getElementById('totalCarrito').value = '0.00';
        if (modoCarrito === 'venta') {
            const cp = document.getElementById('contenedorPagos');
            if (cp && cp.children.length === 0) { agregarFilaPago(); }
        }
        return;
    }

    container.innerHTML = carrito.map((item, index) => {
        const producto = productos.find(p => p.productoID == item.productoID);
        const stockDisponible = producto ? (parseInt(producto.stock) || 0) : 999;
        const alLimite = modoCarrito === 'venta' && item.cantidad >= stockDisponible;

        return `
        <div class="cart-item">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="mb-1 fw-bold small">${item.nombre}</h6>
                    <p class="mb-0 text-muted small">Bs. <span id="precio-unitario-${index}">${item.precio.toFixed(2)}</span> c/u</p>
                </div>
                <button class="btn btn-sm text-danger" onclick="eliminarDelCarrito(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                ${modoCarrito === 'venta' ? `
                <div class="quantity-control">
                    <button onclick="cambiarCantidad(${index}, -1)">-</button>
                    <input type="text" value="${item.cantidad}" readonly>
                    <button onclick="cambiarCantidad(${index}, 1)" ${alLimite ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''}>+</button>
                </div>` : `
                <div class="quantity-control">
                    <input type="number" value="${item.cantidad}" min="1" tabindex="${index * 2 + 1}"
                        oninput="cambiarCantidadCompra(${index}, this.value)" style="width:60px;"
                        onkeydown="manejarTab(event, ${index})" id="cantidad-${index}">
                </div>`}
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Subtotal:</span>
                    <input type="number" class="price-input" value="${item.subtotal.toFixed(2)}"
                        oninput="actualizarSubtotal(${index}, this.value)" step="0.1" tabindex="${index * 2 + 2}"
                        onkeydown="manejarTab(event, ${index})" id="subtotal-${index}">
                </div>
            </div>
            ${alLimite ? `
            <div class="text-danger fw-bold mt-1" style="font-size: 0.75rem;">
                <i class="fas fa-exclamation-circle me-1"></i>Máximo stock alcanzado (${stockDisponible} dispon.)
            </div>` : ''}
        </div>`;
    }).join('');

    const total = carrito.reduce((sum, item) => sum + item.subtotal, 0);
    document.getElementById('totalCarrito').value = total.toFixed(2);

    if (modoCarrito === 'venta') {
        const cp = document.getElementById('contenedorPagos');
        if (cp && cp.children.length === 0) { agregarFilaPago(); }
    }
}

function actualizarTotalManual(nuevoTotal) {
    actualizarResumenPagosTienda();
}

function cambiarCantidad(index, delta) {
    if (modoCarrito === 'venta' && delta > 0) {
        const item = carrito[index];
        const producto = productos.find(p => p.productoID == item.productoID);
        if (producto) {
            const stockDisponible = parseInt(producto.stock) || 0;
            if (item.cantidad + delta > stockDisponible) {
                renderizarCarrito();
                return;
            }
        }
    }

    carrito[index].cantidad += delta;
    if (carrito[index].cantidad <= 0) {
        carrito.splice(index, 1);
    } else {
        carrito[index].subtotal = carrito[index].cantidad * carrito[index].precio;
    }
    renderizarCarrito();
}

function cambiarCantidadCompra(index, valor) {
    const nuevaCantidad = parseInt(valor) || 0;
    if (nuevaCantidad <= 0) {
        carrito[index].cantidad = 0;
        carrito[index].subtotal = 0;
    } else {
        carrito[index].cantidad = nuevaCantidad;
        carrito[index].subtotal = carrito[index].cantidad * carrito[index].precio;
    }
    const subtotalInput = document.getElementById(`subtotal-${index}`);
    if (subtotalInput) subtotalInput.value = carrito[index].subtotal.toFixed(2);

    const precioSpan = document.getElementById(`precio-unitario-${index}`);
    if (precioSpan) precioSpan.textContent = carrito[index].precio.toFixed(2);

    const total = carrito.reduce((sum, item) => sum + item.subtotal, 0);
    document.getElementById('totalCarrito').value = total.toFixed(2);
    actualizarResumenPagosTienda();
}

function manejarTab(event, index) {
    if (event.key === 'Tab') return true;
}

function actualizarSubtotal(index, valor) {
    const nuevoValor = parseFloat(valor);
    if (!isNaN(nuevoValor) && nuevoValor >= 0) {
        carrito[index].subtotal = nuevoValor;
        if (modoCarrito === 'compra') {
            carrito[index].precio = carrito[index].cantidad > 0 ? (nuevoValor / carrito[index].cantidad) : 0;
            const precioSpan = document.getElementById(`precio-unitario-${index}`);
            if (precioSpan) precioSpan.textContent = carrito[index].precio.toFixed(2);
        }
    }

    const total = carrito.reduce((sum, item) => sum + item.subtotal, 0);
    document.getElementById('totalCarrito').value = total.toFixed(2);
    actualizarResumenPagosTienda();
}

function eliminarDelCarrito(index) {
    carrito.splice(index, 1);
    renderizarCarrito();
}

// ── Confirmar Operación ─────────────────────────────────────
function confirmarOperacion() {
    if (modoCarrito === 'venta') confirmarVenta();
    else confirmarCompra();
}

function confirmarVenta() {
    if (carrito.length === 0) { mostrarNotificacion('Error', 'El carrito está vacío', 'error'); return; }

    const totalVenta = parseFloat(document.getElementById('totalCarrito').value) || 0;
    const alerta = document.getElementById('alertaPagoTienda');
    if (alerta) alerta.style.display = 'none';

    const filas = document.querySelectorAll('.fila-pago');
    if (filas.length === 0) {
        if (alerta) { alerta.innerHTML = '<i class="fas fa-exclamation-circle"></i> Debe añadir al menos una forma de pago.'; alerta.style.display = 'block'; }
        return;
    }

    let formas_pago = [];
    let pagosIncompletos = false;
    filas.forEach(fila => {
        const formapagoID = fila.querySelector('select').value;
        const monto = parseFloat(fila.querySelector('input').value) || 0;
        if (!formapagoID || monto <= 0) pagosIncompletos = true;
        else formas_pago.push({ formapagoID: parseInt(formapagoID), monto });
    });

    if (pagosIncompletos) {
        if (alerta) { alerta.innerHTML = '<i class="fas fa-exclamation-circle"></i> Complete los datos de pago (forma y monto).'; alerta.style.display = 'block'; }
        return;
    }

    const totalPagado = formas_pago.reduce((s, fp) => s + fp.monto, 0);
    const saldo = totalVenta - totalPagado;
    if (Math.abs(saldo) > 0.05) {
        if (alerta) {
            if (saldo > 0) {
                alerta.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> Faltan <b>Bs. ${saldo.toFixed(2)}</b> por pagar (Pagado: Bs. ${totalPagado.toFixed(2)} de Bs. ${totalVenta.toFixed(2)}).`;
            } else {
                alerta.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> Hay un exceso de <b>Bs. ${Math.abs(saldo).toFixed(2)}</b> en el pago (Pagado: Bs. ${totalPagado.toFixed(2)} de Bs. ${totalVenta.toFixed(2)}).`;
            }
            alerta.style.display = 'block';
        }
        return;
    }

    const datos = {
        accion: 'crear',
        monto_total: totalVenta,
        items: carrito.map(item => ({ productoID: item.productoID, cantidad: item.cantidad, precio: item.precio })),
        formas_pago
    };

    fetch('ajax_venta.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'ok') {
                mostrarNotificacion('Éxito', 'Venta registrada exitosamente', 'success');
                carrito = [];
                const inputTotal = document.getElementById('totalCarrito');
                if (inputTotal) inputTotal.value = '0.00';
                const cp = document.getElementById('contenedorPagos');
                if (cp) cp.innerHTML = '';
                renderizarCarrito();
                cargarProductos();
                cargarCaja();
            } else {
                mostrarNotificacion('Error', res.mensaje || 'Error al registrar la venta', 'error');
            }
        })
        .catch(() => mostrarNotificacion('Error', 'Error de conexión al servidor', 'error'));
}

function confirmarCompra() {
    if (carrito.length === 0) { mostrarNotificacion('Error', 'El carrito de compra está vacío', 'error'); return; }

    const totalCompra = parseFloat(document.getElementById('totalCarrito').value) || 0;
    const datos = {
        accion: 'crear',
        monto_total: totalCompra,
        items: carrito.map(item => ({ productoID: item.productoID, cantidad: item.cantidad, costo: item.precio }))
    };

    fetch('ajax_compra.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'ok') {
                mostrarNotificacion('Éxito', 'Compra registrada exitosamente', 'success');
                carrito = [];
                renderizarCarrito();
                cargarProductos();
                cargarCaja();
            } else {
                mostrarNotificacion('Error', res.mensaje || 'Error al registrar la compra', 'error');
            }
        });
}

// ── Retiros ─────────────────────────────────────────────────
function mostrarModalRetiro() {
    const el = document.getElementById('modalRetiro');
    if (!el) {
        mostrarNotificacion('Error', 'No se encontró el modal de retiro', 'error');
        return;
    }
    try {
        if (!modalRetiro) {
            modalRetiro = new bootstrap.Modal(el);
        }
        modalRetiro.show();

        setTimeout(() => {
            const inputEf = document.getElementById('retiroEfectivo');
            if (inputEf) {
                inputEf.focus();
                inputEf.select();
            }
        }, 300);
    } catch (e) {
        mostrarNotificacion('Error', 'Error al abrir modal: ' + e.message, 'error');
    }
}

function registrarRetiro() {
    const efectivo = parseFloat(document.getElementById('retiroEfectivo').value) || 0;
    const qr = parseFloat(document.getElementById('retiroQR').value) || 0;
    const motivo = document.getElementById('retiroMotivo').value.trim();
    const total = efectivo + qr;

    if (total <= 0) {
        mostrarNotificacion('Atención', 'El monto a retirar debe ser mayor a Bs. 0.00', 'warning');
        return;
    }

    if (!motivo) {
        mostrarNotificacion('Atención', 'Debe especificar el motivo del retiro', 'warning');
        const inputMotivo = document.getElementById('retiroMotivo');
        if (inputMotivo) inputMotivo.focus();
        return;
    }

    fetch('ajax_retiro.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion: 'registrar', monto_efectivo: efectivo, monto_qr: qr, motivo })
    })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'ok') {
                mostrarNotificacion('Éxito', `Retiro registrado por Bs. ${total.toFixed(2)}`, 'success');
                if (modalRetiro) modalRetiro.hide();
                document.getElementById('retiroEfectivo').value = '0';
                document.getElementById('retiroQR').value = '0';
                document.getElementById('retiroMotivo').value = '';
                cargarCaja();
            } else {
                mostrarNotificacion('Error', res.mensaje || 'Error al registrar retiro', 'error');
            }
        })
        .catch(err => {
            mostrarNotificacion('Error', 'Error de conexión al servidor', 'error');
        });
}

// ── Nuevo Producto ──────────────────────────────────────────
function mostrarModalNuevoProducto() { modalNuevoProducto.show(); }

function crearProducto() {
    const nombre = document.getElementById('nuevoNombre').value;
    const medida = document.getElementById('nuevaMedida').value;
    const precioVenta = parseFloat(document.getElementById('nuevoPrecio').value) || 0;
    const stock = parseInt(document.getElementById('nuevoStock').value) || 0;
    const imagenInput = document.getElementById('nuevaImagen');

    if (!nombre) { mostrarNotificacion('Error', 'El nombre es obligatorio', 'error'); return; }
    if (!imagenInput.files.length) { mostrarNotificacion('Error', 'La imagen es obligatoria', 'error'); return; }

    const formData = new FormData();
    formData.append('accion', 'crear');
    formData.append('nombre', nombre);
    formData.append('medida', medida);
    formData.append('precio_venta', precioVenta);
    formData.append('stock', stock);
    formData.append('imagen', imagenInput.files[0]);

    fetch('ajax_producto.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'ok') {
                mostrarNotificacion('Éxito', 'Producto creado exitosamente', 'success');
                modalNuevoProducto.hide();
                document.getElementById('nuevoNombre').value = '';
                document.getElementById('nuevoPrecio').value = '0';
                document.getElementById('nuevoStock').value = '0';
                document.getElementById('nuevaImagen').value = '';
                cargarProductos();
            } else {
                mostrarNotificacion('Error', res.mensaje || 'Error al crear el producto', 'error');
            }
        });
}
