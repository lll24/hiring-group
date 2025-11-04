<?php
session_start();
require_once '../usuarios/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'contratado') {
    header("Location: ../login.html");
    exit();
}

$con = conectar();
$id_usuario = $_SESSION['usuario']['id_usuario'];

// Obtener id_postulante
$sql_postulante = "SELECT id_postulante FROM postulante WHERE fk_usuario = $id_usuario LIMIT 1";
$res_postulante = mysqli_query($con, $sql_postulante);

if (!$res_postulante || mysqli_num_rows($res_postulante) === 0) {
    echo "No se encontró tu información como postulante.";
    exit();
}

$row = mysqli_fetch_assoc($res_postulante);
$id_postulante = $row['id_postulante'];

// Obtener datos del contrato
$sql_contrato = "SELECT c.*, b.nombre AS banco 
                 FROM contratado c 
                 JOIN banco b ON c.fk_banco = b.id_banco 
                 WHERE c.fk_postulante = $id_postulante LIMIT 1";

$res_contrato = mysqli_query($con, $sql_contrato);

if (!$res_contrato || mysqli_num_rows($res_contrato) === 0) {
    echo "No se encontró tu contrato.";
    exit();
}

$contrato = mysqli_fetch_assoc($res_contrato);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Contrato</title>
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
            display: flex;
            flex-direction: column;
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
            flex: 1;
            padding: 120px 0 50px;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Estilos de la tabla de contrato */
        .contract-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            max-height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
        }

        .contract-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.8rem;
        }

        .contract-table-container {
            flex: 1;
            overflow: auto;
            margin-bottom: 20px;
        }

        .contract-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            color: white;
        }

        /* Estilo para los campos (th) */
        .contract-table th {
            background: rgba(0, 0, 0, 0.3);
            font-weight: 500;
            padding: 15px;
            text-align: left;
            border: none;
            border-radius: 6px 0 0 6px;
            width: 30%;
            min-width: 200px;
            vertical-align: top;
            position: sticky;
            left: 0;
        }

        /* Estilo para la información (td) */
        .contract-table td {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px 20px;
            border: none;
            border-radius: 0 6px 6px 0;
            width: 70%;
            min-width: 300px;
            border-left: 2px solid rgba(255, 255, 255, 0.1);
        }

        .contract-table tr:last-child td {
            border-bottom: none;
        }

        .contract-table tr:hover td {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Separación visual entre filas */
        .contract-table tr {
            margin-bottom: 15px;
            display: table-row;
        }

        /* Botones */
        .btn {
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            min-width: 180px;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-success {
            background: linear-gradient(90deg, #38ef7d, #11998e);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .main-content > * {
            animation: fadeIn 0.6s ease forwards;
        }

        @media (max-width: 768px) {
            .main-content {
                padding-top: 100px;
                padding-bottom: 30px;
            }
            
            .contract-card {
                padding: 20px;
                max-height: none;
            }
            
            .btn-group {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
            }
            
            .contract-title {
                font-size: 1.5rem;
            }
            
            .contract-table th,
            .contract-table td {
                min-width: auto;
                width: auto;
                display: table-cell;
            }
            
            .contract-table {
                display: table;
                width: 100%;
            }
        }

        /* Scroll personalizado */
        .contract-table-container::-webkit-scrollbar {
            height: 8px;
        }

        .contract-table-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .contract-table-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1>Mi Contrato</h1>
            </div>
            <div class="menu">
                <a href="../index.php">Volver al Inicio</a>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="contract-card">
            <h2 class="contract-title">Detalles del Contrato</h2>
            
            <div class="contract-table-container">
                <table class="contract-table">
                    <tr>
                        <th>Fecha de Inicio</th>
                        <td><?= htmlspecialchars($contrato['fecha_inicio']) ?></td>
                    </tr>
                    <tr>
                        <th>Fecha de Fin</th>
                        <td><?= htmlspecialchars($contrato['fecha_fin']) ?: 'Indefinido' ?></td>
                    </tr>
                    <tr>
                        <th>Tipo de Contrato</th>
                        <td><?= htmlspecialchars($contrato['tipo_contrato']) ?></td>
                    </tr>
                    <tr>
                        <th>Salario Mensual</th>
                        <td><?= number_format($contrato['salario_mensual'], 2) ?> Bs</td>
                    </tr>
                    <tr>
                        <th>Tipo de Sangre</th>
                        <td><?= htmlspecialchars($contrato['tipo_sangre']) ?></td>
                    </tr>
                    <tr>
                        <th>Contacto de Emergencia</th>
                        <td><?= htmlspecialchars($contrato['contacto_emergencia']) ?></td>
                    </tr>
                    <tr>
                        <th>Teléfono de Emergencia</th>
                        <td><?= htmlspecialchars($contrato['telefono_emergencia']) ?></td>
                    </tr>
                    <tr>
                        <th>Número de Cuenta</th>
                        <td><?= htmlspecialchars($contrato['nro_cuenta']) ?></td>
                    </tr>
                    <tr>
                        <th>Banco</th>
                        <td><?= htmlspecialchars($contrato['banco']) ?></td>
                    </tr>
                </table>
            </div>

            <div class="btn-group">
                <a href="../index.php" class="btn btn-primary">Volver al Inicio</a>
                <a href="generar_constancia.php" class="btn btn-success" target="_blank">Generar Constancia</a>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>