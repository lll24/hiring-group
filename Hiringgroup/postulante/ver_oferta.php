<?php
require_once 'conexion.php';
$con = conectar();
session_start();

// Verificación de sesión
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['tipo_usuario'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header('Location: ../login.html');
    exit();
}

$tipos_permitidos = ['postulante', 'contratado'];
if (!in_array($_SESSION['tipo_usuario'], $tipos_permitidos)) {
    $_SESSION['error'] = "No tienes permiso para acceder a esta página";
    header('Location: ../index.php');
    exit();
}

// Validación del ID de oferta
$oferta_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($oferta_id === false || $oferta_id <= 0) {
    $_SESSION['error'] = "ID de oferta inválido";
    header('Location: listar_ofertas.php');
    exit();
}

$usuario_id = $_SESSION['id_usuario'];

// Consulta ampliada para incluir más datos de la empresa
$oferta_query = "SELECT o.*, e.nombre AS nombre_empresa, e.rif, e.persona_contacto, 
                e.telefono_contacto, a.nombre_area, est.nombre_estado
                FROM ofertalaboral o
                JOIN empresa e ON o.fk_empresa = e.id_empresa
                JOIN areaconocimiento a ON o.fk_area = a.id_area
                JOIN estado est ON o.fk_estado = est.id_estado
                WHERE o.id_oferta = ?";

$stmt = $con->prepare($oferta_query);
$stmt->bind_param("i", $oferta_id);
$stmt->execute();
$oferta_result = $stmt->get_result();

if ($oferta_result->num_rows === 0) {
    $_SESSION['error'] = "La oferta solicitada no existe o no está disponible.";
    header('Location: listar_ofertas.php');
    exit();
}

$oferta = $oferta_result->fetch_assoc();

// Verificación de postulación
$ya_aplicado = false;
if ($_SESSION['tipo_usuario'] === 'postulante') {
    $stmt = $con->prepare("SELECT id_postulante FROM postulante WHERE fk_usuario = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $id_result = $stmt->get_result();
    
    if ($id_result->num_rows > 0) {
        $id_data = $id_result->fetch_assoc();
        $id_postulante = $id_data['id_postulante'];
        
        $stmt = $con->prepare("SELECT id_postulacion FROM postulacion 
                              WHERE fk_postulante = ? AND fk_oferta = ?");
        $stmt->bind_param("ii", $id_postulante, $oferta_id);
        $stmt->execute();
        $ya_aplicado = ($stmt->get_result()->num_rows > 0);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Oferta - <?= htmlspecialchars($oferta['cargo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Header styles */
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
            max-width: 1400px;  /* Aumentado para más espacio */
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

        /* Side menu styles */
        #btn-menu {
            display: none;
        }

        .open-menu {
            position: fixed;
            top: 20px;
            left: 20px;
            font-size: 28px;
            color: white;
            z-index: 2000;
            cursor: pointer;
            background: rgba(0, 0, 0, 0.5);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }

        .open-menu:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .container-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.7);
            z-index: 3000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        #btn-menu:checked ~ .container-menu {
            opacity: 1;
            visibility: visible;
        }

        .cont-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 300px;
            height: 100vh;
            background: #1a1a2e;
            padding: 20px;
            font-size: 15px;
            z-index: 3100;
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.3);
        }

        #btn-menu:checked ~ .container-menu .cont-menu {
            transform: translateX(0%);
        }

        .cont-menu nav {
            margin-top: 40px;
        }

        .cont-menu nav a {
            display: block;
            text-decoration: none;
            padding: 15px 20px;
            color: #e6e6e6;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            margin-bottom: 5px;
            border-radius: 4px;
            font-weight: 500;
        }

        .cont-menu nav a:hover {
            border-left: 4px solid #e94560;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            transform: translateX(5px);
        }

        .cont-menu label {
            position: absolute;
            right: 15px;
            top: 15px;
            color: #fff;
            cursor: pointer;
            font-size: 22px;
            transition: all 0.3s ease;
        }

        .cont-menu label:hover {
            color: #e94560;
            transform: rotate(90deg);
        }

        /* Main content styles */
        .container.main-content {
            padding-top: 120px;
            padding-bottom: 50px;
        }

        /* Offer card styles - Modificado para más ancho */
        .oferta-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.6s ease forwards;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            border: none;
            background: transparent;
        }

        .card-header {
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 600;
            padding: 25px;
        }

        .card-header h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .card-header h4 {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2rem;
        }

        .card-body {
            padding: 25px;
            color: #fff;
        }

        .card-body p {
            line-height: 1.8;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .card-footer {
            background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
        }

        /* Info box styles */
        .info-box {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid rgba(255, 138, 0, 0.7);
        }

        .info-box h4 {
            font-size: 1.3rem;
            margin-bottom: 20px;
        }

        /* Badge styles */
        .badge-area {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
            color: white;
            font-size: 0.9rem;
        }

        /* Button styles */
        .btn-option {
            margin: 8px;
            min-width: 150px;
            padding: 10px 20px;
            transition: all 0.3s ease;
            font-weight: 500;
            border: none;
            font-size: 1rem;
        }

        .btn-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
        }

        /* Alert styles */
        .alert {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeIn 0.6s ease forwards;
            max-width: 1200px;
            margin: 0 auto 30px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .container {
                max-width: 95%;
            }
            
            .oferta-container {
                max-width: 95%;
            }
        }

        @media (max-width: 768px) {
            .menu {
                display: none;
            }
            
            .logo h1 {
                font-size: 20px;
            }
            
            .cont-menu {
                width: 250px;
            }
            
            .container.main-content {
                padding-top: 100px;
            }
            
            .card-header h2 {
                font-size: 1.5rem;
            }
            
            .card-header h4 {
                font-size: 1rem;
            }
            
            .info-box h4 {
                font-size: 1.1rem;
            }
            
            .btn-option {
                min-width: 120px;
                font-size: 0.9rem;
                padding: 8px 15px;
            }
        }

        @media (max-width: 576px) {
            .card-header, .card-body, .card-footer {
                padding: 15px;
            }
            
            .info-box {
                padding: 15px;
            }
            
            .btn-option {
                min-width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header section -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1>Hiring Group</h1>
            </div>
            <nav class="menu">
                <a href="../index.php"><i class="bi bi-house-door"></i> Inicio</a>
                <a href="editar_perfil.php"><i class="bi bi-person"></i> Mi Perfil</a>
                <?php if ($_SESSION['tipo_usuario'] === 'postulante'): ?>
                    <a href="listar_ofertas.php"><i class="bi bi-briefcase"></i> Ofertas</a>
                    <a href="mis_postulaciones.php"><i class="bi bi-file-earmark-text"></i> Postulaciones</a>
                <?php endif; ?>
                <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a>
            </nav>
        </div>
    </header>
    
    <!-- Mobile menu -->
    <input type="checkbox" id="btn-menu">
    <div class="container-menu">
        <div class="cont-menu">
            <nav>
                <a href="../index.php"><i class="bi bi-house-door"></i> Inicio</a>
                <a href="editar_perfil.php"><i class="bi bi-person"></i> Mi Perfil</a>
                <?php if ($_SESSION['tipo_usuario'] === 'postulante'): ?>
                    <a href="listar_ofertas.php"><i class="bi bi-briefcase"></i> Ofertas</a>
                    <a href="mis_postulaciones.php"><i class="bi bi-file-earmark-text"></i> Postulaciones</a>
                <?php endif; ?>
                <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a>
            </nav>
            <label for="btn-menu"><i class="bi bi-x"></i></label>
        </div>
    </div>
    <label for="btn-menu" class="open-menu"><i class="bi bi-list"></i></label>

    <!-- Main content -->
    <div class="container main-content">
        <!-- Alert messages -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-12">
                <div class="oferta-container">
                    <div class="card">
                        <div class="card-header">
                            <h2><?= htmlspecialchars($oferta['cargo']) ?></h2>
                            <h4 class="h5"><?= htmlspecialchars($oferta['nombre_empresa']) ?></h4>
                            <small>Publicado: <?= date('d/m/Y', strtotime($oferta['fecha_creacion'])) ?></small>
                        </div>
                        <div class="card-body">
                            <!-- Company information -->
                            <div class="info-box">
                                <h4><i class="bi bi-building"></i> Información de la Empresa</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nombre:</strong> <?= htmlspecialchars($oferta['nombre_empresa']) ?></p>
                                        <p><strong>RIF:</strong> <?= htmlspecialchars($oferta['rif'] ?? 'No especificado') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Contacto:</strong> <?= htmlspecialchars($oferta['persona_contacto'] ?? 'No especificado') ?></p>
                                        <p><strong>Teléfono:</strong> <?= htmlspecialchars($oferta['telefono_contacto'] ?? 'No especificado') ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p><strong><i class="bi bi-briefcase"></i> Modalidad:</strong> <?= htmlspecialchars($oferta['modalidad'] ?? 'No especificado') ?></p>
                                    <?php if (!empty($oferta['salario'])): ?>
                                        <p><strong><i class="bi bi-currency-dollar"></i> Salario:</strong> <?= htmlspecialchars($oferta['salario']) ?></p>
                                    <?php else: ?>
                                        <p><strong><i class="bi bi-currency-dollar"></i> Salario:</strong> No especificado</p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <p><strong><i class="bi bi-geo-alt"></i> Estado:</strong> <?= htmlspecialchars($oferta['nombre_estado']) ?></p>
                                    <p><strong><i class="bi bi-tags"></i> Área:</strong> <span class="badge badge-area"><?= htmlspecialchars($oferta['nombre_area']) ?></span></p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h4><i class="bi bi-file-text"></i> Descripción del puesto</h4>
                                <?php if (!empty($oferta['descripcion_perfil'])): ?>
                                    <p><?= nl2br(htmlspecialchars($oferta['descripcion_perfil'])) ?></p>
                                <?php else: ?>
                                    <p>No se ha proporcionado una descripción del puesto.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer d-flex flex-wrap justify-content-between">
                            <div class="d-flex flex-wrap">
                                <a href="../index.php" class="btn btn-light btn-option">
                                    <i class="bi bi-house-door"></i> Inicio
                                </a>
                                <a href="listar_ofertas.php" class="btn btn-light btn-option">
                                    <i class="bi bi-arrow-left"></i> Ofertas
                                </a>
                            </div>
                            
                            <?php if ($_SESSION['tipo_usuario'] === 'postulante'): ?>
                                <?php if ($ya_aplicado): ?>
                                    <form action="postular.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                        <input type="hidden" name="accion" value="cancelar">
                                        <button type="submit" class="btn btn-danger btn-option">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="postular.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                        <input type="hidden" name="accion" value="postular">
                                        <button type="submit" class="btn btn-primary btn-option">
                                            <i class="bi bi-send-check"></i> Postularme
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-close alerts after 5 seconds
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
<?php 
// Close connection if open
if (isset($con) && $con instanceof mysqli) {
    mysqli_close($con);
}
?>