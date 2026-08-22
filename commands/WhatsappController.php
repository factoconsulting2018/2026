<?php

namespace app\commands;

use app\components\WhatsAppNotifier;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Comandos relacionados con la integracion de WhatsApp.
 *
 * Uso en cron (cada minuto; decide internamente qué enviar):
 *   * * * * * cd /ruta/al/proyecto && php yii whatsapp/daily-deliveries >> runtime/logs/whatsapp-daily.log 2>&1
 *
 * En cada ejecución:
 *  1) Avisos 2h antes de correapartir (admins)
 *  2) Resumen diario (a la hora configurada, anti-duplicado)
 */
class WhatsappController extends Controller
{
    /**
     * Tareas programadas de WhatsApp (mismo cron).
     *
     * @return int Exit code
     */
    public function actionDailyDeliveries()
    {
        $this->stdout('[' . date('Y-m-d H:i:s') . "] WhatsApp cron jobs...\n", Console::FG_CYAN);

        $hadError = false;

        // 1) Recordatorios correapartir (ventana 2h, cada minuto)
        $this->stdout("— Avisos correapartir (2h)...\n", Console::FG_CYAN);
        try {
            $reminderReport = WhatsAppNotifier::sendCorreapartirReminders();
            $this->printReminderReport($reminderReport);
            if (!empty($reminderReport['errors'])) {
                $hadError = true;
            }
        } catch (\Throwable $e) {
            $this->stderr('Excepcion correapartir: ' . $e->getMessage() . "\n", Console::FG_RED);
            $hadError = true;
        }

        // 2) Resumen diario (hora + anti-duplicado)
        $this->stdout("— Resumen diario...\n", Console::FG_CYAN);
        try {
            $dailyReport = WhatsAppNotifier::sendDailyDeliveries(false);
            $this->printReport($dailyReport);
            if (!empty($dailyReport['errors']) && empty($dailyReport['skipped_reason'])) {
                $hadError = true;
            }
        } catch (\Throwable $e) {
            $this->stderr('Excepcion resumen diario: ' . $e->getMessage() . "\n", Console::FG_RED);
            $hadError = true;
        }

        return $hadError ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
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
     * Prueba manual: procesa avisos correapartir ahora (misma lógica del cron).
     *
     * Uso:
     *   php yii whatsapp/correapartir-reminders
     *
     * @return int Exit code
     */
    public function actionCorreapartirReminders()
    {
        $this->stdout('[' . date('Y-m-d H:i:s') . "] WhatsApp correapartir-reminders...\n", Console::FG_CYAN);

        try {
            $report = WhatsAppNotifier::sendCorreapartirReminders();
        } catch (\Throwable $e) {
            $this->stderr('Excepcion: ' . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->printReminderReport($report);
        return ExitCode::OK;
    }

    /**
     * Imprime un reporte estructurado del envio diario.
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

    /**
     * @param array{enabled:bool, attempted:int, sent:int, rentals?:int, errors:array<string>, skipped_reason:?string} $report
     */
    private function printReminderReport(array $report): void
    {
        if (!empty($report['skipped_reason']) && (int) ($report['sent'] ?? 0) === 0) {
            $this->stdout('Omitido: ' . $report['skipped_reason'] . "\n", Console::FG_YELLOW);
            return;
        }

        $rentals = (int) ($report['rentals'] ?? 0);
        $sent = (int) ($report['sent'] ?? 0);
        $attempted = (int) ($report['attempted'] ?? 0);

        $this->stdout(
            "Órdenes en ventana: {$rentals}. Mensajes enviados: {$sent}/{$attempted}.\n",
            $sent > 0 ? Console::FG_GREEN : Console::FG_YELLOW
        );

        if (!empty($report['errors'])) {
            foreach ($report['errors'] as $err) {
                $this->stdout(' - ' . $err . "\n", Console::FG_RED);
            }
        }
    }
}
