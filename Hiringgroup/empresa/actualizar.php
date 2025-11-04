<?php 
include("conexion.php");
$con = conectar();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de empresa no especificado.");
}

$id_empresa = intval($_GET['id']);

$stmt = mysqli_prepare($con, "SELECT * FROM empresa WHERE id_empresa = ?");
mysqli_stmt_bind_param($stmt, "i", $id_empresa);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);
} else {
    die("Empresa no encontrada.");
}

// Obtener lista de usuarios
$sql_usuarios = "SELECT id_usuario, nombre, apellido FROM usuario";
$query_usuarios = mysqli_query($con, $sql_usuarios);

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Empresa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos de Font Awesome para la flecha -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* HEADER CON FLEX */
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 50px;
        }

        /* CONTENEDOR PRINCIPAL */
        .main-container {
            width: 90%;
            max-width: 1200px;
            margin-top: 100px;
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
        }

        /* TARJETA */
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 900px;
            padding: 0;
        }

        .card-header {
            background: rgba(0, 0, 0, 0.25);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 18px;
            padding: 20px;
            text-align: center;
        }

        .card-body {
            padding: 30px;
        }

        /* FORMULARIO */
        .form-control {
            background: rgba(39, 37, 37, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            width: 100%;
            padding: 10px 15px;
            font-size: 16px;
            height: 40px;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(12, 12, 12, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.15);
            outline: none;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 16px;
        }

        /* BOTONES */
        .btn {
            padding: 12px 30px;
            font-size: 18px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(90deg, #11998e, #38ef7d);
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        /* LOGO */
        .logo h1 {
            font-size: 32px;
            margin: 0;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .back-arrow {
            font-size: 23px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .back-arrow:hover {
            color: #ff8a00;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        /* ANIMACIONES */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            animation: fadeIn 0.8s ease forwards;
        }


        @media (max-width: 768px) {
            .header {
                height: 80px;
                padding: 0 20px;
            }
            
            .main-container {
                width: 95%;
                margin-top: 100px;
            }
            
            .logo h1 {
                font-size: 24px;
            }
            
            .back-arrow {
                font-size: 22px;
                width: 40px;
                height: 40px;
            }
            
            .card {
                border-radius: 12px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .form-control {
                padding: 12px 20px;
                height: 55px;
                font-size: 16px;
                margin-bottom: 20px;
                background: rgba(39, 37, 37, 0.4) ;
            }
            
            .form-label {
                font-size: 16px;
            }
            
            .btn {
                padding: 10px 25px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Header con flecha -->
    <header class="header">
        <a href="crudempresa.php" class="back-arrow" title="Volver al CRUD">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="logo">
            <h1>Actualizar Empresa</h1>
        </div>
        <div style="width: 50px;"></div> <!-- Espacio para equilibrar el flex -->
    </header>

    <!-- Contenido principal centrado -->
    <div class="main-container">
        <div class="card">
            <div class="card-header text-white">
                <h4 class="mb-0">ACTUALIZACIÓN DE EMPRESAS</h4>
            </div>
            <div class="card-body">
                <form action="update.php" method="POST">
                    <input type="hidden" name="id_empresa" value="<?= htmlspecialchars($row['id_empresa']) ?>">

                    <div class="mb-4">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($row['nombre']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">RIF</label>
                        <input type="text" class="form-control" name="RIF" value="<?= htmlspecialchars($row['RIF']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Sector</label>
                        <input type="text" class="form-control" name="sector" value="<?= htmlspecialchars($row['sector']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="direccion" value="<?= htmlspecialchars($row['direccion']) ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Persona de contacto</label>
                        <input type="text" class="form-control" name="persona_contacto" value="<?= htmlspecialchars($row['persona_contacto']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono_contacto" value="<?= htmlspecialchars($row['telefono_contacto']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Usuario asociado</label>
                        <input type="text" class="form-control" value="<?php
                            $usuario_id = $row['fk_usuario'];
                            $sql_user = "SELECT nombre, apellido FROM usuario WHERE id_usuario = '$usuario_id'";
                            $query_user = mysqli_query($con, $sql_user);
                            $user_data = mysqli_fetch_assoc($query_user);
                            echo htmlspecialchars($user_data['nombre'] . ' ' . $user_data['apellido'] . " (ID: $usuario_id)");
                        ?>" disabled>
                        <input type="hidden" name="fk_usuario" value="<?= $row['fk_usuario'] ?>">
                    </div>

                    <div class="d-grid gap-3 d-md-flex justify-content-md-end mt-4">
                        <a href="crudempresa.php" class="btn btn-light me-md-3">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Buscar usuario...",
                allowClear: true
            });
        });
    </script>
</body>
</html>