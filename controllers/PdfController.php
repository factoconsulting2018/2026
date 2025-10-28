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
        
        // Agregar segunda página con condiciones si existe
        if ($companyInfo['conditions']) {
            $pdf->AddPage();
            $conditionsHtml = $this->generateConditionsHtml($companyInfo);
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
        // Limpiar buffers ANTES de todo
        if (ob_get_length()) @ob_end_clean();
        while (ob_get_level() > 0) @ob_end_clean();
        
        $rental = $this->findRental($id);
        $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '.pdf';
        $filepath = Yii::getAlias('@app') . '/runtime/' . $filename;
        
        if (!file_exists($filepath)) {
            throw new NotFoundHttpException('El archivo PDF no existe. Por favor, genere el PDF primero.');
        }
        
        // Desactivar cualquier compresión
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        @ini_set('output_buffering', 0);
        
        // Usar response de Yii
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->removeAll();
        
        // Headers
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        Yii::$app->response->headers->set('Content-Length', filesize($filepath));
        
        // Leer archivo
        $handle = fopen($filepath, 'rb');
        if ($handle === false) {
            throw new \Exception('No se puede abrir el archivo PDF.');
        }
        
        Yii::$app->response->stream = $handle;
        Yii::$app->response->send();
        fclose($handle);
        
        Yii::$app->end();
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
        
        // Agregar segunda página con condiciones si existe
        if ($companyInfo['conditions']) {
            $pdf->AddPage();
            $conditionsHtml = $this->generateConditionsHtml($companyInfo);
            $pdf->writeHTML($conditionsHtml, true, false, true, false, '');
        }
        
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
            @page { size: letter; margin: 0.75in; }
            body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.3; margin: 0; padding: 0; color: #000; }
            .document { max-width: 100%; }
            .header { margin-bottom: 15px; }
            .header-table { width: 100%; border-collapse: collapse; }
            .header-table td { vertical-align: top; padding: 0; }
            .company-info { width: 70%; text-align: left; }
            .logo-container { width: 30%; text-align: right; }
            .company-name { font-size: 18px; font-weight: bold; font-style: italic; margin-bottom: 5px; }
            .company-legal { font-size: 12px; margin-bottom: 10px; }
            .company-address { font-size: 10px; margin-bottom: 10px; }
            .logo { width: 90px; height: 90px; object-fit: contain; }
            .order-info { margin-bottom: 15px; text-align: left; font-size: 10px; }
            .order-title { font-size: 10px; font-weight: normal; margin: 0; }
            .section-title { font-size: 10px; font-weight: bold; margin-top: 15px; margin-bottom: 5px; text-align: left; }
            .info-row { margin-bottom: 3px; }
            .info-label { font-weight: normal; }
            .info-value { font-weight: normal; }
            .vehicle-table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #000; }
            .vehicle-table td { border: 1px solid #000; padding: 5px; text-align: left; font-size: 10px; }
            .vehicle-header { font-weight: bold; text-align: center; }
            .vehicle-quantity { text-align: center; }
            .price-row td { text-align: left; }
            .total-label { text-align: right; font-weight: bold; }
            .total-value { text-align: right; font-weight: bold; }
            .bank-section { margin-top: 15px; }
            .bank-title { font-size: 10px; font-weight: bold; margin-bottom: 5px; }
            .bank-info { font-size: 10px; margin-bottom: 3px; }
            .bank-name { font-weight: bold; }
            .reservation-info { margin-top: 15px; font-size: 10px; }
            .reservation-item { margin-bottom: 3px; }
            .reservation-label { font-weight: bold; }
            .reservation-value { font-weight: bold; }
        </style>
        
        <div class="document">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="company-info">
                        <div class="company-name">' . htmlspecialchars($companyInfo['name']) . '</div>
                        <div class="company-legal">FACTO AUTOS DE ALQUILER S.A</div>
                        <div class="company-address">
                            3-101-880789<br>
                            San Ramón, Alajuela.<br>
                            Costa Rica
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
                Orden: ' . htmlspecialchars($rentalId) . ' - ' . htmlspecialchars($car ? $car->nombre : 'Vehículo no encontrado') . '
            </div>
        </div>
        <div class="info-row">
            <span class="info-label">Nombre del cliente:</span> 
            <span class="info-value">' . htmlspecialchars($client ? $client->full_name : 'Cliente no encontrado') . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cédula:</span> 
            <span class="info-value">' . htmlspecialchars($client ? $client->cedula_fisica : 'N/A') . '</span>
        </div>
        ' . ($client && $client->telefono ? '<div class="info-row">
            <span class="info-label">Teléfono:</span> 
            <span class="info-value">' . htmlspecialchars($client->telefono) . '</span>
        </div>' : '') . '
        <div class="section-title">ENTREGA DEL VEHÍCULO:</div>
        <div class="info-row">
            <span class="info-label">Fecha de alquiler:</span> 
            <span class="info-value">' . $this->formatDateSpanish($rental->fecha_inicio) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha recoge vehículo:</span> 
            <span class="info-value">' . $this->formatDateTimeSafe($rental->fecha_inicio, $rental->hora_inicio) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Lugar:</span> 
            <span class="info-value">' . htmlspecialchars($rental->lugar_entrega ?: 'San Ramón') . '</span>
        </div>
        <div class="section-title">DEVOLUCIÓN DEL VEHÍCULO:</div>
        <div class="info-row">
            <span class="info-label">Fecha de entrega:</span> 
            <span class="info-value">' . $this->formatDateTimeSafe($rental->fecha_final, $rental->hora_final) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Lugar:</span> 
            <span class="info-value">' . htmlspecialchars($rental->lugar_retiro ?: 'San Ramón') . '</span>
        </div>
        <table class="vehicle-table">
            <tr>
                <td class="vehicle-header" colspan="5">Tipo de Vehículo: ' . htmlspecialchars($car ? ($car->nombre . ' - ' . ($car->cantidad_pasajeros ?: 5) . ' pasajeros') : 'Vehículo no encontrado') . '</td>
            </tr>
            <tr>
                <td class="vehicle-quantity" colspan="5">Cantidad de días: ' . str_pad($rental->cantidad_dias, 2, '0', STR_PAD_LEFT) . '</td>
            </tr>
            <tr>
                <td class="vehicle-quantity" colspan="5">Cantidad de vehículos: 1 unidad</td>
            </tr>
            <tr class="price-row">
                <td>Precio:</td>
                <td>¢' . number_format($rental->precio_por_dia, 0) . '</td>
                <td>1 Unidad x ' . str_pad($rental->cantidad_dias, 2, '0', STR_PAD_LEFT) . '</td>
                <td class="total-label">Total:</td>
                <td class="total-value">¢' . number_format($rental->total_precio, 0) . '</td>
            </tr>
        </table>
        
        <div class="bank-section">
            <div class="bank-title">Cuentas Bancarias</div>
            <div class="bank-info">
                <span class="bank-name">BCR®:</span> IBAN: CR75015201001050506181
            </div>
            <div class="bank-info">
                <span class="bank-name">BN®:</span> IBAN: CR49015102020010977051
            </div>
        </div>
        
        <div class="reservation-info">
            <div class="reservation-item">
                <span class="reservation-label">SIMPEMOVIL:</span> 83670937
            </div>
            <div class="reservation-item">
                <span class="reservation-label">Monto de la Reservación:</span> ¢' . number_format($rental->total_precio, 0) . '
            </div>
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
            @page { size: letter; margin: 0.75in; }
            body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.3; margin: 0; padding: 0; color: #000; }
            .document { max-width: 100%; }
            .header { margin-bottom: 15px; }
            .header-table { width: 100%; border-collapse: collapse; }
            .header-table td { vertical-align: top; padding: 0; }
            .company-info { width: 70%; text-align: left; }
            .logo-container { width: 30%; text-align: right; }
            .company-name { font-size: 18px; font-weight: bold; font-style: italic; margin-bottom: 5px; }
            .company-legal { font-size: 12px; margin-bottom: 10px; }
            .company-address { font-size: 10px; margin-bottom: 10px; }
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
                        <div class="company-legal">FACTO AUTOS DE ALQUILER S.A</div>
                        <div class="company-address">
                            3-101-880789<br>
                            San Ramón, Alajuela.<br>
                            Costa Rica
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
                <td>¢' . number_format($order->unit_price, 0) . '</td>
                <td>¢' . number_format($order->total_price, 0) . '</td>
            </tr>
            <tr>
                <td colspan="3"><strong>Total:</strong></td>
                <td><strong>¢' . number_format($order->total_price, 0) . '</strong></td>
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
    public function generateConditionsHtml($companyInfo)
    {
        $html = '
        <style>
            body { font-family: Arial, sans-serif; font-size: 10px; }
            .conditions-title { font-size: 16px; font-weight: bold; color: #2c3e50; text-align: center; margin-bottom: 20px; }
            .conditions-content { line-height: 1.6; }
        </style>
        
        <div class="conditions-title">CONDICIONES DE ALQUILER</div>
        
        <div class="conditions-content">
            <p>Las condiciones de alquiler se adjuntan como segunda página de esta orden.</p>
            <p>Para consultar las condiciones completas, visite nuestro sitio web o contacte con nosotros.</p>
        </div>
        </div>
        ';
        
        return $html;
    }

    /**
     * Buscar alquiler por ID
     */
    protected function findRental($id)
    {
        if (($model = Rental::findOne($id)) !== null) {
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
        $hora = date('H:i', $timestamp);
        
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
        // Limpiar buffers ANTES de todo
        if (ob_get_length()) @ob_end_clean();
        while (ob_get_level() > 0) @ob_end_clean();
        
        $rental = $this->findRental($id);
        $companyInfo = CompanyConfig::getCompanyInfo();
        
        // Desactivar compresión
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        @ini_set('output_buffering', 0);
        
        try {
            // Cargar mPDF
            require_once Yii::getAlias('@vendor/autoload.php');
            
            // Crear directorio temporal personalizado para mPDF
            $tempDir = Yii::getAlias('@app') . '/runtime/mpdf_temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            Yii::info('Generando PDF con mPDF para rental ID: ' . $id, 'pdf');
            
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 20,
                'margin_bottom' => 10,
                'default_font' => 'dejavusans',
                'tempDir' => $tempDir
            ]);
            
            Yii::info('mPDF inicializado correctamente', 'pdf');
            
            // Generar HTML
            $html = $this->renderPartial('_rental-pdf', [
                'model' => $rental,
                'companyInfo' => $companyInfo
            ], true);
            
            Yii::info('HTML generado. Tamaño: ' . strlen($html) . ' bytes', 'pdf');
            
            // Escribir HTML al PDF
            $mpdf->WriteHTML($html);
            
            Yii::info('HTML escrito en mPDF', 'pdf');
            
            // Nombre del archivo
            $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '_PDF2.pdf';
            
            Yii::info('Enviando PDF con nombre: ' . $filename, 'pdf');
            Yii::info('Headers actuales: ' . json_encode(headers_list()), 'pdf');
            Yii::info('Buffers activos: ' . ob_get_level(), 'pdf');
            
            // Configurar respuesta para descarga con limpieza de headers
            Yii::$app->response->headers->removeAll();
            Yii::$app->response->headers->set('Pragma', 'public');
            Yii::$app->response->headers->set('Expires', '0');
            Yii::$app->response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            Yii::$app->response->headers->set('Content-Type', 'application/pdf');
            Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            
            Yii::info('Enviando PDF al navegador', 'pdf');
            
            // Enviar directamente sin guardar
            $mpdf->Output($filename, 'D');
            exit;
            
        } catch (\Exception $e) {
            Yii::error('Error generando PDF con mPDF: ' . $e->getMessage(), 'pdf');
            Yii::error('Stack trace: ' . $e->getTraceAsString(), 'pdf');
            throw new NotFoundHttpException('Error al generar el PDF: ' . $e->getMessage());
        }
    }
}
