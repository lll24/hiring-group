<?php 
include("conexion.php");
$con = conectar();

if (isset($_GET['id'])) {
    $id_empresa = $_GET['id'];

    $sql = "DELETE FROM empresa WHERE id_empresa='$id_empresa'";
    $query = mysqli_query($con, $sql);

    if ($query) {
        header("Location: crudempresa.php");
        exit();
    } else {
        echo "Error al eliminar usuario: " . mysqli_error($con);
    }
} else {
    echo "ID no especificado";
}
?>
