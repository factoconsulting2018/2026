<?php

namespace app\commands;

use app\models\Car;
use app\models\Rental;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Comandos de mantenimiento para alquileres.
 */
class RentalController extends Controller
{
    /**
     * Finaliza ordenes pagadas cuya fecha final ya paso y sincroniza vehiculos.
     *
     * Uso:
     *   php yii rental/auto-finalize
     *
     * @return int Exit code
     */
    public function actionAutoFinalize()
    {
        $this->stdout("Finalizando ordenes pagadas vencidas...\n", Console::FG_CYAN);

        try {
            $finalized = Rental::autoFinalizeCompleted();
            $syncedCars = Car::syncAllStatuses();
        } catch (\Throwable $e) {
            $this->stderr("Error finalizando ordenes: {$e->getMessage()}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if ($finalized > 0) {
            $this->stdout("Ordenes finalizadas: {$finalized}\n", Console::FG_GREEN);
        } else {
            $this->stdout("No habia ordenes pagadas vencidas por finalizar.\n", Console::FG_YELLOW);
        }

        if ($syncedCars > 0) {
            $this->stdout("Vehiculos sincronizados: {$syncedCars}\n", Console::FG_GREEN);
        } else {
            $this->stdout("No hubo cambios en estados de vehiculos.\n", Console::FG_YELLOW);
        }

        return ExitCode::OK;
    }
}
