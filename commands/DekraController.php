<?php

namespace app\commands;

use app\models\MaintenanceOrder;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Comandos para generar recordatorios automaticos de Dekra.
 */
class DekraController extends Controller
{
    /**
     * Genera las ordenes faltantes de Dekra segun la configuracion guardada.
     *
     * Uso:
     *   php yii dekra
     *   php yii dekra/index 2027
     *
     * @param int|null $startYear Año inicial. Si se omite, usa el año actual.
     * @return int Exit code
     */
    public function actionIndex($startYear = null)
    {
        $year = $startYear !== null ? (int) $startYear : null;

        $this->stdout("Generando recordatorios Dekra...\n", Console::FG_CYAN);
        if ($year !== null) {
            $this->stdout("Ano inicial: {$year}\n", Console::FG_CYAN);
        }

        try {
            $created = MaintenanceOrder::ensureDekraReminders($year);
        } catch (\Throwable $e) {
            $this->stderr("Error generando recordatorios Dekra: {$e->getMessage()}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if ($created > 0) {
            $this->stdout("Recordatorios creados: {$created}\n", Console::FG_GREEN);
        } else {
            $this->stdout("No habia recordatorios nuevos por crear.\n", Console::FG_YELLOW);
        }

        return ExitCode::OK;
    }
}
