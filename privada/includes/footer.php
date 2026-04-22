<?php
/**
 * Footer común para todas las páginas
 */
?>

    </main>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0 text-muted">
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - Versión <?php echo APP_VERSION; ?>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="../../js/main.js"></script>
    
    <!-- CSRF Token para formularios -->
    <script>
        // Configuración global de AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Funciones globales
        function showAlert(message, type = 'info') {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger', 
                'warning': 'alert-warning',
                'info': 'alert-info'
            };
            
            const alertHtml = `
                <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insertar al principio del contenido principal
            $('main').prepend(alertHtml);
            
            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                $('.alert').first().fadeOut(500, function() {
                    $(this).remove();
                });
            }, 5000);
        }
        
        // Confirmación para acciones destructivas
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
        
        // Loading indicator
        function showLoading() {
            if (!$('#loadingModal').length) {
                $('body').append(`
                    <div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Procesando...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }
            new bootstrap.Modal(document.getElementById('loadingModal')).show();
        }
        
        function hideLoading() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
            if (modal) {
                modal.hide();
            }
        }
        
        // Auto-formato para campos de texto
        $(document).ready(function() {
            // Convertir a mayúsculas automáticamente
            $('input[data-uppercase="true"]').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });
            
            // Solo letras
            $('input[data-alpha="true"]').on('input', function() {
                $(this).val($(this).val().replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, ''));
            });
            
            // Solo números
            $('input[data-numeric="true"]').on('input', function() {
                $(this).val($(this).val().replace(/[^0-9]/g, ''));
            });
            
            // Formato de moneda
            $('input[data-currency="true"]').on('blur', function() {
                const value = parseFloat($(this).val().replace(/[^0-9.-]/g, ''));
                if (!isNaN(value)) {
                    $(this).val(value.toFixed(2));
                }
            });
        });
    </script>
</body>
</html>
?>
