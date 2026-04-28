<?php
/**
 * Sidebar navigation menu
 * @var string $requestPath
 */

// Inicializar variables
$menuActive = '';
$submenuActive = '';

// Determinar menú activo basado en la ruta de la solicitud
if (strpos($requestPath, 'impuesto') !== false) {
    $menuActive = 'contabilidad';
    $submenuActive = 'impuestos';
} elseif (strpos($requestPath, 'metodo') !== false) {
    $menuActive = 'contabilidad';
    $submenuActive = 'metodos-pago';
} elseif (strpos($requestPath, 'gasto') !== false) {
    $menuActive = 'contabilidad';
    $submenuActive = 'gastos';
} elseif (strpos($requestPath, 'contabilidad/bancos') !== false) {
    $menuActive = 'contabilidad';
    $submenuActive = 'bancos';
} elseif (strpos($requestPath, 'contabilidad') !== false) {
    $menuActive = 'contabilidad';
} elseif (strpos($requestPath, 'recursos-humanos') !== false || strpos($requestPath, 'empleado') !== false || strpos($requestPath, 'vacacion') !== false || strpos($requestPath, 'planilla') !== false) {
    $menuActive = 'recursos-humanos';
} elseif (strpos($requestPath, 'configuracion') !== false) {
    $menuActive = 'configuracion';
}
