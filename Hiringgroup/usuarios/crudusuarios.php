<?php 
    include("conexion.php");
    $con=conectar();

    $sql="SELECT *  FROM usuario";
    $query=mysqli_query($con,$sql);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title> Gestor de Usuarios</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            .contenedor2 {
                padding-top: 120px;
                padding-bottom: 50px;
                width: 90%;
                max-width: 1200px;
                margin: 0 auto;
            }

            .titulazo {
                font-family: 'Montserrat', sans-serif;
                font-weight: 600;
                margin-bottom: 30px;
                background: linear-gradient(90deg, #ff8a00, #e52e71);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }

            /* Formulario */
            .form-control {
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: white;
                padding: 12px 15px;
                margin-bottom: 15px;
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

            /* Tabla */
            .table {
                color: white;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 10px;
                overflow: hidden;
                margin-top: 20px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            }

            .table thead th {
                background: rgba(0, 0, 0, 0.3);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-size: 14px;
                padding: 15px;
            }

            .table tbody tr {
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                transition: all 0.3s ease;
            }

            .table tbody tr:hover {
                background: rgba(255, 255, 255, 0.05);
            }

            .table tbody td {
                padding: 12px 15px;
                vertical-align: middle;
            }

            /* Botones */
            .btn {
                padding: 8px 20px;
                font-weight: 500;
                border-radius: 30px;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                border: none;
            }

            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 7px 20px rgba(0, 0, 0, 0.15);
            }

            .btn-primary {
                background: linear-gradient(90deg, #ff8a00, #e52e71);
            }

            .btn-info {
                background: linear-gradient(90deg, #00b4db, #0083b0);
            }

            .btn-danger {
                background: linear-gradient(90deg, #f85032, #e73827);
            }

            .btn-success {
                background: linear-gradient(90deg, #11998e, #38ef7d);
            }

            /* Badges para tipos de usuario */
            .badge-admin {
                background: linear-gradient(135deg, #667eea, #764ba2);
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 12px;
                text-transform: uppercase;
            }

            .badge-empresa {
                background: linear-gradient(135deg, #11998e, #38ef7d);
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 12px;
                text-transform: uppercase;
            }

            .badge-hiring-group {
                background: linear-gradient(135deg, #f46b45, #eea849);
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 12px;
                text-transform: uppercase;
            }

            .badge-postulante {
                background: linear-gradient(135deg, #4b6cb7, #182848);
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 12px;
                text-transform: uppercase;
            }


            /* Animaciones */
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .contenedor2 > * {
                animation: fadeIn 0.6s ease forwards;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .menu {
                    display: none;
                }
                
                .contenedor2 {
                    padding-top: 100px;
                    width: 95%;
                }
                
                .table-responsive {
                    overflow-x: auto;
                }
            }
        </style>
    </head>
    <body>
        <header class="header">
            <div class="container">
                <div class="logo">
                    <h1>Gestor de Usuarios</h1>
                </div>
                <nav class="menu">
                    <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
                    <a href="#" class="active"><i class="fas fa-users"></i> Usuarios</a>
                </nav>
            </div>
        </header>

        <div class="contenedor2">
            <div class="container mt-5">
                <div class="row"> 
                    <div class="col-md-12">
                        <h4 class="titulazo">Usuarios Para el Sistema</h4>
                        <form action="insertar.php" method="POST" class="mb-5">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control mb-3" name="nombre" placeholder="Nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control mb-3" name="apellido" placeholder="Apellido" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control mb-3" name="correo" placeholder="Correo" required> 
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control mb-3" name="clave" placeholder="Clave" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-control mb-3" name="tipo_usuario" required>
                                        <option value="" disabled selected>Seleccione el tipo de usuario</option>
                                        <option id="options" value ="admin">Admin</option>
                                        <option id="options" value ="empresa">Empresa</option>
                                        <option id="options"value ="hiring-group">Hiring Group</option>
                                        <option id="options"value ="postulante">Postulante</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="submit" class="btn btn-primary w-100" value="Agregar Usuario">
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Correo</th>
                                        <th>Tipo de Usuario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row=mysqli_fetch_array($query)): ?>
                                        <tr>
                                            <td><?php echo $row['nombre'] ?></td>
                                            <td><?php echo $row['apellido'] ?></td>
                                            <td><?php echo $row['correo'] ?></td>
                                            <td>
                                                <span class="badge-<?php echo $row['tipo_usuario'] ?>">
                                                    <?php echo $row['tipo_usuario'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="actualizar.php?id=<?php echo $row['id_usuario'] ?>" class="btn btn-info me-2">Editar</a>
                                                    <a href="delete.php?id=<?php echo $row['id_usuario'] ?>" class="btn btn-danger me-2">Eliminar</a>
                                                    
                                                </div>
                                                
                                            </td>
                                        </tr>
                                       
                                    <?php endwhile; ?>
                                    <a href="reporte.php?id=<?php echo $row['correo'] ?>" class="btn btn-success" target="_blank">Reporte de Usuarios</a>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>  
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>