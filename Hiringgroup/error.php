<?php
session_start();
include("usuarios/conexion.php");
$con = conectar();

// Verificar si el usuario esta autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: /hiring-group/login.html');
    exit();
}

$usuario = $_SESSION['usuario'];
$nombre = htmlspecialchars($usuario['nombre']);
$apellido = htmlspecialchars($usuario['apellido']);
$tipo = $usuario['tipo_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error - Áreas de Conocimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .error-icon {
            font-size: 72px;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container text-center">
            <div class="error-icon">⚠️</div>
            <h2 class="text-danger">¡Atención <?php echo "$nombre $apellido"; ?>!</h2>
            <p class="lead">No tienes áreas de conocimiento registradas en tu perfil.</p>
            <p>Para poder ver ofertas laborales relevantes, necesitas agregar al menos un área de conocimiento a tu perfil.</p>
            
            <div class="d-grid gap-2 col-md-6 mx-auto mt-4">
                <a href="./postulante/editar_perfil.php" class="btn btn-primary btn-lg">
                    Agregar Áreas de Conocimiento
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                    Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</body>
</html>