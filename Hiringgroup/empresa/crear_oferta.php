<?php
session_start();

// 1. Verificar si el usuario está logueado y es una empresa
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
$id_empresa = $empresa_data['id_empresa'];

// 4. Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cargo = mysqli_real_escape_string($con, $_POST['cargo']);
    $descripcion = mysqli_real_escape_string($con, $_POST['descripcion']);
    $modalidad = mysqli_real_escape_string($con, $_POST['modalidad']);
    $salario = !empty($_POST['salario']) ? mysqli_real_escape_string($con, $_POST['salario']) : 'NULL';
    $area = mysqli_real_escape_string($con, $_POST['area']);
    $estado = mysqli_real_escape_string($con, $_POST['estado']);
    
    $sql = "INSERT INTO ofertalaboral (
                cargo, 
                descripcion_perfil, 
                modalidad, 
                salario, 
                estado_oferta,
                fecha_creacion,
                fk_empresa,
                fk_area,
                fk_estado
            ) VALUES (
                '$cargo', 
                '$descripcion', 
                '$modalidad', 
                $salario, 
                'Activa',
                NOW(),
                $id_empresa,
                $area,
                $estado
            )";
    
    if (mysqli_query($con, $sql)) {
        header("Location: ofertas.php?success=1");
        exit();
    } else {
        $error = "Error al crear la oferta: " . mysqli_error($con);
    }
}

// 5. Obtener las áreas de conocimiento para el select
$areas = mysqli_query($con, "SELECT id_area, nombre_area FROM areaconocimiento ORDER BY nombre_area");

// 6. Obtener los estados de Venezuela
$estados = mysqli_query($con, "SELECT id_estado, nombre_estado FROM estado ORDER BY nombre_estado");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Oferta Laboral</title>
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

        .input-group-text{
            background:rgba(29, 28, 28, 0.7);
            border: none;
        }
        
        body {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            color: #fff;
            padding: 20px;
            overflow-y: auto;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
  
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #fff;
            margin-bottom: 25px;
            text-align: center;
            position: relative;
            padding-bottom: 10px;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
        }
        
        .form-label {
            font-weight: 500;
            color: #fff;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            background:rgba(29, 28, 28, 0.4);
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 0 0 0.25rem rgba(255, 138, 0, 0.25);
        }
        
        .required-field::after {
            content: " *";
            color: #ff4757;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            border-radius: 30px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        
        .alert {
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        
        .input-group-text {
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
            border: none;
        }
        
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
            z-index: 100;
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <!-- Botón para volver -->
    <a href="ofertas.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
    </a>
    
    <div class="container">
        <div class="form-container">
            <h2>Crear Nueva Oferta Laboral</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <!-- Campo Cargo -->
                <div class="mb-3">
                    <label for="cargo" class="form-label required-field">Cargo</label>
                    <input type="text" class="form-control" id="cargo" name="cargo" required
                           value="<?= isset($_POST['cargo']) ? htmlspecialchars($_POST['cargo']) : '' ?>">
                </div>
                
                <!-- Campo Descripción -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label required-field">Descripción del Puesto</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?= 
                        isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' 
                    ?></textarea>
                </div>
                
                <div class="row">
                    <!-- Campo Modalidad -->
                    <div class="col-md-6 mb-3">
                        <label for="modalidad" class="form-label required-field">Modalidad de Trabajo</label>
                        <select class="form-select" id="modalidad" name="modalidad" required>
                            <option value="">Seleccione...</option>
                            <option value="Presencial" <?= (isset($_POST['modalidad']) && $_POST['modalidad'] === 'Presencial') ? 'selected' : '' ?>>Presencial</option>
                            <option value="Remoto" <?= (isset($_POST['modalidad']) && $_POST['modalidad'] === 'Remoto') ? 'selected' : '' ?>>Remoto</option>
                            <option value="Híbrido" <?= (isset($_POST['modalidad']) && $_POST['modalidad'] === 'Híbrido') ? 'selected' : '' ?>>Híbrido</option>
                        </select>
                    </div>
                    
                    <!-- Campo Salario -->
                    <div class="col-md-6 mb-3">
                        <label for="salario" class="form-label">Salario (Opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" id="salario" name="salario"
                                   value="<?= isset($_POST['salario']) ? htmlspecialchars($_POST['salario']) : '' ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Área de Conocimiento -->
                    <div class="col-md-6 mb-3">
                        <label for="area" class="form-label required-field">Área de Conocimiento</label>
                        <select class="form-select" id="area" name="area" required>
                            <option value="">Seleccione un área...</option>
                            <?php 
                            mysqli_data_seek($areas, 0);
                            while ($area = mysqli_fetch_assoc($areas)): ?>
                                <option value="<?= $area['id_area'] ?>" 
                                    <?= (isset($_POST['area']) && $_POST['area'] == $area['id_area']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($area['nombre_area']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Campo Estado de Venezuela -->
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label required-field">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="">Seleccione un estado...</option>
                            <?php 
                            mysqli_data_seek($estados, 0);
                            while ($estado = mysqli_fetch_assoc($estados)): ?>
                                <option value="<?= $estado['id_estado'] ?>" 
                                    <?= (isset($_POST['estado']) && $_POST['estado'] == $estado['id_estado']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estado['nombre_estado']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="ofertas.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Publicar Oferta</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>