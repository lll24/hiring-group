<?php
session_start();

// 1. Verificar autenticación y tipo de usuario
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'empresa') {
    header('Location: /hiring-group/login.html');
    exit();
}

// 2. Conectar a la base de datos
include("../usuarios/conexion.php");
$con = conectar();

// 3. Obtener el ID del usuario empresa desde la sesión
$id_usuario = $_SESSION['usuario']['id_usuario'];
$empresa_query = mysqli_query($con, "SELECT id_empresa FROM empresa WHERE fk_usuario = $id_usuario");
$empresa_data = mysqli_fetch_assoc($empresa_query);
$id_empresa = $empresa_data['id_empresa']; // <- Este es el ID que usaremos

// 4. Obtener las ofertas de esta empresa
$ofertas_query = mysqli_query($con, 
    "SELECT ol.*, ac.nombre_area as area_nombre
     FROM ofertalaboral ol
     JOIN areaconocimiento ac ON ol.fk_area = ac.id_area
     WHERE ol.fk_empresa = $id_empresa
     ORDER BY ol.fecha_creacion DESC");

// 5. Contar ofertas por estado (para estadísticas)
$estados_query = mysqli_query($con,
    "SELECT estado_oferta, COUNT(*) as total
     FROM ofertalaboral
     WHERE fk_empresa = $id_empresa
     GROUP BY estado_oferta");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Ofertas Laborales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
       
    .header-ofertas {
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

    .breadcrumb-container {
        display: flex;
        flex-direction: column;
    }

    .header-ofertas h2 {
        color: #fff;
        font-weight: 700;
        font-size: 24px;
        font-family: 'Montserrat', sans-serif;
        margin-bottom: 0;
    }

    .header-ofertas .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
    }

    .header-ofertas .breadcrumb-item a {
        color: #fff !important;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .header-ofertas .breadcrumb-item a:hover {
        color: #ff8a00 !important;
    }

    .header-ofertas .btn-primary {
        background: linear-gradient(90deg, #ff8a00, #e52e71);
        border: none;
        border-radius: 30px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 14px;
    }

    .header-ofertas .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
    }

    .container.py-4 {
        padding-top: 100px !important;
    }

    .container { max-width: 1200px; }
    .card-oferta { 
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        background-color: rgba(106, 106, 106, 0.5);
    }
    
    .card-oferta h5,
    .card-oferta h2 {
        color: 
    }
    .card-oferta:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    body {
        background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        min-height: 100vh;
        color: #fff;
    }
    .badge-estado { font-size: 0.85rem; }
    .badge-activa { background-color: #28a745; }
    .badge-inactiva { background-color: #6c757d; }
    .stats-card { 
        border-left: 4px solid; 
        margin-bottom: 15px;
        background-color: rgba(106, 106, 106, 0.5);
    }
    .stats-activas { border-left-color: #28a745; }
    .stats-inactivas { border-left-color: #6c757d; }
    .stats-total { border-left-color: #0d6efd; }
    .truncate-3-lines {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .btn-eliminar:hover {
        transform: scale(1.1);
        transition: transform 0.2s;
    }
</style>
</head>
<body>
   
    <div class="container py-4">
        <!-- Header y breadcrumb -->
        <div class="header-ofertas">
    <div class="header-content">
        <div class="breadcrumb-container">
            <h2 class="mb-1">Mis Ofertas Laborales</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Mis Ofertas</li>
                </ol>
            </nav>
        </div>
        <a href="crear_oferta.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva Oferta
        </a>
    </div>
</div>

        <!-- Mensajes de éxito/error -->
        <?php if (isset($_GET['exito'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <?php
                switch($_GET['exito']) {
                    case 'creada': echo '<i class="bi bi-check-circle-fill me-2"></i> Oferta creada exitosamente'; break;
                    case 'editada': echo '<i class="bi bi-check-circle-fill me-2"></i> Oferta actualizada correctamente'; break;
                    case 'eliminada': echo '<i class="bi bi-check-circle-fill me-2"></i> Oferta eliminada con éxito'; break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= $_GET['error'] === 'eliminacion' ? 'Error al eliminar la oferta' : 'Ocurrió un error' ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <?php 
            $totales = ['Activa' => 0, 'Inactiva' => 0];
            while ($estado = mysqli_fetch_assoc($estados_query)) {
                $totales[$estado['estado_oferta']] = $estado['total'];
            }
            $total_ofertas = array_sum($totales);
            ?>
            
            <div class="col-md-4">
                <div class="card stats-card stats-activas h-100">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Ofertas Activas</h5>
                        <h2 class="text-success"><?= $totales['Activa'] ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card stats-card stats-inactivas h-100">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Ofertas Inactivas</h5>
                        <h2 class="text-secondary"><?= $totales['Inactiva'] ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card stats-card stats-total h-100">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Total Ofertas</h5>
                        <h2 class="text-primary"><?= $total_ofertas ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listado de ofertas -->
        <?php if (mysqli_num_rows($ofertas_query) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php while ($oferta = mysqli_fetch_assoc($ofertas_query)): ?>
                    <div class="col">
                        <div class="card card-oferta">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($oferta['cargo']) ?></h5>
                                    <span class="badge badge-estado badge-<?= strtolower($oferta['estado_oferta']) ?>">
                                        <?= $oferta['estado_oferta'] ?>
                                    </span>
                                </div>
                                
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <i class="bi bi-grid-fill text-primary me-1"></i>
                                    <?= htmlspecialchars($oferta['area_nombre']) ?>
                                </h6>
                                
                                <div class="card-text mb-3 truncate-3-lines">
                                    <?= htmlspecialchars($oferta['descripcion_perfil']) ?>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted me-3">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($oferta['fecha_creacion'])) ?>
                                        </small>
                                        <?php if ($oferta['salario']): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-currency-dollar me-1"></i>
                                                <?= number_format($oferta['salario'], 2) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <a href="detalle_oferta.php?id=<?= $oferta['id_oferta'] ?>" 
                                           class="btn btn-sm btn-outline-primary me-2"
                                           title="Ver detalles y gestionar">
                                            <i class="bi bi-eye-fill"></i> Gestionar
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger btn-eliminar" 
                                                onclick="confirmarEliminacion(<?= $oferta['id_oferta'] ?>)"
                                                title="Eliminar oferta">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <img src="../imagenes/error.png" alt="Sin ofertas" style="max-width: 250px;" class="mb-4">
                <h4 class="text-muted mb-3">No has creado ninguna oferta</h4>
                <p class="text-muted mb-4">Comienza a publicar ofertas laborales para encontrar candidatos</p>
                <a href="crear_oferta.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-lg me-2"></i> Crear primera oferta
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmarEliminacion(idOferta) {
        if (confirm('¿Estás seguro que deseas eliminar esta oferta laboral?\n\nEsta acción no se puede deshacer y se perderán todos los datos asociados.')) {
            // Crear un formulario dinámico para enviar por POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'detalle_oferta.php?id=' + idOferta;
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'eliminar';
            input.value = '1';
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>