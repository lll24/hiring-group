<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $clave  = isset($_POST['clave']) ? trim($_POST['clave']) : '';
    $tipo_usuario = isset($_POST['tipo_usuario']) ? $_POST['tipo_usuario'] : 'postulante';

    if (empty($nombre) || empty($correo) || empty($clave)) {
        echo "<script>
            alert('Todos los campos son obligatorios.');
            window.location.href = 'registrar.php';
        </script>";
        exit();
    }

    $conexion = mysqli_connect("localhost", "root", "", "hiring_group");

    if (!$conexion) {
        die("Conexión fallida: " . mysqli_connect_error());
    }

    $consulta_check = "SELECT id_usuario FROM usuario WHERE correo = '$correo'";
    $resultado_check = mysqli_query($conexion, $consulta_check);

    if (mysqli_num_rows($resultado_check) > 0) {
        echo "<script>
            alert('Este correo ya está registrado.');
            window.location.href = 'registrar.php';
        </script>";
        mysqli_close($conexion);
        exit();
    }

    $consulta_insert = "INSERT INTO usuario (nombre, apellido, correo, clave, tipo_usuario)
                        VALUES ('$nombre', '$apellido', '$correo', '$clave', '$tipo_usuario')";

    if (mysqli_query($conexion, $consulta_insert)) {
        echo "<script>
            alert('Usuario registrado correctamente.');
            window.location.href = '../login.html';
        </script>";
    } else {
        echo "Error al registrar usuario: " . mysqli_error($conexion);
    }

    mysqli_close($conexion);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Usuario</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,700" rel="stylesheet">
  <link rel="stylesheet" href="./style.css">

  <style>
    .form-signup {
      top: 0 !important;
    }

    .form-signup-left {
      transform: translateX(-399px);
      opacity: 1;
      top: 0 !important;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="frame frame-long">
      <div class="nav">
        <ul>
          <li class="signup-active"><a class="btn">Registro</a></li>
        </ul>
      </div>
      <form class="form-signup form-signup-left" method="POST" action="registrar.php">
        <label for="nombre">Nombre</label>
        <input class="form-styling" type="text" name="nombre" required />

        <label for="apellido">Apellido</label>
        <input class="form-styling" type="text" name="apellido" required />

        <label for="correo">Correo</label>
        <input class="form-styling" type="email" name="correo" required />

        <label for="clave">Clave</label>
        <input class="form-styling" type="password" name="clave" required />

        <label for="tipo_usuario">Tipo de Usuario</label>
        <select class="form-styling" name="tipo_usuario" required>
          <option value="postulante">Postulante</option>
          <option value="empresa">Empresa</option>
        </select>

        <div class="btn-animate">
          <button class="btn-signup" type="submit">Registrar</button>
        </div>

        <div class="btn-animate">
          <button class="btn-signup" type="button" onclick="window.location.href='../login.html'">Volver al login</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
