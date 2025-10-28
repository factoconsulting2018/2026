<?php
/** @var yii\web\View $this */
/** @var string $content */
/** @var string $logFile */

$this->title = 'Logs de Error';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">description</span>
                Logs de Error
            </h1>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">info</span>
                        Archivo: <?= htmlspecialchars($logFile) ?>
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <?php if (!empty($content)): ?>
                        <pre style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 12px; line-height: 1.5;"><?= htmlspecialchars($content) ?></pre>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">info</span>
                            No hay registros de log disponibles.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="<?= \yii\helpers\Url::to(['/site/index']) ?>" class="btn btn-secondary">
                        <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 4px;">arrow_back</span>
                        Volver
                    </a>
                    <button onclick="location.reload()" class="btn btn-primary">
                        <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 4px;">refresh</span>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
pre {
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
