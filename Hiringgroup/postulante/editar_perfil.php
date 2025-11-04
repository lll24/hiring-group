<?php
require_once 'conexion.php';
session_start();

// Verificación de sesión
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['tipo_usuario'], ['postulante', 'contratado'])) {
    header('Location: ../login.html');
    exit();
}

$con = conectar();
$usuario = $_SESSION['usuario'];
$usuario_id = $usuario['id_usuario'];

// Inicializar array de datos con valores por defecto
$datos = [
    'nombre' => '',
    'apellido' => '',
    'cedula' => '', // Añadido campo cédula
    'correo' => '',
    'telefono' => '',
    'universidad_egreso' => '',
    'fecha_nacimiento' => ''
];

// Obtener datos del usuario
$usuario_query = "SELECT nombre, apellido,correo FROM usuario WHERE id_usuario = $usuario_id";
$usuario_result = mysqli_query($con, $usuario_query);

if ($usuario_result && mysqli_num_rows($usuario_result) > 0) {
    $datos_usuario = mysqli_fetch_assoc($usuario_result);
    $datos['nombre'] = $datos_usuario['nombre'];
    $datos['apellido'] = $datos_usuario['apellido'];
    $datos['correo'] = $datos_usuario['correo'];
    
   $postulante_query = "SELECT telefono, universidad_egreso, fecha_nacimiento, cedula 
                    FROM postulante WHERE fk_usuario = $usuario_id";
    $postulante_result = mysqli_query($con, $postulante_query);
    
    if ($postulante_result && mysqli_num_rows($postulante_result) > 0) {
        $datos_postulante = mysqli_fetch_assoc($postulante_result);
        $datos['telefono'] = $datos_postulante['telefono'];
        $datos['universidad_egreso'] = $datos_postulante['universidad_egreso'];
        $datos['fecha_nacimiento'] = $datos_postulante['fecha_nacimiento'];
        $datos['cedula'] = $datos_postulante['cedula'];
    }
}
// Obtener id_postulante para consultas relacionadas
$id_postulante = null;
$id_query = "SELECT id_postulante FROM postulante WHERE fk_usuario = $usuario_id";
$id_result = mysqli_query($con, $id_query);
if ($id_result && mysqli_num_rows($id_result) > 0) {
    $id_data = mysqli_fetch_assoc($id_result);
    $id_postulante = $id_data['id_postulante'];
} else {
    // Crear registro en postulante si no existe
    $insert_postulante = "INSERT INTO postulante (fk_usuario) VALUES ($usuario_id)";
    if (mysqli_query($con, $insert_postulante)) {
        $id_postulante = mysqli_insert_id($con);
    }
}

// Obtener áreas de conocimiento del postulante
$areas = [];
if ($id_postulante) {
    $areas_query = "SELECT a.id_area, a.nombre_area, a.descripcion, pa.id_PostArea
                   FROM postulantearea pa
                   JOIN areaconocimiento a ON pa.fk_area = a.id_area
                   WHERE pa.fk_postulante = $id_postulante";
    $areas_result = mysqli_query($con, $areas_query);
    if ($areas_result) {
        $areas = mysqli_fetch_all($areas_result, MYSQLI_ASSOC);
    }
}

// Obtener TODAS las áreas de conocimiento disponibles
$todas_areas = [];
$todas_areas_query = "SELECT * FROM areaconocimiento";
$todas_areas_result = mysqli_query($con, $todas_areas_query);
if ($todas_areas_result) {
    $todas_areas = mysqli_fetch_all($todas_areas_result, MYSQLI_ASSOC);
}

// Obtener experiencias laborales
$experiencias = [];
if ($id_postulante) {
    $exp_query = "SELECT * FROM experiencialaboral 
                 WHERE fk_postulante = $id_postulante
                 ORDER BY fecha_inicio DESC";
    $exp_result = mysqli_query($con, $exp_query);
    if ($exp_result) {
        $experiencias = mysqli_fetch_all($exp_result, MYSQLI_ASSOC);
    }
}

// Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar datos básicos
    if (isset($_POST['actualizar_perfil'])) {
        $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
        $apellido = mysqli_real_escape_string($con, $_POST['apellido']);
        $cedula = mysqli_real_escape_string($con, $_POST['cedula']);
        $correo = mysqli_real_escape_string($con, $_POST['correo']);
        $telefono = mysqli_real_escape_string($con, $_POST['telefono']);
        $universidad = mysqli_real_escape_string($con, $_POST['universidad_egreso']);
        $fecha_nacimiento = mysqli_real_escape_string($con, $_POST['fecha_nacimiento']);
        
        // Actualizar usuario (incluyendo cédula)
        $update_usuario = "UPDATE usuario SET 
                          nombre = '$nombre',
                          apellido = '$apellido',
                          correo = '$correo'
                          WHERE id_usuario = $usuario_id";
        
        // Actualizar o insertar datos de postulante
        if ($id_postulante) {
            $update_postulante = "UPDATE postulante SET 
                                telefono = '$telefono',
                                universidad_egreso = '$universidad',
                                cedula = '$cedula',
                                fecha_nacimiento = '$fecha_nacimiento'
                                WHERE id_postulante = $id_postulante";
        } else {
            $update_postulante = "INSERT INTO postulante 
                                (fk_usuario, telefono, universidad_egreso, fecha_nacimiento)
                                VALUES ($usuario_id, '$telefono', '$universidad', '$fecha_nacimiento')";
        }
        
        // Ejecutar actualizaciones
        $success = true;
        if (!mysqli_query($con, $update_usuario)) {
            $_SESSION['error'] = "Error al actualizar datos de usuario: " . mysqli_error($con);
            $success = false;
        }
        
        if (!mysqli_query($con, $update_postulante)) {
            $_SESSION['error'] = "Error al actualizar datos de postulante: " . mysqli_error($con);
            $success = false;
        }
        
        if ($success) {
            $_SESSION['mensaje'] = "Perfil actualizado correctamente";
            // Actualizar datos en array para mostrar
            $datos['nombre'] = $nombre;
            $datos['apellido'] = $apellido;
            $datos['cedula'] = $cedula;
            $datos['correo'] = $correo;
            $datos['telefono'] = $telefono;
            $datos['universidad_egreso'] = $universidad;
            $datos['fecha_nacimiento'] = $fecha_nacimiento;
            
            // Actualizar id_postulante si se creó nuevo registro
            if (!$id_postulante) {
                $id_postulante = mysqli_insert_id($con);
            }
        }
        
        header("Location: editar_perfil.php");
        exit();
    }

    // Agregar área de conocimiento
    if (isset($_POST['agregar_area']) && $id_postulante) {
        $area_id = intval($_POST['area_id']);
        
        // Verificar si el área ya está asignada
        $check_query = "SELECT * FROM postulantearea 
                       WHERE fk_postulante = $id_postulante AND fk_area = $area_id";
        $check_result = mysqli_query($con, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Esta área ya está asignada a tu perfil";
        } else {
            $insert_query = "INSERT INTO postulantearea (fk_postulante, fk_area)
                            VALUES ($id_postulante, $area_id)";
            
            if (mysqli_query($con, $insert_query)) {
                $_SESSION['mensaje'] = "Área de conocimiento agregada";
            } else {
                $_SESSION['error'] = "Error al agregar área: " . mysqli_error($con);
            }
        }
        header("Location: editar_perfil.php");
        exit();
    }

    // Agregar experiencia laboral
    if (isset($_POST['agregar_experiencia']) && $id_postulante) {
        $empresa = mysqli_real_escape_string($con, $_POST['empresa']);
        $cargo = mysqli_real_escape_string($con, $_POST['cargo']);
        $fecha_inicio = mysqli_real_escape_string($con, $_POST['fecha_inicio']);
        $fecha_fin = mysqli_real_escape_string($con, $_POST['fecha_fin']);
        $descripcion = mysqli_real_escape_string($con, $_POST['descripcion']);
        
        $insert_query = "INSERT INTO experiencialaboral 
                        (fk_postulante, empresa, cargo, fecha_inicio, fecha_fin, descripcion)
                        VALUES ($id_postulante, '$empresa', '$cargo', 
                        '$fecha_inicio', '$fecha_fin', '$descripcion')";
        
        if (mysqli_query($con, $insert_query)) {
            $_SESSION['mensaje'] = "Experiencia laboral agregada";
        } else {
            $_SESSION['error'] = "Error al agregar experiencia: " . mysqli_error($con);
        }
        header("Location: editar_perfil.php");
        exit();
    }
}

// Eliminar área de conocimiento
if (isset($_GET['eliminar_area']) && $id_postulante) {
    $area_id = intval($_GET['eliminar_area']);
    $delete_query = "DELETE FROM postulantearea 
                    WHERE id_PostArea = $area_id 
                    AND fk_postulante = $id_postulante";
    
    if (mysqli_query($con, $delete_query)) {
        $_SESSION['mensaje'] = "Área eliminada";
    } else {
        $_SESSION['error'] = "Error al eliminar área: " . mysqli_error($con);
    }
    header("Location: editar_perfil.php");
    exit();
}

// Eliminar experiencia laboral
if (isset($_GET['eliminar_experiencia']) && $id_postulante) {
    $exp_id = intval($_GET['eliminar_experiencia']);
    $delete_query = "DELETE FROM experiencialaboral 
                    WHERE id_experiencia = $exp_id 
                    AND fk_postulante = $id_postulante";
    
    if (mysqli_query($con, $delete_query)) {
        $_SESSION['mensaje'] = "Experiencia eliminada";
    } else {
        $_SESSION['error'] = "Error al eliminar experiencia: " . mysqli_error($con);
    }
    header("Location: editar_perfil.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Postulante</title>
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
        overflow-x: hidden
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
    width: 100%;
    max-width: 100%;
    padding: 120px 20px 20px 0; /* Reducir padding izquierdo */
    margin-left: 0;
}

    /* Card styles */
    .profile-card {
        width: 98%; /* Ocupa casi todo el ancho */
        margin-left: 0;
        border-left: none; /* Eliminar borde izquierdo */
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        margin-bottom: 30px;
        animation: fadeIn 0.6s ease forwards;
    }

    .profile-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 5px;
    background: linear-gradient(to bottom, #ff8a00, #e52e71);
}

    .card-header {
        background: rgba(0, 0, 0, 0.2);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h2 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        margin: 0;
        font-size: 1.5rem;
    }

    .card-body {
        padding: 25px;
        color: #fff;
    }

    /* Personal info styles */
    .personal-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .personal-info p {
        margin-bottom: 15px;
        font-size: 1rem;
        line-height: 1.6;
    }

    .personal-info strong {
        color: #ff8a00;
    }

    /* Table styles - Modificado para igualar cards */
    .table-responsive {
        overflow-x: auto;
        margin-top: 15px;
    }

    .table {
        width: 100%;
        color: #fff;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        overflow: hidden;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th {
        background: rgba(0, 0, 0, 0.3);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        vertical-align: middle;
        background: transparent;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    /* Button styles */
    .btn {
        border: none;
        border-radius: 30px;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
    }

    .btn-primary {
        background: linear-gradient(90deg, #ff8a00, #e52e71);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
    }

    .btn-danger {
        background: linear-gradient(90deg, #c31432, #240b36);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.7rem;
    }

    /* Alert styles */
    .alert {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        animation: fadeIn 0.6s ease forwards;
    }

    /* Modal styles */
    .modal-content {
        background: rgba(26, 26, 46, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        color: #fff;
    }

    .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .modal-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .form-control, .form-select {
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        padding: 10px 15px;
        border-radius: 8px;
    }

    .form-control:focus, .form-select:focus {
        background: rgba(0, 0, 0, 0.3);
        border-color: rgba(255, 138, 0, 0.5);
        box-shadow: 0 0 0 0.25rem rgba(255, 138, 0, 0.25);
        color: #fff;
    }

    .table td:nth-child(2), 
.table td:nth-child(4) { 
    max-width: 200px; 
    word-wrap: break-word; 
    white-space: normal !important; 
}


.table th:nth-child(2), 
.table td:nth-child(2) {
    width: 20%;
}

.table th:nth-child(4), 
.table td:nth-child(4) {
    width: 25%;
}

  
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
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
        
        .personal-info {
            grid-template-columns: 1fr;
        }
        
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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
                <a href="listar_ofertas.php"><i class="bi bi-briefcase"></i> Ofertas</a>
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
                <a href="editar_perfil.php" class="active"><i class="bi bi-person"></i> Mi Perfil</a>
                <a href="listar_ofertas.php"><i class="bi bi-briefcase"></i> Ofertas</a>
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
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Sección de datos personales -->
        <div class="profile-card">
            <div class="card-header">
                <h2><i class="bi bi-person-lines-fill"></i> Datos Personales</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editarPerfilModal">
                    <i class="bi bi-pencil-square"></i> Editar Perfil
                </button>
            </div>
            <div class="card-body">
                <div class="personal-info">
                    <div>
                        <p><strong><i class="bi bi-person-fill"></i> Nombre:</strong> <?= htmlspecialchars($datos['nombre']) ?></p>
                        <p><strong><i class="bi bi-person-badge"></i> Apellido:</strong> <?= htmlspecialchars($datos['apellido']) ?></p>
                        <p><strong><i class="bi bi-credit-card"></i> Cédula:</strong> <?= htmlspecialchars($datos['cedula']) ?></p>
                        <p><strong><i class="bi bi-envelope"></i> Correo:</strong> <?= htmlspecialchars($datos['correo']) ?></p>
                    </div>
                    <div>
                        <p><strong><i class="bi bi-telephone"></i> Teléfono:</strong> <?= htmlspecialchars($datos['telefono']) ?></p>
                        <p><strong><i class="bi bi-building"></i> Universidad:</strong> <?= htmlspecialchars($datos['universidad_egreso']) ?></p>
                        <p><strong><i class="bi bi-calendar"></i> Fecha Nacimiento:</strong> <?= htmlspecialchars($datos['fecha_nacimiento']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de áreas de conocimiento -->
        <div class="profile-card">
            <div class="card-header">
                <h2><i class="bi bi-bookmarks-fill"></i> Áreas de Conocimiento</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarAreaModal">
                    <i class="bi bi-plus-circle"></i> Agregar Área
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($areas)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Área</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($areas as $area): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($area['nombre_area']) ?></td>
                                        <td><?= htmlspecialchars($area['descripcion']) ?></td>
                                        <td>
                                            <a href="editar_perfil.php?eliminar_area=<?= $area['id_PostArea'] ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de eliminar esta área?')">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="mb-0 text-center">No hay áreas de conocimiento registradas.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección de experiencias laborales -->
        <div class="profile-card">
            <div class="card-header">
                <h2><i class="bi bi-briefcase-fill"></i> Experiencias Laborales</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarExpModal">
                    <i class="bi bi-plus-circle"></i> Agregar Experiencia
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($experiencias)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>Cargo</th>
                                    <th>Periodo</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($experiencias as $exp): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($exp['empresa']) ?></td>
                                        <td><?= htmlspecialchars($exp['cargo']) ?></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($exp['fecha_inicio'])) ?> - 
                                            <?= $exp['fecha_fin'] ? date('d/m/Y', strtotime($exp['fecha_fin'])) : 'Actualidad' ?>
                                        </td>
                                        <td><?= htmlspecialchars($exp['descripcion']) ?></td>
                                        <td>
                                            <a href="editar_perfil.php?eliminar_experiencia=<?= $exp['id_experiencia'] ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de eliminar esta experiencia?')">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="mb-0 text-center">No hay experiencias laborales registradas.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div class="modal fade" id="editarPerfilModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-lines-fill"></i> Editar Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="editar_perfil.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" 
                                       value="<?= htmlspecialchars($datos['nombre']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-control" name="apellido" 
                                       value="<?= htmlspecialchars($datos['apellido']) ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cédula</label>
                                <input type="text" class="form-control" name="cedula" 
                                       value="<?= htmlspecialchars($datos['cedula']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" name="correo" 
                                       value="<?= htmlspecialchars($datos['correo']) ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" name="telefono" 
                                       value="<?= htmlspecialchars($datos['telefono']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" name="fecha_nacimiento" 
                                       value="<?= htmlspecialchars($datos['fecha_nacimiento']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Universidad de Egreso</label>
                            <input type="text" class="form-control" name="universidad_egreso" 
                                   value="<?= htmlspecialchars($datos['universidad_egreso']) ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" name="actualizar_perfil" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Área -->
    <div class="modal fade" id="agregarAreaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-bookmarks-fill"></i> Agregar Área de Conocimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="editar_perfil.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Seleccione un área</label>
                            <select class="form-select" name="area_id" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($todas_areas as $area): ?>
                                    <option value="<?= $area['id_area'] ?>">
                                        <?= htmlspecialchars($area['nombre_area']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" name="agregar_area" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Agregar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Experiencia -->
    <div class="modal fade" id="agregarExpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-briefcase-fill"></i> Agregar Experiencia Laboral</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="editar_perfil.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Empresa</label>
                                <input type="text" class="form-control" name="empresa" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cargo</label>
                                <input type="text" class="form-control" name="cargo" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" name="fecha_inicio" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de Finalización</label>
                                <input type="date" class="form-control" name="fecha_fin">
                                <small class="text-muted">Dejar en blanco si es el trabajo actual</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" name="agregar_experiencia" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Agregar Experiencia
                        </button>
                    </div>
                </form>
            </div>
        </div>
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