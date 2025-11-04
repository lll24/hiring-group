<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso inválido. Por favor ingresa por el formulario de login.');
}

var_dump($_POST);

$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$clave  = isset($_POST['clave']) ? trim($_POST['clave']) : '';

echo "<br>Correo recibido: [$correo]<br>";
echo "Clave recibida: [$clave]<br>";

$conexion = mysqli_connect("localhost", "root", "", "hiring_group");

if (!$conexion) {
    die("Conexión fallida: " . mysqli_connect_error());
}

$consulta = "SELECT * FROM usuario WHERE correo='$correo' AND clave='$clave'";
$resultado = mysqli_query($conexion, $consulta);

echo "Consulta ejecutada: $consulta<br>";

if ($resultado) {
    $filas = mysqli_num_rows($resultado);
    echo "Filas encontradas: $filas<br>";
    if ($filas === 1) {
        $usuario = mysqli_fetch_assoc($resultado);
        $_SESSION['usuario'] = $usuario;
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
        $_SESSION['id_usuario'] = $usuario['id_usuario'];

        echo "Login exitoso. Redirigiendo...";
        header("Location: /hiring-group/index.php");
        exit();
    } else {
        echo "<script>
        alert('Correo o clave incorrectos.');
        window.location.href = '/hiring-group/login.html';
    </script>";
    }
} else {
    echo "Error en la consulta: " . mysqli_error($conexion);
}

mysqli_close($conexion);
?>
