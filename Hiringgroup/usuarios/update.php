<?php

include("conexion.php");
$con=conectar();

$correo=$_POST['correo'];
$clave=$_POST['clave'];
$nombre=$_POST['nombre'];
$apellido=$_POST['apellido'];






$sql="UPDATE usuario SET correo='$correo',clave='$clave',nombre='$nombre',apellido='$apellido' WHERE correo='$correo'";
$query=mysqli_query($con,$sql);

    if($query){
        $ultimo_id = mysqli_insert_id($con);
        Header("Location: crudusuarios.php");
    }
?>