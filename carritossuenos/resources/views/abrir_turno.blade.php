<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Abrir Turno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-4 text-center">
    <h1 class="mb-4">Abrir Turno</h1>
    <button class="btn btn-primary" id="abrirTurnoBtn"><i class="fa fa-bell"></i> Abrir Turno</button>
</div>
<script>
    const api = axios.create({ baseURL: '/api/carrito' });
    document.getElementById('abrirTurnoBtn').addEventListener('click', async () => {
        // Llamada placeholder – la lógica la iremos completando después
        const resp = await api.get('/consultar-despacho');
        if (!resp.data.hay_despacho) {
            alert('No hay despacho para hoy');
        } else {
            alert('Despacho disponible – implementación pendiente');
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>