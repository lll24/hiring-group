<?php
include("conexion.php");
$con = conectar();
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: crudempresa.php?error=Acceso%20denegado");
    exit();
}

// Validar datos recibidos
$required_fields = ['nombre', 'RIF', 'sector', 'direccion', 'persona_contacto', 'telefono_contacto', 'fk_usuario'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        header("Location: crudempresa.php?error=Campo%20$field%20requerido");
        exit();
    }
}

// Sanitizar datos
$nombre = mysqli_real_escape_string($con, $_POST['nombre']);
$RIF = mysqli_real_escape_string($con, $_POST['RIF']);
$sector = mysqli_real_escape_string($con, $_POST['sector']);
$direccion = mysqli_real_escape_string($con, $_POST['direccion']);
$persona_contacto = mysqli_real_escape_string($con, $_POST['persona_contacto']);
$telefono_contacto = mysqli_real_escape_string($con, $_POST['telefono_contacto']);
$fk_usuario = intval($_POST['fk_usuario']);
$creado_por = $_SESSION['usuario']['id_usuario'];

// Verificar RIF único
$check_rif = mysqli_query($con, "SELECT id_empresa FROM empresa WHERE RIF = '$RIF'");
if (mysqli_num_rows($check_rif) > 0) {
    header("Location: crudempresa.php?error=El%20RIF%20ya%20existe");
    exit();
}

// Verificar usuario único (1 empresa por usuario)
$check_user = mysqli_query($con, "SELECT id_empresa FROM empresa WHERE fk_usuario = $fk_usuario");
if (mysqli_num_rows($check_user) > 0) {
    header("Location: crudempresa.php?error=El%20usuario%20ya%20tiene%20una%20empresa");
    exit();
}

// Insertar empresa
$sql = "INSERT INTO empresa 
        (nombre, RIF, sector, direccion, persona_contacto, telefono_contacto, fk_usuario, creado_por) 
        VALUES ('$nombre', '$RIF', '$sector', '$direccion', '$persona_contacto', '$telefono_contacto', $fk_usuario, '$creado_por')";

if (mysqli_query($con, $sql)) {
    header("Location: crudempresa.php?success=1");
} else {
    header("Location: crudempresa.php?error=" . urlencode(mysqli_error($con)));
}
exit();
?>