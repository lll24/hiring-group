<?php
session_start();
require_once '../usuarios/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'postulante') {
    header("Location: ../login.php");
    exit();
}

$con = conectar();
$usuario_id = $_SESSION['usuario']['id_usuario'];

// Obtener id_postulante relacionado
$sql_postulante = "SELECT id_postulante FROM postulante WHERE fk_usuario = $usuario_id LIMIT 1";
$res_postulante = mysqli_query($con, $sql_postulante);
if (!$res_postulante || mysqli_num_rows($res_postulante) === 0) {
    echo "No se encontró el perfil de postulante.";
    exit();
}
$postulante = mysqli_fetch_assoc($res_postulante);
$id_postulante = $postulante['id_postulante'];

// Obtener todas las postulaciones del postulante junto con detalles de la oferta
$sql = "SELECT p.id_postulacion, p.fecha_postulacion, p.estado_postulacion, 
        o.cargo, o.descripcion_perfil 
        FROM postulacion p
        JOIN ofertalaboral o ON p.fk_oferta = o.id_oferta
        WHERE p.fk_postulante = $id_postulante
        ORDER BY p.fecha_postulacion DESC";

$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Postulaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            color: #fff;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Header estilo */
        .header {
            width: 100%;
            height: 80px;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo h1 {
            color: #fff;
            font-weight: 700;
            font-size: 24px;
            margin-left: 10px;
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .menu {
            display: flex;
            align-items: center;
        }

        .menu a {
            display: inline-block;
            padding: 10px 15px;
            margin: 0 5px;
            text-decoration: none;
            color: #fff;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            transition: all 0.3s ease;
            border-radius: 4px;
        }

        .menu a:hover {
            color: #ff8a00;
            background: rgba(255, 255, 255, 0.1);
        }

        .menu a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            transition: width 0.3s ease;
        }

        .menu a:hover::after {
            width: 80%;
        }

        /* Contenido principal */
        .main-content {
            padding-top: 120px;
            padding-bottom: 50px;
            width: 95%;
            max-width: 1400px;
            margin: 0 auto;
            animation: fadeIn 0.6s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Tabla estilizada */
        .table {
            width: 100%;
            color: #fff;
            border-collapse: collapse;
            margin-top: 30px;
            background: rgb(57, 56, 56);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table th {
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            vertical-align: middle;
        }

        .table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Badges de estado */
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-secondary {
            background: rgba(108, 117, 125, 0.7);
        }

        .badge-success {
            background: rgba(40, 167, 69, 0.7);
        }

        .badge-danger {
            background: rgba(220, 53, 69, 0.7);
        }

        .badge-warning {
            background: rgba(255, 193, 7, 0.7);
            color: #000;
        }

        /* Botones */
        .btn {
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: none;
            margin-top: 20px;
            margin-right: 10px;
        }

        .btn-light {
            background: rgba(37, 35, 35, 0.8);
            color: black;
        }

        .btn-light:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.15);
        }

        /* Alertas */
        .alert {
            background: rgba(0, 0, 0, 0.3);
            border: none;
            border-radius: 8px;
            backdrop-filter: blur(5px);
            color: #fff;
            margin-top: 20px;
        }

        .alert-info {
            border-left: 4px solid #17a2b8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .menu {
                display: none;
            }
            
            .logo h1 {
                font-size: 20px;
            }
            
            .main-content {
                padding-top: 100px;
                width: 100%;
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .table {
                display: block;
                overflow-x: auto;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1><i class="fas fa-users-cog"></i> HIRING GROUP</h1>
            </div>
            <nav class="menu">
                <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
                <a href="editar_perfil.php"><i class="fas fa-user-edit"></i> Mi Perfil</a>
                <a href="listar_ofertas.php"><i class="fas fa-search"></i> Ofertas</a>
                <a href="../logout.php" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </div>
    </header>

    <div class="main-content">
        <h1><i class="fas fa-clipboard-list me-2"></i>Mis Postulaciones</h1>
        
        <?php if (mysqli_num_rows($result) === 0): ?>
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle me-2"></i>No has realizado ninguna postulación aún.
            </div>
            <div class="d-flex mt-4">
                <a href="../index.php" class="btn btn-light me-3"><i class="fas fa-home me-2"></i>Inicio</a>
                <a href="listar_ofertas.php" class="btn btn-light"><i class="fas fa-search me-2"></i>Ver Ofertas Disponibles</a>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-briefcase me-2"></i>Oferta</th>
                        <th><i class="fas fa-align-left me-2"></i>Descripción</th>
                        <th><i class="fas fa-calendar-alt me-2"></i>Fecha de Postulación</th>
                        <th><i class="fas fa-info-circle me-2"></i>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($postulacion = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($postulacion['cargo']) ?></td>
                        <td><?= htmlspecialchars(substr($postulacion['descripcion_perfil'],0,100)) ?>...</td>
                        <td><?= date('d/m/Y', strtotime($postulacion['fecha_postulacion'])) ?></td>
                        <td>
                            <?php 
                            $estado = $postulacion['estado_postulacion'];
                            $color = 'secondary';
                            if ($estado === 'Aceptada') $color = 'success';
                            elseif ($estado === 'Rechazada') $color = 'danger';
                            elseif ($estado === 'Pendiente') $color = 'warning';
                            ?>
                            <span class="badge badge-<?= $color ?>"><?= $estado ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="d-flex mt-4">
                <a href="../index.php" class="btn btn-light me-3"><i class="fas fa-home me-2"></i>Inicio</a>
                <a href="listar_ofertas.php" class="btn btn-light"><i class="fas fa-search me-2"></i>Ver Ofertas Disponibles</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>