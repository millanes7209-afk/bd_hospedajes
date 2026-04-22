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
    <title>Reporte de Hospedajes</title>


    <script type="text/javascript">
        function validar() {
            let fecha1 = document.formu.date1.value;
            let fecha2 = document.formu.date2.value;
            if (fecha1 === '' || fecha2 === '' || fecha1 > fecha2) {
                alert('Las fechas son incorrectas');
                document.formu.date1.focus();
                return;
            }
            let ventanaCalendario = window.open(
                'partediario1.php?fecha1=' + fecha1 + '&fecha2=' + fecha2, 
                'calendario', 
                'width=600, height=550, left=100, top=100, scrollbars=yes, menubars=no, statusbar=NO, status=NO, resizable=YES, location=NO'
            );
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Reporte de Hospedajes</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <form method="post" name="formu">
                            <div class="mb-3">
                                <label for="date1" class="form-label">Fecha Inicio</label>
                                <input type="date" name="date1" class="form-control" id="date1">
                            </div>
                            <div class="mb-3">
                                <label for="date2" class="form-label">Fecha Fin</label>
                                <input type="date" name="date2" class="form-control" id="date2">
                            </div>
                            <div class="text-center">
                                <input type="hidden" name="accion" value="">
                                <button type="button" class="btn btn-dark w-100" onclick="validar();">
                                    Aceptar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Opcional para efectos adicionales) -->

</body>
</html>
