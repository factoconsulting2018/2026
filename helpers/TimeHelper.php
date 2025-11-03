<?php
namespace app\helpers;

class TimeHelper
{
    /**
     * Convierte hora de formato 24h a formato 12h con AM/PM
     * @param string $hora24 - Hora en formato "HH:MM" (ej: "14:30")
     * @return string - Hora en formato "H:MM AM/PM" (ej: "2:30 PM")
     */
    public static function convertTo12Hour($hora24)
    {
        if (empty($hora24) || !strpos($hora24, ':')) {
            return '12:00 AM';
        }
        
        $parts = explode(':', $hora24);
        $horas = (int)$parts[0];
        $minutos = isset($parts[1]) ? (int)$parts[1] : 0;
        
        if ($horas === 0) {
            $hora12 = 12;
            $periodo = 'AM';
        } elseif ($horas === 12) {
            $hora12 = 12;
            $periodo = 'PM';
        } elseif ($horas > 12) {
            $hora12 = $horas - 12;
            $periodo = 'PM';
        } else {
            $hora12 = $horas;
            $periodo = 'AM';
        }
        
        return sprintf('%d:%02d %s', $hora12, $minutos, $periodo);
    }
}

