<?php
include("../../config/connection.php");
require('../../libraries/fpdf1_86/fpdf.php');

$id = $_GET['id_venta'];

$result = $conn->query("SELECT `tipo_cliente`, 
                                `nombre_cliente`, 
                                `nombre_empresa`, 
                                `razon_social`, 
                                `nit`, 
                                `fecha_venta`, 
                                `total_sin_iva`, 
                                `total_iva` 
                        FROM `ventas` 
                        WHERE `id_venta` = $id");
$venta = $result->fetch_assoc();

$result_detalle = $conn->query("SELECT vp.id_producto,
                                        p.nombre_producto,
                                        p.codigo_producto,
                                        vp.cantidad_vendida, 
                                        vp.subtotal, 
                                        vp.total 
                                    FROM venta_producto as vp
                                    INNER JOIN productos as p ON p.id_producto = vp.id_producto
                                    WHERE vp.id_venta = $id");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(0, 10, 'Factura', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
if ($venta['tipo_cliente'] === 'natural') {
    $pdf->Cell(0, 10, 'Cliente: ' . $venta['nombre_cliente'], 0, 1);
} else {
    $pdf->Cell(0, 10, 'Empresa: ' . $venta['nombre_empresa'], 0, 1);
    $pdf->Cell(0, 10, 'Razon Social: ' . $venta['razon_social'], 0, 1);
    $pdf->Cell(0, 10, 'NIT: ' . $venta['nit'], 0, 1);
}
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y', strtotime($venta['fecha_venta'])), 0, 1);
$pdf->Ln(10);

$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(25, 10, 'Codigo', 1, 0, 'C', true);
$pdf->Cell(80, 10, 'Producto', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Subtotal', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Total', 1, 1, 'C', true);

$total = 0;
$cantidad_total = 0;
$subtotal_total = 0;

while ($detalle = $result_detalle->fetch_assoc()) {
    $total += $detalle['total'];
    $cantidad_total += $detalle['cantidad_vendida'];
    $subtotal_total += $detalle['subtotal'];

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 8, $detalle['codigo_producto'], 1);
    $pdf->Cell(80, 8, $detalle['nombre_producto'], 1);
    $pdf->Cell(25, 8, $detalle['cantidad_vendida'], 1);
    $pdf->Cell(25, 8, '$' . number_format($detalle['subtotal'], 2), 1);
    $pdf->Cell(25, 8, '$' . number_format($detalle['total'], 2), 1);
    $pdf->Ln();
}

$pdf->Cell(105, 8, 'Total:', 1);
$pdf->Cell(25, 8, $cantidad_total, 1);
$pdf->Cell(25, 8, '$' . number_format($subtotal_total, 2), 1);
$pdf->Cell(25, 8, '$' . number_format($total, 2), 1);

$pdf->Output('I', 'factura.pdf');
?>
