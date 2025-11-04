<?php
require('../fpdf/fpdf.php');
require('conexion.php');

class PDF extends FPDF
{
    // Header
    function Header()
    {   $this->SetX(10);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 10, 'Reporte de Usuarios', 0, 1, 'C');
        $this->Ln(8); // Ajusta el espacio debajo del título

        $this->SetX(45);
        $this->Cell(42, 6, 'Correo', 1, 0, 'C');
        $this->Cell(25, 6, 'Nombre', 1, 0, 'C');
        $this->Cell(25, 6, 'Apellido', 1, 0, 'C');
        $this->Cell(28, 6, 'Tipo de Usuario', 1, 1, 'C');

        
       
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Usa la función conectar() para obtener la conexión
$mysqli = conectar();
$consulta = "SELECT * FROM usuario";
$resultado = $mysqli->query($consulta);

// Verifica si la consulta falló
if (!$resultado) {
    die("Error en la consulta: " . $mysqli->error);
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Agrega los datos al PDF
while ($row = $resultado->fetch_assoc()) {
    $pdf->SetX(45);
    $pdf->Cell(42, 6, $row['correo'], 1, 0, 'C');
    $pdf->Cell(25, 6, $row['nombre'], 1, 0, 'C');
    $pdf->Cell(25, 6, $row['apellido'], 1, 0, 'C');
    $pdf->Cell(28, 6, $row['tipo_usuario'], 1, 1, 'C');
    
}

$pdf->Output();
?>