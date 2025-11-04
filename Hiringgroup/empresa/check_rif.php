<?php
include("conexion.php");
$con = conectar();

header('Content-Type: application/json');

if(isset($_POST['RIF'])) {
    $RIF = trim($_POST['RIF']);
    $consulta = "SELECT id_empresa FROM empresa WHERE RIF = '$RIF'";
    $resultado = mysqli_query($con, $consulta);
    
    echo json_encode([
        'exists' => mysqli_num_rows($resultado) > 0
    ]);
} else {
    echo json_encode([
        'exists' => false,
        'error' => 'No se recibió el RIF'
    ]);
}

mysqli_close($con);
?>