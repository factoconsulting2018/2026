<?php
namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * Modelo para manejar la disponibilidad de vehículos
 */
class CarAvailability
{
    /**
     * Condición SQL: el alquiler NO se solapa con el rango solicitado.
     *
     * - Inicio efectivo: si correapartir_enabled = 1 y fecha_correapartir está definida,
     *   se usa DATE(fecha_correapartir); de lo contrario, fecha_inicio. Así un vehículo
     *   con una orden marcada con "correapartir" el mismo día del cambio sigue disponible
     *   hasta esa fecha.
     * - Fin efectivo: si la orden fue reemplazada (swap), se bloquea solo hasta el día
     *   anterior a swap_date; de lo contrario, DATE(fecha_final). El operador <= permite
     *   devoluciones tempranas el mismo día.
     */
    private static function noOverlapCondition($startDate, $endDate): array
    {
        $startDay = substr((string) $startDate, 0, 10);
        $endDay = substr((string) $endDate, 0, 10);

        $effectiveStart = new Expression(
            'IF(correapartir_enabled = 1 AND fecha_correapartir IS NOT NULL, DATE(fecha_correapartir), DATE(fecha_inicio))'
        );
        $effectiveEnd = new Expression(
            'IF(swapped_to_rental_id IS NOT NULL AND swap_date IS NOT NULL, DATE_SUB(swap_date, INTERVAL 1 DAY), DATE(fecha_final))'
        );

        return [
            'not',
            [
                'or',
                ['>=', $effectiveStart, $endDay],
                ['<=', $effectiveEnd, $startDay],
            ],
        ];
    }

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
            ->andWhere(['is_async' => 0])
            ->andWhere(self::noOverlapCondition($startDate, $endDate));

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
            ->andWhere(['is_async' => 0])
            ->andWhere(['<=', 'fecha_inicio', $endOfMonth])
            ->andWhere([
                'or',
                ['>=', 'fecha_final', $startOfMonth],
                [
                    'and',
                    ['not', ['swapped_to_rental_id' => null]],
                    ['not', ['swap_date' => null]],
                    ['>=', new Expression('DATE_SUB(swap_date, INTERVAL 1 DAY)'), substr($startOfMonth, 0, 10)],
                ],
            ])
            ->all();

        $occupiedDates = [];
        foreach ($rentals as $rental) {
            $blockEnd = Rental::getEffectiveBlockEndDate($rental);
            $start = max($rental->fecha_inicio, $startOfMonth);
            $end = min($blockEnd . ' 23:59:59', $endOfMonth);

            $current = strtotime($start);
            $endTime = strtotime($end);

            if ($current === false || $endTime === false || $current > $endTime) {
                continue;
            }

            while ($current <= $endTime) {
                $occupiedDates[] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
            }
        }

        return array_unique($occupiedDates);
    }

    /**
     * Vehículos sin renta activa (no cancelada, síncrona) que cubran el día calendario.
     * Excluye estado mantenimiento y fuera de servicio.
     *
     * @param string $day Fecha Y-m-d (zona horaria del servidor / aplicación)
     * @return Car[]
     */
    public static function getCarsAvailableOnDate(string $day): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            $day = date('Y-m-d');
        }

        $month = substr($day, 0, 7);
        $query = Car::find()
            ->where(['not in', 'status', ['fuera_servicio', 'mantenimiento']])
            ->orderBy(['nombre' => SORT_ASC]);

        $available = [];
        foreach ($query->each() as $car) {
            $occupied = self::getOccupiedDates($car->id, $month);
            if (!in_array($day, $occupied, true)) {
                $available[] = $car;
            }
        }

        return $available;
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
                'available_dates' => self::getAvailableDates($car->id, $month),
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
            ->andWhere(['!=', 'estado_pago', 'cancelado'])
            ->andWhere(['is_async' => 0]);

        if ($startDate && $endDate) {
            $query->andWhere(self::noOverlapCondition($startDate, $endDate));
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
        if (strtotime($startDate) < strtotime('today')) {
            return [
                'valid' => false,
                'message' => 'La fecha de inicio no puede ser en el pasado.',
            ];
        }

        if (strtotime($endDate) <= strtotime($startDate)) {
            return [
                'valid' => false,
                'message' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            ];
        }

        if (!self::isCarAvailable($carId, $startDate, $endDate, $excludeRentalId)) {
            return [
                'valid' => false,
                'message' => 'El vehículo no está disponible en las fechas seleccionadas.',
            ];
        }

        return [
            'valid' => true,
            'message' => 'Fechas válidas.',
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

        $searchDays = 90;
        $current = strtotime($fromDate);

        for ($i = 0; $i < $searchDays; $i++) {
            $startDate = date('Y-m-d H:i:s', $current);
            $endDate = date('Y-m-d H:i:s', strtotime("+{$durationDays} days", $current));

            if (self::isCarAvailable($carId, $startDate, $endDate)) {
                return [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
            }

            $current = strtotime('+1 day', $current);
        }

        return null;
    }
}
