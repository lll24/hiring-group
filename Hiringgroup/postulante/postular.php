<?php
require_once 'conexion.php';
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo_usuario'] !== 'postulante') {
    header('Location: ../login.php');
    exit();
}

$con = conectar();
$id_usuario = $_SESSION['id_usuario'];
$id_oferta = isset($_POST['id_oferta']) ? intval($_POST['id_oferta']) : 0;
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if ($id_oferta <= 0 || ($accion !== 'postular' && $accion !== 'cancelar')) {
    $_SESSION['error'] = 'Solicitud inválida.';
    header('Location: listar_ofertas.php');
    exit();
}

// Obtener id_postulante
$res = mysqli_query($con, "SELECT id_postulante FROM postulante WHERE fk_usuario = $id_usuario LIMIT 1");
if (!$res || mysqli_num_rows($res) === 0) {
    $_SESSION['error'] = 'No se encontró el perfil de postulante.';
    header('Location: listar_ofertas.php');
    exit();
}
$id_postulante = mysqli_fetch_assoc($res)['id_postulante'];

if ($accion === 'postular') {
    // Insertar si no existe
    $check = mysqli_query($con, "SELECT * FROM postulacion WHERE fk_postulante = $id_postulante AND fk_oferta = $id_oferta");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = 'Ya estás postulado a esta oferta.';
    } else {
        $insert = mysqli_query($con, "INSERT INTO postulacion (fk_postulante, fk_oferta, estado_postulacion, fecha_postulacion)
                                      VALUES ($id_postulante, $id_oferta, 'Pendiente', NOW())");
        $_SESSION['mensaje'] = $insert ? 'Postulación registrada exitosamente.' : 'Error al postularte.';
    }

} elseif ($accion === 'cancelar') {
    // Eliminar si existe
    $delete = mysqli_query($con, "DELETE FROM postulacion WHERE fk_postulante = $id_postulante AND fk_oferta = $id_oferta");
    $_SESSION['mensaje'] = $delete ? 'Postulación cancelada correctamente.' : 'No se pudo cancelar la postulación.';
}

header('Location: listar_ofertas.php');
exit();
?>
