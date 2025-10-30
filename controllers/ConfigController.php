<?php

namespace app\controllers;

use Yii;
use app\models\CompanyConfig;
use app\models\Client;
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

        return $this->render('index', [
            'companyInfo' => $companyInfo,
            'fileConfigs' => $fileConfigs,
            'model' => $model,
        ]);
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
}