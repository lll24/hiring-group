<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: /hiring-group/login.html');
    exit();
}

include("usuarios/conexion.php");
$con = conectar();

// Inicializar variables con valores por defecto
$nombre = '';
$apellido = '';
$error = '';
$success = '';

$usuario = $_SESSION['usuario'];
$id_usuario = $usuario['id_usuario'];
$nombre = htmlspecialchars($usuario['nombre'] ?? '');
$apellido = htmlspecialchars($usuario['apellido'] ?? '');
$tipo = $usuario['tipo_usuario'] ?? '';

// Procesar el formulario de cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validar que las contraseñas no estén vacías
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Todos los campos son obligatorios';
    } 
    // Validar que la nueva contraseña coincida con la confirmación
    elseif ($new_password !== $confirm_password) {
        $error = 'La nueva contraseña y la confirmación no coinciden';
    } 
    // Validar que la nueva contraseña sea diferente a la actual
    elseif ($current_password === $new_password) {
        $error = 'La nueva contraseña debe ser diferente a la actual';
    } else {
        // Verificar que la contraseña actual sea correcta
        $sql = "SELECT clave FROM usuario WHERE id_usuario = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if ($user && $current_password === $user['clave']) {
            // Actualizar la contraseña
            $update_sql = "UPDATE usuario SET clave = ? WHERE id_usuario = ?";
            $update_stmt = mysqli_prepare($con, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "si", $new_password, $id_usuario);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $success = 'Contraseña actualizada correctamente';
            } else {
                $error = 'Error al actualizar la contraseña';
            }
        } else {
            $error = 'La contraseña actual es incorrecta';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Hiring Group</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos personalizados */
        body {
            padding-top: 80px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .password-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .password-container {
            width: 100%;
            max-width: 600px; /* Ancho aumentado */
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.6s ease forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .password-header h2 {
            font-weight: 600;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 10px;
        }
        
        .password-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }
        
        .form-control {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(0, 0, 0, 0.3);
            border-color: #e52e71;
            box-shadow: 0 0 0 0.25rem rgba(229, 46, 113, 0.25);
            color: #fff;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            border: none;
            padding: 10px 25px;
            font-weight: 500;
            border-radius: 30px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 25px;
            border-radius: 30px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: none;
        }
        
        .alert-danger {
            background: rgba(199, 56, 79, 0.3);
            border-left: 4px solid #e52e71;
            color: #ff6b81;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.3);
            border-left: 4px solid #28a745;
            color: #7bed9f;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            color: rgba(255, 255, 255, 0.9);
        }
        
        @media (max-width: 768px) {
            .password-container {
                padding: 30px 20px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
<header class="header">
    <div class="container">
        <div class="logo">
            <h1>HIRING GROUP</h1>
        </div>
        <nav class="menu">
            <a href="index.php">Inicio</a>
            <a href="cambiar_contrasena.php">Cambiar contraseña</a>
            <a href="logout.php">Cerrar sesión</a>
        </nav>
    </div>
</header>

<div class="password-wrapper">
    <div class="password-container">
        <div class="password-header">
            <h2>Cambiar Contraseña</h2>
            <p>Usuario: <?php echo "$nombre $apellido"; ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="cambiar_contrasena.php">
            <div class="mb-3">
                <label for="current_password" class="form-label">Contraseña actual</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Nueva contraseña</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmar nueva contraseña</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<!-- Menú lateral -->
<input type="checkbox" id="btn-menu">
<label for="btn-menu" class="open-menu">☰</label>
<div class="container-menu">
    <div class="cont-menu">
        <nav>
            <a href="index.php">Inicio</a>
            <a href="cambiar_contrasena.php">Cambiar contraseña</a>
            <a href="logout.php">Cerrar sesión</a>
        </nav>
        <label for="btn-menu">✖️</label>
    </div>
</div>

</body>
</html>