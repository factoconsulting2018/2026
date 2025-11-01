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
 * @property int $car_id
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
 * @property float $total_precio
 * @property string $condiciones_especiales
 * @property string $choferes_autorizados
 * @property string $estado_pago
 * @property string $comprobante_pago
 * @property string $ejecutivo
 * @property string $ejecutivo_otro
 * @property string $created_at
 * @property string $updated_at
 */
class Rental extends ActiveRecord
{
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
            [['client_id', 'car_id', 'fecha_inicio', 'cantidad_dias'], 'required'],
            [['client_id', 'car_id', 'correapartir_enabled', 'medio_dia_enabled', 'cantidad_dias'], 'integer'],
            [['fecha_inicio', 'fecha_final', 'hora_inicio', 'hora_final', 'fecha_correapartir', 'created_at', 'updated_at'], 'safe'],
            [['precio_por_dia', 'medio_dia_valor'], 'number'], // Removido total_precio porque es columna generada
            [['rental_id', 'lugar_entrega', 'lugar_retiro', 'estado_pago', 'ejecutivo', 'ejecutivo_otro'], 'string', 'max' => 255],
            [['comprobante_pago'], 'string', 'max' => 500],
            [['condiciones_especiales', 'choferes_autorizados'], 'string'],
            [['custom_conditions_html'], 'string'],
            [['estado_pago'], 'in', 'range' => ['pendiente', 'pagado', 'reservado', 'cancelado']],
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
            'total_precio' => 'Precio Total',
            'condiciones_especiales' => 'Condiciones Especiales',
            'choferes_autorizados' => 'Choferes Autorizados',
            'estado_pago' => 'Estado de Pago',
            'comprobante_pago' => 'Comprobante de Pago',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
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
     * Actualizar total_precio después de guardar usando SQL directo
     * Esto es necesario porque total_precio está excluido de safeAttributes()
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Calcular total_precio (incluye medio_dia_valor si está habilitado)
        $totalCalculado = $this->calculateTotalPrice();
        
        // Actualizar usando SQL directo para evitar problemas con safeAttributes()
        Yii::$app->db->createCommand()
            ->update('rentals', 
                ['total_precio' => $totalCalculado], 
                ['id' => $this->id]
            )
            ->execute();
        
        // Actualizar el atributo en el modelo para que esté sincronizado
        $this->total_precio = $totalCalculado;
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
     * @return float
     */
    public function calculateTotalPrice()
    {
        $total = $this->cantidad_dias * $this->precio_por_dia;
        
        // Si está habilitado el medio día, agregar su valor
        if (!empty($this->medio_dia_enabled) && $this->medio_dia_valor > 0) {
            $total += $this->medio_dia_valor;
        }
        
        return $total;
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
            'cancelado' => 'Cancelado'
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
     * Validar fechas de alquiler
     */
    public function validateDates($attribute, $params)
    {
        if ($this->fecha_inicio && $this->fecha_final) {
            // Verificar que la fecha de inicio no sea en el pasado
            if (strtotime($this->fecha_inicio) < strtotime('today')) {
                $this->addError($attribute, 'La fecha de inicio no puede ser en el pasado.');
                return;
            }

            // Verificar que la fecha de fin no sea anterior a la de inicio (permitir igual para alquileres por horas)
            if (strtotime($this->fecha_final) < strtotime($this->fecha_inicio)) {
                $this->addError($attribute, 'La fecha de fin no puede ser anterior a la fecha de inicio.');
                return;
            }

            // Si fecha_inicio = fecha_final (mismo día): calcular horas
            if ($this->fecha_inicio === $this->fecha_final || strtotime($this->fecha_final) === strtotime($this->fecha_inicio)) {
                // Alquiler por horas - calcular horas entre hora_inicio y hora_final
                if (!empty($this->hora_inicio) && !empty($this->hora_final)) {
                    try {
                        $horaInicio = new \DateTime($this->fecha_inicio . ' ' . $this->hora_inicio);
                        $horaFinal = new \DateTime($this->fecha_final . ' ' . $this->hora_final);
                        
                        // Validar que hora_final sea posterior a hora_inicio
                        if ($horaFinal <= $horaInicio) {
                            $this->addError($attribute, 'La hora final debe ser posterior a la hora de inicio cuando es el mismo día.');
                            return;
                        }
                        
                        // Calcular diferencia en horas
                        $diff = $horaInicio->diff($horaFinal);
                        $horas = ($diff->days * 24) + $diff->h + ($diff->i / 60); // Incluir minutos como fracción
                        $this->cantidad_dias = (int)ceil($horas); // Redondear hacia arriba a horas enteras
                        
                        // Si es menos de 1 hora, establecer como 1 hora mínima
                        if ($this->cantidad_dias < 1) {
                            $this->cantidad_dias = 1;
                        }
                    } catch (\Exception $e) {
                        // Si hay error, no calcular automáticamente
                        Yii::warning('Error al calcular horas: ' . $e->getMessage());
                    }
                }
            } else {
                // Alquiler por días - calcular días como antes
                $start = new \DateTime($this->fecha_inicio);
                $end = new \DateTime($this->fecha_final);
                $diff = $start->diff($end);
                $this->cantidad_dias = $diff->days + 1; // +1 para incluir el día de inicio
            }
        }
    }

    /**
     * Validar disponibilidad del vehículo
     */
    public function validateCarAvailability($attribute, $params)
    {
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
}

