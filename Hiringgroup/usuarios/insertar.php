<?php
include("conexion.php");
$con = conectar();

$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$correo = $_POST['correo'];
$clave = $_POST['clave'];
$tipo_usuario = $_POST['tipo_usuario'];

// No incluir id_usuario, MySQL lo genera solo
$sql = "INSERT INTO usuario (nombre, apellido, correo, clave, tipo_usuario) 
        VALUES ('$nombre', '$apellido', '$correo', '$clave', '$tipo_usuario')";

$query = mysqli_query($con, $sql);

if ($query) {
    $ultimo_id = mysqli_insert_id($con); // ← si quieres usar el id generado
    header("Location: crudusuarios.php");
} else {
    echo "Error al insertar usuario: " . mysqli_error($con);
}
?>
