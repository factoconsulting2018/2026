<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo de Alquiler
 * Tabla: rentals
 *
 * @property int $id
 * @property string $rental_id
 * @property int $client_id
 * @property int|null $car_id
 * @property string|null $tipo_auto_solicitado
 * @property int $is_recurring_request
 * @property int $correapartir_enabled
 * @property string $fecha_correapartir
 * @property string $fecha_inicio
 * @property string $hora_inicio
 * @property string $fecha_final
 * @property string $hora_final
 * @property string $lugar_entrega
 * @property string $lugar_retiro
 * @property int $cantidad_dias
 * @property float $precio_por_dia
 * @property int $medio_dia_enabled
 * @property float $medio_dia_valor
 * @property int $is_async
 * @property float $total_precio
 * @property string $condiciones_especiales
 * @property string $choferes_autorizados
 * @property string $estado_pago
 * @property string $comprobante_pago
 * @property string|null $numero_factura
 * @property string|null $fecha_factura
 * @property string $ejecutivo
 * @property string $ejecutivo_otro
 * @property string $created_at
 * @property string $updated_at
 * @property string $abono1_descripcion
 * @property float $abono1_monto
 * @property string $abono2_descripcion
 * @property float $abono2_monto
 * @property string $abono3_descripcion
 * @property float $abono3_monto
 * @property string $abono4_descripcion
 * @property float $abono4_monto
 * @property string $abono5_descripcion
 * @property float $abono5_monto
 * @property int|null $parent_rental_id
 * @property int|null $swapped_to_rental_id
 * @property string|null $swap_date
 * @property string|null $swap_reason
 */
class Rental extends ActiveRecord
{
    /** Atributos copiados al crear una orden de reemplazo por cambio de vehículo */
    public const SWAP_COPY_ATTRIBUTES = [
        'client_id',
        'correapartir_enabled',
        'fecha_correapartir',
        'hora_inicio',
        'hora_final',
        'lugar_entrega',
        'lugar_retiro',
        'precio_por_dia',
        'medio_dia_enabled',
        'medio_dia_valor',
        'condiciones_especiales',
        'choferes_autorizados',
        'estado_pago',
        'comprobante_pago',
        'ejecutivo',
        'ejecutivo_otro',
        'numero_factura',
        'fecha_factura',
        'abono1_descripcion',
        'abono1_monto',
        'abono2_descripcion',
        'abono2_monto',
        'abono3_descripcion',
        'abono3_monto',
        'abono4_descripcion',
        'abono4_monto',
        'abono5_descripcion',
        'abono5_monto',
    ];
    // Campo virtual de compatibilidad: algunas vistas antiguas envían este nombre
    public $custom_conditions_html;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rentals';
    }

    /**
     * Inicializar valores por defecto
     */
    public function init()
    {
        parent::init();
        
        // Establecer valores por defecto
        if ($this->isNewRecord) {
            $this->fecha_inicio = date('Y-m-d');
            $this->cantidad_dias = 3;
            $this->estado_pago = 'pendiente';
            $this->hora_inicio = '09:00';
            $this->hora_final = '18:00';
            $this->lugar_entrega = 'Base 1';
            $this->lugar_retiro = 'Base 1';
            $this->comprobante_pago = 'Sinpe Móvil';
            $this->ejecutivo = 'Gerardo';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_id', 'fecha_inicio', 'cantidad_dias'], 'required'],
            [['client_id', 'car_id', 'correapartir_enabled', 'medio_dia_enabled', 'cantidad_dias', 'is_async', 'is_recurring_request', 'parent_rental_id', 'swapped_to_rental_id'], 'integer'],
            [['is_recurring_request'], 'default', 'value' => 0],
            [['is_recurring_request'], 'in', 'range' => [0, 1]],
            [['fecha_inicio', 'fecha_final', 'hora_inicio', 'hora_final', 'fecha_correapartir', 'fecha_factura', 'swap_date', 'created_at', 'updated_at'], 'safe'],
            [['swap_reason'], 'string'],
            [['precio_por_dia', 'medio_dia_valor', 'abono1_monto', 'abono2_monto', 'abono3_monto', 'abono4_monto', 'abono5_monto'], 'number'], // Removido total_precio porque es columna generada
            [['rental_id', 'lugar_entrega', 'lugar_retiro', 'estado_pago', 'numero_factura', 'ejecutivo', 'ejecutivo_otro', 'abono1_descripcion', 'abono2_descripcion', 'abono3_descripcion', 'abono4_descripcion', 'abono5_descripcion'], 'string', 'max' => 255],
            [['tipo_auto_solicitado'], 'string', 'max' => 80],
            [['comprobante_pago'], 'string', 'max' => 500],
            [['condiciones_especiales', 'choferes_autorizados'], 'string'],
            [['custom_conditions_html'], 'string'],
            [['estado_pago'], 'in', 'range' => ['pendiente', 'pagado', 'reservado', 'cancelado', 'finalizado']],
            [['fecha_inicio', 'fecha_final'], 'validateDates'],
            // Mover la validación de disponibilidad al final para que se ejecute después de calcular fecha_final
            [['car_id'], 'validateCarAvailability'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rental_id' => 'ID del Alquiler',
            'client_id' => 'Cliente',
            'car_id' => 'Vehículo',
            'tipo_auto_solicitado' => 'Tipo de auto solicitado',
            'is_recurring_request' => 'Solicitud recurrente',
            'correapartir_enabled' => 'Correapartir Habilitado',
            'fecha_correapartir' => 'Fecha Correapartir',
            'fecha_inicio' => 'Fecha de Inicio',
            'hora_inicio' => 'Hora de Inicio',
            'fecha_final' => 'Fecha Final',
            'hora_final' => 'Hora Final',
            'lugar_entrega' => 'Lugar de Entrega',
            'lugar_retiro' => 'Lugar de Retiro',
            'cantidad_dias' => 'Cantidad de Días',
            'precio_por_dia' => 'Precio por Día',
            'medio_dia_enabled' => '1/2 Día',
            'medio_dia_valor' => 'Valor Medio Día',
            'is_async' => 'Orden Asincrónica',
            'total_precio' => 'Precio Total',
            'condiciones_especiales' => 'Condiciones Especiales',
            'choferes_autorizados' => 'Choferes Autorizados',
            'estado_pago' => 'Estado de Pago',
            'comprobante_pago' => 'Comprobante de Pago',
            'numero_factura' => 'Número de Factura',
            'fecha_factura' => 'Fecha de Factura',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
            'abono1_descripcion' => 'Abono 1 Descripción',
            'abono1_monto' => 'Abono 1 Monto',
            'abono2_descripcion' => 'Abono 2 Descripción',
            'abono2_monto' => 'Abono 2 Monto',
            'abono3_descripcion' => 'Abono 3 Descripción',
            'abono3_monto' => 'Abono 3 Monto',
            'abono4_descripcion' => 'Abono 4 Descripción',
            'abono4_monto' => 'Abono 4 Monto',
            'abono5_descripcion' => 'Abono 5 Descripción',
            'abono5_monto' => 'Abono 5 Monto',
            'parent_rental_id' => 'Orden padre (reemplazo)',
            'swapped_to_rental_id' => 'Orden de reemplazo',
            'swap_date' => 'Fecha de cambio de vehículo',
            'swap_reason' => 'Motivo del cambio',
        ];
    }

    /**
     * Formatear fechas después de cargar desde la base de datos
     */
    public function afterFind()
    {
        parent::afterFind();
        
        // Asegurar que las fechas estén en formato correcto
        if (!empty($this->fecha_inicio) && $this->fecha_inicio !== '0000-00-00') {
            try {
                $date = new \DateTime($this->fecha_inicio);
                $this->fecha_inicio = $date->format('Y-m-d');
            } catch (\Exception $e) {
                // Si hay error, limpiar la fecha
                $this->fecha_inicio = null;
            }
        }
        
        if (!empty($this->fecha_final) && $this->fecha_final !== '0000-00-00') {
            try {
                $date = new \DateTime($this->fecha_final);
                $this->fecha_final = $date->format('Y-m-d');
            } catch (\Exception $e) {
                // Si hay error, limpiar la fecha
                $this->fecha_final = null;
            }
        }
    }

    /**
     * Excluir total_precio de las operaciones de inserción y actualización
     * porque es una columna generada y calcular fecha_final automáticamente
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Compatibilidad: mapear custom_conditions_html -> condiciones_especiales si llega desde formularios antiguos
            if (!empty($this->custom_conditions_html) && empty($this->condiciones_especiales)) {
                $this->condiciones_especiales = $this->custom_conditions_html;
            }
            // Generar rental_id si es nuevo
            if ($insert && empty($this->rental_id)) {
                $this->rental_id = $this->generateRentalId();
            }
            
            // Calcular fecha_final automáticamente cuando hay fecha_inicio y cantidad_dias
            // Solo si fecha_inicio ≠ fecha_final (no es alquiler por horas)
            if (!empty($this->fecha_inicio) && !empty($this->cantidad_dias) && $this->cantidad_dias > 0) {
                // Si fecha_final ya está establecida y es igual a fecha_inicio, no calcular (es alquiler por horas)
                if (!empty($this->fecha_final) && ($this->fecha_inicio === $this->fecha_final)) {
                    // Es alquiler por horas, no modificar fecha_final
                } else {
                    // Es alquiler por días, calcular fecha_final
                    try {
                        $fechaInicio = new \DateTime($this->fecha_inicio);
                        // Si cantidad_dias representa horas (número pequeño, típicamente < 24), no calcular como días
                        // Pero si no hay fecha_final establecida, calcularla
                        if (empty($this->fecha_final) || $this->cantidad_dias >= 24) {
                            $fechaInicio->add(new \DateInterval('P' . ($this->cantidad_dias >= 24 ? ($this->cantidad_dias / 24) : $this->cantidad_dias) . 'D'));
                            $this->fecha_final = $fechaInicio->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        // Si hay error en el cálculo de fechas, mantener fecha_final como está
                        Yii::warning('Error al calcular fecha_final: ' . $e->getMessage());
                    }
                }
            }
            
            // Si fecha_inicio está vacía o es inválida, limpiar fecha_final también
            if (empty($this->fecha_inicio) || $this->fecha_inicio === '0000-00-00') {
                $this->fecha_final = null;
            }
            
            // No necesitamos excluir total_precio aquí porque se maneja en safeAttributes()
            
            return true;
        }
        return false;
    }
    
    /**
     * Refrescar el modelo después de guardar para obtener el total_precio calculado
     * por la columna generada de la base de datos
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Refrescar el modelo para obtener el valor calculado de total_precio
        // Esto es necesario porque total_precio es una columna generada
        $this->refresh();
    }

    /**
     * Define qué atributos son seguros para asignación masiva
     * Excluye total_precio porque es una columna generada
     */
    public function safeAttributes()
    {
        $safe = parent::safeAttributes();
        $unsafe = ['total_precio']; // Excluir total_precio de operaciones de guardado
        
        return array_diff($safe, $unsafe);
    }

    /**
     * Genera un ID único para el alquiler (máximo 8 caracteres)
     * @return string
     */
    protected function generateRentalId()
    {
        // Usar solo los últimos 3 dígitos del timestamp + 4 dígitos aleatorios
        $timestamp = substr(time(), -3);
        $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        return 'R' . $timestamp . $random;
    }

    /**
     * Obtiene el cliente asociado
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    /**
     * Obtiene el vehículo asociado
     * @return \yii\db\ActiveQuery
     */
    public function getCar()
    {
        return $this->hasOne(Car::class, ['id' => 'car_id']);
    }

    /**
     * Orden original de la que proviene este reemplazo
     */
    public function getParentRental()
    {
        return $this->hasOne(self::class, ['id' => 'parent_rental_id']);
    }

    /**
     * Orden de reemplazo creada tras cambio de vehículo
     */
    public function getReplacementRental()
    {
        return $this->hasOne(self::class, ['id' => 'swapped_to_rental_id']);
    }

    /**
     * Esta orden fue sustituida por otra (vehículo cambiado)
     */
    public function isSwapped(): bool
    {
        return !empty($this->swapped_to_rental_id);
    }

    /**
     * Esta orden es el reemplazo de una orden anterior
     */
    public function isReplacement(): bool
    {
        return !empty($this->parent_rental_id);
    }

    /**
     * Fecha efectiva de fin de bloqueo del vehículo (antes del cambio si aplica)
     */
    public function getEffectiveFinalDate(): ?string
    {
        if ($this->isSwapped() && !empty($this->swap_date)) {
            return $this->swap_date;
        }
        return $this->fecha_final ? substr((string) $this->fecha_final, 0, 10) : null;
    }

    /**
     * Último día en que el vehículo de esta orden bloquea disponibilidad
     */
    public static function getEffectiveBlockEndDate(Rental $rental): string
    {
        if ($rental->isSwapped() && !empty($rental->swap_date)) {
            return date('Y-m-d', strtotime($rental->swap_date . ' -1 day'));
        }
        return substr((string) $rental->fecha_final, 0, 10);
    }

    /**
     * Indica si se puede iniciar un cambio de vehículo desde esta orden
     */
    public function canSwapVehicle(): bool
    {
        return !$this->isSwapped()
            && !$this->isReplacement()
            && (int) $this->is_async === 0
            && $this->estado_pago !== 'cancelado';
    }

    /**
     * Indica si el precio por dia del reemplazo es igual al de esta orden.
     * Si retorna true, el reemplazo no implica venta adicional.
     */
    public function swapPriceMatches(): bool
    {
        if (!$this->isSwapped()) {
            return false;
        }
        $replacement = $this->replacementRental;
        if (!$replacement) {
            return false;
        }
        return (float) $replacement->precio_por_dia === (float) $this->precio_por_dia;
    }

    /**
     * Indica si esta orden permite deshacer el cambio de vehiculo. Solo cuando
     * el reemplazo conservo el mismo precio por dia (sin venta adicional) y
     * aun no se ha cobrado/facturado.
     */
    public function canUndoSwap(): bool
    {
        if (!$this->isSwapped()) {
            return false;
        }
        $replacement = $this->replacementRental;
        if (!$replacement) {
            return false;
        }
        if ((float) $replacement->precio_por_dia !== (float) $this->precio_por_dia) {
            return false;
        }
        if (!empty($replacement->numero_factura)) {
            return false;
        }
        return true;
    }

    /**
     * Crea una renta hija copiando campos de la original
     */
    public static function createReplacementFrom(Rental $original, array $overrides = []): Rental
    {
        $replacement = new self();
        foreach (self::SWAP_COPY_ATTRIBUTES as $attr) {
            $replacement->$attr = $original->$attr;
        }
        foreach ($overrides as $key => $value) {
            $replacement->$key = $value;
        }
        $replacement->parent_rental_id = $original->id;
        $replacement->is_async = 0;
        return $replacement;
    }

    /**
     * Obtiene las órdenes asociadas
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['rental_id' => 'id']);
    }

    /**
     * Calcula el número de días del alquiler
     * @return int
     */
    public function getDays()
    {
        $inicio = new \DateTime($this->fecha_inicio);
        $fin = new \DateTime($this->fecha_final);
        $diff = $inicio->diff($fin);
        return $diff->days + 1; // Incluye el día de inicio
    }

    /**
     * Verifica si el alquiler está activo
     * @return bool
     */
    public function isActive()
    {
        return $this->estado_pago === 'pagado';
    }

    /**
     * Verifica si el alquiler ha finalizado
     * @return bool
     */
    public function isCompleted()
    {
        return $this->estado_pago === 'reservado';
    }

    /**
     * Calcula el precio total basado en días y tarifa del vehículo
     * Si medio_dia_enabled = 1, el total es solo medio_dia_valor (tarifa fija)
     * @return float
     */
    public function calculateTotalPrice()
    {
        // Si está habilitado el medio día, el total es solo el valor del medio día (tarifa fija)
        if (!empty($this->medio_dia_enabled) && $this->medio_dia_valor > 0) {
            return (float)$this->medio_dia_valor;
        }
        
        // Si no, calcular normalmente: días * precio por día
        return (float)($this->cantidad_dias * $this->precio_por_dia);
    }

    /**
     * Obtiene la URL del comprobante de pago
     * @return string|null
     */
    public function getComprobanteUrl()
    {
        if ($this->comprobante_pago) {
            return \Yii::getAlias('@web/' . $this->comprobante_pago);
        }
        return null;
    }

    /**
     * Verifica si existe un comprobante de pago
     * @return bool
     */
    public function hasComprobante()
    {
        if (!$this->comprobante_pago) {
            return false;
        }
        
        $filePath = \Yii::getAlias('@webroot/' . $this->comprobante_pago);
        return file_exists($filePath);
    }

    /**
     * Obtiene el nombre del archivo del comprobante
     * @return string|null
     */
    public function getComprobanteFileName()
    {
        if ($this->comprobante_pago) {
            return basename($this->comprobante_pago);
        }
        return null;
    }

    /**
     * Obtiene el tamaño del archivo del comprobante en bytes
     * @return int|null
     */
    public function getComprobanteSize()
    {
        if ($this->hasComprobante()) {
            $filePath = \Yii::getAlias('@webroot/' . $this->comprobante_pago);
            return filesize($filePath);
        }
        return null;
    }

    /**
     * Obtiene el tamaño del archivo del comprobante formateado
     * @return string|null
     */
    public function getComprobanteSizeFormatted()
    {
        $size = $this->getComprobanteSize();
        if ($size === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Verifica si el comprobante es una imagen
     * @return bool
     */
    public function isComprobanteImage()
    {
        if (!$this->comprobante_pago) {
            return false;
        }
        
        $extension = strtolower(pathinfo($this->comprobante_pago, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
    }

    /**
     * Elimina el archivo del comprobante
     * @return bool
     */
    public function deleteComprobante()
    {
        if ($this->hasComprobante()) {
            $filePath = \Yii::getAlias('@webroot/' . $this->comprobante_pago);
            if (unlink($filePath)) {
                $this->comprobante_pago = null;
                return $this->save(false);
            }
        }
        return false;
    }

    /**
     * Obtiene las opciones de estados de pago
     * @return array
     */
    public static function getPaymentStatusOptions()
    {
        return [
            'pendiente' => 'Pendiente',
            'pagado' => 'Pagado',
            'reservado' => 'Reservado',
            'finalizado' => 'Finalizado',
            'cancelado' => 'Cancelado',
        ];
    }

    /**
     * Obtiene el label del estado de pago
     * @return string
     */
    public function getPaymentStatusLabel()
    {
        $options = self::getPaymentStatusOptions();
        return $options[$this->estado_pago] ?? ucfirst($this->estado_pago);
    }

    /**
     * Cierra automaticamente las ordenes pagadas cuya fecha final ya paso.
     *
     * "Pagado" confirma el dinero; "Finalizado" libera el vehiculo para
     * disponibilidad, conflictos y sincronizacion de estados.
     *
     * @param string|null $today Fecha de corte en formato Y-m-d. Si se omite, usa hoy.
     * @return int cantidad de ordenes actualizadas.
     */
    public static function autoFinalizeCompleted($today = null): int
    {
        $cutoff = $today ?: date('Y-m-d');

        return (int) static::updateAll(
            [
                'estado_pago' => 'finalizado',
                'updated_at' => new \yii\db\Expression('NOW()'),
            ],
            [
                'and',
                ['estado_pago' => 'pagado'],
                ['is_async' => 0],
                ['swapped_to_rental_id' => null],
                ['<', 'fecha_final', $cutoff],
            ]
        );
    }

    /**
     * Validar fechas de alquiler
     */
    public function validateDates($attribute, $params)
    {
        if ($this->fecha_inicio && $this->fecha_final) {
            $allowPastDates = ((int)$this->is_async === 1);

            // Verificar que la fecha de inicio no sea en el pasado (solo para rentas normales)
            if (!$allowPastDates && strtotime($this->fecha_inicio) < strtotime('today')) {
                $this->addError($attribute, 'La fecha de inicio no puede ser en el pasado.');
                return;
            }

            // Verificar que la fecha de fin no sea anterior a la de inicio (permitir igual para alquileres por horas)
            if (strtotime($this->fecha_final) < strtotime($this->fecha_inicio)) {
                $this->addError($attribute, 'La fecha de fin no puede ser anterior a la fecha de inicio.');
                return;
            }

            // Si fecha_inicio = fecha_final (mismo día): es alquiler por horas/medio día
            if ($this->fecha_inicio === $this->fecha_final || strtotime($this->fecha_final) === strtotime($this->fecha_inicio)) {
                // Alquiler por horas - validar horas
                if (!empty($this->hora_inicio) && !empty($this->hora_final)) {
                    try {
                        $horaInicio = new \DateTime($this->fecha_inicio . ' ' . $this->hora_inicio);
                        $horaFinal = new \DateTime($this->fecha_final . ' ' . $this->hora_final);
                        
                        // Validar que hora_final sea posterior a hora_inicio
                        if ($horaFinal <= $horaInicio) {
                            $this->addError($attribute, 'La hora final debe ser posterior a la hora de inicio cuando es el mismo día.');
                            return;
                        }
                        
                        // Si es alquiler por horas en el mismo día, cantidad_dias = 1 (no las horas totales)
                        // Si está marcado como medio día, podría ser 0.5, pero por ahora usamos 1
                        $this->cantidad_dias = 1;
                    } catch (\Exception $e) {
                        // Si hay error, establecer como 1 día por defecto
                        $this->cantidad_dias = 1;
                        Yii::warning('Error al calcular horas: ' . $e->getMessage());
                    }
                } else {
                    // Si no hay horas, establecer como 1 día por defecto
                    $this->cantidad_dias = 1;
                }
            } else {
                // Alquiler por días - calcular días correctamente
                $start = new \DateTime($this->fecha_inicio);
                $end = new \DateTime($this->fecha_final);
                $diff = $start->diff($end);
                $this->cantidad_dias = $diff->days; // No sumar 1, diff ya da la diferencia correcta
            }
        }
    }

    /**
     * Validar disponibilidad del vehículo
     */
    public function validateCarAvailability($attribute, $params)
    {
        if ((int)$this->is_async === 1) {
            return;
        }

        if ((int)($this->is_recurring_request ?? 0) === 1) {
            return;
        }

        if ($this->car_id && $this->fecha_inicio && $this->cantidad_dias) {
            // Asegurar que fecha_final esté calculada antes de la validación
            if (empty($this->fecha_final) && !empty($this->fecha_inicio) && !empty($this->cantidad_dias) && $this->cantidad_dias > 0) {
                try {
                    $fechaInicio = new \DateTime($this->fecha_inicio);
                    $fechaInicio->add(new \DateInterval('P' . $this->cantidad_dias . 'D'));
                    $this->fecha_final = $fechaInicio->format('Y-m-d');
                } catch (\Exception $e) {
                    // Si hay error en el cálculo, no validar disponibilidad
                    return;
                }
            }
            
            if ($this->fecha_final) {
                $excludeId = $this->isNewRecord ? null : $this->id;
                
                if (!CarAvailability::isCarAvailable($this->car_id, $this->fecha_inicio, $this->fecha_final, $excludeId)) {
                    $this->addError($attribute, 'El vehículo no está disponible en las fechas seleccionadas. Por favor, seleccione otras fechas.');
                }
            }
        }
    }

    /**
     * Obtener alquileres que se solapan con este
     */
    public function getOverlappingRentals()
    {
        if (!$this->car_id || !$this->fecha_inicio || !$this->fecha_final) {
            return [];
        }

        return static::find()
            ->where(['car_id' => $this->car_id])
            ->andWhere(['!=', 'estado_pago', 'cancelado'])
            ->andWhere(['!=', 'id', $this->id])
            ->andWhere([
                'or',
                // Solapamiento en la fecha de inicio
                ['and',
                    ['<=', 'fecha_inicio', $this->fecha_inicio],
                    ['>=', 'fecha_final', $this->fecha_inicio]
                ],
                // Solapamiento en la fecha de fin
                ['and',
                    ['<=', 'fecha_inicio', $this->fecha_final],
                    ['>=', 'fecha_final', $this->fecha_final]
                ],
                // El rango está completamente dentro de otro alquiler
                ['and',
                    ['>=', 'fecha_inicio', $this->fecha_inicio],
                    ['<=', 'fecha_final', $this->fecha_final]
                ]
            ])
            ->all();
    }

    /**
     * Solicitudes de alquiler enviadas por clientes ya registrados (formulario público).
     *
     * @return self[]
     */
    public static function findRecurringRequests(): array
    {
        return self::find()
            ->with(['client'])
            ->where(['is_recurring_request' => 1])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }
}

