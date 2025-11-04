<?php
session_start();
require_once '../usuarios/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'hiring-group') {
    header("Location: ../login.html");
    exit();
}

$con = conectar();

// Obtener empresas para el select
$sql_empresas = "SELECT id_empresa, nombre FROM empresa ORDER BY nombre";
$res_empresas = mysqli_query($con, $sql_empresas);

$empresa_id = $_GET['empresa'] ?? null;
$mes = $_GET['mes'] ?? null;
$anio = $_GET['anio'] ?? null;
$total_bruto = 0;
$total_ivss = 0;
$total_inces = 0;
$total_hiring = 0;
$total_neto = 0;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Preparación de Nómina Mensual</title>
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

        /* FORMULARIO DE FILTROS */
        .filter-form {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .form-label {
            color: #fff;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-select, .form-control {
            background: rgba(128, 128, 128, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .form-select:focus, .form-control:focus {
            background: rgba(128, 128, 128, 0.6) !important;
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        /* ESTILOS PARA TODAS LAS LISTAS DESPLEGABLES */
        select.form-select option {
            background: #333 !important;
            color: #fff !important;
            padding: 10px;
        }

        /* BOTONES */
        .btn {
            padding: 10px 20px;
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
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            vertical-align: middle;
        }

        .table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .text-end {
            text-align: right;
        }

        /* ALERTAS */
        .alert {
            background: rgba(0, 0, 0, 0.3);
            border: none;
            border-radius: 8px;
            backdrop-filter: blur(5px);
        }

        .alert-danger {
            color: #ff6b6b;
        }

        .alert-info {
            color: #66d9ff;
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
            
            .filter-form {
                padding: 15px;
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
                color: rgba(255, 255, 255, 0.7);
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
        <h1>Preparación de Nómina Mensual</h1>
        
        <form method="GET" class="row g-3 mb-4 filter-form">
            <div class="col-md-4">
                <label for="empresa" class="form-label">Empresa</label>
                <select name="empresa" id="empresa" class="form-select" required>
                    <option value="">Seleccione una empresa</option>
                    <?php while ($empresa = mysqli_fetch_assoc($res_empresas)) : ?>
                        <option value="<?= $empresa['id_empresa'] ?>"
                            <?= ($empresa_id == $empresa['id_empresa']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="mes" class="form-label">Mes</label>
                <select name="mes" id="mes" class="form-select" required>
                    <?php 
                    $meses = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                    foreach ($meses as $num => $nombre_mes): ?>
                        <option value="<?= $num ?>" <?= ($mes == $num) ? 'selected' : '' ?>><?= $nombre_mes ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="anio" class="form-label">Año</label>
                <input type="number" name="anio" id="anio" class="form-control" min="2000" max="<?= date('Y') ?>" 
                       value="<?= htmlspecialchars($anio ?? date('Y')) ?>" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Generar Nómina</button>
            </div>
        </form>

        <?php
        if ($empresa_id && $mes && $anio) {
            // Sanitizar y validar
            $empresa_id = intval($empresa_id);
            $mes = intval($mes);
            $anio = intval($anio);

            if ($mes < 1 || $mes > 12 || $anio < 2000 || $anio > intval(date('Y'))) {
                echo '<div class="alert alert-danger">Mes o año inválido.</div>';
            } else {
                $fecha_inicio_mes = "$anio-$mes-01";
                $fecha_fin_mes = date("Y-m-t", strtotime($fecha_inicio_mes));

                // Traer datos con id_contratacion para inserciones posteriores
                $sql_nomina = "
                    SELECT u.nombre, u.apellido, o.cargo, c.salario_mensual, c.fecha_inicio, c.fecha_fin, c.id_contratacion
                    FROM contratado c
                    JOIN postulante p ON c.fk_postulante = p.id_postulante
                    JOIN usuario u ON p.fk_usuario = u.id_usuario
                    JOIN ofertalaboral o ON c.fk_oferta = o.id_oferta
                    WHERE o.fk_empresa = $empresa_id
                    AND (c.fecha_fin IS NULL OR c.fecha_fin >= '$fecha_inicio_mes')
                    AND c.fecha_inicio <= '$fecha_fin_mes'
                    ORDER BY u.apellido, u.nombre
                ";

                $res_nomina = mysqli_query($con, $sql_nomina);

                if (!$res_nomina) {
                    echo '<div class="alert alert-danger">Error en la consulta: ' . mysqli_error($con) . '</div>';
                } elseif (mysqli_num_rows($res_nomina) === 0) {
                    echo '<div class="alert alert-info">No se encontraron empleados para esta nómina.</div>';
                } else {
                    // Aquí podrías insertar o actualizar la nómina mensual y detalles en la BD (opcional)
                    
                   
                    $sql_check_nomina = "SELECT id_nomina FROM nominamensual WHERE fk_empresa = $empresa_id AND mes = $mes AND anio = $anio";
                    $res_check_nomina = mysqli_query($con, $sql_check_nomina);

                    if (mysqli_num_rows($res_check_nomina) > 0) {
                        $row_nomina = mysqli_fetch_assoc($res_check_nomina);
                        $id_nomina = $row_nomina['id_nomina'];
                    } else {
                            $sql_insert_nomina = "
                                INSERT INTO nominamensual (fk_empresa, mes, anio, fecha_generacion, total_salario_base, total_ivss, total_inces, total_hiring_group, total_neto)
                                VALUES ($empresa_id, $mes, $anio, NOW(), $total_bruto, $total_ivss, $total_inces, $total_hiring, $total_neto)
                            ";
                            mysqli_query($con, $sql_insert_nomina);
                            $id_nomina = mysqli_insert_id($con);
                    }
                    
                    mysqli_data_seek($res_nomina, 0);
                    while ($empleado = mysqli_fetch_assoc($res_nomina)) {
                        $salario = floatval($empleado['salario_mensual']);
                        $ivss = $salario * 0.01;
                        $inces = $salario * 0.005;
                        $hiring = $salario * 0.02;
                        $id_contratacion = $empleado['id_contratacion'];

                        // Insertar detalle nómina si no existe
                        $sql_check_detalle = "SELECT id_detalle FROM nomina_detalle WHERE fk_nomina = $id_nomina AND fk_contratado = $id_contratacion";
                        $res_check_detalle = mysqli_query($con, $sql_check_detalle);
                        if (mysqli_num_rows($res_check_detalle) === 0) {
                            $sql_insert_detalle = "INSERT INTO nomina_detalle 
                                (fk_nomina, fk_contratado, salario_base, inces, ivss, hiring_group) VALUES
                                ($id_nomina, $id_contratacion, $salario, $inces, $ivss, $hiring)";
                            mysqli_query($con, $sql_insert_detalle);
                        }
                    }
                    mysqli_data_seek($res_nomina, 0);


                    // Mostrar tabla
                    $total_bruto = $total_ivss = $total_inces = $total_hiring = $total_neto = 0;

                    echo '<table class="table">';
                    echo '<thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Cargo</th>
                            <th class="text-end">Salario Bruto (Bs)</th>
                            <th class="text-end">IVSS 1%</th>
                            <th class="text-end">INCES 0.5%</th>
                            <th class="text-end">Hiring Group 2%</th>
                            <th class="text-end">Salario Neto (Bs)</th>
                        </tr>
                        </thead><tbody>';

                    while ($empleado = mysqli_fetch_assoc($res_nomina)) {
                        $salario = floatval($empleado['salario_mensual']);
                        $ivss = $salario * 0.01;
                        $inces = $salario * 0.005;
                        $hiring = $salario * 0.02;
                        $salario_neto = $salario - $ivss - $inces - $hiring;

                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($empleado['nombre']) . '</td>';
                        echo '<td>' . htmlspecialchars($empleado['apellido']) . '</td>';
                        echo '<td>' . htmlspecialchars($empleado['cargo']) . '</td>';
                        echo '<td class="text-end">' . number_format($salario, 2, ',', '.') . '</td>';
                        echo '<td class="text-end">' . number_format($ivss, 2, ',', '.') . '</td>';
                        echo '<td class="text-end">' . number_format($inces, 2, ',', '.') . '</td>';
                        echo '<td class="text-end">' . number_format($hiring, 2, ',', '.') . '</td>';
                        echo '<td class="text-end">' . number_format($salario_neto, 2, ',', '.') . '</td>';
                        echo '</tr>';

                        $total_bruto += $salario;
                        $total_ivss += $ivss;
                        $total_inces += $inces;
                        $total_hiring += $hiring;
                        $total_neto += $salario_neto;
                    }

                    // Si la nómina ya existe, actualizar totales
                    $sql_update_nomina = "
            UPDATE nominamensual SET
            total_salario_base = $total_bruto,
            total_ivss = $total_ivss,
            total_inces = $total_inces,
            total_hiring_group = $total_hiring,
            total_neto = $total_neto,
            fecha_generacion = NOW()
            WHERE id_nomina = $id_nomina
        ";

        mysqli_query($con, $sql_update_nomina);


                    // Totales
                    echo '<tr class="fw-bold" style="background: rgba(0, 0, 0, 0.2)">';
                    echo '<td colspan="3" class="text-end">Totales:</td>';
                    echo '<td class="text-end">' . number_format($total_bruto, 2, ',', '.') . '</td>';
                    echo '<td class="text-end">' . number_format($total_ivss, 2, ',', '.') . '</td>';
                    echo '<td class="text-end">' . number_format($total_inces, 2, ',', '.') . '</td>';
                    echo '<td class="text-end">' . number_format($total_hiring, 2, ',', '.') . '</td>';
                    echo '<td class="text-end">' . number_format($total_neto, 2, ',', '.') . '</td>';
                    echo '</tr>';

                    echo '</tbody></table>';
                }
            }
        }

        mysqli_close($con);
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>