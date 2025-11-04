<?php
session_start();
require_once '../usuarios/conexion.php';

// Verificación de sesión y tipo de usuario
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'contratado') {
    header("Location: login.html");
    exit();
}

$con = conectar();
if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

$id_usuario = $_SESSION['usuario']['id_usuario'];

// Obtener id_postulante y oferta
$sql = "SELECT pos.id_postulante, p.fk_oferta
        FROM postulante pos
        JOIN postulacion p ON pos.id_postulante = p.fk_postulante
        WHERE pos.fk_usuario = $id_usuario AND p.estado_postulacion = 'Aceptada'
        LIMIT 1";

$res = mysqli_query($con, $sql);
if (!$res) {
    die("Error en la consulta: " . mysqli_error($con));
}

if (mysqli_num_rows($res) === 0) {
    die("No se pudo recuperar tu información de contratación. No tienes postulaciones aceptadas.");
}

$data = mysqli_fetch_assoc($res);
$id_postulante = $data['id_postulante'];
$id_oferta = $data['fk_oferta'];

// Obtener lista de bancos para el select
$consulta_bancos = mysqli_query($con, "SELECT id_banco, nombre FROM banco");
if (!$consulta_bancos) {
    die("Error al obtener los bancos: " . mysqli_error($con));
}

$bancos = [];
if (mysqli_num_rows($consulta_bancos) > 0) {
    $bancos = mysqli_fetch_all($consulta_bancos, MYSQLI_ASSOC);
} else {
    $error_bancos = "No se encontraron bancos registrados en el sistema.";
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos
    $tipo_sangre = mysqli_real_escape_string($con, $_POST['tipo_sangre']);
    $contacto_emergencia = mysqli_real_escape_string($con, $_POST['contacto_emergencia']);
    $telefono_emergencia = mysqli_real_escape_string($con, $_POST['telefono_emergencia']);
    $nro_cuenta = mysqli_real_escape_string($con, $_POST['nro_cuenta']);
    $fk_banco = intval($_POST['fk_banco']);
    
    // Validar teléfono venezolano (11 dígitos)
    if (!preg_match('/^\d{11}$/', $telefono_emergencia)) {
        $error = "El teléfono de emergencia debe tener 11 dígitos";
    } else {
        // Insertar datos del empleado
        $sql_insert = "INSERT INTO contratado (
            tipo_sangre, 
            contacto_emergencia, 
            telefono_emergencia,
            nro_cuenta, 
            fk_banco, 
            fk_postulante, 
            fk_oferta
        ) VALUES (
            '$tipo_sangre', 
            '$contacto_emergencia', 
            '$telefono_emergencia',
            '$nro_cuenta', 
            $fk_banco, 
            $id_postulante, 
            $id_oferta
        )";

        if (mysqli_query($con, $sql_insert)) {
            $_SESSION['mensaje'] = "Tus datos se han registrado correctamente. La empresa completará el contrato próximamente.";
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Error al guardar: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos de Contratación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
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

        /* Estilos para el encabezado */
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

        /* Contenido principal */
        .main-content {
            padding-top: 120px;
            padding-bottom: 50px;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Estilos del formulario */
        .form-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .form-section:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        h1, h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .form-control, .form-select {
            background: rgba(31, 29, 29, 0.4);
            border: none;
            border-radius: 6px;
            padding: 10px 15px;
            transition: all 0.3s ease;
            color: white;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(53, 50, 50, 0.5);
            box-shadow: 0 0 0 0.25rem rgba(255, 138, 0, 0.25);
            color: white;
        }

        .form-select option {
            background: #1a1a2e;
            color: white;
        }

        .btn {
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.2);
        }

        .required:after {
            content: " *";
            color: #e52e71;
        }

        .invalid-feedback {
            color: #ff6b6b;
            font-size: 0.85rem;
        }

        .was-validated .form-control:invalid,
        .was-validated .form-select:invalid {
            border-color: #ff6b6b;
        }

        .was-validated .form-control:valid,
        .was-validated .form-select:valid {
            border-color: #38ef7d;
        }

        .alert {
            background: rgba(199, 56, 79, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(199, 56, 79, 0.3);
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .main-content > * {
            animation: fadeIn 0.6s ease forwards;
        }

        @media (max-width: 768px) {
            .main-content {
                padding-top: 100px;
            }
            
            .form-section {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1>Contratación</h1>
            </div>
            <div class="menu">
                <a href="../index.php">Volver</a>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="mb-4">Tus Datos de Contratación</h1>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <!-- Sección Datos Médicos -->
                    <div class="form-section">
                        <h3 class="mb-3">Datos Médicos</h3>
                        
                        <div class="mb-3">
                            <label for="tipo_sangre" class="form-label required">Tipo de Sangre</label>
                            <select name="tipo_sangre" class="form-select" required>
                                <option value="">Selecciona tu tipo de sangre</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor selecciona tu tipo de sangre
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contacto_emergencia" class="form-label required">Nombre Contacto Emergencia</label>
                            <input type="text" name="contacto_emergencia" class="form-control" required>
                            <div class="invalid-feedback">
                                Por favor ingresa el nombre de tu contacto de emergencia
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telefono_emergencia" class="form-label required">Teléfono Emergencia</label>
                            <input type="tel" name="telefono_emergencia" class="form-control" 
                                   pattern="[0-9]{11}" title="11 dígitos sin espacios" required>
                            <small class="text-muted">Formato: 04121234567 (11 dígitos)</small>
                            <div class="invalid-feedback">
                                Por favor ingresa un número de teléfono válido (11 dígitos)
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección Datos Bancarios -->
                    <div class="form-section">
                        <h3 class="mb-3">Datos Bancarios</h3>
                        
                        <div class="mb-3">
                            <label for="nro_cuenta" class="form-label required">Número de Cuenta</label>
                            <input type="text" name="nro_cuenta" class="form-control" 
                                   pattern="[0-9]+" title="Solo números" required>
                            <div class="invalid-feedback">
                                Por favor ingresa un número de cuenta válido
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="fk_banco" class="form-label required">Banco</label>
                            <select name="fk_banco" class="form-select" required>
                                <option value="">Selecciona tu banco</option>
                                <?php if (!empty($bancos)): ?>
                                    <?php foreach ($bancos as $banco): ?>
                                        <option value="<?= $banco['id_banco'] ?>">
                                            <?= htmlspecialchars($banco['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay bancos disponibles</option>
                                <?php endif; ?>
                            </select>
                            <?php if (isset($error_bancos)): ?>
                                <div class="text-danger small mt-2"><?= $error_bancos ?></div>
                            <?php endif; ?>
                            <div class="invalid-feedback">
                                Por favor selecciona tu banco
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="../index.php" class="btn btn-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Datos</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación del formulario
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>