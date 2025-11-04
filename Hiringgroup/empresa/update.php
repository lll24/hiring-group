<?php
include("conexion.php");
$con = conectar();

$id_empresa = $_POST['id_empresa'];
$nombre = $_POST['nombre'];
$RIF = $_POST['RIF'];
$sector = $_POST['sector'];
$direccion = $_POST['direccion'];
$persona_contacto = $_POST['persona_contacto'];
$telefono_contacto = $_POST['telefono_contacto'];
$fk_usuario = $_POST['fk_usuario'];


$sql = "UPDATE empresa SET 
        nombre = '$nombre',
        RIF = '$RIF',
        sector = '$sector',
        direccion = '$direccion',
        persona_contacto = '$persona_contacto',
        telefono_contacto = '$telefono_contacto',
        fk_usuario = '$fk_usuario'  
        WHERE id_empresa = '$id_empresa'";

$query = mysqli_query($con, $sql);

if($query){
    Header("Location: crudempresa.php");
} else {
    echo "Error al actualizar: " . mysqli_error($con);
}
?>