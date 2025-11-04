<?php 
include("conexion.php");
$con = conectar();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de usuario no especificado.");
}

$id_usuario = intval($_GET['id']); // Convertir a entero para seguridad

$stmt = mysqli_prepare($con, "SELECT * FROM usuario WHERE id_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);
} else {
    die("Usuario no encontrado.");
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Actualizar Usuario</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        /* Header estilo similar a estilos.css */
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
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Formulario de actualización */
        .update-form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.6s ease forwards;
        }

        .form-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin-bottom: 30px;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-align: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 15px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-color: rgba(255, 138, 0, 0.5);
            box-shadow: 0 0 0 0.25rem rgba(255, 138, 0, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        label {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .btn-update {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            border: none;
            border-radius: 30px;
            padding: 12px 25px;
            font-weight: 500;
            color: white;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.2);
        }

        /* Menú lateral móvil */
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
            display: none;
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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .menu {
                display: none;
            }
            
            .open-menu {
                display: flex;
            }
            
            .main-content {
                padding-top: 100px;
                width: 95%;
            }
            
            .update-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1>Actualizar Usuario</h1>
            </div>
            <nav class="menu">
                <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
                <a href="crudusuarios.php"><i class="fas fa-users"></i> Usuarios</a>
            </nav>
        </div>
    </header>

    <!-- Menú lateral móvil -->
    <input type="checkbox" id="btn-menu">
    <div class="container-menu">
        <div class="cont-menu">
            <nav>
                <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
                <a href="crudusuarios.php"><i class="fas fa-users"></i> Usuarios</a>
            </nav>
            <label for="btn-menu"><i class="fas fa-times"></i></label>
        </div>
    </div>

    <!-- Botón para abrir menú en móvil -->
    <label for="btn-menu" class="open-menu"><i class="fas fa-bars"></i></label>

    <!-- Contenido principal -->
    <div class="main-content">
        <div class="update-form">
            <h3 class="form-title"><i class="fas fa-user-edit"></i> ACTUALIZACIÓN DE USUARIO</h3>
            
            <form action="update.php" method="POST">
                <!-- Campo oculto con el id -->
                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($row['id_usuario']) ?>">

                <div class="row">
                    <div class="col-md-6">
                        <label><i class="fas fa-user"></i> Nombre</label>
                        <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($row['nombre']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label><i class="fas fa-user-tag"></i> Apellido</label>
                        <input type="text" class="form-control" name="apellido" value="<?= htmlspecialchars($row['apellido']) ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label><i class="fas fa-envelope"></i> Correo</label>
                        <input type="email" class="form-control" name="correo" value="<?= htmlspecialchars($row['correo']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label><i class="fas fa-key"></i> Clave</label>
                        <input type="text" class="form-control" name="clave" value="<?= htmlspecialchars($row['clave']) ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label><i class="fas fa-user-tie"></i> Tipo de usuario</label>
                        <?php if ($row['tipo_usuario'] === 'admin'): ?>
                            <select class="form-control" name="tipo_usuario" disabled>
                                <option value="admin" selected>Administrador</option>
                            </select>
                            <input type="hidden" name="tipo_usuario" value="admin">
                        <?php else: ?>
                            <select class="form-control" name="tipo_usuario" required>
                                <option value="empresa" <?= $row['tipo_usuario'] == 'empresa' ? 'selected' : '' ?>>Empresa</option>
                                <option value="hiring-group" <?= $row['tipo_usuario'] == 'hiring-group' ? 'selected' : '' ?>>Hiring Group</option>
                                <option value="postulante" <?= $row['tipo_usuario'] == 'postulante' ? 'selected' : '' ?>>Postulante</option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn-update">
                    <i class="fas fa-save"></i> ACTUALIZAR USUARIO
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>