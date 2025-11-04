<?php
session_start();

// Verificar autenticación y tipo de usuario
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'empresa') {
    header('Location: /hiring-group/login.html');
    exit();
}

include("../usuarios/conexion.php");
$con = conectar();

// Obtener datos de la empresa
$id_empresa = $_SESSION['usuario']['id_empresa'];

// Obtener ofertas de la empresa
$sql = "SELECT o.*, a.nombre_area as area, e.nombre_estado as estado 
        FROM oferta o
        JOIN area a ON o.fk_area = a.id_area
        JOIN estado_oferta e ON o.fk_estado = e.id_estado
        WHERE o.fk_empresa = $id_empresa
        ORDER BY o.fecha_creacion DESC";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Ofertas de Trabajo</title>
    <link rel="stylesheet" href="../estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("../header.php"); ?>

<div class="container mt-5" style="padding-top: 100px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Mis Ofertas de Trabajo</h2>
        <a href="crear_oferta.php" class="btn btn-primary">Nueva Oferta</a>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Cargo</th>
                    <th>Área</th>
                    <th>Modalidad</th>
                    <th>Salario</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($oferta = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($oferta['cargo']) ?></td>
                        <td><?= htmlspecialchars($oferta['area']) ?></td>
                        <td><?= htmlspecialchars($oferta['modalidad']) ?></td>
                        <td><?= $oferta['salario'] ? '$' . number_format($oferta['salario'], 2) : 'No especificado' ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                $oferta['estado_oferta'] === 'Activa' ? 'success' : 
                                ($oferta['estado_oferta'] === 'Inactiva' ? 'warning' : 'secondary') 
                            ?>">
                                <?= htmlspecialchars($oferta['estado']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($oferta['fecha_creacion'])) ?></td>
                        <td>
                            <a href="editar_oferta.php?id=<?= $oferta['id_oferta'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <a href="cambiar_estado.php?id=<?= $oferta['id_oferta'] ?>&estado=<?= $oferta['estado_oferta'] === 'Activa' ? 'Inactiva' : 'Activa' ?>" class="btn btn-sm btn-outline-<?= $oferta['estado_oferta'] === 'Activa' ? 'warning' : 'success' ?>">
                                <?= $oferta['estado_oferta'] === 'Activa' ? 'Desactivar' : 'Activar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>