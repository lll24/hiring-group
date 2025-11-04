<?php
require_once 'conexion.php';
$con = conectar();
session_start();
$tipo_usuario = $_SESSION['tipo_usuario'] ?? '';

// Verificación de sesión
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['tipo_usuario'], ['postulante', 'contratado'])) {
    header('Location: ../login.html');
    exit();
}

$usuario_id = $_SESSION['id_usuario'];

$id_postulante = null;
$id_query = "SELECT id_postulante FROM postulante WHERE fk_usuario = $usuario_id";
$id_result = mysqli_query($con, $id_query);
if ($id_result && mysqli_num_rows($id_result) > 0) {
    $id_data = mysqli_fetch_assoc($id_result);
    $id_postulante = $id_data['id_postulante'];
}

// Obtener todas las ofertas activas con LEFT JOIN
$ofertas = [];
$ofertas_query = "SELECT o.*, e.nombre AS nombre_empresa, a.nombre_area 
                 FROM ofertalaboral o
                 JOIN empresa e ON o.fk_empresa = e.id_empresa
                 JOIN areaconocimiento a ON o.fk_area = a.id_area
                 WHERE o.estado_oferta = 'Activa'
                 ORDER BY o.fecha_creacion DESC";

$ofertas_result = mysqli_query($con, $ofertas_query);
if (!$ofertas_result) {
    die("Error al cargar ofertas: " . mysqli_error($con));
}

$ofertas = mysqli_fetch_all($ofertas_result, MYSQLI_ASSOC);

// Verificar aplicaciones del postulante
$aplicaciones = [];
if ($id_postulante && !empty($ofertas)) {
    $ofertas_ids = array_column($ofertas, 'id_oferta');
    $ofertas_ids_str = implode(",", $ofertas_ids);
    
    $aplicaciones_query = "SELECT fk_oferta FROM postulacion 
                          WHERE fk_postulante = $id_postulante 
                          AND fk_oferta IN ($ofertas_ids_str)";
    $aplicaciones_result = mysqli_query($con, $aplicaciones_query);
    if ($aplicaciones_result) {
        while ($row = mysqli_fetch_assoc($aplicaciones_result)) {
            $aplicaciones[] = $row['fk_oferta'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ofertas Disponibles - Postulante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            width: 95%;  /* Aumentado el ancho */
            max-width: 1400px;  /* Aumentado el máximo */
            margin: 0 auto;
            animation: fadeIn 0.6s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Tarjetas - Más anchas y mejor espaciado */
        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            margin-bottom: 25px;  /* Más espacio entre cards */
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            height: 100%;  /* Asegura misma altura */
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
            color: #fff;
            padding: 15px 20px;  /* Más padding */
        }

        .card-body {
            padding: 25px;  /* Más padding */
        }

        .card-footer {
            background: rgba(0, 0, 0, 0.2);
            padding: 15px 20px;
        }

        .btn {
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: none;
            margin-right: 10px;
            margin-bottom: 5px;
        }

        .btn-primary {
            background: linear-gradient(90deg, #11998e, #38ef7d);
        }

        .btn-secondary {
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .btn-danger {
            background: linear-gradient(90deg, #f46b45, #eea849);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.15);
            opacity: 0.9;
        }

        /* Alertas */
        .alert {
            background: rgba(0, 0, 0, 0.3);
            border: none;
            border-radius: 8px;
            backdrop-filter: blur(5px);
            color: #fff;
            margin-bottom: 25px;
        }

        .alert-success {
            border-left: 4px solid #38ef7d;
        }

        .alert-danger {
            border-left: 4px solid #f46b45;
        }

        .alert-info {
            border-left: 4px solid #667eea;
        }

        .btn-close {
            filter: invert(1);
        }

        /* Badges */
        .badge-area {
            background-color: #6f42c1;
            margin-right: 5px;
            margin-bottom: 5px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        /* Mejor distribución del grid */
        .row {
            margin-left: -15px;
            margin-right: -15px;
        }

        .col-md-6 {
            padding-left: 15px;
            padding-right: 15px;
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
                max-width: 100%;
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .card {
                margin-bottom: 20px;
            }
            
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
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
                <?php if ($tipo_usuario === 'contratado'): ?>
                    <!-- Usuario contratado: solo link "inicio" -->
                <?php else: ?>
                    <!-- Usuarios postulantes u otros: menú completo -->
                    <a href="editar_perfil.php"><i class="fas fa-user-edit"></i> Mi Perfil</a>
                    <a href="listar_ofertas.php" class="active"><i class="fas fa-search"></i> Ofertas</a>
                    <a href="mis_postulaciones.php"><i class="fas fa-clipboard-check"></i> Postulaciones</a>
                <?php endif; ?>
                <a href="../logout.php" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </div>
    </header>

    <div class="main-content">
        <!-- Mensajes -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['mensaje'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <h1 class="mb-4">Ofertas Disponibles</h1>

        <?php if (empty($ofertas)): ?>
            <div class="alert alert-info">
                No hay ofertas disponibles en este momento. Por favor, revisa más tarde.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($ofertas as $oferta): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="h5 mb-0"><?= htmlspecialchars($oferta['cargo']) ?></h3>
                                <small>Publicado: <?= date('d/m/Y', strtotime($oferta['fecha_creacion'])) ?></small>
                            </div>
                            <div class="card-body">
                                <h4 class="h6"><?= htmlspecialchars($oferta['nombre_empresa']) ?></h4>
                                <p><strong>Modalidad:</strong> <?= htmlspecialchars($oferta['modalidad']) ?></p>
                                <p><strong>Salario:</strong> <?= htmlspecialchars($oferta['salario']) ?></p>
                                
                                <div class="mb-3">
                                    <strong>Área:</strong>
                                    <span class="badge badge-area"><?= htmlspecialchars($oferta['nombre_area']) ?></span>
                                </div>
                                
                                <p><?= nl2br(htmlspecialchars(substr($oferta['descripcion_perfil'], 0, 200))) ?>...</p>
                            </div>
                            <div class="card-footer">
                                <?php if (in_array($oferta['id_oferta'], $aplicaciones)): ?>
                                    <form action="postular.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                        <input type="hidden" name="accion" value="cancelar">
                                        <button type="submit" class="btn btn-danger">Cancelar postulación</button>
                                    </form>
                                    <a href="ver_oferta.php?id=<?= $oferta['id_oferta'] ?>" class="btn btn-secondary">Ver Detalles</a>
                                <?php else: ?>
                                    <?php if ($_SESSION['tipo_usuario'] === 'postulante'): ?>
                                        <form action="postular.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                            <input type="hidden" name="accion" value="postular">
                                            <button type="submit" class="btn btn-primary">Postularme</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="ver_oferta.php?id=<?= $oferta['id_oferta'] ?>" class="btn btn-secondary">Ver Detalles</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cerrar automáticamente las alertas después de 5 segundos
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
<?php mysqli_close($con); ?>