<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Modelo para manejar la disponibilidad de vehículos
 */
class CarAvailability
{
    /**
     * Verificar si un vehículo está disponible en un rango de fechas
     * @param int $carId ID del vehículo
     * @param string $startDate Fecha de inicio (Y-m-d H:i:s)
     * @param string $endDate Fecha de fin (Y-m-d H:i:s)
     * @param int $excludeRentalId ID del alquiler a excluir (para edición)
     * @return bool
     */
    public static function isCarAvailable($carId, $startDate, $endDate, $excludeRentalId = null)
    {
        $query = Rental::find()
            ->where(['car_id' => $carId])
            ->andWhere(['!=', 'estado_pago', 'cancelado'])
            ->andWhere([
                'or',
                // Solapamiento en la fecha de inicio
                ['and',
                    ['<=', 'fecha_inicio', $startDate],
                    ['>=', 'fecha_final', $startDate]
                ],
                // Solapamiento en la fecha de fin
                ['and',
                    ['<=', 'fecha_inicio', $endDate],
                    ['>=', 'fecha_final', $endDate]
                ],
                // El rango está completamente dentro de otro alquiler
                ['and',
                    ['>=', 'fecha_inicio', $startDate],
                    ['<=', 'fecha_final', $endDate]
                ]
            ]);

        // Excluir el alquiler actual si se está editando
        if ($excludeRentalId) {
            $query->andWhere(['!=', 'id', $excludeRentalId]);
        }

        return $query->count() == 0;
    }

    /**
     * Obtener todas las fechas ocupadas para un vehículo en un mes específico
     * @param int $carId ID del vehículo
     * @param string $month Mes en formato Y-m
     * @return array Array de fechas ocupadas
     */
    public static function getOccupiedDates($carId, $month)
    {
        $startOfMonth = $month . '-01 00:00:00';
        $endOfMonth = date('Y-m-t 23:59:59', strtotime($startOfMonth));

        $rentals = Rental::find()
            ->where(['car_id' => $carId])
            ->andWhere(['!=', 'estado_pago', 'cancelado'])
            ->andWhere(['<=', 'fecha_inicio', $endOfMonth])
            ->andWhere(['>=', 'fecha_final', $startOfMonth])
            ->all();

        $occupiedDates = [];
        foreach ($rentals as $rental) {
            $start = max($rental->fecha_inicio, $startOfMonth);
            $end = min($rental->fecha_final, $endOfMonth);
            
            $current = strtotime($start);
            $endTime = strtotime($end);
            
            while ($current <= $endTime) {
                $occupiedDates[] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
            }
        }

        return array_unique($occupiedDates);
    }

    /**
     * Obtener la disponibilidad de todos los vehículos para un mes
     * @param string $month Mes en formato Y-m
     * @return array Array con la disponibilidad por vehículo
     */
    public static function getMonthlyAvailability($month)
    {
        $cars = Car::find()->where(['!=', 'status', 'fuera_servicio'])->all();
        $availability = [];

        foreach ($cars as $car) {
            $availability[$car->id] = [
                'car' => $car,
                'occupied_dates' => self::getOccupiedDates($car->id, $month),
                'available_dates' => self::getAvailableDates($car->id, $month)
            ];
        }

        return $availability;
    }

    /**
     * Obtener las fechas disponibles para un vehículo en un mes
     * @param int $carId ID del vehículo
     * @param string $month Mes en formato Y-m
     * @return array Array de fechas disponibles
     */
    public static function getAvailableDates($carId, $month)
    {
        $startOfMonth = $month . '-01';
        $endOfMonth = date('Y-m-t', strtotime($startOfMonth));
        
        $allDates = [];
        $current = strtotime($startOfMonth);
        $endTime = strtotime($endOfMonth);
        
        while ($current <= $endTime) {
            $allDates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }
        
        $occupiedDates = self::getOccupiedDates($carId, $month);
        
        return array_diff($allDates, $occupiedDates);
    }

    /**
     * Obtener alquileres activos para un vehículo en un rango de fechas
     * @param int $carId ID del vehículo
     * @param string $startDate Fecha de inicio
     * @param string $endDate Fecha de fin
     * @return array Array de alquileres
     */
    public static function getActiveRentals($carId, $startDate = null, $endDate = null)
    {
        $query = Rental::find()
            ->where(['car_id' => $carId])
            ->andWhere(['!=', 'estado_pago', 'cancelado']);

        if ($startDate && $endDate) {
            $query->andWhere([
                'or',
                // Solapamiento en la fecha de inicio
                ['and',
                    ['<=', 'fecha_inicio', $endDate],
                    ['>=', 'fecha_final', $startDate]
                ]
            ]);
        }

        return $query->orderBy(['fecha_inicio' => SORT_ASC])->all();
    }

    /**
     * Validar fechas de alquiler antes de guardar
     * @param int $carId ID del vehículo
     * @param string $startDate Fecha de inicio
     * @param string $endDate Fecha de fin
     * @param int $excludeRentalId ID del alquiler a excluir
     * @return array Array con 'valid' => bool y 'message' => string
     */
    public static function validateRentalDates($carId, $startDate, $endDate, $excludeRentalId = null)
    {
        // Verificar que la fecha de inicio no sea en el pasado
        if (strtotime($startDate) < strtotime('today')) {
            return [
                'valid' => false,
                'message' => 'La fecha de inicio no puede ser en el pasado.'
            ];
        }

        // Verificar que la fecha de fin sea posterior a la de inicio
        if (strtotime($endDate) <= strtotime($startDate)) {
            return [
                'valid' => false,
                'message' => 'La fecha de fin debe ser posterior a la fecha de inicio.'
            ];
        }

        // Verificar disponibilidad del vehículo
        if (!self::isCarAvailable($carId, $startDate, $endDate, $excludeRentalId)) {
            return [
                'valid' => false,
                'message' => 'El vehículo no está disponible en las fechas seleccionadas.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Fechas válidas.'
        ];
    }

    /**
     * Obtener el próximo período disponible para un vehículo
     * @param int $carId ID del vehículo
     * @param int $durationDays Duración en días
     * @param string $fromDate Fecha desde la cual buscar (opcional)
     * @return array Array con 'start_date' y 'end_date' o null si no hay disponibilidad
     */
    public static function getNextAvailablePeriod($carId, $durationDays, $fromDate = null)
    {
        if (!$fromDate) {
            $fromDate = date('Y-m-d');
        }

        $searchDays = 90; // Buscar en los próximos 90 días
        $current = strtotime($fromDate);
        
        for ($i = 0; $i < $searchDays; $i++) {
            $startDate = date('Y-m-d H:i:s', $current);
            $endDate = date('Y-m-d H:i:s', strtotime("+{$durationDays} days", $current));
            
            if (self::isCarAvailable($carId, $startDate, $endDate)) {
                return [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ];
            }
            
            $current = strtotime('+1 day', $current);
        }
        
        return null;
    }
}
