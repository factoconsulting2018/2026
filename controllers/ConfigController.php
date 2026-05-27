<?php

namespace app\controllers;

use Yii;
use app\models\CompanyConfig;
use app\models\Client;
use app\models\ApiKey;
use app\components\WhatsAppNotifier;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * ConfigController maneja la configuración de la empresa
 */
class ConfigController extends Controller
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
     * Página principal de configuración
     */
    public function actionIndex()
    {
        // Crear directorios si no existen
        CompanyConfig::createDirectories();

        $companyInfo = CompanyConfig::getCompanyInfo();
        $fileConfigs = CompanyConfig::getFileConfigs();

        $model = new CompanyConfig();
        
        // Obtener API Keys (manejar caso cuando la tabla no existe todavía)
        $apiKeys = [];
        try {
            // Verificar si la tabla existe antes de consultarla
            $tableSchema = Yii::$app->db->getTableSchema('api_keys', true);
            if ($tableSchema !== null) {
                $apiKeys = ApiKey::find()->orderBy(['created_at' => SORT_DESC])->all();
            }
        } catch (\Exception $e) {
            // Si hay error (tabla no existe), usar array vacío
            Yii::warning('Tabla api_keys no existe aún: ' . $e->getMessage(), 'config');
            $apiKeys = [];
        }

        return $this->render('index', [
            'companyInfo' => $companyInfo,
            'fileConfigs' => $fileConfigs,
            'model' => $model,
            'apiKeys' => $apiKeys,
            'incidentNotifEnabled' => CompanyConfig::getConfig(CompanyConfig::INCIDENT_NOTIF_ENABLED, '0') === '1',
            'incidentNotifFrequencyDays' => max(1, min(365, (int) CompanyConfig::getConfig(CompanyConfig::INCIDENT_NOTIF_FREQUENCY_DAYS, '3'))),
            'dekraConfig' => CompanyConfig::getDekraConfig(),
            'dekraDefaultMap' => CompanyConfig::getDekraDefaultPlateMonthMap(),
            'rentalOrderPdfFormat' => CompanyConfig::getRentalOrderPdfFormat(),
            'rentalOrderPdfVehicleImgMaxW' => CompanyConfig::getRentalOrderPdfVehicleImageMaxWidth(),
            'rentalOrderPdfVehicleImgMaxH' => CompanyConfig::getRentalOrderPdfVehicleImageMaxHeight(),
            'rentalOrderPdfTextMode' => CompanyConfig::getRentalOrderPdfTextMode(),
            'rentalOrderPdfTextScale' => CompanyConfig::getRentalOrderPdfTextScalePercent(),
            'rentalOrderPdfTextSizes' => CompanyConfig::getRentalOrderPdfTextSizes(),
            'rentalOrderPdfTextBaseSizes' => CompanyConfig::getRentalOrderPdfTextBaseSizes(),
            'whatsappConfig' => CompanyConfig::getWhatsAppConfig(),
            'rentalOrderPdfTextFormValues' => (function () {
                $base = CompanyConfig::getRentalOrderPdfTextBaseSizes();
                return [
                    'header_titulo' => (int) CompanyConfig::getConfig(CompanyConfig::RENTAL_ORDER_PDF_TEXT_HEADER_TITULO, (string) $base['header_titulo']),
                    'header_modelo' => (int) CompanyConfig::getConfig(CompanyConfig::RENTAL_ORDER_PDF_TEXT_HEADER_MODELO, (string) $base['header_modelo']),
                    'header_meta' => (int) CompanyConfig::getConfig(CompanyConfig::RENTAL_ORDER_PDF_TEXT_HEADER_META, (string) $base['header_meta']),
                    'empresa_nombre' => (int) CompanyConfig::getConfig(CompanyConfig::RENTAL_ORDER_PDF_TEXT_EMPRESA_NOMBRE, (string) $base['empresa_nombre']),
                    'empresa_linea' => (int) CompanyConfig::getConfig(CompanyConfig::RENTAL_ORDER_PDF_TEXT_EMPRESA_LINEA, (string) $base['empresa_linea']),
                ];
            })(),
        ]);
    }

    /**
     * Notificaciones post-login de insidentes con saldo pendiente.
     */
    public function actionUpdateIncidentNotifications()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(Url::to(['config/index']) . '#notificaciones');
        }

        $post = Yii::$app->request->post();
        $enabled = isset($post['incident_notifications_enabled']) && $post['incident_notifications_enabled'] === '1';
        $freq = (int) ($post['incident_notifications_frequency_days'] ?? 3);
        $freq = max(1, min(365, $freq));

        $wasEnabled = CompanyConfig::getConfig(CompanyConfig::INCIDENT_NOTIF_ENABLED, '0') === '1';
        if ($wasEnabled && !$enabled) {
            $pwd = (string) ($post['disable_password'] ?? '');
            $expected = (string) (Yii::$app->params['incidentDeletePassword'] ?? '3030');
            if (!hash_equals($expected, $pwd)) {
                Yii::$app->session->setFlash('error', 'Contraseña incorrecta. Las notificaciones siguen activas.');
                return $this->redirect(Url::to(['config/index']) . '#notificaciones');
            }
        }

        CompanyConfig::setConfig(
            CompanyConfig::INCIDENT_NOTIF_ENABLED,
            $enabled ? '1' : '0',
            'Modal de insidentes pendientes tras el inicio de sesión'
        );
        CompanyConfig::setConfig(
            CompanyConfig::INCIDENT_NOTIF_FREQUENCY_DAYS,
            (string) $freq,
            'Días de pausa tras cerrar el aviso tres veces antes de volver a mostrarlo'
        );

        Yii::$app->response->cookies->remove(\app\components\IncidentNotificationHelper::COOKIE_SNOOZE_UNTIL);

        Yii::$app->session->setFlash('success', 'Configuración de notificaciones guardada.');
        return $this->redirect(Url::to(['config/index']) . '#notificaciones');
    }

    /**
     * Formato PDF de la orden de alquiler (General o Moderna).
     */
    public function actionUpdateRentalOrderPdfFormat()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(Url::to(['config/index']) . '#orden-renta-pdf');
        }
        $fmt = (string) Yii::$app->request->post('rental_order_pdf_format', 'general');
        $fmt = $fmt === 'moderna' ? 'moderna' : 'general';
        CompanyConfig::setConfig(
            CompanyConfig::RENTAL_ORDER_PDF_FORMAT,
            $fmt,
            'Vista HTML para PDF de orden de alquiler: general o moderna'
        );

        $imgW = (int) Yii::$app->request->post('rental_order_pdf_vehicle_img_max_w', 170);
        $imgH = (int) Yii::$app->request->post('rental_order_pdf_vehicle_img_max_h', 90);
        CompanyConfig::setConfig(
            CompanyConfig::RENTAL_ORDER_PDF_VEHICLE_IMG_MAX_W,
            (string) max(40, min(400, $imgW)),
            'Ancho máximo (px) imagen vehículo en PDF moderna'
        );
        CompanyConfig::setConfig(
            CompanyConfig::RENTAL_ORDER_PDF_VEHICLE_IMG_MAX_H,
            (string) max(30, min(280, $imgH)),
            'Alto máximo (px) imagen vehículo en PDF moderna'
        );

        $textMode = (string) Yii::$app->request->post('rental_order_pdf_text_mode', 'proporcional');
        $textMode = $textMode === 'numeros' ? 'numeros' : 'proporcional';
        CompanyConfig::setConfig(
            CompanyConfig::RENTAL_ORDER_PDF_TEXT_MODE,
            $textMode,
            'Modo de tamaño de textos PDF moderna: proporcional o numeros'
        );

        $textScale = (int) Yii::$app->request->post('rental_order_pdf_text_scale', 100);
        CompanyConfig::setConfig(
            CompanyConfig::RENTAL_ORDER_PDF_TEXT_SCALE,
            (string) max(50, min(300, $textScale)),
            'Escala porcentual de textos PDF moderna'
        );

        $base = CompanyConfig::getRentalOrderPdfTextBaseSizes();
        $numericFields = [
            CompanyConfig::RENTAL_ORDER_PDF_TEXT_HEADER_TITULO => ['post' => 'rental_order_pdf_text_header_titulo', 'default' => $base['header_titulo']],
            CompanyConfig::RENTAL_ORDER_PDF_TEXT_HEADER_MODELO => ['post' => 'rental_order_pdf_text_header_modelo', 'default' => $base['header_modelo']],
            CompanyConfig::RENTAL_ORDER_PDF_TEXT_HEADER_META => ['post' => 'rental_order_pdf_text_header_meta', 'default' => $base['header_meta']],
            CompanyConfig::RENTAL_ORDER_PDF_TEXT_EMPRESA_NOMBRE => ['post' => 'rental_order_pdf_text_empresa_nombre', 'default' => $base['empresa_nombre']],
            CompanyConfig::RENTAL_ORDER_PDF_TEXT_EMPRESA_LINEA => ['post' => 'rental_order_pdf_text_empresa_linea', 'default' => $base['empresa_linea']],
        ];
        foreach ($numericFields as $configKey => $meta) {
            $pt = (int) Yii::$app->request->post($meta['post'], $meta['default']);
            $pt = max(8, min(120, $pt > 0 ? $pt : $meta['default']));
            CompanyConfig::setConfig($configKey, (string) $pt, 'Tamaño de texto (pt) PDF moderna');
        }

        Yii::$app->session->setFlash('success', 'Formato de orden de renta (PDF) guardado.');

        return $this->redirect(Url::to(['config/index']) . '#orden-renta-pdf');
    }

    /**
     * Actualizar información de la empresa
     */
    public function actionUpdateCompany()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            
            // Actualizar información básica
            CompanyConfig::setConfig('company_name', $post['company_name'] ?? '');
            CompanyConfig::setConfig('company_address', $post['company_address'] ?? '');
            CompanyConfig::setConfig('company_phone', $post['company_phone'] ?? '');
            CompanyConfig::setConfig('company_email', $post['company_email'] ?? '');
            CompanyConfig::setConfig('simemovil_number', $post['simemovil_number'] ?? '');

            // Actualizar cuentas bancarias
            if (isset($post['bank_accounts'])) {
                $bankAccounts = [];
                foreach ($post['bank_accounts'] as $account) {
                    if (!empty($account['bank']) && !empty($account['account'])) {
                        $bankAccounts[] = [
                            'bank' => $account['bank'],
                            'account' => $account['account'],
                            'currency' => $account['currency'] ?? '₡'
                        ];
                    }
                }
                CompanyConfig::setConfig('bank_accounts', json_encode($bankAccounts));
            }

            Yii::$app->session->setFlash('success', 'Información de la empresa actualizada exitosamente.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Guardar condiciones del alquiler en HTML (configuración global)
     */
    public function actionUpdateConditionsHtml()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['index']);
        }
        $html = Yii::$app->request->post('conditions_html', '');
        \app\models\CompanyConfig::setConfig('rental_conditions_html', $html, 'Condiciones de alquiler (HTML)');
        Yii::$app->session->setFlash('success', 'Condiciones del alquiler (HTML) actualizadas.');
        return $this->redirect(['index']);
    }

    /**
     * Subir logo
     */
    public function actionUploadLogo()
    {
        if (Yii::$app->request->isPost) {
            $model = new CompanyConfig();
            $model->logoFile = UploadedFile::getInstance($model, 'logoFile');

            if (!$model->logoFile) {
                Yii::$app->session->setFlash('error', 'No se seleccionó ningún archivo.');
                return $this->redirect(['index']);
            }

            // Verificar errores de carga
            if ($model->logoFile->hasError) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor.',
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario.',
                    UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente.',
                    UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo.',
                    UPLOAD_ERR_NO_TMP_DIR => 'No hay directorio temporal.',
                    UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo.',
                    UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida.',
                ];
                
                $errorCode = $model->logoFile->error;
                $errorMessage = $errorMessages[$errorCode] ?? "Error de subida desconocido (código: {$errorCode})";
                Yii::$app->session->setFlash('error', $errorMessage);
                return $this->redirect(['index']);
            }

            // Validar el archivo
            if ($model->validate(['logoFile'])) {
                // Usar el nuevo método de procesamiento que redimensiona a 300x300
                $fileName = $model->processLogo($model->logoFile, CompanyConfig::LOGO_FILE);
                if ($fileName) {
                    Yii::$app->session->setFlash('success', 'Logo subido y procesado exitosamente (redimensionado a 150x150px).');
                } else {
                    Yii::$app->session->setFlash('error', 'Error al procesar el logo. Verifica que sea una imagen válida.');
                }
            } else {
                $errors = $model->getErrors();
                $errorMessage = 'Error en la validación: ';
                foreach ($errors as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $errorMessage .= $error . ' ';
                    }
                }
                Yii::$app->session->setFlash('error', trim($errorMessage));
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Subir condiciones de alquiler
     */
    public function actionUploadConditions()
    {
        if (Yii::$app->request->isPost) {
            $model = new CompanyConfig();
            $model->conditionsFile = UploadedFile::getInstance($model, 'conditionsFile');

            if ($model->conditionsFile) {
                $fileName = $model->uploadFile($model->conditionsFile, CompanyConfig::RENTAL_CONDITIONS_FILE);
                if ($fileName) {
                    Yii::$app->session->setFlash('success', 'Condiciones de alquiler subidas exitosamente.');
                } else {
                    Yii::$app->session->setFlash('error', 'Error al subir las condiciones.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'No se seleccionó ningún archivo.');
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Eliminar logo
     */
    public function actionDeleteLogo()
    {
        if (Yii::$app->request->isPost) {
            $model = new CompanyConfig();
            if ($model->deleteFile(CompanyConfig::LOGO_FILE)) {
                Yii::$app->session->setFlash('success', 'Logo eliminado exitosamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al eliminar el logo.');
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Eliminar condiciones
     */
    public function actionDeleteConditions()
    {
        if (Yii::$app->request->isPost) {
            $model = new CompanyConfig();
            if ($model->deleteFile(CompanyConfig::RENTAL_CONDITIONS_FILE)) {
                Yii::$app->session->setFlash('success', 'Condiciones eliminadas exitosamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al eliminar las condiciones.');
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Vista previa del logo
     */
    public function actionPreviewLogo()
    {
        $logoPath = CompanyConfig::getLogoPath();
        if ($logoPath && file_exists(Yii::getAlias('@webroot' . $logoPath))) {
            return $this->redirect($logoPath);
        }
        
        throw new NotFoundHttpException('Logo no encontrado.');
    }

    /**
     * Descargar condiciones
     */
    public function actionDownloadConditions()
    {
        $conditionsPath = CompanyConfig::getConditionsPath();
        if ($conditionsPath && file_exists(Yii::getAlias('@webroot' . $conditionsPath))) {
            return Yii::$app->response->sendFile(
                Yii::getAlias('@webroot' . $conditionsPath),
                'condiciones_alquiler.pdf'
            );
        }
        
        throw new NotFoundHttpException('Archivo de condiciones no encontrado.');
    }

    /**
     * Obtener información de la empresa via AJAX
     */
    public function actionGetCompanyInfo()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => true,
                'data' => CompanyConfig::getCompanyInfo(),
            ];
        }

        throw new NotFoundHttpException('Página no encontrada.');
    }

    /**
     * Obtener información del logo via AJAX
     */
    public function actionGetLogoInfo()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            $logoPath = CompanyConfig::getLogoPath();
            $logoFile = CompanyConfig::getConfig(CompanyConfig::LOGO_FILE);
            $logoExists = $logoPath && file_exists(Yii::getAlias('@webroot/' . CompanyConfig::LOGO_DIR . $logoFile));
            
            return [
                'success' => true,
                'data' => [
                    'hasLogo' => $logoExists,
                    'logoPath' => $logoPath,
                    'logoFile' => $logoFile,
                    'logoUrl' => $logoExists ? $logoPath : null,
                ],
            ];
        }

        throw new NotFoundHttpException('Página no encontrada.');
    }

    /**
     * Exportar plantilla Excel para importar clientes
     */
    public function actionExportClientTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar encabezados
            $headers = [
                'A' => 'Nombre Completo',
                'B' => 'Cédula Física',
                'C' => 'Email',
                'D' => 'WhatsApp',
                'E' => 'Dirección',
                'F' => 'Es Cliente Facto',
                'G' => 'Es Aliado',
                'H' => 'Estado',
                'I' => 'Notas'
            ];
            
            // Escribir encabezados
            $row = 1;
            foreach ($headers as $col => $header) {
                $sheet->setCellValue($col . $row, $header);
            }
            
            // Estilo para encabezados
            $headerRange = 'A1:I1';
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            
            // Agregar datos de ejemplo
            $examples = [
                ['Juan Pérez González', '123456789', 'juan@email.com', '8888-8888', 'San José, Costa Rica', '1', '0', 'active', 'Cliente preferencial'],
                ['María García López', '987654321', 'maria@email.com', '7777-7777', 'Alajuela, Costa Rica', '1', '1', 'active', 'Aliado comercial'],
                ['Carlos Rodríguez', '456789123', '', '', 'Cartago, Costa Rica', '0', '0', 'active', '']
            ];
            
            $row = 2;
            foreach ($examples as $example) {
                $col = 'A';
                foreach ($example as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }
            
            // Ajustar ancho de columnas
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Configurar respuesta
            $filename = 'plantilla_clientes_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error al generar la plantilla: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    /**
     * Importar clientes desde archivo Excel
     */
    public function actionImportClients()
    {
        if (Yii::$app->request->isPost) {
            $model = new CompanyConfig();
            $model->clientsFile = UploadedFile::getInstance($model, 'clientsFile');

            if (!$model->clientsFile) {
                Yii::$app->session->setFlash('error', 'No se seleccionó ningún archivo.');
                return $this->redirect(['index']);
            }

            try {
                // Validar tipo de archivo
                $allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
                if (!in_array($model->clientsFile->type, $allowedTypes)) {
                    Yii::$app->session->setFlash('error', 'Tipo de archivo no válido. Solo se permiten archivos Excel (.xlsx, .xls).');
                    return $this->redirect(['index']);
                }

                // Leer archivo Excel
                $inputFileName = $model->clientsFile->tempName;
                $spreadsheet = IOFactory::load($inputFileName);
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();

                $imported = 0;
                $errors = [];
                $duplicates = 0;

                // Procesar cada fila (empezando desde la fila 2 para saltar encabezados)
                for ($row = 2; $row <= $highestRow; $row++) {
                    try {
                        $data = [
                            'full_name' => trim($sheet->getCell('A' . $row)->getValue()),
                            'cedula_fisica' => trim($sheet->getCell('B' . $row)->getValue()),
                            'email' => trim($sheet->getCell('C' . $row)->getValue()),
                            'whatsapp' => trim($sheet->getCell('D' . $row)->getValue()),
                            'address' => trim($sheet->getCell('E' . $row)->getValue()),
                            'es_cliente_facto' => $sheet->getCell('F' . $row)->getValue() == '1' ? 1 : 0,
                            'es_aliado' => $sheet->getCell('G' . $row)->getValue() == '1' ? 1 : 0,
                            'status' => trim($sheet->getCell('H' . $row)->getValue()) ?: 'active',
                            'notes' => trim($sheet->getCell('I' . $row)->getValue())
                        ];

                        // Validar campos requeridos
                        if (empty($data['full_name']) || empty($data['cedula_fisica'])) {
                            $errors[] = "Fila {$row}: Nombre completo y cédula física son requeridos";
                            continue;
                        }

                        // Verificar si el cliente ya existe
                        $existingClient = Client::find()->where(['cedula_fisica' => $data['cedula_fisica']])->one();
                        if ($existingClient) {
                            $duplicates++;
                            continue;
                        }

                        // Crear nuevo cliente
                        $client = new Client();
                        $client->attributes = $data;
                        $client->status = in_array($data['status'], ['active', 'inactive']) ? $data['status'] : 'active';

                        if ($client->save()) {
                            $imported++;
                        } else {
                            $errorMsg = "Fila {$row}: " . implode(', ', $client->getFirstErrors());
                            $errors[] = $errorMsg;
                        }

                    } catch (\Exception $e) {
                        $errors[] = "Fila {$row}: Error al procesar - " . $e->getMessage();
                    }
                }

                // Mostrar resultados
                $message = "✅ Importación completada:\n";
                $message .= "• Clientes importados: {$imported}\n";
                $message .= "• Clientes duplicados omitidos: {$duplicates}\n";
                $message .= "• Errores: " . count($errors);

                if (!empty($errors)) {
                    $message .= "\n\n❌ Errores encontrados:\n" . implode("\n", array_slice($errors, 0, 10));
                    if (count($errors) > 10) {
                        $message .= "\n... y " . (count($errors) - 10) . " errores más";
                    }
                }

                Yii::$app->session->setFlash('success', $message);

            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        }

        return $this->redirect(['index']);
    }

    // ==================== API KEYS ====================

    /**
     * Crear nueva API Key
     */
    public function actionCreateApiKey()
    {
        // Verificar si la tabla existe
        try {
            $tableSchema = Yii::$app->db->getTableSchema('api_keys', true);
            if ($tableSchema === null) {
                Yii::$app->session->setFlash('error', 'La tabla api_keys no existe. Por favor ejecuta la migración: php yii migrate');
                return $this->redirect(['index']);
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error al verificar tabla api_keys: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
        
        if (Yii::$app->request->isPost) {
            $model = new ApiKey();
            $post = Yii::$app->request->post();
            
            $model->name = $post['name'] ?? '';
            $model->description = $post['description'] ?? '';
            $model->is_active = 1;
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'API Key creada exitosamente. La key se mostrará una sola vez: ' . $model->key);
                Yii::$app->session->setFlash('new_api_key', $model->key);
            } else {
                $errors = implode(', ', $model->getFirstErrors());
                Yii::$app->session->setFlash('error', 'Error al crear API Key: ' . $errors);
            }
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Activar/Desactivar API Key
     */
    public function actionToggleApiKey($id)
    {
        // Verificar si la tabla existe
        try {
            $tableSchema = Yii::$app->db->getTableSchema('api_keys', true);
            if ($tableSchema === null) {
                Yii::$app->session->setFlash('error', 'La tabla api_keys no existe. Por favor ejecuta la migración: php yii migrate');
                return $this->redirect(['index']);
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error al verificar tabla api_keys: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
        
        if (Yii::$app->request->isPost) {
            $model = ApiKey::findOne($id);
            if ($model) {
                $model->is_active = $model->is_active ? 0 : 1;
                if ($model->save(false)) {
                    $status = $model->is_active ? 'activada' : 'desactivada';
                    Yii::$app->session->setFlash('success', "API Key {$status} exitosamente.");
                } else {
                    Yii::$app->session->setFlash('error', 'Error al actualizar API Key.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'API Key no encontrada.');
            }
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Eliminar API Key
     */
    public function actionDeleteApiKey($id)
    {
        // Verificar si la tabla existe
        try {
            $tableSchema = Yii::$app->db->getTableSchema('api_keys', true);
            if ($tableSchema === null) {
                Yii::$app->session->setFlash('error', 'La tabla api_keys no existe. Por favor ejecuta la migración: php yii migrate');
                return $this->redirect(['index']);
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error al verificar tabla api_keys: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
        
        if (Yii::$app->request->isPost) {
            $model = ApiKey::findOne($id);
            if ($model) {
                if ($model->delete()) {
                    Yii::$app->session->setFlash('success', 'API Key eliminada exitosamente.');
                } else {
                    Yii::$app->session->setFlash('error', 'Error al eliminar API Key.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'API Key no encontrada.');
            }
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Mostrar documentación de la API
     */
    public function actionApiDocs()
    {
        return $this->render('api-docs');
    }

    /**
     * Guardar configuración de recordatorios automáticos de Dekra.
     */
    public function actionUpdateDekraConfig()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(Url::to(['config/index']) . '#dekra');
        }

        $post = Yii::$app->request->post();
        $enabled = isset($post['dekra_enabled']) && $post['dekra_enabled'] === '1';
        $yearsAhead = (int) ($post['dekra_years_ahead'] ?? 3);
        $dayOfMonth = (int) ($post['dekra_day_of_month'] ?? 1);
        $tallerName = (string) ($post['dekra_taller_name'] ?? 'Dekra (Revisión Vehicular)');

        $defaultMap = CompanyConfig::getDekraDefaultPlateMonthMap();
        $rawMap = is_array($post['dekra_map'] ?? null) ? $post['dekra_map'] : [];
        $plateMap = [];
        for ($digit = 0; $digit <= 9; $digit++) {
            $plateMap[$digit] = isset($rawMap[$digit]) ? (int) $rawMap[$digit] : $defaultMap[$digit];
        }

        CompanyConfig::saveDekraConfig($enabled, $yearsAhead, $dayOfMonth, $tallerName, $plateMap);

        $regenerated = 0;
        if ($enabled) {
            try {
                $regenerated = \app\models\MaintenanceOrder::ensureDekraReminders();
            } catch (\Throwable $e) {
                Yii::error('Error regenerando recordatorios Dekra: ' . $e->getMessage(), __METHOD__);
            }
        }

        $message = 'Configuración de recordatorios Dekra guardada.';
        if ($regenerated > 0) {
            $message .= ' Se generaron ' . $regenerated . ' órdenes nuevas con el mapeo actualizado.';
        }
        Yii::$app->session->setFlash('success', $message);

        return $this->redirect(Url::to(['config/index']) . '#dekra');
    }

    // ==================== WhatsApp API ====================

    /**
     * Guarda la configuración de la integración con WhatsApp.
     */
    public function actionUpdateWhatsapp()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(Url::to(['config/index']) . '#whatsapp');
        }

        $post = Yii::$app->request->post();
        $phones = [];
        for ($i = 1; $i <= 5; $i++) {
            $phones[$i] = (string) ($post['whatsapp_admin_phone_' . $i] ?? '');
        }

        CompanyConfig::saveWhatsAppConfig(
            isset($post['whatsapp_enabled']) && $post['whatsapp_enabled'] === '1',
            (string) ($post['whatsapp_api_url'] ?? 'https://descargapro.com'),
            (string) ($post['whatsapp_session_id'] ?? 'facto_rent'),
            (string) ($post['whatsapp_country_code'] ?? '506'),
            isset($post['whatsapp_notify_on_create']) && $post['whatsapp_notify_on_create'] === '1',
            $phones,
            (string) ($post['whatsapp_public_base_url'] ?? ''),
            isset($post['whatsapp_notify_client']) && $post['whatsapp_notify_client'] === '1',
            isset($post['whatsapp_daily_enabled']) && $post['whatsapp_daily_enabled'] === '1',
            (string) ($post['whatsapp_daily_time'] ?? '08:00')
        );

        Yii::$app->session->setFlash('success', 'Configuración de WhatsApp guardada.');
        return $this->redirect(Url::to(['config/index']) . '#whatsapp');
    }

    /**
     * Si el cliente envió por POST un api_url o session_id distinto del guardado,
     * lo persiste en la configuración antes de ejecutar la acción.
     *
     * @return array{api_url:string, session_id:string} La configuración efectiva ya aplicada.
     */
    private function applyWhatsappSessionOverrides(): array
    {
        $cfg = CompanyConfig::getWhatsAppConfig();

        $apiUrlIn = (string) (Yii::$app->request->post('api_url')
            ?? Yii::$app->request->getBodyParam('api_url')
            ?? '');
        $sidIn = (string) (Yii::$app->request->post('session_id')
            ?? Yii::$app->request->getBodyParam('session_id')
            ?? '');

        $apiUrl = $apiUrlIn !== '' ? rtrim(trim($apiUrlIn), '/') : $cfg['api_url'];
        $sid = $sidIn !== '' ? trim($sidIn) : $cfg['session_id'];

        $needsSave = false;
        if ($apiUrlIn !== '' && rtrim(trim($apiUrlIn), '/') !== rtrim((string) $cfg['api_url'], '/')) {
            $needsSave = true;
        }
        if ($sidIn !== '' && trim($sidIn) !== (string) $cfg['session_id']) {
            $needsSave = true;
        }

        if ($needsSave) {
            // Persistir solo URL/Session ID (mantener el resto de la configuración intacta)
            CompanyConfig::setConfig(CompanyConfig::WHATSAPP_API_URL, $apiUrl, 'URL base de la API WhatsApp');
            CompanyConfig::setConfig(CompanyConfig::WHATSAPP_SESSION_ID, $sid !== '' ? $sid : 'facto_rent', 'sessionId de WhatsApp');
            Yii::info("WhatsApp config actualizada desde acción: api_url={$apiUrl} session_id={$sid}", 'whatsapp');
        }

        return ['api_url' => $apiUrl, 'session_id' => $sid];
    }

    /**
     * Inicia (o reinicia) la sesión de WhatsApp en el servidor remoto.
     * Si el formulario envió api_url/session_id, los guarda primero en configuración
     * para que la sesión creada se llame exactamente como el usuario indicó.
     */
    public function actionWhatsappStart()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $eff = $this->applyWhatsappSessionOverrides();
        $res = WhatsAppNotifier::startSession($eff['api_url'], $eff['session_id']);
        $bodyStatus = is_array($res['body'] ?? null) ? ($res['body']['status'] ?? null) : null;
        return [
            'success' => $res['ok'] || in_array($bodyStatus, ['ok', 'exists', 'starting', 'connecting', 'connected', 'created'], true),
            'status' => $res['status'],
            'data' => $this->decorateWhatsappBody($res['body']),
            'error' => $res['error'],
            'session_id' => $eff['session_id'],
        ];
    }

    /**
     * Devuelve el QR base64 de la sesión actual (si está pendiente de escanear).
     * Permite override transitorio de api_url/session_id vía query (?session_id=...).
     */
    public function actionWhatsappQr()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cfg = CompanyConfig::getWhatsAppConfig();
        $sid = trim((string) Yii::$app->request->get('session_id', $cfg['session_id']));
        $apiUrl = rtrim(trim((string) Yii::$app->request->get('api_url', $cfg['api_url'])), '/');
        $res = WhatsAppNotifier::getQr($apiUrl, $sid);
        return [
            'success' => $res['ok'],
            'status' => $res['status'],
            'data' => $this->decorateWhatsappBody($res['body']),
            'error' => $res['error'],
            'session_id' => $sid,
        ];
    }

    /**
     * Devuelve el estado actual de la sesión (conectada / pendiente / no existe).
     * Permite override transitorio de api_url/session_id vía query.
     */
    public function actionWhatsappStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cfg = CompanyConfig::getWhatsAppConfig();
        $sid = trim((string) Yii::$app->request->get('session_id', $cfg['session_id']));
        $apiUrl = rtrim(trim((string) Yii::$app->request->get('api_url', $cfg['api_url'])), '/');
        $res = WhatsAppNotifier::getStatus($apiUrl, $sid);
        return [
            'success' => $res['ok'],
            'status' => $res['status'],
            'data' => $this->decorateWhatsappBody($res['body']),
            'error' => $res['error'],
            'session_id' => $sid,
        ];
    }

    /**
     * Cierra la sesión actual de WhatsApp en el servidor remoto.
     * Acepta override transitorio (POST) para cerrar una sesión específica si el formulario
     * tiene un session_id distinto al guardado. La sesión que efectivamente se cerró se
     * persiste en config (para mantener la consistencia con la UI).
     */
    public function actionWhatsappDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $eff = $this->applyWhatsappSessionOverrides();
        $res = WhatsAppNotifier::deleteSession($eff['api_url'], $eff['session_id']);
        $bodyStatus = is_array($res['body'] ?? null) ? ($res['body']['status'] ?? null) : null;
        // Considerar éxito si la API confirmó el cierre o si la sesión ya no existía.
        $success = $res['ok'] || $bodyStatus === 'not_found' || (is_array($res['body'] ?? null) && !empty($res['body']['ok']));

        Yii::info(
            'WhatsApp DELETE session=' . $eff['session_id']
            . ' http=' . $res['status']
            . ' body_status=' . ($bodyStatus ?? 'null')
            . ' ok=' . ($success ? '1' : '0'),
            'whatsapp'
        );

        return [
            'success' => $success,
            'status' => $res['status'],
            'data' => $this->decorateWhatsappBody($res['body']),
            'error' => $res['error'],
            'session_id' => $eff['session_id'],
        ];
    }

    /**
     * Normaliza el cuerpo de respuesta agregando isConnected/sessionExists
     * para que el frontend pueda interpretar el nuevo formato de la API
     * ({ status: "connected"|"connecting"|"not_found" }).
     *
     * @param array<string,mixed>|null $body
     * @return array<string,mixed>
     */
    private function decorateWhatsappBody($body): array
    {
        if (!is_array($body)) {
            $body = [];
        }
        $status = isset($body['status']) ? strtolower((string) $body['status']) : '';
        $message = isset($body['message']) ? (string) $body['message'] : '';

        $notFound = $status === 'not_found'
            || ($status === 'error' && preg_match('/no encontrada|not found|no existe/i', $message));

        $body['isConnected'] = $status === 'connected'
            || (isset($body['isConnected']) && (bool) $body['isConnected'] === true);
        $body['sessionExists'] = !$notFound && ($status !== '' || isset($body['sessionId']) || isset($body['qr']));
        return $body;
    }

    /**
     * Envía un mensaje de prueba a los teléfonos administrativos configurados.
     */
    public function actionWhatsappTest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cfg = CompanyConfig::getWhatsAppConfig();

        $numbers = WhatsAppNotifier::getAdminNumbers($cfg);
        if (empty($numbers)) {
            return ['success' => false, 'message' => 'No hay teléfonos administradores configurados.'];
        }

        $status = WhatsAppNotifier::getStatus($cfg['api_url'], $cfg['session_id']);
        if (!WhatsAppNotifier::isConnected($status)) {
            return [
                'success' => false,
                'message' => 'La sesión de WhatsApp no está conectada. Escanee el QR primero.',
            ];
        }

        $company = CompanyConfig::getCompanyInfo();
        $msg = '✅ Prueba de integración WhatsApp — ' . ($company['name'] ?? 'Renta de Vehículos')
            . "\nFecha: " . date('d/m/Y h:i A')
            . "\nSi recibe este mensaje, la conexión está funcionando correctamente.";

        $sent = 0;
        $errors = [];
        foreach ($numbers as $number) {
            $res = WhatsAppNotifier::sendText($cfg['api_url'], $cfg['session_id'], $number, $msg);
            if ($res['ok']) {
                $sent++;
            } else {
                $errors[] = $number . ': ' . ($res['error'] ?? 'fallo');
            }
        }

        return [
            'success' => $sent > 0,
            'sent' => $sent,
            'total' => count($numbers),
            'errors' => $errors,
            'message' => $sent === count($numbers)
                ? ('Mensaje enviado a ' . $sent . ' teléfono(s).')
                : ('Enviado a ' . $sent . ' de ' . count($numbers) . ' teléfono(s).'),
        ];
    }

    /**
     * Envía el resumen diario de prueba (ignora hora/anti-duplicado).
     */
    public function actionWhatsappDailyTest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $report = WhatsAppNotifier::sendDailyDeliveries(true);
            return [
                'success' => !empty($report['sent']) && (int) $report['sent'] > 0,
                'report' => $report,
                'message' => !empty($report['skipped_reason'])
                    ? (string) $report['skipped_reason']
                    : ('Enviado a ' . ((int) ($report['sent'] ?? 0)) . ' destinatario(s).'),
            ];
        } catch (\Throwable $e) {
            Yii::error('actionWhatsappDailyTest: ' . $e->getMessage(), 'whatsapp');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}