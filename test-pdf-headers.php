<?php
// Script para verificar headers de PDF
// Acceder a: https://app.factorentacar.com/test-pdf-headers.php

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="test-headers.pdf"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Crear un PDF simple de prueba
require_once __DIR__ . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P'
]);

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test PDF Headers</title>
</head>
<body>
    <h1>Test de Headers PDF</h1>
    <p>Este es un PDF de prueba para verificar que los headers se envían correctamente.</p>
    <p>Fecha: ' . date('Y-m-d H:i:s') . '</p>
    <p>Si ves este contenido como PDF descargado, los headers funcionan correctamente.</p>
</body>
</html>';

$mpdf->WriteHTML($html);
$mpdf->Output('test-headers.pdf', 'D');
