<?php

namespace app\components;

use Yii;
use app\models\CompanyConfig;
use app\models\Incident;

/**
 * Notificaciones post-login de insidentes con saldo pendiente.
 */
class IncidentNotificationHelper
{
    public const SESSION_DISMISS = 'incident_notif_dismiss';
    public const SESSION_PROMPT = 'incident_notif_should_prompt';
    public const COOKIE_SNOOZE_UNTIL = 'incident_notif_snooze_until';

    public static function onSuccessfulLogin(): void
    {
        if (!self::isEnabledInConfig()) {
            Yii::$app->session->remove(self::SESSION_DISMISS);
            Yii::$app->session->remove(self::SESSION_PROMPT);
            return;
        }

        Yii::$app->session->set(self::SESSION_DISMISS, 0);

        $until = (int) Yii::$app->request->cookies->getValue(self::COOKIE_SNOOZE_UNTIL, 0);
        if ($until > time()) {
            Yii::$app->session->set(self::SESSION_PROMPT, false);
            return;
        }

        if ($until > 0) {
            Yii::$app->response->cookies->remove(self::COOKIE_SNOOZE_UNTIL);
        }

        Yii::$app->session->set(self::SESSION_PROMPT, true);
    }

    public static function isEnabledInConfig(): bool
    {
        return CompanyConfig::getConfig(CompanyConfig::INCIDENT_NOTIF_ENABLED, '0') === '1';
    }

    public static function getFrequencyDays(): int
    {
        $n = (int) CompanyConfig::getConfig(CompanyConfig::INCIDENT_NOTIF_FREQUENCY_DAYS, '3');
        return $n >= 1 && $n <= 365 ? $n : 3;
    }

    /**
     * @return Incident[]
     */
    public static function getPendingIncidents(): array
    {
        $rows = Incident::find()
            ->with(['client', 'payments'])
            ->where(['status' => Incident::STATUS_OPEN])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        $out = [];
        foreach ($rows as $inc) {
            if ($inc->getBalance() > 0.01) {
                $out[] = $inc;
            }
        }
        return $out;
    }

    public static function shouldShowModal(): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        if (!self::isEnabledInConfig()) {
            return false;
        }
        if (!Yii::$app->session->get(self::SESSION_PROMPT, false)) {
            return false;
        }
        if ((int) Yii::$app->session->get(self::SESSION_DISMISS, 0) >= 3) {
            return false;
        }
        return count(self::getPendingIncidents()) > 0;
    }
}
