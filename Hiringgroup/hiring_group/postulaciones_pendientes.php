<?php
session_start();
require_once '../usuarios/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'hiring-group') {
    header("Location: ../login.php");
    exit();
}

$con = conectar();

if (!isset($_GET['id_oferta']) || !is_numeric($_GET['id_oferta'])) {
    echo "ID de oferta no válido.";
    exit();
}

$id_oferta = intval($_GET['id_oferta']);

// Obtener información de la oferta
$sql_oferta = "SELECT cargo FROM ofertalaboral WHERE id_oferta = $id_oferta";
$res_oferta = mysqli_query($con, $sql_oferta);
if (!$res_oferta || mysqli_num_rows($res_oferta) == 0) {
    echo "Oferta no encontrada.";
    exit();
}
$oferta = mysqli_fetch_assoc($res_oferta);

// Procesar aceptación o rechazo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_postulacion'])) {
    $accion = $_POST['accion'];
    $id_postulacion = intval($_POST['id_postulacion']);

    if (in_array($accion, ['aceptar', 'rechazar'])) {
        $nuevo_estado = ($accion === 'aceptar') ? 'Aceptada' : 'Rechazada';

        $update_sql = "UPDATE postulacion SET estado_postulacion = '$nuevo_estado' WHERE id_postulacion = $id_postulacion";
        mysqli_query($con, $update_sql);

        $sql_usuario = "SELECT u.id_usuario, o.cargo, p.fk_oferta
                        FROM postulacion p
                        JOIN ofertalaboral o ON p.fk_oferta = o.id_oferta
                        JOIN postulante pos ON p.fk_postulante = pos.id_postulante
                        JOIN usuario u ON pos.fk_usuario = u.id_usuario
                        WHERE p.id_postulacion = $id_postulacion";
        $res_usuario = mysqli_query($con, $sql_usuario);

        if ($res_usuario && mysqli_num_rows($res_usuario) > 0) {
            $row = mysqli_fetch_assoc($res_usuario);
            $id_usuario_postulante = $row['id_usuario'];
            $cargo = $row['cargo'];
            $id_oferta = $row['fk_oferta'];

            $mensaje = ($nuevo_estado === 'Aceptada') 
                ? "Tu postulación a la oferta '$cargo' ha sido ACEPTADA." 
                : "Tu postulación a la oferta '$cargo' ha sido RECHAZADA.";

            $mensaje = mysqli_real_escape_string($con, $mensaje);
            $sql_notif = "INSERT INTO notificacion (fk_usuario, mensaje) VALUES ($id_usuario_postulante, '$mensaje')";
            mysqli_query($con, $sql_notif);

            if ($accion === 'aceptar') {
                $update_sql = "UPDATE postulacion SET estado_postulacion = 'Aceptada' WHERE id_postulacion = $id_postulacion";
                mysqli_query($con, $update_sql);

                $sql_usuario = "SELECT u.id_usuario, o.cargo, p.fk_oferta
                                FROM postulacion p
                                JOIN ofertalaboral o ON p.fk_oferta = o.id_oferta
                                JOIN postulante pos ON p.fk_postulante = pos.id_postulante
                                JOIN usuario u ON pos.fk_usuario = u.id_usuario
                                WHERE p.id_postulacion = $id_postulacion";
                $res_usuario = mysqli_query($con, $sql_usuario);

                if ($res_usuario && mysqli_num_rows($res_usuario) > 0) {
                    $row = mysqli_fetch_assoc($res_usuario);
                    $id_usuario_postulante = $row['id_usuario'];
                    $cargo = $row['cargo'];
                    $id_oferta = $row['fk_oferta'];

                    $mensaje = "Tu postulación a la oferta '$cargo' ha sido ACEPTADA.";
                    $mensaje = mysqli_real_escape_string($con, $mensaje);
                    $sql_notif = "INSERT INTO notificacion (fk_usuario, mensaje) VALUES ($id_usuario_postulante, '$mensaje')";
                    mysqli_query($con, $sql_notif);

                    $rechazar_sql = "SELECT p.id_postulacion, u.id_usuario
                                    FROM postulacion p
                                    JOIN postulante pos ON p.fk_postulante = pos.id_postulante
                                    JOIN usuario u ON pos.fk_usuario = u.id_usuario
                                    WHERE p.fk_oferta = $id_oferta
                                    AND p.id_postulacion != $id_postulacion
                                    AND p.estado_postulacion = 'Pendiente'";
                    $res_otros = mysqli_query($con, $rechazar_sql);

                    while ($row_otros = mysqli_fetch_assoc($res_otros)) {
                        $id_postulacion_otro = $row_otros['id_postulacion'];
                        $id_usuario_otro = $row_otros['id_usuario'];

                        $sql_rechazar = "UPDATE postulacion SET estado_postulacion = 'Rechazada' WHERE id_postulacion = $id_postulacion_otro";
                        mysqli_query($con, $sql_rechazar);

                        $mensaje_rechazo = "Tu postulación a la oferta '$cargo' ha sido RECHAZADA.";
                        $mensaje_rechazo = mysqli_real_escape_string($con, $mensaje_rechazo);
                        $sql_notif_otro = "INSERT INTO notificacion (fk_usuario, mensaje) VALUES ($id_usuario_otro, '$mensaje_rechazo')";
                        mysqli_query($con, $sql_notif_otro);
                    }

                    $actualizar_estado_oferta = "UPDATE ofertalaboral SET estado_oferta = 'Inactiva' WHERE id_oferta = $id_oferta";
                    mysqli_query($con, $actualizar_estado_oferta);

                    $sql_postulante = "SELECT p.fk_postulante FROM postulacion p WHERE p.id_postulacion = $id_postulacion LIMIT 1";
                    $res_postulante = mysqli_query($con, $sql_postulante);

                    if ($res_postulante && mysqli_num_rows($res_postulante) > 0) {
                        $row_postulante = mysqli_fetch_assoc($res_postulante);
                        $id_postulante = $row_postulante['fk_postulante'];

                        $update_usuario = "UPDATE usuario u
                                        JOIN postulante pos ON u.id_usuario = pos.fk_usuario
                                        SET u.tipo_usuario = 'contratado'
                                        WHERE pos.id_postulante = $id_postulante";

                        if (!mysqli_query($con, $update_usuario)) {
                            echo "Error al actualizar tipo de usuario: " . mysqli_error($con);
                        }
                    }
                }
            }

            $_SESSION['mensaje'] = "Postulación aceptada correctamente.";
            header("Location: postulaciones_pendientes.php?id_oferta=$id_oferta");
            exit();
        }

        $_SESSION['mensaje'] = "Postulación $nuevo_estado correctamente.";
        header("Location: postulaciones_pendientes.php?id_oferta=$id_oferta");
        exit();
    }
}

// Obtener postulaciones pendientes
$sql_postulaciones = "SELECT p.id_postulacion, u.nombre, u.apellido, u.correo, p.fecha_postulacion
                      FROM postulacion p
                      JOIN postulante pos ON p.fk_postulante = pos.id_postulante
                      JOIN usuario u ON pos.fk_usuario = u.id_usuario
                      WHERE p.fk_oferta = $id_oferta AND p.estado_postulacion = 'Pendiente'";

$res_postulaciones = mysqli_query($con, $sql_postulaciones);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulaciones Pendientes - <?= htmlspecialchars($oferta['cargo']) ?></title>
    <link rel="stylesheet" href="../estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .main-content {
            padding-top: 120px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .table-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table {
            color: #fff;
        }
        .table th {
            background: rgba(0, 0, 0, 0.5);
            border-color: rgba(255, 255, 255, 0.1);
        }
        .table td {
            background: rgba(72, 69, 69, 0.5);
            border-color: rgba(255, 255, 255, 0.1);
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 8px;
        }
        .btn-action {
            min-width: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .page-title {
            color: #fff;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
        }
    </style>
</head>
<body>
<header class="header">
    <div class="container">
        <div class="logo">
            <h1><i class="fas fa-users-cog"></i> HIRING GROUP</h1>
        </div>
        <nav class="menu">
            <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
            <a href="ofertas_activas.php"><i class="fas fa-briefcase"></i> Ofertas Activas</a>
            <a href="nominas.php"><i class="fas fa-file-invoice-dollar"></i> Nóminas</a>
            <a href="../logout.php" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </nav>
    </div>
</header>

<div class="container main-content">
    <h1 class="page-title">Postulaciones para: <?= htmlspecialchars($oferta['cargo']) ?></h1>
    
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['mensaje'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <div class="table-container">
        <?php if (mysqli_num_rows($res_postulaciones) == 0): ?>
            <div class="alert alert-info">No hay postulaciones pendientes para esta oferta.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Fecha Postulación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($postulacion = mysqli_fetch_assoc($res_postulaciones)): ?>
                            <tr>
                                <td><?= htmlspecialchars($postulacion['nombre']) ?></td>
                                <td><?= htmlspecialchars($postulacion['apellido']) ?></td>
                                <td><?= htmlspecialchars($postulacion['correo']) ?></td>
                                <td><?= date('d/m/Y', strtotime($postulacion['fecha_postulacion'])) ?></td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <form method="POST">
                                            <input type="hidden" name="id_postulacion" value="<?= $postulacion['id_postulacion'] ?>">
                                            <button type="submit" name="accion" value="aceptar" class="btn btn-success btn-sm btn-action">
                                                <i class="fas fa-check"></i> Aceptar
                                            </button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="id_postulacion" value="<?= $postulacion['id_postulacion'] ?>">
                                            <button type="submit" name="accion" value="rechazar" class="btn btn-danger btn-sm btn-action">
                                                <i class="fas fa-times"></i> Rechazar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Menú lateral -->
<input type="checkbox" id="btn-menu">
<label for="btn-menu" class="open-menu">☰</label>
<div class="container-menu">
    <div class="cont-menu">
        <nav>
            <a href="../index.php"><i class="fas fa-home me-2"></i> Inicio</a>
            <a href="ofertas_activas.php"><i class="fas fa-briefcase me-2"></i> Ofertas Activas</a>
            <a href="nominas.php"><i class="fas fa-file-invoice-dollar me-2"></i> Nóminas</a>
        </nav>
        <label for="btn-menu">✖️</label>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
