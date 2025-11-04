<?php
session_start();
include("../login/conexion.php");
$con = conectar();

$id_usuario = $_SESSION['usuario']['id_usuario'];
$empresa_query = mysqli_query($con, "SELECT id_empresa FROM empresa WHERE fk_usuario = $id_usuario");
$empresa_data = mysqli_fetch_assoc($empresa_query);
$id_empresa = $empresa_data['id_empresa']; // <- Este es el ID que usaremos

$sql = "SELECT * FROM nominamensual WHERE fk_empresa = $id_empresa ORDER BY anio DESC, mes DESC";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Nóminas</title>
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
        }
        
        .card-header {
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 600;
        }
        
        .table {
            color: #fff;
        }
        
        .table-dark {
            background: rgba(0, 0, 0, 0.3);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            border: none;
            border-radius: 30px;
            padding: 6px 15px;
            font-weight: 500;
        }
        
        .btn-success {
            background: linear-gradient(90deg, #28a745, #1e7e34);
            border: none;
            border-radius: 30px;
            padding: 6px 15px;
            font-weight: 500;
        }
        
        .btn-outline-primary {
            color: black;
            border-color: #ff8a00;
        }
        
        .btn-outline-primary:hover {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            color: #fff;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .text-muted {
            color: #adb5bd !important;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-nominas">
        <div class="header-content">
            <h2>Historial de Nóminas</h2>
            <a href="../index.php" class="btn btn-primary">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
        </div>
    </div>

    <div class="container py-4">
        <div class="card card-nomina">
            <div class="card-header">
                <h3 class="mb-0">Listado de Nóminas Mensuales</h3>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Mes</th>
                            <th>Año</th>
                            <th>Fecha Creación</th>
                            <th>Salario Base Total</th>
                            <th>IVSS</th>
                            <th>Inces</th>
                            <th>Hiring Group</th>
                            <th>Total Salario Neto</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <?php
                                    $id_nomina = $row['id_nomina'];

                                    // Verificar si esta nómina ya fue pagada
                                    $sql_detalles = "SELECT COUNT(*) AS total FROM nomina_detalle WHERE fk_nomina = $id_nomina";
                                    $res_detalles = mysqli_query($con, $sql_detalles);
                                    $total_detalles = mysqli_fetch_assoc($res_detalles)['total'];

                                    $sql_pagados = "SELECT COUNT(*) AS pagados FROM recibopago dp 
                                                    JOIN nomina_detalle nd ON dp.fk_nomina_detalle = nd.id_detalle 
                                                    WHERE nd.fk_nomina = $id_nomina";
                                    $res_pagados = mysqli_query($con, $sql_pagados);
                                    $total_pagados = mysqli_fetch_assoc($res_pagados)['pagados'];

                                    $pagada = $total_detalles > 0 && $total_detalles == $total_pagados;
                                ?>
                                <tr>
                                    <td><?= date('F', mktime(0, 0, 0, $row['mes'], 10)) ?></td>
                                    <td><?= $row['anio'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['fecha_generacion'])) ?></td>
                                    <td><?= number_format($row['total_salario_base'], 2) ?></td>
                                    <td><?= number_format($row['total_ivss'], 2) ?></td>
                                    <td><?= number_format($row['total_inces'], 2) ?></td>
                                    <td><?= number_format($row['total_hiring_group'], 2) ?></td>
                                    <td><?= number_format($row['total_neto'], 2) ?></td>
                                    <td>
                                        <a href="ver_detalles_nomina.php?id=<?= $id_nomina ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye-fill me-1"></i> Detalle
                                        </a>
                                        <?php if (!$pagada): ?>
                                            <a href="pagar_nomina.php?id=<?= $id_nomina ?>" 
                                               class="btn btn-sm btn-success mt-1" 
                                               onclick="return confirm('¿Está seguro de realizar el pago de esta nómina?')">
                                               <i class="bi bi-cash-coin me-1"></i> Pagar
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-success mt-2"><i class="bi bi-check-circle-fill me-1"></i> Pagada</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-muted">No hay nóminas registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>