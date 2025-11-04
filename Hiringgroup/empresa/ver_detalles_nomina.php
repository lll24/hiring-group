<?php
session_start();
include("../login/conexion.php");
$con = conectar();

if (!isset($_GET['id'])) {
    die("ID de nómina no especificado.");
}

$id_nomina = intval($_GET['id']);

// Consulta general de la nómina
$sql_nomina = "SELECT * FROM nominamensual WHERE id_nomina = $id_nomina";
$res_nomina = mysqli_query($con, $sql_nomina);
$nomina = mysqli_fetch_assoc($res_nomina);

// Consulta de los detalles de la nómina
$sql_detalles = "
    SELECT dn.*, u.nombre, u.apellido,
           (dn.salario_base - (dn.ivss + dn.inces + dn.hiring_group)) AS total_neto
    FROM nomina_detalle dn
    JOIN contratado c ON dn.fk_contratado = c.id_contratacion
    JOIN postulante p ON c.fk_postulante = p.id_postulante
    JOIN usuario u ON p.fk_usuario = u.id_usuario
    WHERE dn.fk_nomina = $id_nomina
";
$res_detalles = mysqli_query($con, $sql_detalles);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Nómina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
            padding-top: 80px;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Header estilo similar a estilos.css */
        .header-nominas {
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
            padding: 0 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-content {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-nominas h2 {
            color: #fff;
            font-weight: 700;
            font-size: 24px;
            font-family: 'Montserrat', sans-serif;
            margin-bottom: 0;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        /* Contenido principal */
        .container {
            max-width: 1200px;
            margin-top: 30px;
            padding-bottom: 50px;
        }
        
        .card-nomina {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 600;
        }
        
        .card-header-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .card-header-secondary {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }
        
        .table {
            color: #fff;
        }
        
        .table-dark {
            background: rgba(0, 0, 0, 0.3);
        }
        
        .table-bordered {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .btn-outline-secondary {
            color: #fff;
            border-color: #6c757d;
        }
        
        .btn-outline-secondary:hover {
            background: rgba(108, 117, 125, 0.2);
            color: #fff;
        }
        
        .text-muted {
            color: #adb5bd !important;
        }
        
        strong {
            color: #fff;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-nominas">
        <div class="header-content">
            <h2>Detalle de Nómina</h2>
            <a href="ver_nominas.php" class="btn btn-primary">
                <i class="bi bi-arrow-left me-1"></i> Volver a Nóminas
            </a>
        </div>
    </div>

    <div class="container py-4">
        <!-- Card de resumen de nómina -->
        <div class="card card-nomina">
            <div class="card-header card-header-primary">
                <h4 class="mb-0">Nómina - <?= date('F Y', mktime(0, 0, 0, $nomina['mes'], 10, $nomina['anio'])) ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fecha de generación:</strong> <?= date('d/m/Y', strtotime($nomina['fecha_generacion'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Neto:</strong> <?= number_format($nomina['total_neto'], 2) ?> Bs</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de detalles de trabajadores -->
        <div class="card card-nomina">
            <div class="card-header card-header-secondary">
                <h5 class="mb-0">Trabajadores incluidos en la nómina</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Salario Base</th>
                            <th>IVSS</th>
                            <th>Inces</th>
                            <th>Hiring Group</th>
                            <th>Total Salario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($res_detalles) > 0): ?>
                            <?php while ($detalle = mysqli_fetch_assoc($res_detalles)): ?>
                            <tr>
                                <td><?= $detalle['nombre'] . ' ' . $detalle['apellido'] ?></td>
                                <td><?= number_format($detalle['salario_base'], 2) ?></td>
                                <td><?= number_format($detalle['ivss'], 2) ?></td>
                                <td><?= number_format($detalle['inces'], 2) ?></td>
                                <td><?= number_format($detalle['hiring_group'], 2) ?></td>
                                <td><?= number_format($detalle['total_neto'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-muted">No hay detalles registrados para esta nómina.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="text-center mt-3">
                    <a href="ver_nominas.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Historial
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>