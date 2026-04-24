<script>
    // LÓGICA DE BAJA LABORAL
    function showModal() {
        document.getElementById('confirmModal').style.display = 'block';
        document.body.insertAdjacentHTML('beforeend', '<div class="modal-backdrop"></div>');
    }

    function hideModal() {
        document.getElementById('confirmModal').style.display = 'none';
        let b = document.querySelector('.modal-backdrop');
        if (b) b.remove();
    }

    document.querySelectorAll('.dar-baja').forEach(btn => {
        btn.addEventListener('click', function () {
            let id = this.dataset.empleadoid;
            let nombre = this.dataset.nombre;

            document.getElementById('EmpleadoNombre').textContent = nombre;
            document.getElementById('EmpleadoID').value = id;
            document.getElementById('bajaStatus').innerHTML = '';
            document.getElementById('bajaBody').style.display = 'block';
            document.getElementById('confirmDeleteBtn').style.display = 'inline-block';
            showModal();

            document.getElementById('confirmDeleteBtn').onclick = function () {
                const id = document.getElementById('EmpleadoID').value;
                fetch('ajax_dar_baja_laboral.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'empleadoID=' + id
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'SUCCESS') {
                        document.getElementById('bajaStatus').innerHTML = `<div class="alert alert-success">Baja procesada con éxito.</div>`;
                        document.getElementById('bajaBody').style.display = 'none';
                        document.getElementById('confirmDeleteBtn').style.display = 'none';
                        
                        // Inhabilitar botones de cierre
                        const btnCancel = document.getElementById('cancelModalBtn');
                        const btnClose = document.getElementById('closeModalBtn');
                        btnCancel.disabled = true;
                        btnCancel.style.opacity = '0.5';
                        btnCancel.style.cursor = 'not-allowed';
                        btnClose.disabled = true;
                        btnClose.style.opacity = '0.5';
                        btnClose.style.pointerEvents = 'none';

                        setTimeout(() => { location.reload(); }, 1500);
                    } else {
                        document.getElementById('bajaStatus').innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                    }
                });
            };
        });
    });

    document.getElementById('closeModalBtn').onclick = hideModal;
    document.getElementById('cancelModalBtn').onclick = hideModal;

    // LÓGICA DE RESET PASSWORD
    let empleadoIDReset = null;
    function hideResetModal() {
        document.getElementById('modalResetPass').style.display = 'none';
        let b = document.querySelector('.modal-backdrop');
        if (b) b.remove();
    }

    document.querySelectorAll('.btn-reset-pass').forEach(btn => {
        btn.addEventListener('click', function () {
            empleadoIDReset = this.dataset.id;
            document.getElementById('resetUsuarioNombre').textContent = this.dataset.user;
            document.getElementById('resetStatus').innerHTML = '';
            document.getElementById('resetBody').style.display = 'block';
            document.getElementById('confirmResetBtn').style.display = 'inline-block';
            document.getElementById('cancelResetBtn').style.display = 'inline-block';
            document.getElementById('modalResetPass').style.display = 'block';
            document.body.insertAdjacentHTML('beforeend', '<div class="modal-backdrop"></div>');
        });
    });

    document.getElementById('confirmResetBtn').addEventListener('click', function () {
        if (empleadoIDReset) {
            fetch('ajax_reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'empleadoID=' + empleadoIDReset
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'SUCCESS') {
                    document.getElementById('resetStatus').innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    document.getElementById('resetBody').style.display = 'none';
                    document.getElementById('confirmResetBtn').style.display = 'none';
                    document.getElementById('cancelResetBtn').style.display = 'none';
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    document.getElementById('resetStatus').innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                }
            });
        }
    });

    // LÓGICA DE MODIFICAR CONTRATO
    function hideContratoModal() {
        document.getElementById('modalContrato').style.display = 'none';
        let b = document.querySelector('.modal-backdrop');
        if (b) b.remove();
    }

    document.querySelectorAll('.btn-edit-contrato').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('contratoEmpleadoID').value = this.dataset.id;
            document.getElementById('contratoEmpleadoNombre').textContent = this.dataset.nombre;
            document.getElementById('contratoRolID').value = this.dataset.rolid;
            document.getElementById('contratoSueldo').value = this.dataset.sueldo;
            document.getElementById('contratoStatus').innerHTML = '';

            document.getElementById('modalContrato').style.display = 'block';
            document.body.insertAdjacentHTML('beforeend', '<div class="modal-backdrop"></div>');
        });
    });

    document.getElementById('btnGuardarContrato').addEventListener('click', function () {
        const formData = new FormData(document.getElementById('formContrato'));

        fetch('ajax_contrato_actualizar.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'SUCCESS') {
                document.getElementById('contratoStatus').innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => { location.reload(); }, 1000);
            } else {
                document.getElementById('contratoStatus').innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
            }
        });
    });

    // Cerrar con la tecla Escape (Todos los modales)
    window.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            hideModal();
            hideResetModal();
            hideContratoModal();
        }
    });
</script>
