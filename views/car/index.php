<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */
/** @var string|null $status */
/** @var string|null $empresa */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = 'Gestión de Vehículos';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="car-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">directions_car</span><?= Html::encode($this->title) ?></h1>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a(
                '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">campaign</span>Análisis de campaña',
                ['analytics'],
                ['class' => 'btn btn-info']
            ) ?>
            <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">add</span>Nuevo Vehículo', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">search</span>Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nombre, placa, VIN, marca..."
                           value="<?= Html::encode($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">category</span>Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="disponible" <?= ($status ?? '') === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="alquilado" <?= ($status ?? '') === 'alquilado' ? 'selected' : '' ?>>Alquilado</option>
                        <option value="mantenimiento" <?= ($status ?? '') === 'mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                        <option value="fuera_servicio" <?= ($status ?? '') === 'fuera_servicio' ? 'selected' : '' ?>>Fuera de Servicio</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">business</span>Empresa</label>
                    <select name="empresa" class="form-select">
                        <option value="">Todas</option>
                        <option value="Facto Rent a Car" <?= ($empresa ?? '') === 'Facto Rent a Car' ? 'selected' : '' ?>>Facto Rent a Car</option>
                        <option value="Moviliza" <?= ($empresa ?? '') === 'Moviliza' ? 'selected' : '' ?>>Moviliza</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">search</span>Buscar</button>
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">clear</span>Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Vehículo</th>
                            <th>Placa</th>
                            <th class="d-none d-md-table-cell">VIN</th>
                            <th class="d-none d-md-table-cell">Marca</th>
                            <th class="d-none d-md-table-cell">Modelo</th>
                            <th class="d-none d-md-table-cell">Año</th>
                            <th class="d-none d-md-table-cell">Estado</th>
                            <th class="d-none d-md-table-cell">Empresa</th>
                            <th class="d-none d-md-table-cell">Precio/Día</th>
                            <th class="d-none d-md-table-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataProvider->getModels() as $model): ?>
                        <tr>
                            <td><?= Html::encode($model->id) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined me-2" style="font-size: 20px; color: #3fa9f5;">directions_car</span>
                                    <strong><?= Html::encode($model->nombre) ?></strong>
                                </div>
                                <div class="d-md-none mt-2 d-flex flex-wrap gap-1 justify-content-start">
                                    <a href="<?= Url::to(['view', 'id' => $model->id]) ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Ver detalles">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                                    </a>
                                    <a href="<?= Url::to(['update', 'id' => $model->id]) ?>"
                                       class="btn btn-sm btn-outline-warning"
                                       title="Editar">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                    </a>
                                    <a href="<?= Url::to(['/rental/create', 'car_id' => $model->id]) ?>"
                                       class="btn btn-sm btn-outline-success"
                                       title="Nuevo alquiler">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">add_circle</span>
                                    </a>
                                    <?= Html::a(
                                        '<span class="material-symbols-outlined" style="font-size: 16px;">' . ($model->skipsPriority() ? 'filter_alt_off' : 'filter_alt') . '</span>',
                                        ['toggle-skip-priority', 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-sm ' . ($model->skipsPriority() ? 'btn-warning' : 'btn-outline-secondary'),
                                            'title' => $model->skipsPriority()
                                                ? 'Saltar prioridad: ON (no bloquea Moviliza) — clic para desactivar'
                                                : 'Saltar prioridad: OFF — clic para activar (vehículo opcional)',
                                            'data-method' => 'post',
                                            'data-confirm' => $model->skipsPriority()
                                                ? '¿Desactivar «Saltar prioridad» para este vehículo?'
                                                : '¿Activar «Saltar prioridad»? Este vehículo no contará como prioridad Facto frente a Moviliza.',
                                        ]
                                    ) ?>
                                    <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       title="Eliminar"
                                       data-confirm="¿Estás seguro de eliminar este vehículo?"
                                       data-method="post">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= Html::encode($model->placa) ?></span>
                            </td>
                            <td class="d-none d-md-table-cell"><?= Html::encode($model->vin ?? 'N/A') ?></td>
                            <td class="d-none d-md-table-cell"><?= Html::encode($model->marca ? $model->marca->name : 'N/A') ?></td>
                            <td class="d-none d-md-table-cell"><?= Html::encode($model->getDisplayModelo() ?: 'N/A') ?></td>
                            <td class="d-none d-md-table-cell"><?= Html::encode($model->getDisplayAnio() ?: 'N/A') ?></td>
                            <td class="d-none d-md-table-cell">
                                <?php
                                $statusConfig = [
                                    'disponible' => ['class' => 'bg-success', 'text' => 'Disponible', 'icon' => 'check_circle'],
                                    'alquilado' => ['class' => 'bg-warning', 'text' => 'Alquilado', 'icon' => 'schedule'],
                                    'mantenimiento' => ['class' => 'bg-info', 'text' => 'Mantenimiento', 'icon' => 'build'],
                                    'fuera_servicio' => ['class' => 'bg-danger', 'text' => 'Fuera de Servicio', 'icon' => 'error']
                                ];
                                $currentStatus = $statusConfig[$model->status] ?? $statusConfig['fuera_servicio'];
                                $isRented = $model->status === 'alquilado';
                                ?>
                                <?php if ($isRented): ?>
                                <button type="button"
                                        class="badge <?= $currentStatus['class'] ?> border-0 car-status-rented"
                                        style="cursor: pointer;"
                                        title="Ver alquiler(es) activo(s)"
                                        onclick="openCarActiveRentalsModal(<?= $model->id ?>)">
                                    <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"><?= $currentStatus['icon'] ?></span>
                                    <?= $currentStatus['text'] ?>
                                </button>
                                <?php else: ?>
                                <span class="badge <?= $currentStatus['class'] ?>">
                                    <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"><?= $currentStatus['icon'] ?></span>
                                    <?= $currentStatus['text'] ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?= Html::encode($model->empresa ?? 'N/A') ?>
                                <?php if ($model->skipsPriority()): ?>
                                    <span class="badge bg-warning text-dark ms-1" title="No cuenta como prioridad Facto">Saltar prioridad</span>
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <strong>₡<?= number_format($model->precio_dia ?? 0, 2) ?></strong>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <div class="btn-group" role="group">
                                    <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Ver detalles">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                                    </a>
                                    <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" 
                                       class="btn btn-sm btn-outline-warning" 
                                       title="Editar">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                    </a>
                                    <a href="<?= Url::to(['/rental/create', 'car_id' => $model->id]) ?>" 
                                       class="btn btn-sm btn-outline-success" 
                                       title="Nuevo alquiler">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">add_circle</span>
                                    </a>
                                    <?= Html::a(
                                        '<span class="material-symbols-outlined" style="font-size: 16px;">' . ($model->skipsPriority() ? 'filter_alt_off' : 'filter_alt') . '</span>',
                                        ['toggle-skip-priority', 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-sm ' . ($model->skipsPriority() ? 'btn-warning' : 'btn-outline-secondary'),
                                            'title' => $model->skipsPriority()
                                                ? 'Saltar prioridad: ON (no bloquea Moviliza) — clic para desactivar'
                                                : 'Saltar prioridad: OFF — clic para activar (vehículo opcional)',
                                            'data-method' => 'post',
                                            'data-confirm' => $model->skipsPriority()
                                                ? '¿Desactivar «Saltar prioridad» para este vehículo?'
                                                : '¿Activar «Saltar prioridad»? Este vehículo no contará como prioridad Facto frente a Moviliza.',
                                        ]
                                    ) ?>
                                    <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Eliminar"
                                       data-confirm="¿Estás seguro de eliminar este vehículo?" 
                                       data-method="post">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($dataProvider->getCount() == 0): ?>
            <div class="text-center py-5">
                <span class="material-symbols-outlined" style="font-size: 64px; color: #ccc;">directions_car</span>
                <h4 class="text-muted mt-3">No hay vehículos registrados</h4>
                <p class="text-muted">Comienza agregando tu primer vehículo al sistema.</p>
                <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">add</span>Agregar Vehículo', ['create'], ['class' => 'btn btn-success']) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Paginación -->
    <div class="d-flex justify-content-center mt-4">
        <?= \yii\widgets\LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'options' => ['class' => 'pagination'],
            'linkOptions' => ['class' => 'page-link'],
            'pageCssClass' => 'page-item',
            'prevPageCssClass' => 'page-item',
            'nextPageCssClass' => 'page-item',
            'activePageCssClass' => 'active',
            'disabledPageCssClass' => 'disabled',
        ]) ?>
    </div>

    <?php Pjax::end(); ?>
</div>

<!-- Modal de alquileres activos del vehículo -->
<div class="modal fade" id="carActiveRentalsModal" tabindex="-1" aria-labelledby="carActiveRentalsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="carActiveRentalsModalLabel">
                    <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 6px;">schedule</span>
                    Alquileres activos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="carActiveRentalsLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
                </div>
                <div id="carActiveRentalsError" class="alert alert-danger d-none"></div>
                <div id="carActiveRentalsCarInfo" class="mb-3 d-none">
                    <h6 class="mb-1" id="carActiveRentalsCarName"></h6>
                    <small class="text-muted">Placa: <span id="carActiveRentalsCarPlate"></span> · Al <span id="carActiveRentalsToday"></span></small>
                </div>
                <div id="carActiveRentalsList"></div>
                <div id="carActiveRentalsEmpty" class="text-center py-4 d-none">
                    <span class="material-symbols-outlined" style="font-size: 48px; color: #ccc;">info</span>
                    <p class="text-muted mb-0">No se encontró alquiler activo hoy para este vehículo.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
$activeRentalsUrl = Url::to(['active-rentals']);
$js = <<<JS
const CAR_ACTIVE_RENTALS_URL = '$activeRentalsUrl';
let carActiveRentalsModalInstance = null;

function openCarActiveRentalsModal(carId) {
    const modalEl = document.getElementById('carActiveRentalsModal');
    const loading = document.getElementById('carActiveRentalsLoading');
    const errBox = document.getElementById('carActiveRentalsError');
    const info = document.getElementById('carActiveRentalsCarInfo');
    const list = document.getElementById('carActiveRentalsList');
    const empty = document.getElementById('carActiveRentalsEmpty');

    loading.classList.remove('d-none');
    errBox.classList.add('d-none');
    info.classList.add('d-none');
    empty.classList.add('d-none');
    list.innerHTML = '';

    if (!carActiveRentalsModalInstance && typeof bootstrap !== 'undefined') {
        carActiveRentalsModalInstance = new bootstrap.Modal(modalEl);
    }
    carActiveRentalsModalInstance ? carActiveRentalsModalInstance.show() : window.jQuery && jQuery(modalEl).modal('show');

    fetch(CAR_ACTIVE_RENTALS_URL + '?id=' + carId, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            loading.classList.add('d-none');
            if (!data.success) {
                errBox.textContent = data.message || 'No se pudo cargar la información.';
                errBox.classList.remove('d-none');
                return;
            }
            document.getElementById('carActiveRentalsCarName').textContent = data.car.nombre || '';
            document.getElementById('carActiveRentalsCarPlate').textContent = data.car.placa || '—';
            document.getElementById('carActiveRentalsToday').textContent = data.today || '';
            info.classList.remove('d-none');

            if (!data.rentals || data.rentals.length === 0) {
                empty.classList.remove('d-none');
                return;
            }

            const fmtDate = d => d ? new Date(d).toLocaleDateString('es-CR') : '—';
            const fmtMoney = n => '₡' + (Number(n) || 0).toLocaleString('es-CR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const escapeHtml = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

            const statusClass = {
                pagado: 'bg-success',
                pendiente: 'bg-warning text-dark',
                reservado: 'bg-info text-dark',
                cancelado: 'bg-danger'
            };

            const html = data.rentals.map(r => `
                <div class="card mb-2 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong>\${escapeHtml(r.rental_id)}</strong>
                                <span class="badge \${statusClass[r.estado_pago] || 'bg-secondary'} ms-2">\${escapeHtml(r.estado_pago)}</span>
                                \${r.is_replacement ? '<span class="badge bg-info text-dark ms-1">Reemplazo</span>' : ''}
                            </div>
                            <strong class="text-success">\${fmtMoney(r.total_precio)}</strong>
                        </div>
                        <div class="row small text-muted">
                            <div class="col-sm-6">
                                <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">person</span>
                                \${escapeHtml(r.client_name)}
                                \${r.client_phone ? ' · ' + escapeHtml(r.client_phone) : ''}
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">event</span>
                                \${fmtDate(r.fecha_inicio)} → \${fmtDate(r.fecha_final)}
                            </div>
                        </div>
                        <div class="mt-2 d-flex gap-2">
                            <a href="\${r.view_url}" class="btn btn-sm btn-outline-primary">
                                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">visibility</span>
                                Ver
                            </a>
                            <a href="\${r.pdf_url}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">description</span>
                                PDF
                            </a>
                        </div>
                    </div>
                </div>
            `).join('');
            list.innerHTML = html;
        })
        .catch(() => {
            loading.classList.add('d-none');
            errBox.textContent = 'Error de conexión.';
            errBox.classList.remove('d-none');
        });
}
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>