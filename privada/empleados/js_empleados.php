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
                    body: 'empleadoID=' + id + '&auth=empleados.php'
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
                body: 'empleadoID=' + empleadoIDReset + '&auth=empleados.php'
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

        formData.append('auth', 'empleados.php');
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

    // LÓGICA DE CREAR USUARIO
    function hideCrearUsuarioModal() {
        document.getElementById('modalCrearUsuario').style.display = 'none';
        let b = document.querySelector('.modal-backdrop');
        if (b) b.remove();
    }

    document.querySelectorAll('.btn-crear-usuario').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('crearUsuarioEmpleadoID').value = this.dataset.id;
            document.getElementById('crearUsuarioEmpleadoNombre').textContent = this.dataset.nombre;
            document.getElementById('crearUsuarioNombre').value = '';
            document.getElementById('crearUsuarioStatus').innerHTML = '';
            document.getElementById('crearUsuarioBody').style.display = 'block';
            document.getElementById('btnGuardarNuevoUsuario').style.display = 'inline-block';
            document.getElementById('cancelCrearUsuarioBtn').style.display = 'inline-block';

            document.getElementById('modalCrearUsuario').style.display = 'block';
            document.body.insertAdjacentHTML('beforeend', '<div class="modal-backdrop"></div>');
        });
    });

    document.getElementById('btnGuardarNuevoUsuario').addEventListener('click', function () {
        const usuarioInput = document.getElementById('crearUsuarioNombre').value.trim();
        const empleadoID = document.getElementById('crearUsuarioEmpleadoID').value;

        if (!usuarioInput) {
            document.getElementById('crearUsuarioStatus').innerHTML = `<div class="alert alert-warning py-1 small">Ingrese un nombre de usuario.</div>`;
            return;
        }

        const formData = new FormData();
        formData.append('empleadoID', empleadoID);
        formData.append('usuario', usuarioInput);

        fetch('ajax_crear_usuario.php', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'SUCCESS') {
                    document.getElementById('crearUsuarioStatus').innerHTML = `<div class="alert alert-success">✅ Usuario <b>${usuarioInput}</b> creado con éxito con clave <b>123456</b>.</div>`;
                    document.getElementById('crearUsuarioBody').style.display = 'none';
                    document.getElementById('btnGuardarNuevoUsuario').style.display = 'none';
                    document.getElementById('cancelCrearUsuarioBtn').style.display = 'none';
                    setTimeout(() => { location.reload(); }, 1200);
                } else {
                    document.getElementById('crearUsuarioStatus').innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                }
            })
            .catch(err => {
                document.getElementById('crearUsuarioStatus').innerHTML = `<div class="alert alert-danger">Error de conexión al servidor.</div>`;
                console.error(err);
            });
    });

    // Cerrar con la tecla Escape (Todos los modales)
    window.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            hideModal();
            hideResetModal();
            hideContratoModal();
            hideCrearUsuarioModal();
        }
    });
</script>