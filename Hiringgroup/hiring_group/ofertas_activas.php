<?php
session_start();
require_once '../usuarios/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'hiring-group') {
    header("Location: ../login.php");
    exit();
}

$con = conectar();

// Obtener ofertas activas con información adicional
$sql = "SELECT o.id_oferta, o.cargo, o.fecha_creacion, o.modalidad, o.salario, 
               a.nombre_area, e.nombre AS nombre_empresa 
        FROM ofertalaboral o
        JOIN areaconocimiento a ON o.fk_area = a.id_area
        JOIN empresa e ON o.fk_empresa = e.id_empresa
        WHERE o.estado_oferta = 'Activa'
        ORDER BY o.fecha_creacion DESC";

$result = mysqli_query($con, $sql);
$ofertas = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ofertas Activas - Hiring Group</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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

        /* HEADER ESTILO */
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 50px;
        }

        .logo h1 {
            color: #fff;
            font-weight: 700;
            font-size: 24px;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
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

        /* CONTENIDO PRINCIPAL */
        .main-container {
            width: 90%;
            max-width: 1200px;
            margin: 100px auto 50px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        h1 {
            margin-bottom: 25px;
            font-weight: 700;
            color: #fff;
            text-align: center;
        }

        /* TABLA ESTILIZADA */
        .table {
            width: 100%;
            color: #fff;
            border-collapse: collapse;
            margin-top: 20px;
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
            background-color: rgba(62, 58, 58, 0.5);
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            vertical-align: middle;
        }

        .table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* BOTONES */
        .btn {
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(90deg, #11998e, #38ef7d);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.2);
            opacity: 0.9;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* LINK DE INICIO */
        .home-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .home-link:hover {
            color: #ff8a00;
            transform: translateX(-3px);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .header {
                height: 70px;
                padding: 0 20px;
            }
            
            .main-container {
                width: 95%;
                margin-top: 90px;
                padding: 20px;
            }
            
            .table thead {
                display: none;
            }
            
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            
            .table tr {
                margin-bottom: 15px;
                border-radius: 8px;
                overflow: hidden;
                background: rgba(255, 255, 255, 0.05);
            }
            
            .table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }
            
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: calc(50% - 15px);
                padding-right: 15px;
                font-weight: 600;
                text-align: left;
                color: rgba(34, 32, 32, 0.7);
            }
        }
    </style>
</head>
<body>
    <!-- Header estilo estilos.css -->
    <header class="header">
        <div class="logo">
            <h1>Hiring Group</h1>
        </div>
        <nav class="menu">
            <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
            <a href="#"><i class="fas fa-user"></i> Perfil</a>
        </nav>
    </header>

    <!-- Contenido principal -->
    <div class="main-container">
        <h1>Ofertas Laborales Activas</h1>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Cargo</th>
                    <th>Empresa</th>
                    <th>Área</th>
                    <th>Modalidad</th>
                    <th>Salario</th>
                    <th>Fecha de creación</th>
                    <th>Postulaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ofertas as $oferta): ?>
                    <tr>
                        <td data-label="Cargo"><?= htmlspecialchars($oferta['cargo']) ?></td>
                        <td data-label="Empresa"><?= htmlspecialchars($oferta['nombre_empresa']) ?></td>
                        <td data-label="Área"><?= htmlspecialchars($oferta['nombre_area']) ?></td>
                        <td data-label="Modalidad"><?= htmlspecialchars($oferta['modalidad']) ?></td>
                        <td data-label="Salario"><?= htmlspecialchars($oferta['salario']) ?></td>
                        <td data-label="Fecha"><?= date('d/m/Y', strtotime($oferta['fecha_creacion'])) ?></td>
                        <td data-label="Postulaciones">
                            <a href="postulaciones_pendientes.php?id_oferta=<?= $oferta['id_oferta'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-users"></i> Ver
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>