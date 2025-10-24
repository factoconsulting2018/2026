<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Rental;
use app\models\Order;
use app\models\Client;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

class ReportsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Reporte de Ventas 2 - Con colores y diseño mejorado
     */
    public function actionVentas2Report($format = 'pdf')
    {
        try {
            // Obtener alquileres organizados por empresa
            $rentalsByCompany = $this->getRentalsByCompany();
            
            // Calcular totales por empresa
            $totalsByCompany = $this->calculateTotalsByCompany($rentalsByCompany);
            $totalAmount = array_sum($totalsByCompany);
            
            switch ($format) {
                case 'pdf':
                    return $this->generateVentas2Pdf($rentalsByCompany, $totalsByCompany, $totalAmount);
                case 'excel':
                    return $this->generateVentas2Excel($rentalsByCompany, $totalsByCompany, $totalAmount);
                case 'word':
                    // Generar PDF en lugar de Word por ahora
                    Yii::$app->session->setFlash('info', 'Formato Word no disponible. Generando PDF en su lugar.');
                    return $this->generateVentas2Pdf($rentalsByCompany, $totalsByCompany, $totalAmount);
                default:
                    throw new \Exception('Formato no soportado');
            }
        } catch (\Exception $e) {
            Yii::error('Error generando reporte Ventas 2: ' . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error generando el reporte: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    /**
     * Reporte de Ventas (Alquileres) en múltiples formatos
     */
    public function actionRentalsReport($format = 'pdf')
    {
        try {
            // Obtener alquileres organizados por empresa
            $rentalsByCompany = $this->getRentalsByCompany();
            
            // Calcular totales por empresa
            $totalsByCompany = $this->calculateTotalsByCompany($rentalsByCompany);
            
            // Calcular total general
            $totalAmount = array_sum($totalsByCompany);

            switch ($format) {
                case 'pdf':
                    return $this->generateRentalsPdf($rentalsByCompany, $totalsByCompany, $totalAmount);
                case 'excel':
                    return $this->generateRentalsExcel($rentalsByCompany, $totalsByCompany, $totalAmount);
                case 'word':
                    return $this->generateRentalsWord($rentalsByCompany, $totalsByCompany, $totalAmount);
                default:
                    throw new \Exception('Formato no soportado');
            }
        } catch (\Exception $e) {
            Yii::error('Error en actionRentalsReport: ' . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error generando reporte: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    /**
     * Método de prueba para Excel simple
     */
    public function actionTestExcel()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Prueba');
            
            $sheet->setCellValue('A1', 'Prueba');
            $sheet->setCellValue('B1', 'Funciona');
            
            return $this->downloadExcel($spreadsheet, 'prueba_' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            Yii::error('Error en test Excel: ' . $e->getMessage());
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Método de prueba para Excel con datos reales
     */
    public function actionTestRentalsExcel()
    {
        try {
            // Obtener algunos alquileres para prueba
            $rentals = Rental::find()->with(['client', 'car'])->limit(5)->all();
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Prueba Alquileres');
            
            // Encabezados
            $headers = ['ID', 'Fecha', 'Cliente', 'Vehículo', 'Monto'];
            $col = 1;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($col, 1, $header);
                $col++;
            }
            
            // Datos
            $row = 2;
            foreach ($rentals as $rental) {
                $sheet->setCellValueByColumnAndRow(1, $row, $rental->id);
                $sheet->setCellValueByColumnAndRow(2, $row, date('d/m/Y', strtotime($rental->created_at)));
                $sheet->setCellValueByColumnAndRow(3, $row, $rental->client ? $rental->client->full_name : 'N/A');
                $sheet->setCellValueByColumnAndRow(4, $row, $rental->car ? $rental->car->nombre : 'N/A');
                $sheet->setCellValueByColumnAndRow(5, $row, (float)$rental->total_precio);
                $row++;
            }
            
            return $this->downloadExcel($spreadsheet, 'prueba_alquileres_' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            Yii::error('Error en test alquileres Excel: ' . $e->getMessage());
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Reporte de Órdenes en múltiples formatos
     */
    public function actionOrdersReport($format = 'pdf')
    {
        $orders = Order::find()
            ->with(['client'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $totalAmount = Order::find()->sum('total_price');

        switch ($format) {
            case 'pdf':
                return $this->generateOrdersPdf($orders, $totalAmount);
            case 'excel':
                return $this->generateOrdersExcel($orders, $totalAmount);
            case 'word':
                return $this->generateOrdersWord($orders, $totalAmount);
            default:
                throw new \Exception('Formato no soportado');
        }
    }

    /**
     * Reporte de Clientes en múltiples formatos
     */
    public function actionClientsReport($format = 'pdf')
    {
        $clients = Client::find()
            ->where(['status' => 'active'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        switch ($format) {
            case 'pdf':
                return $this->generateClientsPdf($clients);
            case 'excel':
                return $this->generateClientsExcel($clients);
            case 'word':
                return $this->generateClientsWord($clients);
            default:
                throw new \Exception('Formato no soportado');
        }
    }

    /**
     * Reporte de Ventas por Cliente en múltiples formatos
     */
    public function actionSalesByClientReport($format = 'pdf')
    {
        // Obtener todas las órdenes ordenadas por cliente y fecha
        $rentals = Rental::find()
            ->with(['client', 'car'])
            ->orderBy(['client_id' => SORT_ASC, 'created_at' => SORT_DESC])
            ->all();

        // Agrupar por cliente para obtener totales
        $salesByClient = [];
        foreach ($rentals as $rental) {
            $clientId = $rental->client_id;
            if (!isset($salesByClient[$clientId])) {
                $salesByClient[$clientId] = [
                    'client' => $rental->client,
                    'total_rentals' => 0,
                    'total_amount' => 0,
                    'orders' => []
                ];
            }
            $salesByClient[$clientId]['total_rentals']++;
            $salesByClient[$clientId]['total_amount'] += $rental->total_precio;
            $salesByClient[$clientId]['orders'][] = $rental;
        }

        // Ordenar por monto total descendente
        uasort($salesByClient, function($a, $b) {
            return $b['total_amount'] <=> $a['total_amount'];
        });

        switch ($format) {
            case 'pdf':
                return $this->generateSalesByClientPdf($salesByClient);
            case 'excel':
                return $this->generateSalesByClientExcel($salesByClient);
            case 'word':
                return $this->generateSalesByClientWord($salesByClient);
            default:
                throw new \Exception('Formato no soportado');
        }
    }

    /**
     * Reporte de Calendario Mensual en Excel
     */
    public function actionCalendarReport($year = null, $month = null)
    {
        $year = $year ?: date('Y');
        $month = $month ?: date('n');
        
        return $this->generateCalendarExcel($year, $month);
    }

    /**
     * Obtener alquileres organizados por empresa
     */
    private function getRentalsByCompany()
    {
        $rentals = Rental::find()
            ->with(['client', 'car'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $rentalsByCompany = [
            'Facto Rent a Car' => [],
            'Moviliza' => []
        ];

        foreach ($rentals as $rental) {
            $company = $rental->car ? $rental->car->empresa : 'Sin Empresa';
            if (isset($rentalsByCompany[$company])) {
                $rentalsByCompany[$company][] = $rental;
            } else {
                // Si no es una empresa conocida, agregar a Facto por defecto
                $rentalsByCompany['Facto Rent a Car'][] = $rental;
            }
        }

        return $rentalsByCompany;
    }

    /**
     * Calcular totales por empresa
     */
    private function calculateTotalsByCompany($rentalsByCompany)
    {
        $totals = [];
        foreach ($rentalsByCompany as $company => $rentals) {
            $total = 0;
            foreach ($rentals as $rental) {
                $total += $rental->total_precio;
            }
            $totals[$company] = $total;
        }
        return $totals;
    }

    /**
     * Generar PDF de Alquileres
     */
    private function generateRentalsPdf($rentalsByCompany, $totalsByCompany, $totalAmount)
    {
        $html = $this->renderPartial('_rentals_pdf', [
            'rentalsByCompany' => $rentalsByCompany,
            'totalsByCompany' => $totalsByCompany,
            'totalAmount' => $totalAmount,
            'reportNumber' => $this->generateReportNumber()
        ]);

        return $this->generateSimplePdf($html, 'reporte_ventas_alquileres_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Generar Excel de Alquileres
     */
    private function generateRentalsExcel($rentalsByCompany, $totalsByCompany, $totalAmount)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Reporte de Ventas');

            $row = 1;
            $reportNumber = $this->generateReportNumber();

            // Encabezados usando setCellValueByColumnAndRow
            $headers = ['Número de Reporte', 'Fecha', 'Cliente', 'Vehículo', 'Monto (₡)', 'Monto Total (₡)', 'Método de Pago', 'Ejecutivo', 'Empresa'];
            for ($col = 1; $col <= count($headers); $col++) {
                $sheet->setCellValueByColumnAndRow($col, $row, $headers[$col - 1]);
            }
            $row++;

            // Datos organizados por empresa
            foreach ($rentalsByCompany as $company => $rentals) {
                if (!empty($rentals)) {
                    // Agregar separador de empresa
                    $sheet->setCellValueByColumnAndRow(1, $row, '=== ' . $company . ' ===');
                    $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('22487A');
                    $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->getColor()->setRGB('FFFFFF');
                    $row++;

                    // Agregar datos de la empresa
                    foreach ($rentals as $rental) {
                        try {
                            $sheet->setCellValueByColumnAndRow(1, $row, $reportNumber);
                            $sheet->setCellValueByColumnAndRow(2, $row, date('d/m/Y', strtotime($rental->created_at)));
                            $sheet->setCellValueByColumnAndRow(3, $row, $rental->client ? $rental->client->full_name : 'N/A');
                            $sheet->setCellValueByColumnAndRow(4, $row, $rental->car ? $rental->car->nombre . ' (' . $rental->car->placa . ')' : 'N/A');
                            $sheet->setCellValueByColumnAndRow(5, $row, (float)$rental->total_precio);
                            $sheet->setCellValueByColumnAndRow(6, $row, (float)$rental->total_precio);
                            
                            // Manejo seguro de campos opcionales
                            $comprobante = isset($rental->comprobante_pago) && !empty($rental->comprobante_pago) ? $rental->comprobante_pago : 'N/A';
                            $ejecutivo = isset($rental->ejecutivo) && !empty($rental->ejecutivo) ? $rental->ejecutivo : 'N/A';
                            
                            $sheet->setCellValueByColumnAndRow(7, $row, $comprobante);
                            $sheet->setCellValueByColumnAndRow(8, $row, $ejecutivo);
                            $sheet->setCellValueByColumnAndRow(9, $row, $company);
                            $row++;
                        } catch (\Exception $e) {
                            Yii::error('Error procesando alquiler ID ' . $rental->id . ': ' . $e->getMessage());
                            $row++;
                        }
                    }

                    // Agregar total de la empresa
                    $sheet->setCellValueByColumnAndRow(5, $row, 'TOTAL ' . $company . ':');
                    $sheet->setCellValueByColumnAndRow(6, $row, (float)$totalsByCompany[$company]);
                    $sheet->getStyle('E' . $row . ':F' . $row)->getFont()->setBold(true);
                    $row += 2;
                }
            }

            // Total general
            $sheet->setCellValueByColumnAndRow(5, $row, 'TOTAL GENERAL:');
            $sheet->setCellValueByColumnAndRow(6, $row, (float)$totalAmount);
            $sheet->getStyle('E' . $row . ':F' . $row)->getFont()->setBold(true);

            // Formatear encabezados
            $sheet->getStyle('A1:I1')->getFont()->setBold(true);

            return $this->downloadExcel($spreadsheet, 'reporte_ventas_alquileres_' . date('Y-m-d_H-i-s') . '.xlsx');
            
        } catch (\Exception $e) {
            Yii::error('Error generando Excel: ' . $e->getMessage());
            throw new \Exception('Error generando reporte Excel: ' . $e->getMessage());
        }
    }

    /**
     * Generar Word de Alquileres
     */
    private function generateRentalsWord($rentalsByCompany, $totalsByCompany, $totalAmount)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Título
        $section->addText('REPORTE DE VENTAS (ALQUILERES)', ['bold' => true, 'size' => 16]);
        $section->addText('Número de Reporte: ' . $this->generateReportNumber(), ['bold' => true]);
        $section->addText('Fecha: ' . date('d/m/Y H:i:s'));
        $section->addTextBreak();

        // Datos organizados por empresa
        foreach ($rentalsByCompany as $company => $rentals) {
            if (!empty($rentals)) {
                // Título de la empresa
                $section->addText($company, ['bold' => true, 'size' => 14, 'color' => '22487A']);
                $section->addTextBreak();

                // Tabla para la empresa
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
                
                // Encabezados
                $table->addRow();
                $table->addCell(1200)->addText('Fecha', ['bold' => true]);
                $table->addCell(2000)->addText('Cliente', ['bold' => true]);
                $table->addCell(2000)->addText('Vehículo', ['bold' => true]);
                $table->addCell(1200)->addText('Monto (₡)', ['bold' => true]);
                $table->addCell(1200)->addText('Método de Pago', ['bold' => true]);
                $table->addCell(1200)->addText('Ejecutivo', ['bold' => true]);

                // Datos de la empresa
                foreach ($rentals as $rental) {
                    $table->addRow();
                    $table->addCell(1200)->addText(date('d/m/Y', strtotime($rental->created_at)));
                    $table->addCell(2000)->addText($rental->client ? $rental->client->full_name : 'N/A');
                    $table->addCell(2000)->addText($rental->car ? $rental->car->nombre . ' (' . $rental->car->placa . ')' : 'N/A');
                    $table->addCell(1200)->addText('₡' . number_format($rental->total_precio, 2));
                    $table->addCell(1200)->addText($rental->comprobante_pago ?? 'N/A');
                    $table->addCell(1200)->addText($rental->ejecutivo ?? 'N/A');
                }

                // Total de la empresa
                $section->addTextBreak();
                $section->addText('TOTAL ' . $company . ': ₡' . number_format($totalsByCompany[$company], 2), 
                    ['bold' => true, 'size' => 12, 'color' => '22487A']);
                $section->addTextBreak(2);
            }
        }

        // Total general
        $section->addText('TOTAL GENERAL: ₡' . number_format($totalAmount, 2), 
            ['bold' => true, 'size' => 14, 'color' => '000000']);

        return $this->downloadWord($phpWord, 'reporte_ventas_alquileres_' . date('Y-m-d_H-i-s') . '.docx');
    }

    /**
     * Generar PDF de Órdenes
     */
    private function generateOrdersPdf($orders, $totalAmount)
    {
        $html = $this->renderPartial('_orders_pdf', [
            'orders' => $orders,
            'totalAmount' => $totalAmount,
            'reportNumber' => $this->generateReportNumber()
        ]);

        return $this->generateSimplePdf($html, 'reporte_ordenes_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Generar Excel de Órdenes
     */
    private function generateOrdersExcel($orders, $totalAmount)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte de Órdenes');

        // Encabezados
        $headers = ['Número de Reporte', 'ID Ticket', 'Cliente', 'Artículo', 'Cantidad', 'Precio Unit.', 'Total (₡)'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Datos
        $row = 2;
        $reportNumber = $this->generateReportNumber();
        foreach ($orders as $order) {
            $sheet->setCellValue('A' . $row, $reportNumber);
            $sheet->setCellValue('B' . $row, $order->ticket_id);
            $sheet->setCellValue('C' . $row, $order->client ? $order->client->full_name : 'N/A');
            $sheet->setCellValue('D' . $row, 'Artículo #' . $order->article_id);
            $sheet->setCellValue('E' . $row, $order->quantity);
            $sheet->setCellValue('F' . $row, '₡' . number_format($order->unit_price, 2));
            $sheet->setCellValue('G' . $row, '₡' . number_format($order->total_price, 2));
            $row++;
        }

        // Total
        $sheet->setCellValue('F' . $row, 'TOTAL:');
        $sheet->setCellValue('G' . $row, '₡' . number_format($totalAmount, 2));

        // Formatear
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('F' . $row . ':G' . $row)->getFont()->setBold(true);

        return $this->downloadExcel($spreadsheet, 'reporte_ordenes_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Generar Word de Órdenes
     */
    private function generateOrdersWord($orders, $totalAmount)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Título
        $section->addText('REPORTE DE ÓRDENES', ['bold' => true, 'size' => 16]);
        $section->addText('Número de Reporte: ' . $this->generateReportNumber(), ['bold' => true]);
        $section->addText('Fecha: ' . date('d/m/Y H:i:s'));
        $section->addTextBreak();

        // Tabla
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        // Encabezados
        $table->addRow();
        $table->addCell(1500)->addText('ID Ticket', ['bold' => true]);
        $table->addCell(2000)->addText('Cliente', ['bold' => true]);
        $table->addCell(1500)->addText('Artículo', ['bold' => true]);
        $table->addCell(1000)->addText('Cantidad', ['bold' => true]);
        $table->addCell(1500)->addText('Total (₡)', ['bold' => true]);

        // Datos
        foreach ($orders as $order) {
            $table->addRow();
            $table->addCell(1500)->addText($order->ticket_id);
            $table->addCell(2000)->addText($order->client ? $order->client->full_name : 'N/A');
            $table->addCell(1500)->addText('Artículo #' . $order->article_id);
            $table->addCell(1000)->addText($order->quantity);
            $table->addCell(1500)->addText('₡' . number_format($order->total_price, 2));
        }

        $section->addTextBreak();
        $section->addText('TOTAL: ₡' . number_format($totalAmount, 2), ['bold' => true, 'size' => 14]);

        return $this->downloadWord($phpWord, 'reporte_ordenes_' . date('Y-m-d_H-i-s') . '.docx');
    }

    /**
     * Generar PDF de Clientes
     */
    private function generateClientsPdf($clients)
    {
        $html = $this->renderPartial('_clients_pdf', [
            'clients' => $clients,
            'reportNumber' => $this->generateReportNumber()
        ]);

        return $this->generateSimplePdf($html, 'reporte_clientes_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Generar Excel de Clientes
     */
    private function generateClientsExcel($clients)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte de Clientes');

        // Encabezados
        $headers = ['Número de Reporte', 'ID', 'Nombre Completo', 'Cédula', 'WhatsApp', 'Email', 'Dirección', 'Fecha Registro'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Datos
        $row = 2;
        $reportNumber = $this->generateReportNumber();
        foreach ($clients as $client) {
            $sheet->setCellValue('A' . $row, $reportNumber);
            $sheet->setCellValue('B' . $row, $client->id);
            $sheet->setCellValue('C' . $row, $client->full_name);
            $sheet->setCellValue('D' . $row, $client->cedula_fisica ?: 'N/A');
            $sheet->setCellValue('E' . $row, $client->whatsapp ?: 'N/A');
            $sheet->setCellValue('F' . $row, $client->email ?: 'N/A');
            $sheet->setCellValue('G' . $row, $client->address ?: 'N/A');
            $sheet->setCellValue('H' . $row, date('d/m/Y', strtotime($client->created_at)));
            $row++;
        }

        // Formatear
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        return $this->downloadExcel($spreadsheet, 'reporte_clientes_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Generar Word de Clientes
     */
    private function generateClientsWord($clients)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Título
        $section->addText('REPORTE DE CLIENTES', ['bold' => true, 'size' => 16]);
        $section->addText('Número de Reporte: ' . $this->generateReportNumber(), ['bold' => true]);
        $section->addText('Fecha: ' . date('d/m/Y H:i:s'));
        $section->addTextBreak();

        // Tabla
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        // Encabezados
        $table->addRow();
        $table->addCell(600)->addText('ID', ['bold' => true]);
        $table->addCell(2500)->addText('Nombre Completo', ['bold' => true]);
        $table->addCell(1200)->addText('Cédula', ['bold' => true]);
        $table->addCell(1200)->addText('WhatsApp', ['bold' => true]);
        $table->addCell(2000)->addText('Email', ['bold' => true]);
        $table->addCell(2000)->addText('Dirección', ['bold' => true]);

        // Datos
        foreach ($clients as $client) {
            $table->addRow();
            $table->addCell(600)->addText($client->id);
            $table->addCell(2500)->addText($client->full_name);
            $table->addCell(1200)->addText($client->cedula_fisica ?: 'N/A');
            $table->addCell(1200)->addText($client->whatsapp ?: 'N/A');
            $table->addCell(2000)->addText($client->email ?: 'N/A');
            $table->addCell(2000)->addText($client->address ?: 'N/A');
        }

        $section->addTextBreak();
        $section->addText('Total de Clientes: ' . count($clients), ['bold' => true, 'size' => 14]);

        return $this->downloadWord($phpWord, 'reporte_clientes_' . date('Y-m-d_H-i-s') . '.docx');
    }

    /**
     * Generar PDF de Ventas por Cliente
     */
    private function generateSalesByClientPdf($salesByClient)
    {
        $html = $this->renderPartial('_sales_by_client_pdf', [
            'salesByClient' => $salesByClient,
            'reportNumber' => $this->generateReportNumber()
        ]);

        return $this->generateSimplePdf($html, 'reporte_ventas_por_cliente_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Generar Excel de Ventas por Cliente
     */
    private function generateSalesByClientExcel($salesByClient)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ventas por Cliente');

        // Encabezados
        $headers = ['Cliente', 'ID Alquiler', 'Fecha Creación', 'Fecha Inicio', 'Fecha Final', 'Días', 'Tipo de Carro', 'Monto (₡)', 'Estado Pago', 'Método Pago'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Datos
        $row = 2;
        $grandTotal = 0;
        
        foreach ($salesByClient as $clientId => $clientData) {
            $client = $clientData['client'];
            $orders = $clientData['orders'];
            
            foreach ($orders as $order) {
                $sheet->setCellValue('A' . $row, $client ? strtoupper($client->full_name) : 'CLIENTE #' . $clientId);
                $sheet->setCellValue('B' . $row, $order->rental_id);
                $sheet->setCellValue('C' . $row, date('d/m/Y', strtotime($order->created_at)));
                $sheet->setCellValue('D' . $row, $order->fecha_inicio ? date('d/m/Y', strtotime($order->fecha_inicio)) : 'N/A');
                $sheet->setCellValue('E' . $row, $order->fecha_final ? date('d/m/Y', strtotime($order->fecha_final)) : 'N/A');
                $sheet->setCellValue('F' . $row, $order->cantidad_dias);
                $sheet->setCellValue('G' . $row, $order->car ? $order->car->nombre : 'N/A');
                $sheet->setCellValue('H' . $row, $order->total_precio);
                $sheet->setCellValue('I' . $row, strtoupper($order->estado_pago));
                $sheet->setCellValue('J' . $row, $order->comprobante_pago ?: 'N/A');
                
                // Aplicar color al estado de pago
                $statusCell = 'I' . $row;
                $statusStyle = $sheet->getStyle($statusCell);
                
                switch (strtolower($order->estado_pago)) {
                    case 'pendiente':
                        $statusStyle->getFont()->getColor()->setRGB('DC3545'); // Rojo
                        break;
                    case 'pagado':
                        $statusStyle->getFont()->getColor()->setRGB('28A745'); // Verde
                        break;
                    case 'reservado':
                        $statusStyle->getFont()->getColor()->setRGB('007BFF'); // Azul
                        break;
                    case 'cancelado':
                        $statusStyle->getFont()->getColor()->setRGB('6C757D'); // Gris
                        break;
                }
                
                $grandTotal += $order->total_precio;
                $row++;
            }
        }

        // Total general
        $sheet->setCellValue('G' . $row, 'TOTAL GENERAL:');
        $sheet->setCellValue('H' . $row, $grandTotal);

        // Formatear
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('H2:H' . ($row-1))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);

        return $this->downloadExcel($spreadsheet, 'reporte_ventas_por_cliente_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Generar Word de Ventas por Cliente
     */
    private function generateSalesByClientWord($salesByClient)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Título
        $section->addText('REPORTE DE VENTAS POR CLIENTE', ['bold' => true, 'size' => 16]);
        $section->addText('Número de Reporte: ' . $this->generateReportNumber(), ['bold' => true]);
        $section->addText('Fecha: ' . date('d/m/Y H:i:s'));
        $section->addTextBreak();

        // Datos por cliente
        $grandTotal = 0;
        foreach ($salesByClient as $clientId => $clientData) {
            $client = $clientData['client'];
            $orders = $clientData['orders'];
            
            // Encabezado del cliente
            $section->addText($client ? strtoupper($client->full_name) : 'CLIENTE #' . $clientId, ['bold' => true, 'size' => 14]);
            $section->addText('Total de Alquileres: ' . $clientData['total_rentals'] . ' | Monto Total: ₡' . number_format($clientData['total_amount'], 2), ['italic' => true]);
            $section->addTextBreak();

            // Tabla de órdenes del cliente
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
            
            // Encabezados de la tabla
            $table->addRow();
            $table->addCell(600)->addText('#', ['bold' => true]);
            $table->addCell(1000)->addText('ID Alquiler', ['bold' => true]);
            $table->addCell(1000)->addText('Fecha Creación', ['bold' => true]);
            $table->addCell(1000)->addText('Fecha Inicio', ['bold' => true]);
            $table->addCell(1000)->addText('Fecha Final', ['bold' => true]);
            $table->addCell(600)->addText('Días', ['bold' => true]);
            $table->addCell(1200)->addText('Tipo de Carro', ['bold' => true]);
            $table->addCell(800)->addText('Monto (₡)', ['bold' => true]);
            $table->addCell(800)->addText('Estado', ['bold' => true]);
            $table->addCell(800)->addText('Método Pago', ['bold' => true]);

            // Datos de las órdenes
            foreach ($orders as $index => $order) {
                $table->addRow();
                $table->addCell(600)->addText($index + 1);
                $table->addCell(1000)->addText($order->rental_id);
                $table->addCell(1000)->addText(date('d/m/Y', strtotime($order->created_at)));
                $table->addCell(1000)->addText($order->fecha_inicio ? date('d/m/Y', strtotime($order->fecha_inicio)) : 'N/A');
                $table->addCell(1000)->addText($order->fecha_final ? date('d/m/Y', strtotime($order->fecha_final)) : 'N/A');
                $table->addCell(600)->addText($order->cantidad_dias);
                $table->addCell(1200)->addText($order->car ? $order->car->nombre : 'N/A');
                $table->addCell(800)->addText('₡' . number_format($order->total_precio, 2));
                // Aplicar color al estado de pago
                $statusText = strtoupper($order->estado_pago);
                $statusColor = '000000'; // Negro por defecto
                
                switch (strtolower($order->estado_pago)) {
                    case 'pendiente':
                        $statusColor = 'DC3545'; // Rojo
                        break;
                    case 'pagado':
                        $statusColor = '28A745'; // Verde
                        break;
                    case 'reservado':
                        $statusColor = '007BFF'; // Azul
                        break;
                    case 'cancelado':
                        $statusColor = '6C757D'; // Gris
                        break;
                }
                
                $table->addCell(800)->addText($statusText, ['color' => $statusColor, 'bold' => true]);
                $table->addCell(800)->addText($order->comprobante_pago ?: 'N/A');
                
                $grandTotal += $order->total_precio;
            }

            $section->addTextBreak();
        }

        $section->addTextBreak();
        $section->addText('TOTAL GENERAL: ₡' . number_format($grandTotal, 2), ['bold' => true, 'size' => 14]);

        return $this->downloadWord($phpWord, 'reporte_ventas_por_cliente_' . date('Y-m-d_H-i-s') . '.docx');
    }

    /**
     * Generar Excel de Calendario Mensual
     */
    private function generateCalendarExcel($year, $month)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Calendario ' . $month . '-' . $year);

        // Obtener el número de días del mes
        $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
        $monthName = $this->getMonthName($month);
        
        // Título
        $sheet->setCellValue('A1', 'CALENDARIO MENSUAL - ' . strtoupper($monthName) . ' ' . $year);
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Encabezados de días de la semana
        $daysOfWeek = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $col = 'A';
        foreach ($daysOfWeek as $day) {
            $sheet->setCellValue($col . '2', $day);
            $col++;
        }

        // Obtener el primer día del mes y calcular el desplazamiento
        $firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
        $firstDay = $firstDay == 0 ? 7 : $firstDay; // Convertir domingo (0) a 7
        $startCol = chr(65 + $firstDay - 1); // A = 65

        // Generar los días del mes
        $row = 3;
        $currentCol = $startCol;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $cell = $currentCol . $row;
            $sheet->setCellValue($cell, $day);
            
            // Obtener alquileres para este día
            $rentals = Rental::find()
                ->where([
                    'and',
                    ['>=', 'fecha_inicio', $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $day)],
                    ['<=', 'fecha_final', $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $day)]
                ])
                ->with(['car'])
                ->all();

            if (!empty($rentals)) {
                $carsList = [];
                foreach ($rentals as $rental) {
                    if ($rental->car) {
                        $carsList[] = $rental->car->nombre . ' (' . $rental->car->placa . ')';
                    }
                }
                
                if (!empty($carsList)) {
                    $sheet->setCellValue($currentCol . ($row + 1), implode(', ', $carsList));
                    $sheet->getStyle($currentCol . ($row + 1))->getFont()->setSize(8);
                }
            }

            // Avanzar al siguiente día
            $currentCol++;
            if ($currentCol > 'G') {
                $currentCol = 'A';
                $row += 2; // Saltar una fila para los vehículos
            }
        }

        // Formatear
        $sheet->getStyle('A2:G2')->getFont()->setBold(true);
        $sheet->getStyle('A2:G2')->getAlignment()->setHorizontal('center');

        return $this->downloadExcel($spreadsheet, 'calendario_' . $monthName . '_' . $year . '.xlsx');
    }

    /**
     * Generar número de reporte único
     */
    private function generateReportNumber()
    {
        return 'REP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener nombre del mes en español
     */
    private function getMonthName($month)
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        return $months[$month] ?? 'Mes';
    }

    /**
     * Descargar archivo Excel
     */
    private function downloadExcel($spreadsheet, $filename)
    {
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Descargar archivo Word
     */
    private function downloadWord($phpWord, $filename)
    {
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Generar PDF simple usando HTML a PDF básico
     */
    private function generateSimplePdf($html, $filename)
    {
        // Configurar headers para PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Crear PDF básico usando HTML
        $pdfContent = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $filename . '</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; color: #22487a; margin-bottom: 10px; }
        .report-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .report-info { font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .number { text-align: right; }
        .total-section { margin-top: 20px; text-align: right; border-top: 2px solid #333; padding-top: 10px; }
        .total-amount { font-size: 16px; font-weight: bold; color: #22487a; }
    </style>
</head>
<body>
' . $html . '
</body>
</html>';

        // Para una implementación más robusta, aquí podrías usar una librería como mPDF o TCPDF
        // Por ahora, retornamos el HTML que el navegador puede convertir a PDF
        echo $pdfContent;
        exit;
    }

    /**
     * Generar PDF para Ventas 2 con colores y diseño mejorado
     */
    private function generateVentas2Pdf($rentalsByCompany, $totalsByCompany, $totalAmount)
    {
        $reportNumber = $this->generateReportNumber();
        $currentDate = date('d/m/Y H:i:s');
        
        $html = $this->renderPartial('_ventas2_pdf', [
            'rentalsByCompany' => $rentalsByCompany,
            'totalsByCompany' => $totalsByCompany,
            'totalAmount' => $totalAmount,
            'reportNumber' => $reportNumber,
            'currentDate' => $currentDate
        ]);
        
        $filename = 'Ventas_Reporte_' . date('Y-m-d_H-i-s') . '.pdf';
        return $this->generateSimplePdf($html, $filename);
    }

    /**
     * Generar Excel para Ventas 2 con colores y formato mejorado
     */
    private function generateVentas2Excel($rentalsByCompany, $totalsByCompany, $totalAmount)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Ventas - Reporte Colorido');
            
            // Estilos y colores
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86AB']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            
            $companyHeaderStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A23B72']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            
            $dataHeaderStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F18F01']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            
            $totalStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a365d']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            
            $row = 1;
            
            // Título principal
            $sheet->setCellValue('A1', 'VENTAS - REPORTE DE ALQUILERES CON COLORES');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->applyFromArray($headerStyle);
            $row += 2;
            
            // Información del reporte
            $sheet->setCellValue('A' . $row, 'Número de Reporte: ' . $this->generateReportNumber());
            $sheet->setCellValue('A' . ($row + 1), 'Fecha de Generación: ' . date('d/m/Y H:i:s'));
            $sheet->setCellValue('A' . ($row + 2), 'Total de Registros: ' . array_sum(array_map('count', $rentalsByCompany)));
            $row += 4;
            
            // Procesar cada empresa
            foreach ($rentalsByCompany as $company => $rentals) {
                if (empty($rentals)) continue;
                
                // Encabezado de empresa
                $sheet->setCellValue('A' . $row, $company);
                $sheet->mergeCells('A' . $row . ':J' . $row);
                $sheet->getStyle('A' . $row)->applyFromArray($companyHeaderStyle);
                $row++;
                
                // Encabezados de datos
                $headers = ['N°', 'Fecha', 'Cliente', 'Vehículo', 'Placa', 'Monto (₡)', 'Método Pago', 'Ejecutivo', 'Días', 'Estado'];
                for ($col = 0; $col < count($headers); $col++) {
                    $sheet->setCellValueByColumnAndRow($col + 1, $row, $headers[$col]);
                }
                $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($dataHeaderStyle);
                $row++;
                
                // Datos de alquileres
                $counter = 1;
                foreach ($rentals as $rental) {
                    $sheet->setCellValueByColumnAndRow(1, $row, $counter);
                    $sheet->setCellValueByColumnAndRow(2, $row, date('d/m/Y', strtotime($rental->created_at)));
                    $sheet->setCellValueByColumnAndRow(3, $row, $rental->client ? $rental->client->full_name : 'N/A');
                    $sheet->setCellValueByColumnAndRow(4, $row, $rental->car ? $rental->car->nombre : 'N/A');
                    $sheet->setCellValueByColumnAndRow(5, $row, $rental->car ? $rental->car->placa : 'N/A');
                    $sheet->setCellValueByColumnAndRow(6, $row, '₡' . number_format($rental->total_precio, 2, '.', ','));
                    
                    $comprobante = isset($rental->comprobante_pago) && !empty($rental->comprobante_pago) ? $rental->comprobante_pago : 'N/A';
                    $ejecutivo = isset($rental->ejecutivo) && !empty($rental->ejecutivo) ? $rental->ejecutivo : 'N/A';
                    
                    $sheet->setCellValueByColumnAndRow(7, $row, $comprobante);
                    $sheet->setCellValueByColumnAndRow(8, $row, $ejecutivo);
                    $sheet->setCellValueByColumnAndRow(9, $row, $rental->cantidad_dias);
                    $sheet->setCellValueByColumnAndRow(10, $row, $rental->estado_pago);
                    
                    // Aplicar colores alternados a las filas
                    if ($counter % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->setStartColor(new Color('F8F9FA'));
                    }
                    
                    $row++;
                    $counter++;
                }
                
                // Total de la empresa
                $sheet->setCellValue('A' . $row, 'TOTAL ' . $company);
                $sheet->setCellValue('F' . $row, '₡' . number_format($totalsByCompany[$company], 2, '.', ','));
                $sheet->mergeCells('A' . $row . ':E' . $row);
                $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($totalStyle);
                $row += 2;
            }
            
            // Total general
            $sheet->setCellValue('A' . $row, 'TOTAL GENERAL');
            $sheet->setCellValue('F' . $row, '₡' . number_format($totalAmount, 2, '.', ','));
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($totalStyle);
            
            // Autoajustar columnas
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Configurar headers para descarga
            $filename = 'Ventas_Reporte_' . date('Y-m-d_H-i-s') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            Yii::error('Error generando Excel Ventas 2: ' . $e->getMessage());
            throw $e;
        }
    }

}
