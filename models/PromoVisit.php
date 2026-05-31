<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Registro de visitas a la landing /promo/{slug}.
 *
 * @property int $id
 * @property int|null $car_id
 * @property string $slug
 * @property string|null $ip
 * @property string|null $user_agent
 * @property string|null $referer
 * @property string $created_at
 */
class PromoVisit extends ActiveRecord
{
    public static function tableName()
    {
        return 'promo_visits';
    }

    public function rules()
    {
        return [
            [['slug'], 'required'],
            [['car_id'], 'integer'],
            [['slug'], 'string', 'max' => 120],
            [['ip'], 'string', 'max' => 64],
            [['user_agent'], 'string', 'max' => 255],
            [['referer'], 'string', 'max' => 500],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * Registra una visita. De-duplicate por IP + slug en una ventana de 30 minutos
     * para evitar inflar contadores con refrescos o crawlers.
     */
    public static function recordVisit(?int $carId, string $slug, array $req): ?self
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $ip = (string) ($req['ip'] ?? '');
        $ua = (string) ($req['user_agent'] ?? '');
        $ref = (string) ($req['referer'] ?? '');

        if ($ip !== '') {
            $cutoff = (new \DateTimeImmutable('-30 minutes'))->format('Y-m-d H:i:s');
            $exists = self::find()
                ->andWhere(['slug' => $slug, 'ip' => $ip])
                ->andWhere(['>=', 'created_at', $cutoff])
                ->exists();
            if ($exists) {
                return null;
            }
        }

        $v = new self();
        $v->car_id = $carId;
        $v->slug = $slug;
        $v->ip = $ip !== '' ? mb_substr($ip, 0, 64) : null;
        $v->user_agent = $ua !== '' ? mb_substr($ua, 0, 255) : null;
        $v->referer = $ref !== '' ? mb_substr($ref, 0, 500) : null;
        $v->created_at = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        try {
            if ($v->save(false)) {
                return $v;
            }
        } catch (\Throwable $e) {
            Yii::warning('PromoVisit::recordVisit fallo: ' . $e->getMessage(), 'promo');
        }
        return null;
    }

    /**
     * Cuenta visitas por car_id en el rango [start, end] (inclusive).
     *
     * @return array<int,int> ['car_id' => count]
     */
    public static function countByCarInRange(string $startDate, string $endDate): array
    {
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';

        $rows = self::find()
            ->select(['car_id', 'cnt' => new Expression('COUNT(*)')])
            ->andWhere(['between', 'created_at', $start, $end])
            ->andWhere(['not', ['car_id' => null]])
            ->groupBy(['car_id'])
            ->asArray()
            ->all();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['car_id']] = (int) $r['cnt'];
        }
        return $out;
    }

    /**
     * Visitas por día (YYYY-MM-DD) en el rango [start, end] (total agregado).
     *
     * @return array<string,int> ['YYYY-MM-DD' => count]
     */
    public static function countByDayInRange(string $startDate, string $endDate): array
    {
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';

        $rows = self::find()
            ->select(['d' => new Expression('DATE(created_at)'), 'cnt' => new Expression('COUNT(*)')])
            ->andWhere(['between', 'created_at', $start, $end])
            ->groupBy(new Expression('DATE(created_at)'))
            ->orderBy(new Expression('DATE(created_at) ASC'))
            ->asArray()
            ->all();

        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r['d']] = (int) $r['cnt'];
        }
        return $out;
    }
}
