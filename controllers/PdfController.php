<?php

namespace app\controllers;

use Yii;
use app\models\Rental;
use app\models\Order;
use app\models\CompanyConfig;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use TCPDF;
use Mpdf\Mpdf;

/**
 * PdfController maneja la generación de PDFs para órdenes y alquileres
 */
class PdfController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Solo usuarios autenticados
                    ],
                ],
            ],
        ];
    }

    /**
     * Generar PDF y guardarlo en servidor
     */
    public function actionGenerateRentalPdf($id)
    {
        $rental = $this->findRental($id);
        $companyInfo = CompanyConfig::getCompanyInfo();
        
        // Limpiar buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Desactivar compresión
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        @ini_set('output_buffering', 0);
        
        // Crear PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Facto Rent a Car');
        $pdf->SetAuthor('Facto Rent a Car');
        $pdf->SetTitle('Orden de Alquiler - ' . $rental->rental_id);
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->SetFont('dejavusans', '', 10);
        
        // Agregar página
        $pdf->AddPage();
        
        // Generar contenido
        $html = $this->generateRentalOrderHtml($rental, $companyInfo);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Agregar segunda página con condiciones (prioridad: alquiler > global > archivo)
        $customConditions = $rental->condiciones_especiales ?? '';
        $globalConditions = CompanyConfig::getConfig('rental_conditions_html', '');
        if (!empty($customConditions) || !empty($globalConditions) || $companyInfo['conditions']) {
            $pdf->AddPage();
            $conditionsHtml = $this->generateConditionsHtml($companyInfo, $customConditions ?: $globalConditions);
            $pdf->writeHTML($conditionsHtml, true, false, true, false, '');
        }
        
        // Generar nombre del archivo
        $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '.pdf';
        $filepath = Yii::getAlias('@app') . '/pdfs/' . $filename;
        
        // Guardar PDF en disco
        $pdf->Output($filepath, 'F');
        
        return json_encode([
            'success' => true,
            'filename' => $filename,
            'url' => '/pdf/download-rental?id=' . $id
        ]);
    }

    /**
     * Descargar PDF desde carpeta
     */
    public function actionDownloadRental($id)
    {
        // Limpiar TODOS los buffers de salida ANTES de cualquier cosa
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Desactivar completamente el output buffering
        @ini_set('output_buffering', 0);
        @ini_set('zlib.output_compression', 0);
        @ini_set('zlib.output_compression_level', 0);
        
        // Desactivar compresión de Apache si está disponible
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
            @apache_setenv('no-gzip', '1');
        }
        
        $rental = $this->findRental($id);
        $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '.pdf';
        $filepath = Yii::getAlias('@app') . '/runtime/' . $filename;
        
        if (!file_exists($filepath)) {
            throw new NotFoundHttpException('El archivo PDF no existe. Por favor, genere el PDF primero.');
        }
        
        // Usar headers nativos de PHP para evitar interferencia de Cloudflare
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        
        // Leer y enviar archivo
        $handle = fopen($filepath, 'rb');
        if ($handle === false) {
            throw new \Exception('No se puede abrir el archivo PDF.');
        }
        
        // Enviar archivo en chunks para archivos grandes
        while (!feof($handle)) {
            echo fread($handle, 8192);
            flush();
        }
        
        fclose($handle);
        exit;
    }

    /**
     * Verificar si existe PDF
     */
    public function actionCheckRentalPdf($id)
    {
        $rental = $this->findRental($id);
        $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '.pdf';
        $filepath = Yii::getAlias('@app') . '/runtime/' . $filename;
        
        return json_encode([
            'exists' => file_exists($filepath),
            'url' => '/pdf/download-rental?id=' . $id
        ]);
    }

    /**
     * Preview del PDF (genera HTML para mostrar en el modal)
     */
    public function actionPreviewPdf($id)
    {
        $rental = $this->findRental($id);
        $companyInfo = CompanyConfig::getCompanyInfo();
        
        // Renderizar el HTML del PDF
        $html = $this->renderPartial('_rental-pdf', [
            'model' => $rental,
            'companyInfo' => $companyInfo
        ], true);
        
        // Retornar el HTML para mostrarlo en el iframe
        return $this->renderPartial('_pdf-preview', [
            'html' => $html
        ]);
    }

    /**
     * Generar PDF de forma asíncrona (retorna JSON)
     */
    public function actionGenerateMpdfAsync($id)
    {
        // Configurar respuesta JSON
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $rental = $this->findRental($id);
            $companyInfo = CompanyConfig::getCompanyInfo();
            
            // Cargar mPDF
            require_once Yii::getAlias('@vendor/autoload.php');
            
            // Crear directorio para PDFs asíncronos
            $pdfDir = Yii::getAlias('@app') . '/runtime/pdfs';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0777, true);
            }
            
            // Crear directorio temporal personalizado para mPDF
            $tempDir = Yii::getAlias('@app') . '/runtime/mpdf_temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 20,
                'margin_bottom' => 20,
                'default_font' => 'arial',
                'tempDir' => $tempDir,
                'autoScriptToLang' => false,
                'autoLangToFont' => false,
                'debug' => false,
                'simpleTables' => true,
                'useSubstitutions' => false,
                'shrink_tables_to_fit' => 1,
                'max_colH_correction' => 1.8,
                'mirrorMargins' => false,
                'use_kwt' => false,
                'showImageErrors' => false,
                'img_dpi' => 96,
                'dpi' => 96,
            ]);
            
            // Generar HTML usando la vista completa del PDF
            $html = $this->renderPartial('_rental-pdf', [
                'model' => $rental,
                'companyInfo' => $companyInfo
            ], true);
            
            // Log del HTML generado para debug
            Yii::info('HTML generado para PDF asíncrono: ' . substr($html, 0, 500) . '...', 'pdf');
            Yii::info('Tamaño del HTML: ' . strlen($html) . ' caracteres', 'pdf');
            
            // Verificar que el HTML no esté vacío
            if (empty(trim($html))) {
                throw new \Exception('El HTML generado está vacío');
            }
            
            // Limpiar HTML de caracteres problemáticos
            $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html);
            $html = str_replace(["\r\n", "\r"], "\n", $html);
            
            // Limpiar espacios en blanco excesivos que pueden causar páginas vacías
            $html = preg_replace('/\n{3,}/', "\n\n", $html);
            $html = preg_replace('/\s{4,}/', ' ', $html);
            
            // Escribir HTML al PDF con configuración específica
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            
            // Verificar que el PDF se generó correctamente
            $pageCount = $mpdf->page;
            Yii::info('Número de páginas generadas: ' . $pageCount, 'pdf');
            
            if ($pageCount == 0) {
                throw new \Exception('El PDF no tiene páginas');
            }
            
            // Validar que no haya demasiadas páginas (máximo 5 páginas para una orden normal)
            if ($pageCount > 5) {
                Yii::warning('PDF generado con ' . $pageCount . ' páginas, que es más de lo esperado. Revisar HTML.', 'pdf');
                // No lanzar excepción, solo log de advertencia
            }
            
            // Generar nombre único del archivo
            $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '_PDF2.pdf';
            $filepath = $pdfDir . '/' . $filename;
            
            // Guardar PDF en disco
            $mpdf->Output($filepath, 'F');
            
            return [
                'success' => true,
                'filename' => $filename,
                'downloadUrl' => '/pdf/download-async-pdf?id=' . $id
            ];
            
        } catch (\Exception $e) {
            Yii::error('Error generando PDF asíncrono: ' . $e->getMessage(), 'pdf');
            return [
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar estado del PDF asíncrono
     */
    public function actionCheckPdfStatus($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $rental = $this->findRental($id);
        $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '_PDF2.pdf';
        $filepath = Yii::getAlias('@app') . '/runtime/pdfs/' . $filename;
        
        return [
            'ready' => file_exists($filepath),
            'downloadUrl' => '/pdf/download-async-pdf?id=' . $id
        ];
    }

    /**
     * Descargar PDF generado asíncronamente
     */
    public function actionDownloadAsyncPdf($id)
    {
        // Limpiar TODOS los buffers de salida ANTES de cualquier cosa
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Desactivar completamente el output buffering
        @ini_set('output_buffering', 0);
        @ini_set('zlib.output_compression', 0);
        @ini_set('zlib.output_compression_level', 0);
        
        // Desactivar compresión de Apache si está disponible
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
            @apache_setenv('no-gzip', '1');
        }
        
        $rental = $this->findRental($id);
        $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '_PDF2.pdf';
        $filepath = Yii::getAlias('@app') . '/runtime/pdfs/' . $filename;
        
        if (!file_exists($filepath)) {
            throw new NotFoundHttpException('El archivo PDF no existe. Por favor, genere el PDF primero.');
        }
        
        // Usar headers nativos de PHP para evitar interferencia de Cloudflare
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        
        // Leer y enviar archivo
        $handle = fopen($filepath, 'rb');
        if ($handle === false) {
            throw new \Exception('No se puede abrir el archivo PDF.');
        }
        
        // Enviar archivo en chunks para archivos grandes
        while (!feof($handle)) {
            echo fread($handle, 8192);
            flush();
        }
        
        fclose($handle);
        
        // Limpiar archivo después de descargar
        unlink($filepath);
        
        exit;
    }

    /**
     * Generar PDF de una orden de alquiler (método original para compatibilidad)
     */
    public function actionRentalOrder($id)
    {
        $rental = $this->findRental($id);
        $companyInfo = CompanyConfig::getCompanyInfo();
        
        // Limpiar TODOS los buffers de salida
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Desactivar compresión de salida
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        @ini_set('output_buffering', 0);
        @ini_set('zlib.output_compression_level', 0);
        
        // Crear PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Configuración del documento
        $pdf->SetCreator('Facto Rent a Car');
        $pdf->SetAuthor('Facto Rent a Car');
        $pdf->SetTitle('Orden de Alquiler - ' . $rental->rental_id);
        
        // Configurar márgenes
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        
        // Configurar fuente
        $pdf->SetFont('dejavusans', '', 10);
        
        // Agregar página
        $pdf->AddPage();
        
        // Generar contenido
        $html = $this->generateRentalOrderHtml($rental, $companyInfo);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Agregar segunda página con condiciones (SIEMPRE se agrega, prioridad: personalizado > global > fallback por defecto)
        $pdf->AddPage();
        $customConditions = $rental->condiciones_especiales ?? '';
        $globalConditions = CompanyConfig::getConfig('rental_conditions_html', '');
        $conditionsHtml = $this->generateConditionsHtml($companyInfo, $customConditions ?: $globalConditions);
        $pdf->writeHTML($conditionsHtml, true, false, true, false, '');
        
        // Generar nombre del archivo
        $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '.pdf';
        
        // Configurar la respuesta de Yii para descargar el PDF
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        Yii::$app->response->headers->set('Content-Transfer-Encoding', 'binary');
        Yii::$app->response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        Yii::$app->response->headers->set('Pragma', 'no-cache');
        
        // Generar PDF como string
        $pdfContent = $pdf->Output('', 'S');
        
        // Enviar contenido
        Yii::$app->response->data = $pdfContent;
        Yii::$app->response->send();
        
        Yii::$app->end();
    }

    /**
     * Generar PDF de una orden de venta
     */
    public function actionSaleOrder($id)
    {
        $order = $this->findOrder($id);
        $companyInfo = CompanyConfig::getCompanyInfo();
        
        // Limpiar TODOS los buffers de salida
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Desactivar compresión de salida
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        
        // Limpiar cualquier buffer que Yii pueda tener
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->stream = false;
        
        // Crear PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Configuración del documento
        $pdf->SetCreator('Facto Rent a Car');
        $pdf->SetAuthor('Facto Rent a Car');
        $pdf->SetTitle('Orden de Venta - ' . $order->ticket_id);
        
        // Configurar márgenes
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        
        // Configurar fuente
        $pdf->SetFont('dejavusans', '', 10);
        
        // Agregar página
        $pdf->AddPage();
        
        // Generar contenido
        $html = $this->generateSaleOrderHtml($order, $companyInfo);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Generar nombre del archivo
        $filename = 'Orden_Venta_' . $order->ticket_id . '_' . date('Y-m-d') . '.pdf';
        
        // Configurar headers para descarga forzada
        header('Content-Type: application/pdf', true);
        header('Content-Disposition: attachment; filename="' . $filename . '"', true);
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: no-cache');
        
        // TCPDF Output maneja todo
        $pdf->Output($filename, 'D');
        
        return;
    }

    /**
     * Generar HTML para orden de alquiler
     */
    public function generateRentalOrderHtml($rental, $companyInfo)
    {
        $rentalId = $rental->rental_id ?: ('R' . str_pad($rental->id, 6, '0', STR_PAD_LEFT));
        $client = $rental->client;
        $car = $rental->car;
        
        $html = '
        <style>
            @page { size: letter; margin: 0.3in 0.75in; }
            body { font-family: "Times New Roman", Georgia, serif; font-size: 10px; line-height: 1.5; margin: 0; padding: 0; color: #333; }
            .document { max-width: 100%; }
            .header { margin-bottom: 12px; margin-top: 0; }
            .header-table { width: 100%; border-collapse: collapse; }
            .header-table td { vertical-align: top; padding: 0; }
            .company-info { width: 70%; text-align: left; }
            .logo-container { width: 30%; text-align: right; }
            .company-name { font-size: 20px; font-weight: bold; font-style: italic; margin-bottom: 0; margin-top: 0; padding-bottom: 0; font-family: "Times New Roman", Georgia, serif; text-align: left; letter-spacing: 0.5px; line-height: 1.1; }
            .company-legal { font-size: 12px; margin-bottom: 0; margin-top: 0; padding-top: 0; padding-bottom: 0; font-weight: normal; text-transform: uppercase; letter-spacing: 1px; line-height: 1.2; text-align: left; }
            .company-address { font-size: 10px; margin-bottom: 0; margin-top: 0; padding-top: 0; padding-bottom: 0; line-height: 1.2; text-align: left; }
            .company-address .line { display: block; margin-bottom: 0; padding-bottom: 0; }
            .logo { width: 90px; height: 90px; object-fit: contain; }
            .client-section {
                background-color: #f9f9f9;
                padding: 8px 10px;
                margin: 10px 0;
                border-left: 3px solid #4CAF50;
            }
            .section-container {
                margin: 12px 0;
                padding: 8px;
                border: 1px solid #ddd;
                background-color: #fafafa;
            }
            .order-header {
                background-color: #f5f5f5;
                padding: 8px 10px;
                margin: 12px 0 10px 0;
                border-left: 4px solid #22487a;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .section-title { 
                font-size: 11px; 
                font-weight: bold; 
                margin-bottom: 8px;
                padding-bottom: 4px;
                border-bottom: 1px solid #ccc;
                color: #22487a;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .info-row { 
                margin: 5px 0;
                font-size: 10px;
                padding: 3px 0;
            }
            .info-label { 
                font-weight: bold;
                display: inline-block;
                width: 140px;
            }
            .info-value {
                color: #333;
            }
        .vehicle-table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 2px solid #000; background-color: #fff; }
            .vehicle-table td { border: 1px solid #000; padding: 8px 6px; text-align: center; font-size: 10px; }
            .vehicle-header { background-color: #22487a; color: #fff; font-weight: bold; text-align: center; font-size: 13px; padding: 12px 8px; text-transform: uppercase; letter-spacing: 0.8px; }
            .vehicle-quantity { text-align: center; background-color: #f0f0f0; font-weight: bold; }
            .price-detail-row { background-color: #fff; border-top: 1px dashed #ccc; }
            .price-detail-row td { padding: 6px 8px; font-size: 10px; text-align: center; }
            .total-row { background-color: #e8e8e8; border-top: 2px solid #000; font-weight: bold; }
            .total-row td { padding: 10px 8px; font-size: 11px; text-align: center; }
            .total-row strong { text-transform: uppercase; letter-spacing: 0.5px; }
            .total-label { text-align: left; font-weight: bold; }
            .total-value { text-align: right; font-weight: bold; font-size: 13px; }
            .payment-section { margin-top: 15px; padding: 10px; background-color: #f5f5f5; border: 1px solid #ccc; }
            .payment-title { font-size: 11px; font-weight: bold; margin-bottom: 8px; color: #22487a; border-bottom: 1px solid #22487a; padding-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
            .payment-info { font-size: 9px; margin: 4px 0; line-height: 1.5; }
            .payment-label { font-weight: bold; display: inline-block; min-width: 100px; }
        </style>
        
        <div class="document">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="company-info">
                        <div class="company-name">' . htmlspecialchars($companyInfo['name']) . '</div>
                        <div class="company-address">
                            <span class="line">3-101-880789</span>
                        </div>
                        <div class="company-legal">FACTO AUTOS DE ALQUILER S.A</div>
                        <div class="company-address">
                            <span class="line">San Ramón, Alajuela. Costa Rica</span>
                        </div>
                    </td>
                    <td class="logo-container">';
        
        // Agregar logo pequeño en la esquina superior derecha
        if ($companyInfo['logo']) {
            $logoPath = Yii::getAlias('@webroot' . str_replace(Yii::getAlias('@web'), '', $companyInfo['logo']));
            if (file_exists($logoPath)) {
                $html .= '<img src="' . $companyInfo['logo'] . '" class="logo" alt="Logo">';
            }
        }
        
        $html .= '</td>
                </tr>
            </table>
        </div>
        <div class="order-header">
            Orden de Alquiler: <span style="color: #dc3545; font-weight: bold;">' . htmlspecialchars($rentalId) . '</span> - ' . htmlspecialchars($car ? $car->nombre : 'Vehículo no encontrado') . '
        </div>
        <div class="client-section">
            <div class="section-title">Información del Cliente</div>
            <div class="info-row">
                <span class="info-label">Nombre:</span>
                <span>' . htmlspecialchars($client ? $client->full_name : 'Cliente no encontrado') . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cédula:</span>
                <span>' . htmlspecialchars($client ? $client->cedula_fisica : 'N/A') . '</span>
            </div>';
        
        if ($client && $client->telefono) {
            $html .= '<div class="info-row">
                <span class="info-label">Teléfono:</span>
                <span>' . htmlspecialchars($client->telefono) . '</span>
            </div>';
        }
        
        $html .= '</div>
        <div class="section-container">
            <div class="section-title">Entrega del Vehículo</div>';
        
        if ($rental->correapartir_enabled && !empty($rental->fecha_correapartir)) {
            $html .= '<div class="info-row">
                <span class="info-label">Correapartir (Cortesía):</span>
                <span>' . $this->formatDateTimeSpanish($rental->fecha_correapartir) . '</span>
            </div>';
        }
        
        $html .= '<div class="info-row">
                <span class="info-label">Fecha de alquiler:</span>
                <span>' . $this->formatDateTimeSafe($rental->fecha_inicio, $rental->hora_inicio) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha recoge vehículo:</span>
                <span>' . $this->formatDateTimeSafe($rental->fecha_final, $rental->hora_final) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Lugar de entrega:</span>
                <span>' . htmlspecialchars($rental->lugar_entrega ?: 'San Ramón') . '</span>
            </div>
        </div>
        <div class="section-container">
            <div class="section-title">Devolución del Vehículo</div>
            <div class="info-row">
                <span class="info-label">Fecha de entrega:</span>
                <span>' . $this->formatDateTimeSafe($rental->fecha_final, $rental->hora_final) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Lugar de retiro:</span>
                <span>' . htmlspecialchars($rental->lugar_retiro ?: 'San Ramón') . '</span>
            </div>
        </div>
        <table class="vehicle-table">
            <tr>
                <td class="vehicle-header" colspan="5">
                    Tipo de Vehículo: ' . htmlspecialchars($car ? ($car->nombre . ' - ' . ($car->cantidad_pasajeros ?: 5) . ' pasajeros') : 'Vehículo no encontrado') . '
                </td>
            </tr>
            <tr>
                <td class="vehicle-quantity" colspan="5">
                    Cantidad de días: ' . str_pad($rental->cantidad_dias, 2, '0', STR_PAD_LEFT) . ' | Cantidad de vehículos: 1 unidad
                </td>
            </tr>';
        
        // Calcular valores para desglose
        $medioDiaEnabled = intval($rental->medio_dia_enabled ?? 0);
        $medioDiaValor = floatval($rental->medio_dia_valor ?? 0);
        $medioDiaActivo = ($medioDiaEnabled >= 1) && ($medioDiaValor > 0);
        $subtotalDias = $rental->cantidad_dias * $rental->precio_por_dia;
        
        // Desglose: Cantidad días
        $html .= '
            <tr class="price-detail-row">
                <td colspan="5" style="padding: 8px 10px; text-align: center;">
                    <strong>Cantidad días: ' . str_pad($rental->cantidad_dias, 2, '0', STR_PAD_LEFT) . ' días = ₡' . number_format($subtotalDias, 0, '.', ',') . '</strong>
                </td>
            </tr>';
        
        // Desglose: 1/2 día si está activo
        if ($medioDiaActivo) {
            $html .= '
            <tr class="price-detail-row">
                <td colspan="5" style="padding: 8px 10px; text-align: center;">
                    <strong>1/2 día: ₡' . number_format($medioDiaValor, 0, '.', ',') . '</strong>
                </td>
            </tr>';
        }
        
        // Total
        $html .= '
            <tr class="total-row">
                <td colspan="3" style="text-align: center;">
                    <strong>Monto Total de la Orden:</strong>
                </td>
                <td colspan="2" style="text-align: center;">
                    <strong style="font-size: 13px;">₡' . number_format($rental->total_precio, 0, '.', ',') . ' colones</strong>
                </td>
            </tr>
        </table>
        
        <div class="payment-section">
            <div class="payment-title">Información de Pago</div>
            <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
                <tr>
                    <td style="padding: 4px 0; font-weight: bold; width: 120px; vertical-align: top;">BCR®:</td>
                    <td style="padding: 4px 0; vertical-align: top;">IBAN: CR75015201001050506181</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; font-weight: bold; vertical-align: top;">BN®:</td>
                    <td style="padding: 4px 0; vertical-align: top;">IBAN: CR49015102020010977051</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; font-weight: bold; vertical-align: top;">SINPE MÓVIL:</td>
                    <td style="padding: 4px 0; vertical-align: top;">83670937</td>
                </tr>
            </table>
        </div>
        </div>
        ';
        
        return $html;
    }

    /**
     * Generar HTML para orden de venta
     */
    private function generateSaleOrderHtml($order, $companyInfo)
    {
        $client = $order->client;
        
        $html = '
        <style>
            @page { size: letter; margin: 0.3in 0.75in; }
            body { font-family: "Times New Roman", Georgia, serif; font-size: 10px; line-height: 1.5; margin: 0; padding: 0; color: #333; }
            .document { max-width: 100%; }
            .header { margin-bottom: 12px; margin-top: 0; }
            .header-table { width: 100%; border-collapse: collapse; }
            .header-table td { vertical-align: top; padding: 0; }
            .company-info { width: 70%; text-align: left; }
            .logo-container { width: 30%; text-align: right; }
            .company-name { font-size: 20px; font-weight: bold; font-style: italic; margin-bottom: 0; margin-top: 0; padding-bottom: 0; font-family: "Times New Roman", Georgia, serif; text-align: left; letter-spacing: 0.5px; line-height: 1.1; }
            .company-legal { font-size: 12px; margin-bottom: 0; margin-top: 0; padding-top: 0; padding-bottom: 0; font-weight: normal; text-transform: uppercase; letter-spacing: 1px; line-height: 1.2; text-align: left; }
            .company-address { font-size: 10px; margin-bottom: 0; margin-top: 0; padding-top: 0; padding-bottom: 0; line-height: 1.2; text-align: left; }
            .company-address .line { display: block; margin-bottom: 0; padding-bottom: 0; }
            .logo { width: 90px; height: 90px; object-fit: contain; }
            .order-info { margin-bottom: 15px; text-align: left; font-size: 10px; }
            .order-title { font-size: 10px; font-weight: normal; margin: 0; }
            .section-title { font-size: 10px; font-weight: bold; margin-top: 15px; margin-bottom: 5px; text-align: left; }
            .info-row { margin-bottom: 3px; }
            .info-label { font-weight: normal; }
            .info-value { font-weight: normal; }
            .table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #000; }
            .table th, .table td { border: 1px solid #000; padding: 5px; text-align: left; font-size: 10px; }
            .table th { font-weight: bold; text-align: center; }
        </style>
        
        <div class="document">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="company-info">
                        <div class="company-name">' . htmlspecialchars($companyInfo['name']) . '</div>
                        <div class="company-address">
                            <span class="line">3-101-880789</span>
                        </div>
                        <div class="company-legal">FACTO AUTOS DE ALQUILER S.A</div>
                        <div class="company-address">
                            <span class="line">San Ramón, Alajuela. Costa Rica</span>
                        </div>
                    </td>
                    <td class="logo-container">';
        
        // Agregar logo pequeño en la esquina superior derecha
        if ($companyInfo['logo']) {
            $logoPath = Yii::getAlias('@webroot' . str_replace(Yii::getAlias('@web'), '', $companyInfo['logo']));
            if (file_exists($logoPath)) {
                $html .= '<img src="' . $companyInfo['logo'] . '" class="logo" alt="Logo">';
            }
        }
        
        $html .= '</td>
                </tr>
            </table>
        </div>
        <div class="order-info">
            <div class="order-title">
                Orden de Venta: ' . htmlspecialchars($order->ticket_id) . '
            </div>
        </div>
        <div class="section-title">INFORMACIÓN DEL CLIENTE</div>
        <div class="info-row very-small-gap">
            <span class="info-label">Cliente:</span> 
            <span class="info-value">' . htmlspecialchars($client ? $client->full_name : 'Cliente no encontrado') . '</span>
        </div>
        <div class="info-row very-small-gap">
            <span class="info-label">Cédula:</span> 
            <span class="info-value">' . htmlspecialchars($client ? $client->cedula_fisica : 'N/A') . '</span>
        </div>
        ' . ($client && $client->telefono ? '<div class="info-row very-small-gap">
            <span class="info-label">Teléfono:</span> 
            <span class="info-value">' . htmlspecialchars($client->telefono) . '</span>
        </div>' : '') . '
        <div class="section-title">DETALLES DE LA VENTA</div>
        <table class="table">
            <tr>
                <th>Artículo ID</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>Artículo #' . $order->article_id . '</td>
                <td>' . $order->quantity . '</td>
                <td>₡' . number_format($order->unit_price, 0) . '</td>
                <td>₡' . number_format($order->total_price, 0) . '</td>
            </tr>
            <tr>
                <td colspan="3"><strong>Total:</strong></td>
                <td><strong>₡' . number_format($order->total_price, 0) . '</strong></td>
            </tr>
        </table>
        
        <div class="section-title">INFORMACIÓN ADICIONAL</div>
        <div class="info-row">
            <span class="info-label">Modo de Venta:</span> 
            <span class="info-value">' . ucfirst($order->sale_mode) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha:</span> 
            <span class="info-value">' . $this->formatDateTimeSafe($order->created_at) . '</span>
        </div>
        ';
        
        if ($order->notes) {
            $html .= '
            <div class="info-row">
                <span class="info-label">Notas:</span> 
                <span class="info-value">' . nl2br(htmlspecialchars($order->notes)) . '</span>
            </div>
            ';
        }
        
        $html .= '</div>
        </div>';
        
        return $html;
    }

    /**
     * Generar HTML para condiciones de alquiler
     */
    public function generateConditionsHtml($companyInfo, $customHtml = null)
    {
        // Usar HTML personalizado si se provee
        if (!empty($customHtml)) {
            return '<style>body { font-family: "Times New Roman", Georgia, serif; font-size: 10px; }</style><div>' . $customHtml . '</div>';
        }

        // Usar HTML global desde configuración si existe
        $globalHtml = \app\models\CompanyConfig::getConfig('rental_conditions_html', '');
        if (!empty($globalHtml)) {
            return '<style>body { font-family: Arial, sans-serif; font-size: 10px; }</style><div>' . $globalHtml . '</div>';
        }

        // Fallback: contenido por defecto "Reservación firme contra depósito"
        $html = '
        <style>
            body { font-family: "Times New Roman", Georgia, serif; font-size: 10px; line-height: 1.6; }
            h2 { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 10px; }
            h3 { font-size: 12px; font-weight: bold; margin-top: 12px; margin-bottom: 6px; }
            h4 { font-size: 11px; font-weight: bold; margin-top: 10px; margin-bottom: 5px; }
            ol { margin-left: 15px; padding-left: 10px; }
            li { margin-bottom: 4px; }
        </style>
        <div style="padding: 15px;">
        <h2>Reservación firme contra depósito</h2>
        <h3>Indicaciones Importantes:</h3>
        
        <h4>SOBRE EL RETIRO</h4>
        <ol>
            <li>Revise el estado del vehículo.</li>
            <li>Revise el estado de la gasolina.</li>
            <li>Recuerde firmar la hoja de la Orden de alquiler</li>
            <li>Solicite las llaves e indicaciones sobre alarma y otros.</li>
        </ol>
        
        <h4>SOBRE LA ENTREGA</h4>
        <ol>
            <li>Recuerde entregar el vehículo con el tanque de gasolina lleno. En caso de no poder realizarlo indíquelo a la oficina se cobrará la gasolina + ₡15,000 iva.</li>
            <li>Recuerde revisar el estado del vehículo antes de entregarlo.</li>
            <li>En caso de emergencia o accidente debe llamar al 88781108 con Ing. Ronald.</li>
            <li>En caso de rayones o siniestros debe cancelar el monto de $800 dólares en casos mayores como accidente u otros deberá cancelar $1,000.</li>
            <li>Recuerde que el chofer siempre deberá tener licencia al día ya que es requisito para el alquiler y en temas de seguro del mismo.</li>
            <li>El corre a partir aplica únicamente retirando el auto en nuestras instalaciones.</li>
            <li>En caso de que se le realice un parte, este mismo debe ser cubierto por el responsable de la reservación.</li>
            <li>Es indispensable que el conductor se encuentre presente en el lugar de los hechos en caso de un incidente vial. La cobertura del seguro no aplicará si el conductor no está presente cuando las autoridades de tránsito lleguen al sitio, ya que su ausencia anularía la validez de la póliza. Esta cláusula es fundamental para garantizar la correcta aplicación del seguro y la protección de ambas partes involucradas. De no cumplirse el cliente es responsable al 100% por invalidar la cláusula de cobertura del seguro del vehículo.</li>
        </ol>
        
        <h4>SEGURIDAD:</h4>
        <ol>
            <li>Recuerde revisar el aire de las llantas y cinturones.</li>
            <li>En ningún momento dejar las llaves dentro del auto, pues la mayoría de nuestros automóviles cuentan con cierre automático.</li>
        </ol>
        </div>
        ';
        
        return $html;
    }

    /**
     * Buscar alquiler por ID
     */
    protected function findRental($id)
    {
        // Intentar encontrar en Rental primero
        if (($model = Rental::findOne($id)) !== null) {
            return $model;
        }
        // Si no se encuentra, intentar en Order (ambos apuntan a la tabla rentals)
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('El alquiler solicitado no existe.');
    }

    /**
     * Buscar orden por ID
     */
    protected function findOrder($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La orden solicitada no existe.');
    }

    /**
     * Formatear fecha en español
     */
    private function formatDateSpanish($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '1970-01-01') {
            return 'Fecha no disponible';
        }
        
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        $timestamp = strtotime($date);
        
        // Verificar si la fecha es válida (no es época Unix)
        if ($timestamp === false || $timestamp < 946684800) { // 1 de enero de 2000
            return 'Fecha no disponible';
        }
        
        $dia = date('d', $timestamp);
        $mes = $meses[(int)date('m', $timestamp)];
        $año = date('Y', $timestamp);
        
        return $dia . ' de ' . $mes . ' de ' . $año;
    }

    /**
     * Formatear fecha y hora en español
     */
    private function formatDateTimeSpanish($dateTime)
    {
        if (empty($dateTime) || $dateTime === '0000-00-00 00:00:00' || $dateTime === '1970-01-01 00:00:00') {
            return 'Fecha no disponible';
        }
        
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        $dias = [
            0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
            4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'
        ];
        
        $timestamp = strtotime($dateTime);
        
        // Verificar si la fecha es válida (no es época Unix)
        if ($timestamp === false || $timestamp < 946684800) { // 1 de enero de 2000
            return 'Fecha no disponible';
        }
        
        $diaSemana = $dias[(int)date('w', $timestamp)];
        $dia = date('d', $timestamp);
        $mes = $meses[(int)date('m', $timestamp)];
        $año = date('Y', $timestamp);
        $hora = strtolower(date('h:i a', $timestamp));
        
        return $diaSemana . ' ' . $dia . ' de ' . $mes . ' de ' . $año . ' ' . $hora;
    }

    /**
     * Formatear fecha y hora de manera segura
     */
    private function formatDateTimeSafe($date, $time = null)
    {
        // Si no hay fecha, retornar mensaje
        if (empty($date)) {
            return 'Fecha no disponible';
        }
        
        // Si hay tiempo, concatenar de manera segura
        if (!empty($time) && $time !== '00:00:00') {
            $dateTime = $date . ' ' . $time;
        } else {
            $dateTime = $date . ' 00:00:00';
        }
        
        return $this->formatDateTimeSpanish($dateTime);
    }

    /**
     * Generar PDF con mPDF - PDF2
     */
    public function actionGenerateMpdf($id)
    {
        // Detener cualquier output que Yii2 pueda estar enviando
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Desactivar completamente el output buffering
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Desactivar compresión
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        @ini_set('output_buffering', 0);
        
        $rental = $this->findRental($id);
        $companyInfo = CompanyConfig::getCompanyInfo();
        
        try {
            // Cargar mPDF
            require_once Yii::getAlias('@vendor/autoload.php');
            
            // Crear directorio temporal personalizado para mPDF
            $tempDir = Yii::getAlias('@app') . '/runtime/mpdf_temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            Yii::info('Generando PDF con mPDF para rental ID: ' . $id, 'pdf');
            
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 20,
                'margin_bottom' => 20,
                'default_font' => 'arial',
                'tempDir' => $tempDir,
                'autoScriptToLang' => false,
                'autoLangToFont' => false,
                'debug' => false,
                'simpleTables' => true,
                'useSubstitutions' => false,
                'shrink_tables_to_fit' => 1,
                'max_colH_correction' => 1.8,
                'mirrorMargins' => false,
                'use_kwt' => false,
                'showImageErrors' => false,
                'img_dpi' => 96,
                'dpi' => 96,
            ]);
            
            Yii::info('mPDF inicializado correctamente', 'pdf');
            
            // Generar HTML usando la vista completa del PDF
            $html = $this->renderPartial('_rental-pdf', [
                'model' => $rental,
                'companyInfo' => $companyInfo
            ], true);
            
            Yii::info('HTML generado. Tamaño: ' . strlen($html) . ' bytes', 'pdf');
            
            // Verificar que el HTML no esté vacío
            if (empty(trim($html))) {
                throw new \Exception('El HTML generado está vacío');
            }
            
            // Limpiar HTML de caracteres problemáticos
            $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html);
            $html = str_replace(["\r\n", "\r"], "\n", $html);
            
            // Limpiar espacios en blanco excesivos que pueden causar páginas vacías
            $html = preg_replace('/\n{3,}/', "\n\n", $html);
            $html = preg_replace('/\s{4,}/', ' ', $html);
            
            // Escribir HTML al PDF
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            
            // Verificar número de páginas
            $pageCount = $mpdf->page;
            Yii::info('Número de páginas generadas: ' . $pageCount, 'pdf');
            
            if ($pageCount == 0) {
                throw new \Exception('El PDF no tiene páginas');
            }
            
            // Validar que no haya demasiadas páginas
            if ($pageCount > 5) {
                Yii::warning('PDF generado con ' . $pageCount . ' páginas, que es más de lo esperado. Revisar HTML.', 'pdf');
            }
            
            Yii::info('HTML escrito en mPDF', 'pdf');
            
            // Nombre del archivo
            $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '_PDF2.pdf';
            
            Yii::info('Enviando PDF con nombre: ' . $filename, 'pdf');
            Yii::info('Headers actuales: ' . json_encode(headers_list()), 'pdf');
            Yii::info('Buffers activos: ' . ob_get_level(), 'pdf');
            
            // Configurar headers ANTES de cualquier output usando header() nativo
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            Yii::info('Headers configurados para descarga', 'pdf');
            
            // Enviar PDF directamente
            $mpdf->Output($filename, 'D');
            
            Yii::info('PDF enviado exitosamente', 'pdf');
            
            // Terminar ejecución inmediatamente
            exit;
            
        } catch (\Exception $e) {
            Yii::error('Error generando PDF con mPDF: ' . $e->getMessage(), 'pdf');
            Yii::error('Stack trace: ' . $e->getTraceAsString(), 'pdf');
            throw new NotFoundHttpException('Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generar ZIP con PDF asíncronamente
     */
    public function actionGenerateZipAsync($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $rental = $this->findRental($id);
            $companyInfo = CompanyConfig::getCompanyInfo();
            
            require_once Yii::getAlias('@vendor/autoload.php');
            
            // Crear directorio para ZIPs
            $zipDir = Yii::getAlias('@app') . '/runtime/zips';
            if (!is_dir($zipDir)) {
                mkdir($zipDir, 0777, true);
            }
            
            // Crear directorio temporal para mPDF
            $tempDir = Yii::getAlias('@app') . '/runtime/mpdf_temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            // Generar PDF
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 20,
                'margin_bottom' => 20,
                'default_font' => 'arial',
                'tempDir' => $tempDir,
                'autoScriptToLang' => false,
                'autoLangToFont' => false,
                'debug' => false,
                'simpleTables' => true,
                'useSubstitutions' => false,
                'shrink_tables_to_fit' => 1,
                'max_colH_correction' => 1.8,
                'mirrorMargins' => false,
                'use_kwt' => false,
                'showImageErrors' => false,
                'img_dpi' => 96,
                'dpi' => 96,
            ]);
            
            // Generar HTML usando la vista completa del PDF
            $html = $this->renderPartial('_rental-pdf', [
                'model' => $rental,
                'companyInfo' => $companyInfo
            ], true);
            
            // Verificar que el HTML no esté vacío
            if (empty(trim($html))) {
                throw new \Exception('El HTML generado está vacío');
            }
            
            // Limpiar HTML de caracteres problemáticos
            $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html);
            $html = str_replace(["\r\n", "\r"], "\n", $html);
            
            // Limpiar espacios en blanco excesivos que pueden causar páginas vacías
            $html = preg_replace('/\n{3,}/', "\n\n", $html);
            $html = preg_replace('/\s{4,}/', ' ', $html);
            
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            
            // Verificar número de páginas
            $pageCount = $mpdf->page;
            Yii::info('Número de páginas generadas para ZIP: ' . $pageCount, 'pdf');
            
            if ($pageCount == 0) {
                throw new \Exception('El PDF no tiene páginas');
            }
            
            // Validar que no haya demasiadas páginas
            if ($pageCount > 5) {
                Yii::warning('PDF generado con ' . $pageCount . ' páginas para ZIP, que es más de lo esperado.', 'pdf');
            }
            
            // Guardar PDF temporalmente
            $pdfDir = Yii::getAlias('@app') . '/runtime/pdfs';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0777, true);
            }
            
            $pdfFilename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '_PDF2.pdf';
            $pdfFilepath = $pdfDir . '/' . $pdfFilename;
            $mpdf->Output($pdfFilepath, 'F');
            
            // Crear ZIP
            $zipFilename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '.zip';
            $zipFilepath = $zipDir . '/' . $zipFilename;
            
            $zip = new \ZipArchive();
            if ($zip->open($zipFilepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                $zip->addFile($pdfFilepath, $pdfFilename);
                $zip->close();
                
                // Eliminar PDF temporal
                @unlink($pdfFilepath);
                
                return [
                    'success' => true,
                    'filename' => $zipFilename,
                    'downloadUrl' => '/pdf/download-zip?id=' . $id
                ];
            } else {
                throw new \Exception('No se puede crear el archivo ZIP');
            }
            
        } catch (\Exception $e) {
            Yii::error('Error generando ZIP asíncrono: ' . $e->getMessage(), 'pdf');
            return [
                'success' => false,
                'message' => 'Error al generar el ZIP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Descargar ZIP con PDF
     */
    public function actionDownloadZip($id)
    {
        // Limpiar TODOS los buffers de salida
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        @ini_set('output_buffering', 0);
        @ini_set('zlib.output_compression', 0);
        @ini_set('zlib.output_compression_level', 0);
        
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
            @apache_setenv('no-gzip', '1');
        }
        
        $rental = $this->findRental($id);
        $zipFilename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '.zip';
        $zipFilepath = Yii::getAlias('@app') . '/runtime/zips/' . $zipFilename;
        
        if (!file_exists($zipFilepath)) {
            // Si no existe, generar ahora
            $result = $this->actionGenerateZipAsync($id);
            if (!$result['success']) {
                throw new NotFoundHttpException('Error al generar el ZIP: ' . $result['message']);
            }
            // Re-intentar después de generar
            if (!file_exists($zipFilepath)) {
                throw new NotFoundHttpException('El archivo ZIP no existe después de generarlo');
            }
        }
        
        // Headers para descarga de ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipFilepath));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        
        // Leer y enviar archivo
        $handle = fopen($zipFilepath, 'rb');
        if ($handle === false) {
            throw new \Exception('No se puede abrir el archivo ZIP.');
        }
        
        while (!feof($handle)) {
            echo fread($handle, 8192);
            flush();
        }
        
        fclose($handle);
        exit;
    }

    /**
     * Verificar si ZIP existe
     */
    public function actionCheckZipStatus($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $rental = $this->findRental($id);
        $zipFilename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '.zip';
        $zipFilepath = Yii::getAlias('@app') . '/runtime/zips/' . $zipFilename;
        
        return [
            'ready' => file_exists($zipFilepath),
            'downloadUrl' => '/pdf/download-zip?id=' . $id
        ];
    }
}
