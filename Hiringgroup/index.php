<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: /hiring-group/login.html');
    exit();
}

include("usuarios/conexion.php");
$con = conectar();

$usuario = $_SESSION['usuario'];
$tipo = $usuario['tipo_usuario'];
$nombre = htmlspecialchars($usuario['nombre']);
$apellido = htmlspecialchars($usuario['apellido']);
$notificaciones = [];

// Inicializar todas las variables de conteo
$conteos = [];
$conteo_empresas = 0;
$conteo_ofertas_activas = 0;
$conteo_ofertas = 0;
$conteo_activas = 0;
$conteo_inactivas = 0;
$conteo_postulaciones = 0;
$conteo_ofertas_area = 0;
$conteo_mis_postulaciones = 0;

if (isset($usuario['id_usuario'])) {
    $sql_notificaciones = "SELECT mensaje, fecha, leido FROM notificacion 
                           WHERE fk_usuario = " . $usuario['id_usuario'] . " 
                           ORDER BY fecha DESC LIMIT 5";
    $res_notificaciones = mysqli_query($con, $sql_notificaciones);
    if ($res_notificaciones && mysqli_num_rows($res_notificaciones) > 0) {
        $notificaciones = mysqli_fetch_all($res_notificaciones, MYSQLI_ASSOC);
        
        // Marcar como leídas
        $sql_marcar_leidas = "UPDATE notificacion SET leido = 1 
                              WHERE fk_usuario = " . $usuario['id_usuario'] . " 
                              AND leido = 0";
        mysqli_query($con, $sql_marcar_leidas);
    }
}

// Solo admin o hiring-group pueden ver conteos
if ($tipo === 'admin' || $tipo === 'hiring-group') {
    // Conteo de usuarios por tipo (solo para admin)
    if ($tipo === 'admin') {
        $tipos = ['admin', 'empresa', 'hiring-group', 'postulante'];
        foreach ($tipos as $t) {
            $sql = "SELECT COUNT(*) as total FROM usuario WHERE tipo_usuario = '$t'";
            $query = mysqli_query($con, $sql);
            $result = mysqli_fetch_assoc($query);
            $conteos[$t] = $result['total'];
        }
    }

    // Conteo de ofertas para hiring-group
    if ($tipo === 'hiring-group') {
        $sql_empresas = "SELECT COUNT(*) as total FROM empresa";
        $query_empresas = mysqli_query($con, $sql_empresas);
        $result_empresas = mysqli_fetch_assoc($query_empresas);
        $conteo_empresas = $result_empresas['total'] ?? 0;

        $sql_ofertas_activas = "SELECT COUNT(*) as total FROM ofertalaboral WHERE estado_oferta = 'Activa'";
        $query_ofertas_activas = mysqli_query($con, $sql_ofertas_activas);
        $result_ofertas_activas = mysqli_fetch_assoc($query_ofertas_activas);
        $conteo_ofertas_activas = $result_ofertas_activas['total'] ?? 0;
    }
}

// Conteo de ofertas para empresas
if ($tipo === 'empresa' && isset($usuario['id_usuario'])) {
    // Obtener el ID de la empresa
    $empresa_query = mysqli_query($con, "SELECT id_empresa FROM empresa WHERE fk_usuario = ".$usuario['id_usuario']);
    $empresa_data = mysqli_fetch_assoc($empresa_query);
    $id_empresa = $empresa_data['id_empresa'];

    // Total de ofertas
    $sql_ofertas = "SELECT COUNT(*) as total FROM ofertalaboral WHERE fk_empresa = $id_empresa";
    $query_ofertas = mysqli_query($con, $sql_ofertas);
    $result_ofertas = mysqli_fetch_assoc($query_ofertas);
    $conteo_ofertas = $result_ofertas['total'] ?? 0;
    
    // Ofertas activas e inactivas
    $estados_query = mysqli_query($con,
        "SELECT estado_oferta, COUNT(*) as total
         FROM ofertalaboral
         WHERE fk_empresa = $id_empresa
         GROUP BY estado_oferta");
    
    $totales = ['Activa' => 0, 'Inactiva' => 0];
    while ($estado = mysqli_fetch_assoc($estados_query)) {
        $totales[$estado['estado_oferta']] = $estado['total'];
    }
    
    $conteo_activas = $totales['Activa'] ?? 0;
    $conteo_inactivas = $totales['Inactiva'] ?? 0;
}

if ($tipo === 'postulante' && isset($usuario['id_usuario'])) {
    $usuario_id = $usuario['id_usuario'];

    // Obtener id_postulante
    $id_postulante = null;
    $sql_id = "SELECT id_postulante FROM postulante WHERE fk_usuario = $usuario_id";
    $result_id = mysqli_query($con, $sql_id);

    if ($result_id && mysqli_num_rows($result_id) > 0) {
        $row_id = mysqli_fetch_assoc($result_id);
        $id_postulante = $row_id['id_postulante'];
    }

    // Contar solo las ofertas activas para postulantes
    $conteo_ofertas_area = 0;
    $sql_ofertas = "SELECT COUNT(*) as total FROM ofertalaboral 
                   WHERE estado_oferta = 'Activa'";
    $result_ofertas = mysqli_query($con, $sql_ofertas);

    if ($result_ofertas) {
        $row = mysqli_fetch_assoc($result_ofertas);
        $conteo_ofertas_area = $row['total'];
    }

    // Contar postulaciones realizadas
    $conteo_mis_postulaciones = 0;
    if ($id_postulante) {
        $sql_postulaciones = "SELECT COUNT(*) as total FROM postulacion 
                            WHERE fk_postulante = $id_postulante";
        $result_postulaciones = mysqli_query($con, $sql_postulaciones);

        if ($result_postulaciones) {
            $row_post = mysqli_fetch_assoc($result_postulaciones);
            $conteo_mis_postulaciones = $row_post['total'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hiring Group - Sistema de Gestión</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header class="header">
    <div class="container">
        <div class="logo">
            <h1><i class="fas fa-users-cog"></i> HIRING GROUP</h1>
        </div>
        <nav class="menu">
            <a href="#"><i class="fas fa-home"></i> Inicio</a>
            <?php if ($tipo === 'admin'): ?>
                <a href="./usuarios/crudusuarios.php"><i class="fas fa-users"></i> Usuarios</a>
            <?php elseif ($tipo === 'hiring-group'): ?>
                <a href="./empresa/crudempresa.php"><i class="fas fa-building"></i> Empresas</a>
                <a href="./hiring_group/ofertas_activas.php"><i class="fas fa-briefcase"></i> Ofertas</a>
                <a href="./hiring_group/nominas.php"><i class="fas fa-file-invoice-dollar"></i> Nóminas</a>
            <?php elseif ($tipo === 'empresa'): ?>
                <a href="./empresa/ofertas.php"><i class="fas fa-clipboard-list"></i> Mis Ofertas</a>
                <a href="./empresa/crear_oferta.php"><i class="fas fa-plus-circle"></i> Nueva Oferta</a>
                <a href="./empresa/ver_nominas.php"><i class="fas fa-file-alt"></i> Nóminas</a>                 
            <?php elseif ($tipo === 'postulante'): ?>
                <a href="./postulante/editar_perfil.php"><i class="fas fa-user-edit"></i> Perfil</a>
                <a href="./postulante/listar_ofertas.php"><i class="fas fa-search"></i> Ofertas</a>
                <a href="./postulante/mis_postulaciones.php"><i class="fas fa-clipboard-check"></i> Postulaciones</a>
            <?php elseif ($tipo === 'contratado'): ?>
                <a href="./postulante/editar_perfil.php"><i class="fas fa-user-edit"></i> Perfil</a>
                <a href="./postulante/listar_ofertas.php"><i class="fas fa-search"></i> Ofertas</a>
                <a href="./contratado/ver_recibos.php"><i class="fas fa-receipt"></i> Recibos</a>
            <?php endif; ?>
                <a href="cambiar_contrasena.php">Cambiar contraseña</a>
            <a href="logout.php" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </nav>
    </div>
</header>

<div class="container main-content">
    <!-- Bienvenida -->
    <div class="welcome-section mb-5">
        <h2 class="mb-3">Bienvenido, <?php echo "$nombre $apellido"; ?> <span class="badge bg-primary"><?php echo ucfirst($tipo); ?></span></h2>
        <div class="divider" style="height: 3px; background: linear-gradient(90deg, #ff8a00, #e52e71); width: 100px; border-radius: 3px;"></div>
    </div>

    <!-- Dashboard para admin -->
    <?php if ($tipo === 'admin'): ?>
        <h3 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Panel de Administración</h3>
        <div class="row">
            <?php foreach ($conteos as $rol => $cantidad): ?>
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-<?php echo $rol; ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><?= ucfirst($rol) ?></span>
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= $cantidad ?></h3>
                            <p class="card-text">usuarios registrados</p>
                            <?php if ($rol === 'admin'): ?>
                                <a href="./usuarios/crudusuarios.php" class="btn btn-light btn-sm">Administrar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Dashboard para hiring-group -->
    <?php if ($tipo === 'hiring-group'): ?>
        <h3 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Panel de Hiring Group</h3>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-hiring-group">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Empresas</span>
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= $conteo_empresas ?></h3>
                        <p class="card-text">registradas</p>
                        <a href="./empresa/crudempresa.php" class="btn btn-light btn-sm">Administrar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card text-white bg-warning">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Ofertas Activas</span>
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= $conteo_ofertas_activas ?></h3>
                        <p class="card-text">disponibles</p>
                        <a href="./hiring_group/ofertas_activas.php" class="btn btn-light btn-sm">Ver Ofertas</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card text-white bg-info">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Nóminas</span>
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Preparación de nóminas mensuales</p>
                        <a href="./hiring_group/nominas.php" class="btn btn-light btn-sm">Gestionar</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($tipo === 'empresa'): ?>
    <h3 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Panel de Empresa</h3>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Total Ofertas</span>
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?= $conteo_ofertas ?></h3>
                    <p class="card-text">ofertas publicadas</p>
                    <a href="./empresa/ofertas.php" class="btn btn-light btn-sm">Ver todas</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card text-white bg-success">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Ofertas Activas</span>
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?= $conteo_activas ?></h3>
                    <p class="card-text">activas actualmente</p>
                    <a href="./empresa/ofertas.php?estado=Activa" class="btn btn-light btn-sm">Ver activas</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card text-white bg-secondary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Ofertas Inactivas</span>
                    <i class="fas fa-pause-circle"></i>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?= $conteo_inactivas ?></h3>
                    <p class="card-text">no publicadas</p>
                    <a href="./empresa/ofertas.php?estado=Inactiva" class="btn btn-light btn-sm">Ver inactivas</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

    <!-- Dashboard para postulante -->
    <?php if ($tipo === 'postulante'): ?>
        <h3 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Mi Panel</h3>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-postulante">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Mi Perfil</span>
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Edita tu información personal</p>
                        <a href="./postulante/editar_perfil.php" class="btn btn-light btn-sm">Editar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card text-white bg-info">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Ofertas Activas</span>
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= $conteo_ofertas_area ?></h3>
                        <p class="card-text">disponibles</p>
                        <a href="./postulante/listar_ofertas.php" class="btn btn-light btn-sm">Explorar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card text-white bg-warning">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Mis Postulaciones</span>
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= $conteo_mis_postulaciones ?></h3>
                        <p class="card-text">realizadas</p>
                        <a href="./postulante/mis_postulaciones.php" class="btn btn-light btn-sm">Ver todas</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Dashboard para Usuarios Contratados -->
    <?php if ($tipo === 'contratado'): ?>
        <h3 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Panel de Contratado</h3>
        <div class="row">
            <?php
            require_once 'usuarios/conexion.php';
            $con = conectar();
            $id_usuario = $_SESSION['usuario']['id_usuario'];

            $sql_postulante = "SELECT id_postulante FROM postulante WHERE fk_usuario = $id_usuario LIMIT 1";
            $res_postulante = mysqli_query($con, $sql_postulante);
            $id_postulante = null;

            if ($res_postulante && mysqli_num_rows($res_postulante) > 0) {
                $row = mysqli_fetch_assoc($res_postulante);
                $id_postulante = $row['id_postulante'];

                $sql_contratado = "SELECT * FROM contratado WHERE fk_postulante = $id_postulante LIMIT 1";
                $res_contratado = mysqli_query($con, $sql_contratado);

                if ($res_contratado && mysqli_num_rows($res_contratado) > 0) {
            ?>
                    <div class="col-md-4 mb-4">
                        <div class="card text-white bg-contratado">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Mi Contrato</span>
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Consulta tu contrato laboral</p>
                                <a href="./contratado/ver_contrato.php" class="btn btn-light btn-sm">Ver</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Recibos</span>
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Ver mis recibos de pago</p>
                                <a href="./contratado/ver_recibos.php" class="btn btn-light btn-sm">Consultar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Ofertas</span>
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Explora otras oportunidades</p>
                                <a href="postulante/listar_ofertas.php" class="btn btn-light btn-sm">Explorar</a>
                            </div>
                        </div>
                    </div>
            <?php
                } else {
            ?>
                    <div class="col-md-12 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Contratación</span>
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">Formulario de Contratación</h5>
                                <p class="card-text">Completa este formulario para registrar tu contratación</p>
                                <a href="./contratado/formulario_contratacion.php" class="btn btn-light">Llenar Formulario</a>
                            </div>
                        </div>
                    </div>
            <?php
                }
            }
            mysqli_close($con);
            ?>
        </div>
    <?php endif; ?>
</div>

<!-- Menú lateral -->
<input type="checkbox" id="btn-menu">
<label for="btn-menu" class="open-menu">☰</label>
<div class="container-menu">
    <div class="cont-menu">
        <nav>
            <?php if ($tipo === 'admin'): ?>
                <a href="./usuarios/crudusuarios.php"><i class="fas fa-users me-2"></i>Usuarios</a>
            <?php elseif ($tipo === 'hiring-group'): ?>
                <a href="./empresa/crudempresa.php"><i class="fas fa-building me-2"></i>Empresas</a>
                <a href="./hiring_group/ofertas_activas.php"><i class="fas fa-briefcase me-2"></i>Ofertas</a>
                <a href="./hiring_group/nominas.php"><i class="fas fa-file-invoice-dollar me-2"></i>Nóminas</a>
            <?php elseif ($tipo === 'empresa'): ?>
                <a href="./empresa/ofertas.php"><i class="fas fa-clipboard-list me-2"></i>Mis Ofertas</a>
                <a href="./empresa/crear_oferta.php"><i class="fas fa-plus-circle me-2"></i>Nueva Oferta</a>
                <a href="./empresa/postulantes.php"><i class="fas fa-users me-2"></i>Postulantes</a>
                <a href="./empresa/completar_contrato.php"><i class="fas fa-file-signature me-2"></i>Contratos</a>
            <?php elseif ($tipo === 'postulante'): ?>
                <a href="./postulante/editar_perfil.php"><i class="fas fa-user-edit me-2"></i>Perfil</a>
                <a href="./postulante/listar_ofertas.php"><i class="fas fa-search me-2"></i>Ofertas</a>
                <a href="./postulante/mis_postulaciones.php"><i class="fas fa-clipboard-check me-2"></i>Postulaciones</a>
            <?php endif; ?>   
            <a href="cambiar_contrasena.php">Cambiar contraseña</a>
        </nav>
        <label for="btn-menu">✖️</label>
        
        <?php if (!empty($notificaciones)): ?>
            <div class="notificaciones-container">
                <h6><i class="fas fa-bell" style="color: gold;"></i> <span>Notificaciones</span></h6>
                <ul class="list-unstyled">
                    <?php foreach ($notificaciones as $n): ?>
                        <li>
                            <span><?= htmlspecialchars($n['mensaje']) ?></span><br>
                            <small><?= date('d/m/Y H:i', strtotime($n['fecha'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>