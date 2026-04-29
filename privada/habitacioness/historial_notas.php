<?php
// =========================================================================
// HISTORIAL DE BITÁCORA DE TURNO (Solo Lectura)
// =========================================================================
require_once("../../conexion.php");
require_once("../seguridad/seguridad.php");

$empresaID = $_SESSION['empresaID'] ?? 0;

// Obtener todas las notificaciones de la empresa, ordenadas de las más nuevas a las más viejas
$sql = "SELECT n.mensaje, n.completado, n._fec_insercion, n._fec_modificacion, 
               u1.usuario AS creador, 
               u2.usuario AS completador
        FROM notificaciones n
        INNER JOIN usuarios u1 ON n.usuarioID = u1.usuarioID
        LEFT JOIN usuarios u2 ON n.usuario_completado = u2.usuarioID
        WHERE n.empresaID = ? AND n._estado <> 'X'
        ORDER BY n._fec_insercion DESC";

$historial = $db->obtenerTodo($sql, [$empresaID]);

// Cargar la librería del menú para mantener el diseño y cabecera
require_once("../../libreria_menu.php");
?>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">HISTORIAL DE BITÁCORA DE TURNO</h3>
    </div>

    <div class="card-body">
        <p class="text-muted small">
            Este registro es de solo lectura. Muestra todas las notas creadas en esta empresa para auditoría.
        </p>

        <div class="table-responsive mt-3">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>

                        <th scope="col">Tarea</th>
                        <th scope="col">Creado por</th>
                        <th scope="col">Fecha Creación</th>
                        <th scope="col">Completado por</th>
                        <th scope="col">Fecha Fin</th>
                        <th scope="col">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historial)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">
                                No hay registros en la bitácora aún.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historial as $nota): ?>
                            <tr>

                                <td><?php echo htmlspecialchars($nota['mensaje']); ?></td>
                                <td><?php echo htmlspecialchars($nota['creador']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($nota['_fec_insercion'])); ?></td>
                                <td>
                                    <?php if ($nota['completador']): ?>
                                        <?php echo htmlspecialchars($nota['completador']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($nota['completado'] == 1 && $nota['_fec_modificacion']): ?>
                                        <?php echo date('d/m/Y H:i', strtotime($nota['_fec_modificacion'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($nota['completado'] == 1): ?>
                                        <span style="color: #198754; font-weight: bold;"><i class="fas fa-check-circle"></i>
                                            Listo</span>
                                    <?php else: ?>
                                        <span style="color: #ffc107; font-weight: bold; text-shadow: 0 0 1px #000;"><i
                                                class="fas fa-clock"></i> Pend.</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>

</html>