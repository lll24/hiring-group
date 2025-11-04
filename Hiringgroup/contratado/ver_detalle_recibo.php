<?php
include('../login/conexion.php');
$conexion = conectar();
session_start();



if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'contratado') {
    header("Location: /hiring-group/login.html");
    exit();
}

$id_usuario = $_SESSION['usuario']['id_usuario']; 

$id_recibo = $_GET['id'] ?? null;

if (!$id_recibo) {
    echo "Recibo no especificado.";
    exit();
}

// Obtener los datos del recibo y del usuario
$sql = "SELECT 
    r.*, 
    c.nro_cuenta, 
    b.nombre AS banco_nombre,
    u.nombre AS nombre_usuario,
    u.apellido AS apellido_usuario,
    n.fecha_generacion AS fecha_pago
FROM 
    recibopago r
JOIN 
    contratado c ON r.fk_contratacion = c.id_contratacion
JOIN 
    banco b ON c.fk_banco = b.id_banco
JOIN 
    postulante p ON c.fk_postulante = p.id_postulante
JOIN 
    usuario u ON p.fk_usuario = u.id_usuario
JOIN 
    nomina_detalle nd ON r.fk_nomina_detalle = nd.id_detalle
JOIN 
    nominamensual n ON nd.fk_nomina = n.id_nomina
WHERE 
    r.id_recibo = $id_recibo
    AND p.fk_usuario = $id_usuario

";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    echo "Recibo no encontrado.";
    exit();
}

$recibo = mysqli_fetch_assoc($resultado);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Recibo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS desde CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Detalle del Recibo de Pago</h3>
            </div>
            <div class="card-body">
                <p><strong>Nombre del Contratado:</strong> <?= htmlspecialchars($recibo['nombre_usuario'] . ' ' . $recibo['apellido_usuario']) ?></p>
                <p><strong>Fecha de Pago:</strong> <?= htmlspecialchars($recibo['fecha_pago']) ?></p>
                <p><strong>Monto:</strong> <?= number_format($recibo['salario_neto'], 2) ?> Bs</p>

                <hr>

                <h5 class="mt-4">Información Bancaria</h5>
                <p><strong>Banco:</strong> <?= htmlspecialchars($recibo['banco_nombre']) ?></p>
                <p><strong>Número de Cuenta:</strong> <?= htmlspecialchars($recibo['nro_cuenta']) ?></p>
            </div>
            <div class="card-footer text-end">
                <a href="ver_recibos.php" class="btn btn-secondary">← Volver</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (opcional para interacción) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
