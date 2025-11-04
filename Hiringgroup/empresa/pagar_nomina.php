<?php
include 'conexion.php';

if (!isset($_GET['id'])) {
    echo "ID de nómina no especificado.";
    exit;
}

$con = conectar();
$id_nomina = intval($_GET['id']);

// Obtener mes y año de la nómina
$sql_nomina = "SELECT mes, anio FROM nominamensual WHERE id_nomina = $id_nomina";
$res_nomina = mysqli_query($con, $sql_nomina);
$nomina = mysqli_fetch_assoc($res_nomina);

$mes = $nomina['mes'];
$anio = $nomina['anio'];

// Obtener todos los detalles de esa nómina
$sql_detalles = "SELECT * FROM nomina_detalle WHERE fk_nomina = $id_nomina";
$res_detalles = mysqli_query($con, $sql_detalles);

while ($detalle = mysqli_fetch_assoc($res_detalles)) {
    $id_detalle = $detalle['id_detalle'];
    $salario_base = $detalle['salario_base'];
    $ivss = $detalle['ivss'];
    $inces = $detalle['inces'];
    $hiring = $detalle['hiring_group'];
    $salario_neto = $salario_base - $ivss - $inces - $hiring;
    $fk_contratacion = $detalle['fk_contratado'];

    // Verificar si ya se pagó este detalle
    $verificar = mysqli_query($con, "SELECT COUNT(*) as total FROM recibopago WHERE fk_nomina_detalle = $id_detalle");
    $row_ver = mysqli_fetch_assoc($verificar);

    if ($row_ver['total'] == 0) {
        // Calcular porcentajes
         $salario_neto = $salario_base - $ivss - $inces - $hiring;

    $sql_insert = "INSERT INTO recibopago (
        mes, anio, salario_base, monto_inces, monto_ivss, monto_hiring, salario_neto, fk_contratacion, fk_nomina_detalle
    ) VALUES (
        $mes, $anio, $salario_base, $inces, $ivss, $hiring, $salario_neto, $fk_contratacion, $id_detalle
    )";

        $sql_usuario = "SELECT p.fk_usuario 
                FROM contratado c
                JOIN postulante p ON c.fk_postulante = p.id_postulante
                WHERE c.id_contratacion = $fk_contratacion";
                $res_usuario = mysqli_query($con, $sql_usuario);
                $row_usuario = mysqli_fetch_assoc($res_usuario);
                $id_usuario = $row_usuario['fk_usuario'];
        
        $mensaje = "💰 Se ha registrado tu pago del mes. Revisa tu nómina.";
        $fecha_actual = date('Y-m-d H:i:s');

        // Insertar notificación
       $sql_notif = "INSERT INTO notificacion (fk_usuario, mensaje, fecha, leido) 
              VALUES ($id_usuario, '$mensaje', '$fecha_actual', 0)";
        mysqli_query($con, $sql_notif);

        if (!mysqli_query($con, $sql_insert)) {
            echo "Error al insertar recibopago: " . mysqli_error($con);
            exit;
        }
    }
}

mysqli_close($con);
header("Location: ver_detalles_nomina.php?id=$id_nomina&pagado=1");
exit;
?>
