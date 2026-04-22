<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
$db->debug = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ingresos y Egresos</title>
    <script type="text/javascript">
        function validar() {
            let tipoReporte = document.formu.tipoReporte.value;
            let frecuencia = document.formu.frecuencia.value;
            let usuarioID = document.formu.usuarioID ? document.formu.usuarioID.value : '';
            if (tipoReporte === '' || frecuencia === '') {
                alert('Seleccione el tipo de reporte y la frecuencia');
                return;
            }
            let url = 'finanzas1.php?tipoReporte=' + tipoReporte + '&frecuencia=' + frecuencia;
            if(frecuencia === 'intervalo'){
                let fechaInicio=document.formu.fechaInicio.value;
                let fechaFin = document.formu.fechaFin.value;
                if(fechaInicio===''||fechaFin===''){
                    alert('Seleccione ambas fechas de inicio y fin');
                    return;
                }
                if (fechaInicio > fechaFin) {
                    alert('La fecha de inicio no puede ser mayor que la fecha de fin');
                    return;
                }
                url += '&fechaInicio=' + fechaInicio + '&fechaFin=' + fechaFin;
            }else if (frecuencia === 'mensual') {
                let mes = document.formu.mes.value;
                let anio = document.formu.anio.value;
                if (mes === '' || anio === '') {
                    alert('Seleccione mes y año');
                    return;
                }
                url += '&mes=' + mes + '&anio=' + anio;
            }
            if (usuarioID !== '') {
                url += '&usuarioID=' + usuarioID;
            }
            // Abrir ventana del reporte
            window.open(url, 'reporte', 'width=1000, height=700, left=100, top=100, scrollbars=yes, menubars=no, statusbar=NO, status=NO, resizable=YES, location=NO');
        }

        function mostrarFechas() {
            let frecuencia = document.getElementById("frecuencia").value;
            let fechasIntervalo = document.getElementById("fechasIntervalo");
            let fechaMensual = document.getElementById("fechaMensual");

            if (frecuencia === "intervalo") {
                fechasIntervalo.style.display = "block";
                fechaMensual.style.display = "none";
            } else if (frecuencia === "mensual") {
                fechaMensual.style.display = "block";
                fechasIntervalo.style.display = "none";
            } else {
                fechasIntervalo.style.display = "none";
                fechaMensual.style.display = "none";
            }
        }

    </script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Generar Reporte de Ingresos y Egresos</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <form method="GET" name="formu">
                            <div class="mb-3">
                                <label for="tipoReporte" class="form-label">Tipo de Reporte</label>
                                <select name="tipoReporte" id="tipoReporte" class="form-control" required>
                                    <option value="" disabled selected>Seleccione tipo de reporte</option>
                                    <option value="ingreso">Ingresos</option>
                                    <option value="egreso">Egresos</option>
                                    <option value="total">Total</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="frecuencia" class="form-label">Frecuencia</label>
                                <select name="frecuencia" id="frecuencia" class="form-control" onchange="mostrarFechas();" required>
                                    <option value="" disabled selected>----Seleccione---</option>
                                    <option value="mensual">Mensual</option>
                                    <option value="anual">Anual</option>
                                    <option value="intervalo">Intervalo</option>
                                </select>
                                <div id="fechaMensual" style="display: none;">
                                <div class="mb-3">
                                    <label for="mes" class="form-label">Mes</label>
                                    <select name="mes" id="mes" class="form-control">
                                        <option value="" selected>Seleccione un mes</option>
                                        <?php
                                        for ($m=1; $m<=12; $m++) {
                                            $mes_nombre = strftime('%B', mktime(0, 0, 0, $m, 1, 2000));
                                            echo "<option value='$m'>$mes_nombre</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="anio" class="form-label">Año</label>
                                    <input type="number" name="anio" id="anio" class="form-control" value="<?php echo date('Y'); ?>">
                                </div>
                            </div>

                                <div id="fechasIntervalo" style="display: none;">
                                    <div class="mb-3">
                                        <label for="fechaInicio" class="form-label">Fecha de Inicio</label>
                                        <input type="date" name="fechaInicio" id="fechaInicio" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="fechaFin" class="form-label">Fecha de Fin</label>
                                        <input type="date" name="fechaFin" id="fechaFin" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                            <?php if ($_SESSION["sesion_rol"] == "PROPIETARIO" || $_SESSION["sesion_rol"] == "ADMINISTRADOR"): ?>
                                <div class="mb-3">
                                    <label for="usuarioID" class="form-label">Seleccionar Usuario</label>
                                    <select name="usuarioID" id="usuarioID" class="form-control">
                                        <option value="" selected>Todos los Usuarios</option>
                                        <?php
                                        // Consulta para obtener los usuarios activos
                                        $sql_usuarios = $db->Prepare("SELECT id_usuario, usuario FROM usuarios WHERE _estado = 'A' AND id_usuario>1" );
                                        $rs_usuarios = $db->GetAll($sql_usuarios);

                                        foreach ($rs_usuarios as $usuario) {
                                            echo "<option value='{$usuario['id_usuario']}'>{$usuario['usuario']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            </div>
                            <div class="text-center">
                                <button type="button" class="btn btn-dark w-100" onclick="validar();">
                                    Generar Reporte
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
