<?php
session_start();
require_once 'conexion.php';

// 1. Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'empresa') {
    header('Location: /hiring-group/login.html');
    exit();
}

$con = conectar();
$id_usuario = $_SESSION['usuario']['id_usuario'];
$empresa_query = mysqli_query($con, "SELECT id_empresa FROM empresa WHERE fk_usuario = $id_usuario");
$empresa_data = mysqli_fetch_assoc($empresa_query);
$id_empresa = $empresa_data['id_empresa']; // <- Este es el ID que usaremos

// Consulta para contratos con datos incompletos (ahora incluyendo salario de la oferta)
$sql = "SELECT c.id_contratacion, u.nombre, u.apellido, o.cargo, o.salario as salario_oferta,
               c.tipo_sangre, c.contacto_emergencia, c.telefono_emergencia,
               c.nro_cuenta, b.nombre as banco_nombre
        FROM contratado c
        JOIN postulante p ON c.fk_postulante = p.id_postulante
        JOIN usuario u ON p.fk_usuario = u.id_usuario
        JOIN ofertalaboral o ON c.fk_oferta = o.id_oferta
        LEFT JOIN banco b ON c.fk_banco = b.id_banco
        WHERE o.fk_empresa = $id_empresa 
        AND c.fecha_inicio IS NULL";

$contratos = mysqli_query($con, $sql);

if (!$contratos) {
    die("Error en la consulta: " . mysqli_error($con));
}

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_contratacion = intval($_POST['id_contratado']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = !empty($_POST['fecha_fin']) ? "'".$_POST['fecha_fin']."'" : "NULL";
    $tipo_contrato = mysqli_real_escape_string($con, $_POST['tipo_contrato']);
    $salario_mensual = floatval($_POST['salario_mensual']);

    $sql_update = "UPDATE contratado 
                  SET fecha_inicio = '$fecha_inicio',
                      fecha_fin = $fecha_fin,
                      tipo_contrato = '$tipo_contrato',
                      salario_mensual = $salario_mensual
                  WHERE id_contratacion = $id_contratacion";

    if (mysqli_query($con, $sql_update)) {
        header("Location: completar_contrato.php?success=1");
        exit();
    } else {
        die("Error al actualizar: " . mysqli_error($con));
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Contratos</title>
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
        }

        .table th {
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table td {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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

        /* Modal */
        .modal-content {
            background: rgba(69, 69, 69, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            box-shadow: 0 0 0 0.25rem rgba(255, 138, 0, 0.25);
        }

        /* Alertas */
        .alert {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .menu {
                display: none;
            }
            
            .main-content {
                padding-top: 100px;
            }
            
            .card {
                margin-bottom: 15px;
            }
        }
        .form-select {
    background-color: #6c757d !important; /* Color gris de Bootstrap */
    color: white !important;
}

.form-select option {
    background-color: #6c757d;
    color: white;
}

/* Para navegadores WebKit (Chrome, Safari) */
.form-select::-webkit-scrollbar {
    width: 8px;
}

.form-select::-webkit-scrollbar-track {
    background: #495057;
}

.form-select::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 4px;
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
        <h1 class="mb-4">Contratos Pendientes de Completar</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Datos guardados correctamente
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($contratos) === 0): ?>
            <div class="alert alert-info">No hay contratos pendientes por completar.</div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="h4 mb-0">Contratos Incompletos</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Oferta</th>
                                    <th>Datos Personales</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($contrato = mysqli_fetch_assoc($contratos)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($contrato['nombre']) ?> <?= htmlspecialchars($contrato['apellido']) ?></td>
                                    <td><?= htmlspecialchars($contrato['cargo']) ?></td>
                                    <td>
                                        <strong>Sangre:</strong> <?= htmlspecialchars($contrato['tipo_sangre']) ?><br>
                                        <strong>Emergencia:</strong> <?= htmlspecialchars($contrato['contacto_emergencia']) ?><br>
                                        <strong>Teléfono:</strong> <?= htmlspecialchars($contrato['telefono_emergencia']) ?><br>
                                        <strong>Cuenta:</strong> <?= htmlspecialchars($contrato['nro_cuenta']) ?> (<?= htmlspecialchars($contrato['banco_nombre']) ?>)
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#modal<?= $contrato['id_contratacion'] ?>">
                                            Completar
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modales para cada contrato -->
    <?php 
    mysqli_data_seek($contratos, 0);
    while ($contrato = mysqli_fetch_assoc($contratos)): 
    ?>
    <div class="modal fade" id="modal<?= $contrato['id_contratacion'] ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="id_contratado" value="<?= $contrato['id_contratacion'] ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Completar Contrato</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <h6>Datos del Contratado:</h6>
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($contrato['nombre']) ?> <?= htmlspecialchars($contrato['apellido']) ?></p>
                        <p><strong>Oferta:</strong> <?= htmlspecialchars($contrato['cargo']) ?></p>
                        <p><strong>Salario ofertado:</strong> $<?= number_format($contrato['salario_oferta'], 2) ?></p>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="form-label">Fecha Inicio *</label>
                            <input type="date" name="fecha_inicio" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Fecha Fin (opcional)</label>
                            <input type="date" name="fecha_fin" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo de Contrato *</label>
                            <select name="tipo_contrato" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="Tiempo determinado">Tiempo determinado</option>
                                <option value="Tiempo indeterminado">Tiempo indeterminado</option>
                                <option value="Por obra determinada">Por obra determinada</option>
                                <option value="Practicas profesionales">Prácticas profesionales</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Salario Mensual *</label>
                            <input type="number" step="0.01" name="salario_mensual" class="form-control" 
                                   value="<?= htmlspecialchars($contrato['salario_oferta']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>