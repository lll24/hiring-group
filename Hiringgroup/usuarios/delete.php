<?php 
include("conexion.php");
$con = conectar();

if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];
    
    // Primero eliminamos las notificaciones relacionadas
    $sql_delete_notificaciones = "DELETE FROM notificacion WHERE fk_usuario='$id_usuario'";
    $query_notificaciones = mysqli_query($con, $sql_delete_notificaciones);
    
    if ($query_notificaciones) {
        // Luego eliminamos el usuario
        $sql_delete_usuario = "DELETE FROM usuario WHERE id_usuario='$id_usuario'";
        $query_usuario = mysqli_query($con, $sql_delete_usuario);
        
        if ($query_usuario) {
            header("Location: crudusuarios.php");
            exit();
        } else {
            echo "Error al eliminar usuario: " . mysqli_error($con);
        }
    } else {
        echo "Error al eliminar notificaciones relacionadas: " . mysqli_error($con);
    }
} else {
    echo "ID no especificado";
}
?>
