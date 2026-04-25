<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Seguridad
if (!isset($_SESSION['sesion_id_usuario']) || !in_array($_SESSION['sesion_rol'], ['ADMINISTRADOR', 'PROPIETARIO'])) {
    header("Location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Estadístico</title>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            border: 0;
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .stat-header {
            background-color: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 15px 20px;
            font-weight: 600;
            color: #495057;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .table-stat th { background-color: #f8f9fa; font-size: 0.85rem; color: #6c757d; }
        .table-stat td { font-size: 0.9rem; vertical-align: middle; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid mt-4 mb-5">
    
    <!-- Filtros Superiores -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-line me-2 text-primary"></i> PANEL DE ESTADÍSTICAS</h4>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="setRango('7dias')">Últimos 7 días</button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="setRango('mes')">Mes Actual</button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="setRango('anio')">Año Actual</button>
                        
                        <input type="date" id="fecha_inicio" class="form-control form-control-sm ms-3" style="width: 140px;">
                        <span class="text-muted">al</span>
                        <input type="date" id="fecha_fin" class="form-control form-control-sm" style="width: 140px;">
                        <button class="btn btn-sm btn-primary" onclick="cargarEstadisticas()"><i class="fas fa-search"></i> Filtrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Primera Fila: Curva Financiera -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card stat-card">
                <div class="stat-header"><i class="fas fa-wallet me-2"></i> Flujo de Ingresos vs Gastos</div>
                <div class="card-body">
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="chartFinanzas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda Fila: Donas y Barras -->
    <div class="row">
        <!-- Métodos de Pago -->
        <div class="col-lg-4">
            <div class="card stat-card h-100">
                <div class="stat-header"><i class="fas fa-money-check-alt me-2"></i> Métodos de Pago</div>
                <div class="card-body d-flex justify-content-center">
                    <div class="chart-container" style="height: 250px; width: 100%;">
                        <canvas id="chartPagos"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Categorías de Movimiento -->
        <div class="col-lg-4">
            <div class="card stat-card h-100">
                <div class="stat-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-tags me-2 text-danger"></i> Categorías</div>
                    <ul class="nav nav-pills card-header-pills" style="font-size: 0.85rem;" id="categoria-tabs">
                        <li class="nav-item">
                            <a class="nav-link active py-1 px-2" id="btn-cat-in" href="#" onclick="renderCategorias('in'); return false;">Ingresos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-2" id="btn-cat-out" href="#" onclick="renderCategorias('out'); return false;">Egresos</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <div class="chart-container" style="height: 250px; width: 100%;">
                        <canvas id="chartCategorias"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tipos de Habitación -->
        <div class="col-lg-4">
            <div class="card stat-card h-100">
                <div class="stat-header"><i class="fas fa-bed me-2 text-info"></i> Tipos de Habitación</div>
                <div class="card-body">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="chartHabitaciones"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tercera Fila: Procedencia y Tablas de Inteligencia -->
    <div class="row">
        <!-- Procedencia (Torta con Pestañas) -->
        <div class="col-lg-4">
            <div class="card stat-card h-100">
                <div class="stat-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-globe-americas me-2 text-success"></i> Procedencia</div>
                    <ul class="nav nav-pills card-header-pills" style="font-size: 0.85rem;" id="procedencia-tabs">
                        <li class="nav-item">
                            <a class="nav-link active py-1 px-2" id="btn-depto" href="#" onclick="renderProcedencia('depto'); return false;">Dptos.</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-2" id="btn-pais" href="#" onclick="renderProcedencia('pais'); return false;">País</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <div class="chart-container" style="height: 280px; width: 100%;">
                        <canvas id="chartProcedencia"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clientes Frecuentes -->
        <div class="col-lg-4">
            <div class="card stat-card h-100">
                <div class="stat-header"><i class="fas fa-star me-2 text-warning"></i> Top Clientes Frecuentes</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-stat mb-0" id="tablaClientes">
                            <thead><tr><th>CLIENTE</th><th class="text-center">VISITAS</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productividad Recepcionistas -->
        <div class="col-lg-4">
            <div class="card stat-card h-100">
                <div class="stat-header"><i class="fas fa-user-tie me-2 text-primary"></i> Productividad Personal</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-stat mb-0" id="tablaPersonal">
                            <thead><tr><th>RECEPCIONISTA</th><th class="text-center">OP.</th><th class="text-end">RECAUDADO</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Variables Globales de Gráficos
let chartFinanzas, chartPagos, chartHabitaciones, chartCategorias, chartProcedencia;
let datosGlobales = {}; // Para guardar la caché de datos de procedencia y usar las pestañas
const coloresGlobales = ['#007bff', '#28a745', '#ffc107', '#17a2b8', '#e83e8c', '#fd7e14', '#6610f2', '#6c757d', '#20c997', '#dc3545'];

// Inicialización de fechas
document.addEventListener("DOMContentLoaded", function() {
    setRango('7dias');
});

// Función para establecer rangos rápidos
function setRango(tipo) {
    const hoy = new Date();
    let inicio = new Date();
    
    if (tipo === '7dias') {
        inicio.setDate(hoy.getDate() - 7);
    } else if (tipo === 'mes') {
        inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    } else if (tipo === 'anio') {
        inicio = new Date(hoy.getFullYear(), 0, 1);
    }

    document.getElementById('fecha_inicio').value = inicio.toISOString().split('T')[0];
    document.getElementById('fecha_fin').value = hoy.toISOString().split('T')[0];
    cargarEstadisticas();
}

// Cargar y renderizar
function cargarEstadisticas() {
    const f_ini = document.getElementById('fecha_inicio').value;
    const f_fin = document.getElementById('fecha_fin').value;

    fetch(`ajax_estadisticas.php?inicio=${f_ini}&fin=${f_fin}`)
        .then(response => response.json())
        .then(data => {
            if(data.error) {
                alert("Error: " + data.error);
                return;
            }
            datosGlobales = data; // Guardamos cache global

            renderizarGraficoFinanzas(data.finanzas);
            renderizarGraficoPagos(data.metodos_pago);
            renderCategorias('in'); // Carga inicial Ingresos
            renderizarGraficoHabitaciones(data.tipos_habitacion);
            renderProcedencia('depto'); // Carga inicial por Dptos
            renderizarTablas(data);
        })
        .catch(err => console.error("Error al cargar datos:", err));
}

// 1. Gráfico de Líneas: Finanzas
function renderizarGraficoFinanzas(datos) {
    const ctx = document.getElementById('chartFinanzas').getContext('2d');
    if (chartFinanzas) chartFinanzas.destroy();

    let fechas = [...new Set(datos.map(d => d.fecha))];
    let ingresos = fechas.map(f => {
        let row = datos.find(d => d.fecha === f && d.tipo === 'INGRESO');
        return row ? parseFloat(row.total) : 0;
    });
    let egresos = fechas.map(f => {
        let row = datos.find(d => d.fecha === f && d.tipo === 'EGRESO');
        return row ? parseFloat(row.total) : 0;
    });

    chartFinanzas = new Chart(ctx, {
        type: 'line',
        data: {
            labels: fechas,
            datasets: [
                { label: 'Ingresos (Bs)', data: ingresos, borderColor: '#28a745', backgroundColor: 'rgba(40, 167, 69, 0.1)', borderWidth: 2, fill: true, tension: 0.3 },
                { label: 'Egresos (Bs)', data: egresos, borderColor: '#dc3545', backgroundColor: 'rgba(220, 53, 69, 0.1)', borderWidth: 2, fill: true, tension: 0.3 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
    });
}

// 2. Gráfico Donut: Pagos
function renderizarGraficoPagos(datos) {
    const ctx = document.getElementById('chartPagos').getContext('2d');
    if (chartPagos) chartPagos.destroy();

    const labels = datos.map(d => d.metodo);
    const valores = datos.map(d => parseFloat(d.total));
    const totalSuma = valores.reduce((a, b) => a + b, 0);

    chartPagos = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: valores, backgroundColor: coloresGlobales, borderWidth: 1 }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let percentage = totalSuma > 0 ? ((value * 100) / totalSuma).toFixed(1) : 0;
                            return `${label}: Bs. ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// 3. Gráfico Donut: Categorias
function renderCategorias(tipo) {
    document.getElementById('btn-cat-in').classList.remove('active');
    document.getElementById('btn-cat-out').classList.remove('active');
    document.getElementById('btn-cat-' + tipo).classList.add('active');

    const ctx = document.getElementById('chartCategorias').getContext('2d');
    if (chartCategorias) chartCategorias.destroy();

    let datos = tipo === 'in' ? datosGlobales.categorias_ingreso : datosGlobales.categorias_egreso;
    if (!datos) datos = [];

    const labels = datos.map(d => d.categoria);
    const valores = datos.map(d => parseFloat(d.total));
    const totalSuma = valores.reduce((a, b) => a + b, 0);
    
    let paleta = tipo === 'in' ? coloresGlobales.slice().reverse() : ['#dc3545', '#e83e8c', '#fd7e14', '#f8d7da', '#c82333'];

    chartCategorias = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{ data: valores, backgroundColor: paleta, borderWidth: 1 }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let percentage = totalSuma > 0 ? ((value * 100) / totalSuma).toFixed(1) : 0;
                            return `${label}: Bs. ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// 4. Gráfico de Barras: Habitaciones
function renderizarGraficoHabitaciones(datos) {
    const ctx = document.getElementById('chartHabitaciones').getContext('2d');
    if (chartHabitaciones) chartHabitaciones.destroy();

    const labels = datos.map(d => d.tipo);
    const valores = datos.map(d => parseInt(d.cantidad));

    chartHabitaciones = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Hospedajes',
                data: valores,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
}

// 5. Gráfico de Torta con Tabs: Procedencia
function renderProcedencia(tipo) {
    // Controlar pestañas visualmente
    document.getElementById('btn-depto').classList.remove('active');
    document.getElementById('btn-pais').classList.remove('active');
    document.getElementById('btn-' + tipo).classList.add('active');

    const ctx = document.getElementById('chartProcedencia').getContext('2d');
    if (chartProcedencia) chartProcedencia.destroy();

    let datos = tipo === 'depto' ? datosGlobales.procedencia_depto : datosGlobales.procedencia_pais;
    if (!datos) datos = [];

    const labels = datos.map(d => d.origen);
    const valores = datos.map(d => parseInt(d.cantidad));
    const totalSuma = valores.reduce((a, b) => a + b, 0);

    chartProcedencia = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{ data: valores, backgroundColor: coloresGlobales, borderWidth: 1 }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { 
                legend: { position: 'right' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let percentage = totalSuma > 0 ? ((value * 100) / totalSuma).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            } 
        }
    });
}

// 6. Renderizar Tablas
function renderizarTablas(data) {
    // Clientes Frecuentes
    const tbodyClientes = document.querySelector('#tablaClientes tbody');
    tbodyClientes.innerHTML = '';
    if(data.clientes_frecuentes && data.clientes_frecuentes.length === 0) tbodyClientes.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Sin datos</td></tr>';
    else if(data.clientes_frecuentes) {
        data.clientes_frecuentes.forEach(c => {
            tbodyClientes.innerHTML += `<tr><td><i class="fas fa-user-circle text-muted me-2"></i> ${c.cliente}</td><td class="text-center fw-bold text-dark">${c.visitas}</td></tr>`;
        });
    }

    // Personal
    const tbodyPersonal = document.querySelector('#tablaPersonal tbody');
    tbodyPersonal.innerHTML = '';
    if(data.personal && data.personal.length === 0) tbodyPersonal.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>';
    else if(data.personal) {
        data.personal.forEach(p => {
            tbodyPersonal.innerHTML += `<tr>
                <td><i class="fas fa-user-tie text-muted me-2"></i> ${p.recepcionista}</td>
                <td class="text-center">${p.operaciones}</td>
                <td class="text-end fw-bold text-success">Bs. ${parseFloat(p.dinero_ingresado).toFixed(2)}</td>
            </tr>`;
        });
    }
}
</script>
</body>
</html>
