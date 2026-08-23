<?php
/**
 * Contenido tabulado del detalle de cliente (página y modal).
 *
 * @var app\models\Client $model
 * @var app\models\Rental[] $rentalHistory
 * @var int $clientLibraryFileCount
 * @var string $uid Prefijo único para IDs de tabs
 * @var bool $embedInModal Si true, omite cabecera de página
 */

use yii\helpers\Html;
use yii\helpers\Url;

$rentalHistory = $rentalHistory ?? [];
$clientLibraryFileCount = (int) ($clientLibraryFileCount ?? 0);
$uid = $uid ?? ('cv' . (int) $model->id);
$embedInModal = !empty($embedInModal);

$estadoBadges = [
    'pendiente' => '<span class="badge bg-warning text-dark">Pendiente</span>',
    'pagado' => '<span class="badge bg-success">Pagado</span>',
    'reservado' => '<span class="badge bg-info text-dark">Reservado</span>',
    'finalizado' => '<span class="badge bg-dark">Finalizado</span>',
    'cancelado' => '<span class="badge bg-danger">Cancelado</span>',
];

$fmtDate = static function ($raw) {
    if (empty($raw)) {
        return '—';
    }
    try {
        return \Yii::$app->formatter->asDate($raw);
    } catch (\Throwable $e) {
        $ts = strtotime((string) $raw);
        return $ts ? date('d/m/Y', $ts) : '—';
    }
};

$statusActive = ($model->status === 'active');
$address = $model->address ?: ($model->direccion ?? '');
$phone = $model->whatsapp ?: ($model->celular ?: ($model->telefono ?? ''));
?>
<div class="client-view-tabs" data-client-id="<?= (int) $model->id ?>">
    <?php if (!$embedInModal): ?>
    <div class="client-view-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="h3 mb-2" style="color:#fff;">
                    <span class="material-symbols-outlined" style="font-size:28px;vertical-align:middle;margin-right:6px;">person</span>
                    <?= Html::encode($model->fullNameUppercase) ?>
                </h1>
                <div class="d-flex flex-wrap gap-1">
                    <span class="badge <?= $statusActive ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $statusActive ? 'Activo' : 'Inactivo' ?>
                    </span>
                    <?php if ($model->es_cliente_facto): ?>
                        <span class="badge bg-light text-primary">Facto</span>
                    <?php endif; ?>
                    <?php if ($model->es_aliado): ?>
                        <span class="badge bg-light text-success">Aliado</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?= Html::a(
                    '<span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">edit</span> Editar',
                    ['update', 'id' => $model->id],
                    ['class' => 'btn btn-light btn-sm']
                ) ?>
                <?= Html::a(
                    '<span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">arrow_back</span> Volver',
                    ['index'],
                    ['class' => 'btn btn-outline-light btn-sm']
                ) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <ul class="nav nav-tabs client-colored-tabs mb-3 flex-wrap" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="<?= $uid ?>-datos-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-datos" type="button" role="tab">
                <span class="material-symbols-outlined">badge</span>
                Datos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="<?= $uid ?>-hacienda-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-hacienda" type="button" role="tab">
                <span class="material-symbols-outlined">account_balance</span>
                Hacienda
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="<?= $uid ?>-archivos-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-archivos" type="button" role="tab">
                <span class="material-symbols-outlined">folder</span>
                Archivos
                <span class="badge"><?= $clientLibraryFileCount ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="<?= $uid ?>-historial-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-historial" type="button" role="tab">
                <span class="material-symbols-outlined">history</span>
                Historial
                <span class="badge"><?= count($rentalHistory) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="<?= $uid ?>-notas-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-notas" type="button" role="tab">
                <span class="material-symbols-outlined">notes</span>
                Notas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="<?= $uid ?>-acciones-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-acciones" type="button" role="tab">
                <span class="material-symbols-outlined">settings</span>
                Acciones
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Datos -->
        <div class="tab-pane fade show active" id="<?= $uid ?>-datos" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <tbody>
                        <tr><th style="width:38%;">Nombre</th><td><?= Html::encode($model->fullNameUppercase) ?></td></tr>
                        <tr><th>Cédula</th><td><?= Html::encode($model->cedula_fisica ?: '—') ?></td></tr>
                        <tr><th>Email</th><td><?= Html::encode($model->email ?: '—') ?></td></tr>
                        <tr><th>WhatsApp / Teléfono</th><td><?= Html::encode($phone ?: '—') ?></td></tr>
                        <tr><th>Dirección</th><td><?= Html::encode($address ?: '—') ?></td></tr>
                        <tr><th>Vence licencia</th><td><?= Html::encode($fmtDate($model->fecha_vencimiento_licencia ?? null)) ?></td></tr>
                        <tr><th>Vence cédula</th><td><?= Html::encode($fmtDate($model->fecha_vencimiento_cedula ?? null)) ?></td></tr>
                        <tr><th>Fecha nacimiento</th><td><?= Html::encode($fmtDate($model->fecha_nacimiento ?? null)) ?></td></tr>
                        <tr><th>Registro</th><td><?= Html::encode($fmtDate($model->created_at)) ?></td></tr>
                        <tr><th>Actualizado</th><td><?= Html::encode($fmtDate($model->updated_at)) ?></td></tr>
                        <tr>
                            <th>Estado</th>
                            <td>
                                <span class="badge <?= $statusActive ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $statusActive ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Cliente Facto</th>
                            <td>
                                <span class="badge <?= $model->es_cliente_facto ? 'bg-primary' : 'bg-secondary' ?>">
                                    <?= $model->es_cliente_facto ? 'Sí' : 'No' ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Es Aliado</th>
                            <td>
                                <span class="badge <?= $model->es_aliado ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $model->es_aliado ? 'Sí' : 'No' ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Hacienda -->
        <div class="tab-pane fade" id="<?= $uid ?>-hacienda" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <tbody>
                        <tr><th style="width:38%;">Tipo identificación</th><td><?= Html::encode($model->tipo_identificacion ?: '—') ?></td></tr>
                        <tr><th>Situación tributaria</th><td><?= Html::encode($model->situacion_tributaria ?: '—') ?></td></tr>
                        <tr><th>Régimen tributario</th><td><?= Html::encode($model->regimen_tributario ?: '—') ?></td></tr>
                        <tr><th>Código actividad</th><td><?= Html::encode($model->actividad_economica_codigo ?: '—') ?></td></tr>
                        <tr><th>Actividad económica</th><td><?= Html::encode($model->actividad_economica_descripcion ?: '—') ?></td></tr>
                        <tr><th>Situación financiera</th><td><?= Html::encode($model->situacion_financiera ?: '—') ?></td></tr>
                        <tr>
                            <th>Detalle financiero</th>
                            <td><?= $model->situacion_financiera_detalle ? nl2br(Html::encode($model->situacion_financiera_detalle)) : '—' ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Archivos -->
        <div class="tab-pane fade" id="<?= $uid ?>-archivos" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="mb-0">Biblioteca de archivos</h6>
                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $uid ?>-upload-collapse">
                    <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">upload_file</span>
                    Subir archivo
                </button>
            </div>

            <div class="collapse mb-3" id="<?= $uid ?>-upload-collapse">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <form id="file-upload-form" enctype="multipart/form-data" onsubmit="return false;">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Archivo *</label>
                                    <input type="file" class="form-control" id="file-input" name="file" accept=".pdf,.png,.jpg,.jpeg,.xlsx,.xls,.docx,.doc" required>
                                    <small class="text-muted">PDF, PNG, JPG, XLSX, DOCX (máx. 10MB)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="file-name-input" placeholder="Ej: Contrato 2025" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" id="file-description-input" rows="2" placeholder="Opcional"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" id="upload-file-btn" data-client-id="<?= (int) $model->id ?>" onclick="uploadFile()">
                                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">upload</span>
                                        Subir
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text"><span class="material-symbols-outlined">search</span></span>
                <input type="text" class="form-control" id="file-search-input" placeholder="Buscar archivos...">
                <button class="btn btn-outline-secondary" type="button" onclick="searchFiles()">Buscar</button>
                <button class="btn btn-outline-secondary" type="button" onclick="clearFileSearch()" title="Limpiar">
                    <span class="material-symbols-outlined">clear</span>
                </button>
            </div>

            <div id="file-upload-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;justify-content:center;align-items:center;flex-direction:column;">
                <div class="text-center bg-white p-4 rounded shadow" style="max-width:360px;margin:auto;">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <h6>Subiendo archivo...</h6>
                </div>
            </div>

            <div id="files-container"
                 data-initial-file-count="<?= $clientLibraryFileCount ?>"
                 data-client-id="<?= (int) $model->id ?>">
                <?php if ($clientLibraryFileCount === 0): ?>
                    <div class="text-center text-muted py-4">
                        <span class="material-symbols-outlined" style="font-size:40px;display:block;margin-bottom:8px;">folder_off</span>
                        No hay archivos subidos aún
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <div class="spinner-border" role="status"></div>
                        <p class="mt-2 mb-0">Cargando archivos...</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historial -->
        <div class="tab-pane fade" id="<?= $uid ?>-historial" role="tabpanel">
            <?php if (count($rentalHistory) === 0): ?>
                <div class="text-center text-muted py-4">
                    <span class="material-symbols-outlined" style="font-size:40px;opacity:.45;">history</span>
                    <p class="mb-0 mt-2">Este cliente no tiene alquileres registrados.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Orden</th>
                                <th>Vehículo</th>
                                <th>Período</th>
                                <th>Estado</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rentalHistory as $hist): ?>
                            <?php
                            $histCode = !empty($hist->rental_id) ? $hist->rental_id : ('R' . $hist->id);
                            $histEstado = $hist->estado_pago ?? 'pendiente';
                            $histEstadoHtml = $estadoBadges[$histEstado]
                                ?? ('<span class="badge bg-secondary">' . Html::encode($histEstado) . '</span>');
                            $histCar = $hist->car
                                ? ($hist->car->nombre . (!empty($hist->car->placa) ? ' (' . $hist->car->placa . ')' : ''))
                                : '—';
                            $fi = $hist->fecha_inicio ? date('d/m/Y', strtotime($hist->fecha_inicio)) : '—';
                            $ff = $hist->fecha_final ? date('d/m/Y', strtotime($hist->fecha_final)) : '—';
                            ?>
                            <tr>
                                <td><strong><?= Html::encode($histCode) ?></strong></td>
                                <td><?= Html::encode($histCar) ?></td>
                                <td class="small text-nowrap"><?= Html::encode($fi) ?> → <?= Html::encode($ff) ?></td>
                                <td><?= $histEstadoHtml ?></td>
                                <td class="text-end text-nowrap">₡<?= number_format((float) ($hist->total_precio ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Notas -->
        <div class="tab-pane fade" id="<?= $uid ?>-notas" role="tabpanel">
            <?php if (!empty($model->notes) || !empty($model->notas)): ?>
                <div class="p-3 bg-light rounded">
                    <?= nl2br(Html::encode($model->notes ?: $model->notas)) ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">Sin notas registradas.</p>
            <?php endif; ?>

            <?php if (!empty($model->licencias_choferes)): ?>
                <hr>
                <h6>Choferes / licencias</h6>
                <div class="p-3 bg-light rounded">
                    <?= nl2br(Html::encode($model->licencias_choferes)) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Acciones -->
        <div class="tab-pane fade" id="<?= $uid ?>-acciones" role="tabpanel">
            <div class="row g-2">
                <div class="col-6 col-md-4">
                    <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="btn btn-primary w-100">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">edit</span>
                        Editar
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <a href="<?= Url::to(['/rental/create', 'client_id' => $model->id]) ?>" class="btn btn-success w-100">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">add_circle</span>
                        Nuevo alquiler
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <a href="<?= Url::to(['/rental/index', 'cliente' => $model->full_name]) ?>" class="btn btn-info text-white w-100">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">receipt_long</span>
                        Ver alquileres
                    </a>
                </div>
                <div class="col-6 col-md-4">
                    <?= Html::a(
                        '<span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">delete</span> Eliminar',
                        ['delete', 'id' => $model->id],
                        [
                            'class' => 'btn btn-danger w-100',
                            'data' => [
                                'confirm' => '¿Estás seguro de eliminar este cliente?',
                                'method' => 'post',
                            ],
                        ]
                    ) ?>
                </div>
                <div class="col-6 col-md-4">
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary w-100">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">arrow_back</span>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
