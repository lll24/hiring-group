<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'contratado') {
    header("Location: /hiring-group/login.html");
    exit();
}

include '../usuarios/conexion.php';
$con = conectar();

$id_usuario = $_SESSION['usuario']['id_usuario'];

// Obtener el id_postulante del usuario
$sql_postulante = "SELECT id_postulante FROM postulante WHERE fk_usuario = $id_usuario LIMIT 1";
$res_postulante = mysqli_query($con, $sql_postulante);

if (!$res_postulante || mysqli_num_rows($res_postulante) == 0) {
    die("No se encontró el postulante asociado.");
}

$row_postulante = mysqli_fetch_assoc($res_postulante);
$id_postulante = $row_postulante['id_postulante'];

// Obtener el id_contratacion del postulante
$sql_contratado = "SELECT id_contratacion FROM contratado WHERE fk_postulante = $id_postulante LIMIT 1";
$res_contratado = mysqli_query($con, $sql_contratado);

if (!$res_contratado || mysqli_num_rows($res_contratado) == 0) {
    die("No se encontró la contratación asociada.");
}

$row_contratado = mysqli_fetch_assoc($res_contratado);
$id_contratacion = $row_contratado['id_contratacion'];

// Obtener recibos de pago para este contratado
$sql_recibos = "SELECT * FROM recibopago WHERE fk_contratacion = $id_contratacion ORDER BY anio DESC, mes DESC";
$res_recibos = mysqli_query($con, $sql_recibos);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Recibos de Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
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

        /* Estilos para el encabezado */
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

        /* Contenido principal */
        .main-content {
            padding-top: 120px;
            padding-bottom: 50px;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Estilos de tarjetas */
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
        }

        /* Tabla */
        .table {
            color: white;
            background-color: transparent;
            margin-bottom: 0;
        }

        .table th {
            background: rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            vertical-align: middle;
        }

        .table td {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
        }

        .table tr:hover td {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Botones */
        .btn {
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            border-radius: 30px;
            padding: 8px 20px;
        }

        .btn-primary {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Alertas */
        .alert {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .month-name {
            text-transform: capitalize;
        }

        @media (max-width: 768px) {
            .menu {
                display: none;
            }
            
            .main-content {
                padding-top: 100px;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1>Hiring Group</h1>
            </div>
            <div class="menu">
                <a href="../index.php">Inicio</a>
                <a href="../logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0">Mis Recibos de Pago</h2>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($res_recibos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th>Año</th>
                                    <th>Salario Base</th>
                                    <th>IVSS (1%)</th>
                                    <th>Inces (0.5%)</th>
                                    <th>Hiring Group (2%)</th>
                                    <th>Salario Neto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($recibo = mysqli_fetch_assoc($res_recibos)): ?>
                                    <tr>
                                        <td class="month-name"><?= date('F', mktime(0,0,0,$recibo['mes'],10)) ?></td>
                                        <td><?= $recibo['anio'] ?></td>
                                        <td><?= number_format($recibo['salario_base'], 2) ?> Bs</td>
                                        <td><?= number_format($recibo['monto_ivss'], 2) ?> Bs</td>
                                        <td><?= number_format($recibo['monto_inces'], 2) ?> Bs</td>
                                        <td><?= number_format($recibo['monto_hiring'], 2) ?> Bs</td>
                                        <td><?= number_format($recibo['salario_neto'], 2) ?> Bs</td>
                                        <td>
                                            <a href="ver_detalle_recibo.php?id=<?= $recibo['id_recibo'] ?>" class="btn btn-primary btn-sm">Ver Detalle</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No tienes recibos de pago disponibles.</div>
                <?php endif; ?>
                <div class="mt-3">
                    <a href="../index.php" class="btn btn-secondary">Volver al Inicio</a>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>