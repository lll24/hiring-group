<?php 
include("conexion.php");
$con = conectar();

// Obtener datos para mostrar
$sql = "SELECT * FROM empresa";
$query = mysqli_query($con, $sql);

// Obtener usuarios tipo empresa
$sql_usuarios = "SELECT id_usuario, nombre, apellido FROM usuario WHERE tipo_usuario = 'empresa'";
$query_usuarios = mysqli_query($con, $sql_usuarios);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestor de Empresas</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        .header-empresas {
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
        
        .header-empresas h2 {
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
        
        /* Contenido principal */
        .container {
            max-width: 1200px;
            margin-top: 30px;
            padding-bottom: 50px;
        }
        
        .card-empresa {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 600;
        }
        
        .form-label {
            font-weight: 500;
            color: #fff;
        }
        
        .form-control, .form-select, .select2-selection {
            background: rgba(39, 37, 37, 0.4) !important;
            border: none !important;
        }
        
        .select2-container--default .select2-results__option {
    color: #000 !important; /* Texto negro */
    background-color: rgba(30, 28, 28, 0.5) !important; /* Fondo blanco para mejor contraste */
    padding: 8px 12px;
}

/* Estilo al pasar el mouse */
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color:rgb(53, 47, 47) !important; /* Fondo gris claro al hover */
    color: #000 !important; /* Texto negro */
}

/* Estilo del dropdown */
.select2-dropdown {
    background-color: #fff !important; /* Fondo blanco */
    border: 1px solid #ddd !important; /* Borde gris */
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

/* Estilo del campo de búsqueda (si lo tienes habilitado) */
.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ddd !important;
    color: #000 !important;
    background-color: #fff !important;
}

/* Estilo del texto seleccionado */
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff !important; /* Texto blanco en el selector */
}

/* Estilo del contenedor principal */
.select2-container--default .select2-selection--single {
    background-color: rgba(39, 37, 37, 0.4) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    height: 38px !important;
}

        
        
        .btn-primary {
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            border: none;
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .btn-warning {
            background: linear-gradient(90deg, #ffc107, #fd7e14);
            border: none;
            border-radius: 30px;
            padding: 6px 12px;
            font-weight: 500;
        }
        
        .btn-danger {
            background: linear-gradient(90deg, #dc3545, #a71d2a);
            border: none;
            border-radius: 30px;
            padding: 6px 12px;
            font-weight: 500;
        }
        
        .table {
            color: #fff;
        }
        
        .table-dark {
            background: rgba(0, 0, 0, 0.3);
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .alert {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .is-invalid {
            border-color:rgb(0, 0, 0) !important;
        }
        
        .invalid-feedback {
            color:rgb(0, 0, 0);
        }

        
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-empresas">
        <div class="header-content">
            <h2>Gestión de Empresas</h2>
            <a href="../index.php" class="btn btn-primary">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
        </div>
    </div>

    <div class="container py-4">
        <!-- Mostrar mensaje de éxito/error -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i> Empresa registrada exitosamente!
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Error: <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <!-- Card para agregar empresa -->
        <div class="card card-empresa mb-4">
            <div class="card-header">
                <h5 class="mb-0">Agregar Nueva Empresa</h5>
            </div>
            <div class="card-body">
                <form id="form-empresa" action="insertar.php" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">RIF</label>
                                <input type="text" class="form-control" name="RIF" id="RIF" required>
                                <div id="rif-error" class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sector</label>
                                <input type="text" class="form-control" name="sector" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" class="form-control" name="direccion" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Persona de Contacto</label>
                                <input type="text" class="form-control" name="persona_contacto" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teléfono de Contacto</label>
                                <input type="text" class="form-control" name="telefono_contacto" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario Asociado</label>
                        <select class="form-control select2" name="fk_usuario" required>
                            <option value="">Seleccione un usuario</option>
                            <?php while($usuario = mysqli_fetch_assoc($query_usuarios)): ?>
                                <option  value="<?= $usuario['id_usuario'] ?>">
                                    <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?> (ID: <?= $usuario['id_usuario'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Agregar Empresa
                    </button>
                </form>
            </div>
        </div>

        <!-- Card para listado de empresas -->
        <div class="card card-empresa">
            <div class="card-header">
                <h5 class="mb-0">Listado de Empresas</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-dark table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>RIF</th>
                            <th>Sector</th>
                            <th>Dirección</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_array($query)): ?>
                            <tr>
                                <td><?= $row['id_empresa'] ?></td>
                                <td><?= htmlspecialchars($row['nombre']) ?></td>
                                <td><?= htmlspecialchars($row['RIF']) ?></td>
                                <td><?= htmlspecialchars($row['sector']) ?></td>
                                <td><?= htmlspecialchars($row['direccion']) ?></td>
                                <td><?= htmlspecialchars($row['persona_contacto']) ?></td>
                                <td><?= htmlspecialchars($row['telefono_contacto']) ?></td>
                                <td>
                                    <?php 
                                    $usuario_id = $row['fk_usuario'];
                                    $sql_user = "SELECT nombre, apellido FROM usuario WHERE id_usuario = '$usuario_id'";
                                    $query_user = mysqli_query($con, $sql_user);
                                    $user_data = mysqli_fetch_assoc($query_user);
                                    echo htmlspecialchars($user_data['nombre'] . ' ' . $user_data['apellido']);
                                    ?>
                                </td>
                                <td>
                                    <a href="actualizar.php?id=<?= $row['id_empresa'] ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $row['id_empresa'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta empresa?')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializar Select2
        $('.select2').select2({
            placeholder: "Seleccione un usuario",
            width: '100%'
        });

        // Validar RIF en tiempo real
        $('#RIF').on('blur', function() {
            const rif = $(this).val().trim();
            const errorDiv = $('#rif-error');
            
            if(rif.length > 0) {
                $.ajax({
                    url: 'check_rif.php',
                    type: 'POST',
                    data: { RIF: rif },
                    dataType: 'json',
                    success: function(response) {
                        if(response.exists) {
                            errorDiv.text('Este RIF ya está registrado').show();
                            $(this).addClass('is-invalid');
                        } else {
                            errorDiv.text('').hide();
                            $(this).removeClass('is-invalid');
                        }
                    },
                    error: function() {
                        errorDiv.text('Error al verificar RIF').show();
                    }
                });
            }
        });

        // Validar antes de enviar
        $('#form-empresa').on('submit', function(e) {
            const rif = $('#RIF').val().trim();
            const errorDiv = $('#rif-error');
            
            if(rif.length === 0) {
                errorDiv.text('El RIF es obligatorio').show();
                e.preventDefault();
                return;
            }
            
            // Verificación síncrona para asegurar que no existe
            let rifExists = false;
            $.ajax({
                url: 'check_rif.php',
                type: 'POST',
                data: { RIF: rif },
                async: false,
                dataType: 'json',
                success: function(response) {
                    rifExists = response.exists;
                }
            });
            
            if(rifExists) {
                errorDiv.text('Este RIF ya está registrado').show();
                e.preventDefault();
                alert('No se puede registrar: El RIF ya existe');
            }
        });
    });
    </script>
</body>
</html>