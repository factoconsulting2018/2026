<?php

namespace app\commands;

use app\components\WhatsAppNotifier;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Comandos relacionados con la integracion de WhatsApp.
 *
 * Uso en cron (cada minuto, decide internamente si toca enviar):
 *   * * * * * cd /ruta/al/proyecto && php yii whatsapp/daily-deliveries >> runtime/logs/whatsapp-daily.log 2>&1
 */
class WhatsappController extends Controller
{
    /**
     * Envia el resumen diario por WhatsApp respetando la hora configurada
     * y el control anti-duplicado (daily_last_sent).
     *
     * Diseñado para ser invocado por cron cada minuto.
     *
     * @return int Exit code
     */
    public function actionDailyDeliveries()
    {
        $this->stdout('[' . date('Y-m-d H:i:s') . "] WhatsApp daily-deliveries...\n", Console::FG_CYAN);

        try {
            $report = WhatsAppNotifier::sendDailyDeliveries(false);
        } catch (\Throwable $e) {
            $this->stderr('Excepcion: ' . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->printReport($report);
        return ExitCode::OK;
    }

    /**
     * Envia el resumen diario IGNORANDO la hora y el anti-duplicado.
     * Util para pruebas manuales desde SSH.
     *
     * Uso:
     *   php yii whatsapp/daily-deliveries-force
     *
     * @return int Exit code
     */
    public function actionDailyDeliveriesForce()
    {
        $this->stdout('[' . date('Y-m-d H:i:s') . "] WhatsApp daily-deliveries (force)...\n", Console::FG_YELLOW);

        try {
            $report = WhatsAppNotifier::sendDailyDeliveries(true);
        } catch (\Throwable $e) {
            $this->stderr('Excepcion: ' . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->printReport($report);
        return ExitCode::OK;
    }

    /**
     * Imprime un reporte estructurado del envio.
     *
     * @param array{enabled:bool, attempted:int, sent:int, errors:array<string>, skipped_reason:?string} $report
     */
    private function printReport(array $report): void
    {
        if (!empty($report['skipped_reason'])) {
            $this->stdout('Omitido: ' . $report['skipped_reason'] . "\n", Console::FG_YELLOW);
            return;
        }

        $sent = (int) ($report['sent'] ?? 0);
        $attempted = (int) ($report['attempted'] ?? 0);

        if ($sent > 0) {
            $this->stdout("Enviado a {$sent} de {$attempted} destinatario(s).\n", Console::FG_GREEN);
        } else {
            $this->stdout("No se envió a ningún destinatario (intentos: {$attempted}).\n", Console::FG_RED);
        }

        if (!empty($report['errors'])) {
            foreach ($report['errors'] as $err) {
                $this->stdout(' - ' . $err . "\n", Console::FG_RED);
            }
        }
    }
}
