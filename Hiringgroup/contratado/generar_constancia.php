<?php
session_start();
require('../fpdf/fpdf.php');
require_once '../usuarios/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'contratado') {
    header("Location: ../login.html");
    exit();
}

$con = conectar();
$id_usuario = $_SESSION['usuario']['id_usuario'];

// Obtener id_postulante
$sql_postulante = "SELECT p.id_postulante, u.nombre, u.apellido, o.cargo, e.nombre AS empresa
                  FROM postulante p
                  JOIN usuario u ON p.fk_usuario = u.id_usuario
                  JOIN contratado c ON p.id_postulante = c.fk_postulante
                  JOIN ofertalaboral o ON c.fk_oferta = o.id_oferta
                  JOIN empresa e ON o.fk_empresa = e.id_empresa
                  WHERE p.fk_usuario = $id_usuario
                  LIMIT 1";



$res_postulante = mysqli_query($con, $sql_postulante);

if (!$res_postulante || mysqli_num_rows($res_postulante) === 0) {
    echo "No se pudo recuperar la información.";
    exit();
}

$data = mysqli_fetch_assoc($res_postulante);
$id_postulante = $data['id_postulante'];
$nombre = $data['nombre'];
$apellido = $data['apellido'];
$cargo = $data['cargo'];
$empresa = $data['empresa'];


// Obtener contrato
$sql_contrato = "SELECT fecha_inicio, salario_mensual FROM contratado WHERE fk_postulante = $id_postulante LIMIT 1";
$res_contrato = mysqli_query($con, $sql_contrato);

if (!$res_contrato || mysqli_num_rows($res_contrato) === 0) {
    echo "No se encontró información del contrato.";
    exit();
}

$contrato = mysqli_fetch_assoc($res_contrato);
$fecha_inicio = date("d/m/Y", strtotime($contrato['fecha_inicio']));
$salario = number_format($contrato['salario_mensual'], 2, ',', '.');
$fecha_actual = date("d/m/Y");

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'I', 16);
$pdf->Cell(0, 10, utf8_decode('A QUIEN PUEDA INTERESAR'), 0, 1, 'C');

$pdf->Ln(10); // Salto de línea para separar del cuerpo

$pdf->SetFont('Arial', 'I', 12);

$texto = "Por medio de la presente la empresa HIRING GROUP hace constar que el ciudadano(a) $nombre $apellido labora con nosotros desde $fecha_inicio cumpliendo funciones en el cargo de $cargo en la empresa $empresa devengando un salario mensual de Bs. $salario.

Constancia que se pide por la parte interesada en la ciudad de Puerto Ordaz en fecha $fecha_actual.";

$pdf->MultiCell(0, 10, utf8_decode($texto));


$pdf->SetFont('Arial','I',12);

$texto = "Por medio de la presente la empresa HIRING GROUP hace constar que el ciudadano(a) $nombre $apellido labora con nosotros desde $fecha_inicio cumpliendo funciones en el cargo de $cargo en la empresa $empresa devengando un salario mensual de Bs. $salario.

Constancia que se pide por la parte interesada en la ciudad de Puerto Ordaz en fecha $fecha_actual.";

$pdf->MultiCell(0, 10, utf8_decode($texto));

$pdf->Ln(20);
$pdf->Cell(0, 10, '__________________________', 0, 1, 'C');
$pdf->Cell(0, 10, 'Firma y Sello', 0, 1, 'C');

$pdf->Output("I", "constancia_{$nombre}_{$apellido}.pdf");
exit();