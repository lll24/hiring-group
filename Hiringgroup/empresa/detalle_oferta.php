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

// 3. Obtener el ID de la oferta desde la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ofertas.php');
    exit();
}
$id_oferta = $_GET['id'];

// 3. Obtener el ID del usuario empresa desde la sesión
$id_usuario = $_SESSION['usuario']['id_usuario'];
$empresa_query = mysqli_query($con, "SELECT id_empresa FROM empresa WHERE fk_usuario = $id_usuario");
$empresa_data = mysqli_fetch_assoc($empresa_query);
$id_empresa = $empresa_data['id_empresa'];

// 4. Procesar eliminación si se solicita
if (isset($_POST['eliminar'])) {
    // Verificar que la oferta pertenece a esta empresa
    $verificar_oferta = mysqli_query($con, "SELECT id_oferta FROM ofertalaboral 
                                          WHERE id_oferta = $id_oferta AND fk_empresa = $id_empresa");
    
    if (mysqli_num_rows($verificar_oferta) > 0) {
        // Verificar si tiene contratos (usando id_contratacion en lugar de id_contrato)
        $verificar_contratos = mysqli_query($con, "SELECT id_contratacion FROM contratado 
                                                 WHERE fk_oferta = $id_oferta");
        
        if (mysqli_num_rows($verificar_contratos) > 0) {
            // Tiene contratos, no se puede eliminar
            header('Location: ofertas.php?error=contrato_activo');
            exit();
        }
        
        // Verificar si tiene postulaciones pendientes o aceptadas
        $verificar_postulaciones = mysqli_query($con, "SELECT id_postulacion FROM postulacion 
                                                     WHERE fk_oferta = $id_oferta AND estado_postulacion IN ('Pendiente', 'Aceptada')");
        
        if (mysqli_num_rows($verificar_postulaciones) > 0) {
            // Tiene postulaciones pendientes o aceptadas, no se puede eliminar
            header('Location: ofertas.php?error=postulaciones_activas');
            exit();
        }
        
        // Si no tiene contratos ni postulaciones pendientes/aceptadas, proceder con la eliminación
        // Primero eliminar postulaciones rechazadas si existen
        mysqli_query($con, "DELETE FROM postulacion WHERE fk_oferta = $id_oferta");
        
        // Luego eliminar la oferta
        $sql = "DELETE FROM ofertalaboral WHERE id_oferta = $id_oferta";
        if (mysqli_query($con, $sql)) {
            header('Location: ofertas.php?exito=eliminada');
            exit();
        }
    }
    header('Location: ofertas.php?error=eliminacion');
    exit();
}

// 5. Procesar el formulario cuando se envía (actualización)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 5.1. Verificar que la oferta pertenece a esta empresa
    $verificar_oferta = mysqli_query($con, "SELECT id_oferta FROM ofertalaboral 
                                          WHERE id_oferta = $id_oferta AND fk_empresa = $id_empresa");
    
    if (mysqli_num_rows($verificar_oferta) === 0) {
        header('Location: ofertas.php');
        exit();
    }

    // 5.2. Recoger y validar los datos del formulario
    $cargo = mysqli_real_escape_string($con, $_POST['cargo']);
    $descripcion = mysqli_real_escape_string($con, $_POST['descripcion']);
    $modalidad = mysqli_real_escape_string($con, $_POST['modalidad']);
    $salario = !empty($_POST['salario']) ? mysqli_real_escape_string($con, $_POST['salario']) : 'NULL';
    $estado_oferta = mysqli_real_escape_string($con, $_POST['estado_oferta']);
    $estado_venezuela = mysqli_real_escape_string($con, $_POST['estado_venezuela']);
    $area = mysqli_real_escape_string($con, $_POST['area']);

    // 5.3. Actualizar los datos en la base de datos
    $sql = "UPDATE ofertalaboral SET 
            cargo = '$cargo',
            descripcion_perfil = '$descripcion',
            modalidad = '$modalidad',
            salario = $salario,
            estado_oferta = '$estado_oferta',
            fk_estado = $estado_venezuela,
            fk_area = $area
            WHERE id_oferta = $id_oferta";
    
    if (mysqli_query($con, $sql)) {
        header("Location: ofertas.php?exito=editada");
        exit();
    } else {
        $error = "Error al actualizar la oferta: " . mysqli_error($con);
    }
}

// 6. Obtener los datos de la oferta
$oferta_query = mysqli_query($con, 
    "SELECT ol.*, ac.nombre_area as area_nombre, ev.nombre_estado as estado_nombre
     FROM ofertalaboral ol
     JOIN areaconocimiento ac ON ol.fk_area = ac.id_area
     JOIN estado ev ON ol.fk_estado = ev.id_estado
     WHERE ol.id_oferta = $id_oferta AND ol.fk_empresa = $id_empresa");

if (mysqli_num_rows($oferta_query) === 0) {
    header('Location: ofertas.php');
    exit();
}
$oferta = mysqli_fetch_assoc($oferta_query);

// 7. Obtener los estados de Venezuela para el select
$estados_venezuela = mysqli_query($con, "SELECT id_estado, nombre_estado FROM estado ORDER BY nombre_estado");

// 8. Obtener las áreas de conocimiento para el select
$areas_conocimiento = mysqli_query($con, "SELECT id_area, nombre_area FROM areaconocimiento ORDER BY nombre_area");

// 9. Obtener las modalidades disponibles
$modalidades = ["Presencial", "Remoto", "Híbrido"];

// Verificar si la oferta tiene contratos o postulaciones activas para el modal
$tiene_contratos = mysqli_query($con, "SELECT id_contratacion FROM contratado WHERE fk_oferta = $id_oferta");
$tiene_postulaciones = mysqli_query($con, "SELECT id_postulacion FROM postulacion WHERE fk_oferta = $id_oferta AND estado_postulacion IN ('Pendiente', 'Aceptada')");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Oferta Laboral</title>
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
            padding-top: 80px;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Header estilo similar a estilos.css */
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
        
        .header-ofertas h2 {
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
        
        /* Contenido principal */
        .container {
            max-width: 800px;
            margin-top: 100px;
            padding-bottom: 50px;
        }
        
        .card-oferta {
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
        
        .badge-estado {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        .badge-activa {
            background-color: #28a745;
        }
        
        .badge-inactiva {
            background-color: #6c757d;
        }
        
        .form-label {
            font-weight: 500;
            color: #fff;
        }
        
        .form-control, .form-select {
            background: rgba(29, 28, 28, 0.4);
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            border: none;
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 500;
        }
        .input-group-text{
            background:rgba(29, 28, 28, 0.7);
            border: none;
        }
        
        .btn-danger {
            background: linear-gradient(90deg, #dc3545, #a71d2a);
            border: none;
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
        }
        
        .required-field::after {
            content: " *";
            color: #ff4757;
        }
        
        /* Modal */
        .modal-content {
            background: rgba(8, 8, 8, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-ofertas">
        <div class="header-content">
            <div>
                <h2>Detalle de Oferta</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="ofertas.php">Mis Ofertas</a></li>
                        <li class="breadcrumb-item active">Detalle</li>
                    </ol>
                </nav>
            </div>
            <a href="ofertas.php" class="btn btn-primary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="container py-4">
        <!-- Mensajes de error -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Card principal con TODOS LOS CAMPOS ORIGINALES -->
        <div class="card card-oferta mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Editar oferta laboral</h4>
                <span class="badge badge-estado badge-<?= strtolower($oferta['estado_oferta']) ?>">
                    <?= $oferta['estado_oferta'] ?>
                </span>
            </div>
            <div class="card-body">
                <form method="POST" id="formOferta">
                    <!-- Información básica -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="cargo" class="form-label required-field">Cargo</label>
                            <input type="text" class="form-control" id="cargo" name="cargo" 
                                   value="<?= htmlspecialchars($oferta['cargo']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="area" class="form-label required-field">Área de conocimiento</label>
                            <select class="form-select" id="area" name="area" required>
                                <option value="">Seleccione un área...</option>
                                <?php 
                                mysqli_data_seek($areas_conocimiento, 0);
                                while ($area = mysqli_fetch_assoc($areas_conocimiento)): ?>
                                    <option value="<?= $area['id_area'] ?>" 
                                        <?= $oferta['fk_area'] == $area['id_area'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($area['nombre_area']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modalidad" class="form-label required-field">Modalidad de trabajo</label>
                            <select class="form-select" id="modalidad" name="modalidad" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($modalidades as $mod): ?>
                                    <option value="<?= $mod ?>" <?= $oferta['modalidad'] === $mod ? 'selected' : '' ?>>
                                        <?= $mod ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="salario" class="form-label">Salario (Opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="salario" name="salario"
                                       value="<?= $oferta['salario'] ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-4">
                        <label for="descripcion" class="form-label required-field">Descripción del puesto</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?= 
                            htmlspecialchars($oferta['descripcion_perfil']) ?></textarea>
                    </div>

                    <!-- Estado de la oferta y ubicación -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="estado_oferta" class="form-label required-field">Estado de la oferta</label>
                            <select class="form-select" id="estado_oferta" name="estado_oferta" required>
                                <option value="Activa" <?= $oferta['estado_oferta'] === 'Activa' ? 'selected' : '' ?>>Activa</option>
                                <option value="Inactiva" <?= $oferta['estado_oferta'] === 'Inactiva' ? 'selected' : '' ?>>Inactiva</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estado_venezuela" class="form-label required-field">Estado (Ubicación)</label>
                            <select class="form-select" id="estado_venezuela" name="estado_venezuela" required>
                                <?php 
                                mysqli_data_seek($estados_venezuela, 0);
                                while ($estado = mysqli_fetch_assoc($estados_venezuela)): ?>
                                    <option value="<?= $estado['id_estado'] ?>" 
                                        <?= $oferta['fk_estado'] == $estado['id_estado'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($estado['nombre_estado']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Fechas (solo lectura) -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de creación</label>
                            <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($oferta['fecha_creacion'])) ?>" readonly>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between">
                        <a href="ofertas.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                        <div>
                            <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#confirmarEliminacion">
                                <i class="bi bi-trash-fill me-1"></i> Eliminar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save-fill me-1"></i> Guardar cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="confirmarEliminacion" tabindex="-1" aria-labelledby="confirmarEliminacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmarEliminacionLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (mysqli_num_rows($tiene_contratos) > 0): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Esta oferta tiene contratos asociados y no puede ser eliminada.
                        </div>
                    <?php elseif (mysqli_num_rows($tiene_postulaciones) > 0): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Esta oferta tiene postulaciones pendientes o aceptadas y no puede ser eliminada.
                        </div>
                    <?php else: ?>
                        <p>¿Estás seguro que deseas eliminar esta oferta laboral?</p>
                        <p class="fw-bold">Esta acción no se puede deshacer y se perderán todos los datos asociados.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <?php if (mysqli_num_rows($tiene_contratos) == 0 && mysqli_num_rows($tiene_postulaciones) == 0): ?>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="eliminar" value="1" class="btn btn-danger">
                                <i class="bi bi-trash-fill me-1"></i> Sí, Eliminar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación adicional antes de enviar el formulario de eliminación
        document.querySelector('button[name="eliminar"]')?.addEventListener('click', function(e) {
            if (!confirm('¿Estás completamente seguro de eliminar esta oferta? Esta acción es irreversible.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>