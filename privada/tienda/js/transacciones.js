let modalDetalle;

document.addEventListener('DOMContentLoaded', () => {
    modalDetalle = new bootstrap.Modal(document.getElementById('modalDetalle'));
    cargarTransacciones();
});

function cargarTransacciones() {
    const tipo = document.getElementById('filtroTipo').value;
    const desde = document.getElementById('filtroDesde').value;
    const hasta = document.getElementById('filtroHasta').value;

    fetch(`ajax_transacciones.php?accion=listar&tipo=${tipo}&desde=${desde}&hasta=${hasta}`)
        .then(r => r.json())
        .then(res => {
            const tbody = document.getElementById('listaTransacciones');
            if (res.status === 'ok' && res.data && res.data.length > 0) {
                tbody.innerHTML = res.data.map(t => {
                    let badge = 'bg-secondary';
                    if (t.tipo === 'VENTA') badge = 'bg-success';
                    else if (t.tipo === 'COMPRA') badge = 'bg-primary';
                    else if (t.tipo === 'RETIRO') badge = 'bg-danger';

                    const numProd = t.tipo === 'RETIRO' ? '-' : (t.num_productos || (t.detalles ? t.detalles.length : 1) + ' prod(s)');
                    const fechaStr = t.fecha || '';
                    const horaStr = t.hora || '';
                    const pagoStr = t.forma_pago_nombres || 'EFECTIVO';
                    const userStr = t.usuario_nombre || 'N/A';

                    return `
                    <tr>
                        <td class="ps-4"><span class="badge ${badge}">${t.tipo}</span></td>
                        <td>${numProd}</td>
                        <td class="fw-bold text-danger">Bs. ${parseFloat(t.monto_total).toFixed(2)}</td>
                        <td><span class="badge bg-secondary"><i class="fas fa-wallet"></i> ${pagoStr}</span></td>
                        <td><div>${fechaStr}</div><small class="text-muted">${horaStr}</small></td>
                        <td>${userStr}</td>
                        <td class="text-center pe-4">
                            <button class="btn btn-sm btn-outline-info" onclick='verDetalle(${JSON.stringify(t).replace(/'/g, "\\'")})'>
                                <i class="fas fa-eye"></i> Detalle
                            </button>
                        </td>
                    </tr>
                `;
                }).join('');
            } else if (res.status === 'ok' && (!res.data || res.data.length === 0)) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted p-5 bg-white"><i class="fas fa-info-circle fa-2x mb-2 text-warning"></i><br>No se encontraron transacciones en este rango</td></tr>';
            } else {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger p-5 bg-white"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Error en servidor: ${res.mensaje || 'Respuesta desconocida'}</td></tr>`;
            }
        })
        .catch(e => {
            document.getElementById('listaTransacciones').innerHTML = `<tr><td colspan="7" class="text-center text-danger p-5"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Error de comunicación: ${e.message}</td></tr>`;
        });
}

function verDetalle(t) {
    document.getElementById('detTransID').textContent = t.transacciontiendaID || t.movimientoID;
    document.getElementById('detTotal').textContent = parseFloat(t.monto_total).toFixed(2);

    const tbody = document.getElementById('detItems');
    if (t.detalles && Array.isArray(t.detalles) && t.detalles.length > 0) {
        tbody.innerHTML = t.detalles.map(i => {
            const nombreCompleto = i.medida ? `${i.nombre} (${i.medida}L)` : i.nombre;
            return `
            <tr>
                <td>${nombreCompleto}</td>
                <td class="text-center">${i.cantidad}</td>
                <td class="text-end">${parseFloat(i.precio_unitario).toFixed(2)}</td>
                <td class="text-end">${parseFloat(i.subtotal).toFixed(2)}</td>
            </tr>`;
        }).join('');
    } else if (t.items_detalle && Array.isArray(t.items_detalle)) {
        tbody.innerHTML = t.items_detalle.map(i => `
        <tr>
            <td>${i.producto_nombre}</td>
            <td class="text-center">${i.cantidad}</td>
            <td class="text-end">${parseFloat(i.precio_unitario).toFixed(2)}</td>
            <td class="text-end">${parseFloat(i.subtotal).toFixed(2)}</td>
        </tr>
     `).join('');
    } else {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No hay desglose disponible.</td></tr>`;
    }

    modalDetalle.show();
}
