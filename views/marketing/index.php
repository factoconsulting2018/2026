<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $clients array  */
/* @var $waConfig array */
/* @var $mkConfig array */
/* @var $connected bool */
/* @var $templates array */

$this->title = 'Marketing — Campañas WhatsApp';
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js', ['position' => \yii\web\View::POS_HEAD]);

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
?>

<style>
    .marketing-wrap { padding: 12px 4px; }
    .marketing-card { background: #fff; border-radius: 14px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); padding: 18px; margin-bottom: 18px; }
    .marketing-card h5 { margin-top: 0; }
    .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; }
    .status-pill.connected { background: #d1f5d3; color: #176d3b; }
    .status-pill.disconnected { background: #fde2e1; color: #9a1f1c; }
    .clients-toolbar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 10px; }
    .clients-toolbar .search { flex: 1; min-width: 220px; }
    .clients-list-wrap { max-height: 460px; overflow-y: auto; border: 1px solid #e3e6ef; border-radius: 10px; }
    .clients-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .clients-table thead th { background: #f1f4fa; padding: 8px 10px; text-align: left; position: sticky; top: 0; z-index: 1; font-weight: 600; color: #334; }
    .clients-table tbody td { padding: 6px 10px; border-top: 1px solid #f1f3f7; vertical-align: middle; }
    .clients-table tbody tr:hover { background: #fafbff; }
    .clients-table .col-check { width: 36px; }
    .clients-table .col-cedula { width: 130px; font-family: monospace; color: #555; }
    .clients-table .col-phone { width: 140px; font-family: monospace; color: #1a6db5; }
    .selection-info { font-size: 13px; color: #345; margin-left: auto; }
    .editor-wrap { min-height: 220px; border: 1px solid #d9dde6; border-radius: 0 0 10px 10px; background: #fff; }
    .ql-toolbar.ql-snow { border-radius: 10px 10px 0 0; border-color: #d9dde6 !important; }
    .image-preview { display: flex; gap: 12px; align-items: center; margin-top: 10px; padding: 10px; background: #f7f9ff; border: 1px dashed #b9c3df; border-radius: 10px; }
    .image-preview img { max-height: 84px; max-width: 120px; border-radius: 6px; border: 1px solid #d9dde6; }
    .image-preview .info { flex: 1; font-size: 13px; color: #345; word-break: break-all; }
    .send-progress { margin-top: 12px; background: #f7f9ff; border-radius: 10px; padding: 12px; border: 1px solid #e0e6f3; display: none; }
    .send-progress.active { display: block; }
    .send-progress .bar { height: 10px; background: #e3e7f1; border-radius: 999px; overflow: hidden; }
    .send-progress .bar > i { display: block; height: 100%; background: linear-gradient(90deg, #25d366, #128c7e); width: 0; transition: width 0.3s ease; }
    .send-progress .stats { display: flex; gap: 12px; font-size: 13px; margin-bottom: 6px; flex-wrap: wrap; color: #345; }
    .send-progress .stat-pill { padding: 3px 10px; border-radius: 999px; background: #fff; border: 1px solid #d9dde6; }
    .send-progress .stat-pill.ok { color: #176d3b; border-color: #b9e6c2; }
    .send-progress .stat-pill.fail { color: #9a1f1c; border-color: #f1c4c2; }
    .results-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 12px; }
    .results-table th, .results-table td { padding: 6px 8px; border-bottom: 1px solid #f0f2f7; text-align: left; }
    .results-table .row-ok { color: #176d3b; }
    .results-table .row-fail { color: #9a1f1c; }
    .placeholders-hint { font-size: 12px; color: #6a7186; }
    .placeholders-hint code { background: #f1f3f9; padding: 1px 4px; border-radius: 4px; color: #345; }

    /* Plantillas */
    .templates-bar { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; padding: 10px; background: #f5f8ff; border: 1px solid #e0e6f3; border-radius: 10px; margin-bottom: 12px; }
    .templates-bar select { flex: 1; min-width: 180px; }
    .templates-bar .templates-label { font-size: 13px; color: #345; font-weight: 600; }

    /* Excluir destinatarios */
    .clients-table .col-exclude { width: 44px; text-align: center; }
    .btn-exclude { background: transparent; border: 1px solid transparent; border-radius: 6px; padding: 2px 6px; cursor: pointer; color: #c33; font-size: 14px; line-height: 1; }
    .btn-exclude:hover { background: #fde2e1; }

    /* Panel de excluidos */
    .excluded-panel { margin-top: 12px; border: 1px solid #f1c4c2; background: #fff7f6; border-radius: 10px; overflow: hidden; }
    .excluded-panel .head { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #fde2e1; cursor: pointer; }
    .excluded-panel .head .title { font-weight: 600; color: #9a1f1c; flex: 1; }
    .excluded-panel .head .actions { display: flex; gap: 8px; }
    .excluded-panel .head .toggle { font-size: 12px; color: #9a1f1c; }
    .excluded-panel .body { max-height: 220px; overflow-y: auto; }
    .excluded-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .excluded-table tbody td { padding: 6px 10px; border-top: 1px solid #fbd6d4; vertical-align: middle; color: #5b2422; }
    .excluded-table .col-cedula { width: 130px; font-family: monospace; color: #6f3a37; }
    .excluded-table .col-phone { width: 140px; font-family: monospace; color: #6f3a37; }
    .excluded-table .col-action { width: 110px; text-align: right; padding-right: 12px; }
    .btn-reinclude { font-size: 12px; padding: 3px 10px; border-radius: 999px; border: 1px solid #198754; color: #198754; background: #fff; cursor: pointer; }
    .btn-reinclude:hover { background: #198754; color: #fff; }
</style>

<div class="marketing-wrap">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:10px;">
        <h3 class="m-0"><i class="material-symbols-outlined align-middle">campaign</i> Marketing — Campañas WhatsApp</h3>
        <div>
            <?php if ($connected): ?>
                <span class="status-pill connected"><i class="material-symbols-outlined" style="font-size:14px;">check_circle</i> Sesión WhatsApp conectada</span>
            <?php else: ?>
                <span class="status-pill disconnected"><i class="material-symbols-outlined" style="font-size:14px;">error</i> WhatsApp desconectado</span>
                <a class="btn btn-sm btn-outline-primary ms-2" href="<?= Url::to(['/config/index']) ?>#whatsapp">Ir a WhatsApp</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="marketing-card">
                <h5>1. Seleccionar destinatarios</h5>
                <div class="clients-toolbar">
                    <input type="text" id="mk-search" class="form-control search" placeholder="Buscar por nombre, cédula o teléfono…">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="mk-select-all">Seleccionar todos</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="mk-clear">Limpiar</button>
                    <span class="selection-info"><span id="mk-selected-count">0</span> de <span id="mk-visible-count"><?= count($clients) ?></span> visibles</span>
                </div>
                <div class="clients-list-wrap">
                    <table class="clients-table">
                        <thead>
                            <tr>
                                <th class="col-check"><input type="checkbox" id="mk-check-header" title="Seleccionar visibles"></th>
                                <th>Nombre</th>
                                <th class="col-cedula">Cédula</th>
                                <th class="col-phone">WhatsApp</th>
                                <th class="col-exclude" title="Excluir destinatario">Excluir</th>
                            </tr>
                        </thead>
                        <tbody id="mk-clients-tbody">
                            <?php foreach ($clients as $c): ?>
                                <tr data-cli-id="<?= (int) $c['id'] ?>" data-search="<?= Html::encode(strtolower($c['name'] . ' ' . $c['cedula'] . ' ' . $c['phone'])) ?>">
                                    <td class="col-check"><input type="checkbox" class="mk-row-check" value="<?= (int) $c['id'] ?>"></td>
                                    <td><?= Html::encode($c['name']) ?></td>
                                    <td class="col-cedula"><?= Html::encode($c['cedula']) ?></td>
                                    <td class="col-phone"><?= Html::encode($c['phone']) ?></td>
                                    <td class="col-exclude">
                                        <button type="button" class="btn-exclude" title="Excluir este destinatario de TODAS las campañas (incluido cuando se usa &laquo;Seleccionar todos&raquo;)">
                                            <i class="material-symbols-outlined" style="font-size:18px;">block</i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($clients)): ?>
                                <tr><td colspan="5" class="text-center text-muted p-4">No hay clientes con WhatsApp registrado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="excluded-panel" id="mk-excluded-panel" style="display:none;">
                    <div class="head" id="mk-excluded-head">
                        <span class="material-symbols-outlined" style="color:#9a1f1c;">block</span>
                        <span class="title">Destinatarios excluidos (<span id="mk-excluded-total">0</span>)</span>
                        <div class="actions">
                            <button type="button" class="btn btn-sm btn-outline-success" id="mk-include-all" title="Volver a incluir a todos">
                                Incluir a todos
                            </button>
                            <span class="toggle" id="mk-excluded-toggle">Mostrar ▼</span>
                        </div>
                    </div>
                    <div class="body" id="mk-excluded-body" style="display:none;">
                        <table class="excluded-table">
                            <tbody id="mk-excluded-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="marketing-card">
                <h5>2. Mensaje</h5>
                <p class="placeholders-hint">
                    Puede usar marcadores que se reemplazan por cliente:
                    <code>{nombre}</code>, <code>{cedula}</code>.
                </p>

                <div class="templates-bar">
                    <span class="templates-label">Plantillas guardadas:</span>
                    <select id="mk-template-select" class="form-select form-select-sm">
                        <option value="">— Ninguna —</option>
                        <?php foreach ($templates as $t): ?>
                            <option
                                value="<?= (int) $t['id'] ?>"
                                data-html="<?= Html::encode($t['message_html']) ?>"
                                data-text="<?= Html::encode($t['message_text']) ?>"
                                data-image-url="<?= Html::encode($t['image_public_url']) ?>"
                                data-image-name="<?= Html::encode($t['image_filename']) ?>"
                            >
                                <?= Html::encode($t['name']) ?>
                                <?php if (!empty($t['updated_at'])): ?>
                                    — <?= Html::encode(substr((string) $t['updated_at'], 0, 16)) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="mk-template-load" class="btn btn-sm btn-outline-primary" title="Cargar la plantilla seleccionada en el editor">
                        <i class="material-symbols-outlined align-middle" style="font-size:16px;">download</i> Cargar
                    </button>
                    <button type="button" id="mk-template-save" class="btn btn-sm btn-success" title="Guardar el mensaje actual como plantilla">
                        <i class="material-symbols-outlined align-middle" style="font-size:16px;">save</i> Guardar mensaje
                    </button>
                    <button type="button" id="mk-template-delete" class="btn btn-sm btn-outline-danger" title="Eliminar la plantilla seleccionada" disabled>
                        <i class="material-symbols-outlined align-middle" style="font-size:16px;">delete</i> Eliminar
                    </button>
                </div>

                <div id="mk-editor-toolbar">
                    <span class="ql-formats">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-list" value="ordered"></button>
                        <button class="ql-list" value="bullet"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-link"></button>
                        <button class="ql-clean"></button>
                    </span>
                </div>
                <div id="mk-editor" class="editor-wrap"></div>

                <div class="mt-3">
                    <label class="form-label fw-semibold">Adjuntar imagen (opcional)</label>
                    <input type="file" id="mk-image-input" accept="image/png,image/jpeg,image/gif,image/webp" class="form-control form-control-sm">
                    <div class="image-preview" id="mk-image-preview" style="display:none;">
                        <img id="mk-image-thumb" src="" alt="preview">
                        <div class="info">
                            <div><strong>Archivo:</strong> <span id="mk-image-name">—</span></div>
                            <div><strong>URL pública:</strong> <span id="mk-image-url">—</span></div>
                            <div class="text-muted">Esta imagen se envía como mensaje con el texto como pie. Asegúrese de tener la URL pública configurada en WhatsApp.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="mk-image-remove">Quitar</button>
                    </div>
                </div>
            </div>

            <div class="marketing-card">
                <h5>3. Envío controlado</h5>
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Segundos entre cada mensaje</label>
                        <input type="number" min="1" max="120" id="mk-interval" class="form-control" value="<?= (int) $mkConfig['interval_seconds'] ?>">
                        <div class="form-text">Recomendado: 5 a 10 segundos para no ser marcado como spam.</div>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success w-100" id="mk-send-btn" <?= $connected ? '' : 'disabled' ?>>
                            <i class="material-symbols-outlined align-middle">send</i>
                            Enviar campaña
                        </button>
                    </div>
                </div>

                <div class="send-progress" id="mk-progress">
                    <div class="stats">
                        <span class="stat-pill">Total: <strong id="mk-stat-total">0</strong></span>
                        <span class="stat-pill ok">Enviados: <strong id="mk-stat-ok">0</strong></span>
                        <span class="stat-pill fail">Fallidos: <strong id="mk-stat-fail">0</strong></span>
                        <span class="stat-pill">Restantes: <strong id="mk-stat-pending">0</strong></span>
                    </div>
                    <div class="bar"><i id="mk-progress-bar"></i></div>
                    <div class="mt-2 text-muted" id="mk-progress-text">Preparando…</div>
                </div>

                <div id="mk-results"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const CSRF_PARAM = <?= json_encode($csrfParam) ?>;
    const CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
    const URL_SEND = <?= json_encode(Url::to(['marketing/send'])) ?>;
    const URL_UPLOAD = <?= json_encode(Url::to(['marketing/upload-image'])) ?>;
    const URL_SAVE_TEMPLATE = <?= json_encode(Url::to(['marketing/save-template'])) ?>;
    const URL_DELETE_TEMPLATE = <?= json_encode(Url::to(['marketing/delete-template'])) ?>;
    const BATCH_SIZE = <?= max(1, (int) $mkConfig['batch_size']) ?>;
    const EXCLUDED_STORAGE_KEY = 'mk_excluded_client_ids_v1';

    // Espera a que Quill esté disponible (Yii inyecta el JS de Quill al final del body).
    function whenReady(cb) {
        if (typeof Quill !== 'undefined' && document.readyState !== 'loading') {
            cb();
        } else if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () { whenReady(cb); });
        } else {
            let tries = 0;
            const t = setInterval(function () {
                tries++;
                if (typeof Quill !== 'undefined') {
                    clearInterval(t);
                    cb();
                } else if (tries > 50) {
                    clearInterval(t);
                    console.error('Marketing: Quill no se cargó.');
                }
            }, 100);
        }
    }

    whenReady(function () { init(); });

    function init() {
    const quill = new Quill('#mk-editor', {
        modules: { toolbar: '#mk-editor-toolbar' },
        placeholder: 'Escriba aquí el mensaje para sus clientes…',
        theme: 'snow'
    });

    const tbody = document.getElementById('mk-clients-tbody');
    const searchInput = document.getElementById('mk-search');
    const headerCheck = document.getElementById('mk-check-header');
    const selectAllBtn = document.getElementById('mk-select-all');
    const clearBtn = document.getElementById('mk-clear');
    const excludedTotalEl = document.getElementById('mk-excluded-total');
    const excludedPanel = document.getElementById('mk-excluded-panel');
    const excludedHead = document.getElementById('mk-excluded-head');
    const excludedBody = document.getElementById('mk-excluded-body');
    const excludedTbody = document.getElementById('mk-excluded-tbody');
    const excludedToggle = document.getElementById('mk-excluded-toggle');
    const includeAllBtn = document.getElementById('mk-include-all');
    const selectedCountEl = document.getElementById('mk-selected-count');
    const visibleCountEl = document.getElementById('mk-visible-count');
    const sendBtn = document.getElementById('mk-send-btn');
    const progress = document.getElementById('mk-progress');
    const progressBar = document.getElementById('mk-progress-bar');
    const progressText = document.getElementById('mk-progress-text');
    const statTotal = document.getElementById('mk-stat-total');
    const statOk = document.getElementById('mk-stat-ok');
    const statFail = document.getElementById('mk-stat-fail');
    const statPending = document.getElementById('mk-stat-pending');
    const resultsBox = document.getElementById('mk-results');
    const intervalInput = document.getElementById('mk-interval');

    // ===== Excluidos (persistidos en localStorage) =====
    function loadExcluded() {
        try {
            const raw = localStorage.getItem(EXCLUDED_STORAGE_KEY);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? new Set(parsed.map(Number).filter(n => !isNaN(n))) : new Set();
        } catch (e) {
            return new Set();
        }
    }
    function saveExcluded() {
        try {
            localStorage.setItem(EXCLUDED_STORAGE_KEY, JSON.stringify(Array.from(excludedSet)));
        } catch (e) { /* ignore */ }
    }
    const excludedSet = loadExcluded();

    // Captura los datos originales de cada fila para reconstruir el panel de excluidos.
    const clientsMap = {};
    Array.from(tbody.querySelectorAll('tr[data-cli-id]')).forEach(row => {
        const id = parseInt(row.dataset.cliId, 10);
        const tds = row.querySelectorAll('td');
        clientsMap[id] = {
            id,
            name: (tds[1]?.textContent || '').trim(),
            cedula: (tds[2]?.textContent || '').trim(),
            phone: (tds[3]?.textContent || '').trim(),
        };
    });

    function getAllRows() {
        return Array.from(tbody.querySelectorAll('tr[data-cli-id]'));
    }
    function getCheckedIds() {
        return getAllRows()
            .filter(r => {
                if (r.style.display === 'none') return false;
                const id = parseInt(r.dataset.cliId, 10);
                if (excludedSet.has(id)) return false;
                return r.querySelector('.mk-row-check')?.checked;
            })
            .map(r => parseInt(r.dataset.cliId, 10));
    }
    function getVisibleNonExcludedRows() {
        const term = (searchInput.value || '').trim().toLowerCase();
        return getAllRows().filter(row => {
            const id = parseInt(row.dataset.cliId, 10);
            if (excludedSet.has(id)) return false;
            if (term && row.dataset.search.indexOf(term) === -1) return false;
            return true;
        });
    }
    function refreshSelectedCount() {
        selectedCountEl.textContent = getCheckedIds().length;
        visibleCountEl.textContent = getVisibleNonExcludedRows().length;
    }

    function renderExcludedPanel() {
        const ids = Array.from(excludedSet);
        excludedTotalEl.textContent = ids.length;
        if (ids.length === 0) {
            excludedPanel.style.display = 'none';
            excludedBody.style.display = 'none';
            excludedToggle.textContent = 'Mostrar ▼';
            excludedTbody.innerHTML = '';
            return;
        }
        excludedPanel.style.display = 'block';

        // Construir filas usando el mapa de clientes.
        const html = ids
            .filter(id => clientsMap[id])
            .map(id => {
                const c = clientsMap[id];
                const esc = s => String(s == null ? '' : s).replace(/[<>&"]/g, ch => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[ch]));
                return '<tr data-cli-id="' + id + '">'
                    + '<td>' + esc(c.name) + '</td>'
                    + '<td class="col-cedula">' + esc(c.cedula) + '</td>'
                    + '<td class="col-phone">' + esc(c.phone) + '</td>'
                    + '<td class="col-action"><button type="button" class="btn-reinclude" data-cli-id="' + id + '">↩ Incluir</button></td>'
                    + '</tr>';
            })
            .join('');
        excludedTbody.innerHTML = html;
    }

    function applyExclusionStyles() {
        getAllRows().forEach(row => {
            const id = parseInt(row.dataset.cliId, 10);
            if (excludedSet.has(id)) {
                row.style.display = 'none'; // sacar de la lista principal
                const cb = row.querySelector('.mk-row-check');
                if (cb) cb.checked = false;
            } else if (row.style.display === 'none' && !row.dataset.hiddenBySearch) {
                row.style.display = '';
            }
        });
        renderExcludedPanel();
    }

    searchInput.addEventListener('input', function() {
        const term = this.value.trim().toLowerCase();
        getAllRows().forEach(row => {
            row.style.display = (!term || row.dataset.search.indexOf(term) !== -1) ? '' : 'none';
        });
    });

    headerCheck.addEventListener('change', function() {
        const checked = this.checked;
        getAllRows().forEach(row => {
            if (row.style.display === 'none') return;
            const id = parseInt(row.dataset.cliId, 10);
            if (excludedSet.has(id)) return; // no se selecciona si está excluido
            const cb = row.querySelector('.mk-row-check');
            if (cb) cb.checked = checked;
        });
        refreshSelectedCount();
    });

    selectAllBtn.addEventListener('click', function() {
        getAllRows().forEach(row => {
            const id = parseInt(row.dataset.cliId, 10);
            if (excludedSet.has(id)) return;
            const cb = row.querySelector('.mk-row-check');
            if (cb) cb.checked = true;
        });
        refreshSelectedCount();
    });

    clearBtn.addEventListener('click', function() {
        getAllRows().forEach(row => {
            const cb = row.querySelector('.mk-row-check');
            if (cb) cb.checked = false;
        });
        headerCheck.checked = false;
        refreshSelectedCount();
    });

    clearExcludedBtn.addEventListener('click', function() {
        if (excludedSet.size === 0) return;
        if (!confirm('¿Quitar las ' + excludedSet.size + ' exclusiones guardadas?')) return;
        excludedSet.clear();
        saveExcluded();
        applyExclusionStyles();
        refreshSelectedCount();
    });

    tbody.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-exclude');
        if (!btn) return;
        const row = btn.closest('tr[data-cli-id]');
        if (!row) return;
        const id = parseInt(row.dataset.cliId, 10);
        if (excludedSet.has(id)) {
            excludedSet.delete(id);
        } else {
            excludedSet.add(id);
        }
        saveExcluded();
        applyExclusionStyles();
        refreshSelectedCount();
    });

    tbody.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('mk-row-check')) {
            const row = e.target.closest('tr[data-cli-id]');
            if (row) {
                const id = parseInt(row.dataset.cliId, 10);
                if (excludedSet.has(id)) {
                    e.target.checked = false; // no se puede marcar un excluido
                }
            }
            refreshSelectedCount();
        }
    });

    applyExclusionStyles();

    // === Subida de imagen ===
    const imageInput = document.getElementById('mk-image-input');
    const previewBox = document.getElementById('mk-image-preview');
    const imageThumb = document.getElementById('mk-image-thumb');
    const imageName = document.getElementById('mk-image-name');
    const imageUrlEl = document.getElementById('mk-image-url');
    const imageRemove = document.getElementById('mk-image-remove');
    let uploadedImage = null; // { public_url, url, filename }

    imageInput.addEventListener('change', async function() {
        const file = this.files && this.files[0];
        if (!file) return;
        const fd = new FormData();
        fd.append('image', file);
        fd.append(CSRF_PARAM, CSRF_TOKEN);
        previewBox.style.display = 'flex';
        imageName.textContent = 'Subiendo…';
        imageUrlEl.textContent = '—';
        imageThumb.src = URL.createObjectURL(file);
        try {
            const res = await fetch(URL_UPLOAD, { method: 'POST', body: fd, credentials: 'same-origin' });
            const data = await res.json();
            if (data && data.success) {
                uploadedImage = { public_url: data.public_url, url: data.url, filename: data.filename };
                imageName.textContent = data.filename + ' (' + Math.round((data.size || 0)/1024) + ' KB)';
                imageUrlEl.innerHTML = '<a href="' + data.public_url + '" target="_blank" rel="noopener">' + data.public_url + '</a>';

                // Mostrar alerta si la imagen no es accesible públicamente (la API no podrá descargarla)
                let warn = document.getElementById('mk-image-warn');
                if (!warn) {
                    warn = document.createElement('div');
                    warn.id = 'mk-image-warn';
                    warn.className = 'alert alert-warning mt-2 mb-0 p-2';
                    warn.style.fontSize = '12px';
                    previewBox.parentElement.appendChild(warn);
                }
                if (data.reachable === false) {
                    warn.style.display = 'block';
                    warn.innerHTML = '<strong>⚠ La URL pública NO es accesible desde Internet</strong><br>'
                        + 'La API de WhatsApp no podrá descargar la imagen y los mensajes con imagen fallarán.<br>'
                        + 'Detalle: ' + (data.reachable_error || 'desconocido') + '<br>'
                        + 'Solución: vaya a <code>Configuración → WhatsApp</code> y configure correctamente la URL pública base (https).';
                } else if (data.reachable === true) {
                    warn.style.display = 'block';
                    warn.className = 'alert alert-success mt-2 mb-0 p-2';
                    warn.style.fontSize = '12px';
                    warn.innerHTML = '✅ La URL pública es accesible desde Internet — la API podrá descargar la imagen.';
                } else {
                    warn.style.display = 'none';
                }
            } else {
                uploadedImage = null;
                imageName.textContent = 'Error: ' + (data && data.message ? data.message : 'fallo al subir');
                imageUrlEl.textContent = '—';
            }
        } catch (e) {
            uploadedImage = null;
            imageName.textContent = 'Error: ' + e.message;
        }
    });

    imageRemove.addEventListener('click', function() {
        uploadedImage = null;
        imageInput.value = '';
        previewBox.style.display = 'none';
    });

    // === Plantillas de mensajes ===
    const templateSelect = document.getElementById('mk-template-select');
    const templateLoadBtn = document.getElementById('mk-template-load');
    const templateSaveBtn = document.getElementById('mk-template-save');
    const templateDeleteBtn = document.getElementById('mk-template-delete');

    function refreshTemplateButtons() {
        const hasSelection = templateSelect.value !== '';
        templateLoadBtn.disabled = !hasSelection;
        templateDeleteBtn.disabled = !hasSelection;
    }
    templateSelect.addEventListener('change', refreshTemplateButtons);
    refreshTemplateButtons();

    templateLoadBtn.addEventListener('click', function() {
        const opt = templateSelect.options[templateSelect.selectedIndex];
        if (!opt || !opt.value) return;
        const html = opt.getAttribute('data-html') || '';
        const text = opt.getAttribute('data-text') || '';
        const imgUrl = opt.getAttribute('data-image-url') || '';
        const imgName = opt.getAttribute('data-image-name') || '';

        if (html.trim() !== '') {
            quill.root.innerHTML = html;
        } else {
            quill.setText(text);
        }

        if (imgUrl !== '') {
            uploadedImage = { public_url: imgUrl, url: imgUrl, filename: imgName || 'plantilla.jpg' };
            previewBox.style.display = 'flex';
            imageThumb.src = imgUrl;
            imageName.textContent = imgName || 'plantilla.jpg';
            imageUrlEl.innerHTML = '<a href="' + imgUrl + '" target="_blank" rel="noopener">' + imgUrl + '</a>';
        } else {
            uploadedImage = null;
            imageInput.value = '';
            previewBox.style.display = 'none';
        }
    });

    templateSaveBtn.addEventListener('click', async function() {
        const html = quill.root.innerHTML;
        const text = getPlainMessage();
        if (!text && !uploadedImage) {
            alert('No hay nada que guardar: escriba un mensaje o adjunte una imagen primero.');
            return;
        }

        // Si hay una plantilla seleccionada, preguntar si actualizarla o crear una nueva.
        let id = 0;
        let suggestedName = '';
        if (templateSelect.value !== '') {
            const opt = templateSelect.options[templateSelect.selectedIndex];
            const currentName = opt ? opt.textContent.trim().split(' — ')[0] : '';
            const overwrite = confirm('Hay una plantilla seleccionada («' + currentName + '»). '
                + '¿Reemplazarla con el mensaje actual?\n\n'
                + 'Aceptar = reemplazar la existente.\n'
                + 'Cancelar = guardar como nueva.');
            if (overwrite) {
                id = parseInt(templateSelect.value, 10);
                suggestedName = currentName;
            }
        }
        let name = suggestedName;
        if (!id) {
            name = prompt('Nombre para esta plantilla:', name || 'Mensaje ' + (new Date()).toLocaleDateString());
            if (!name) return;
            name = name.trim();
            if (!name) return;
        }

        const fd = new FormData();
        fd.append(CSRF_PARAM, CSRF_TOKEN);
        if (id) fd.append('id', String(id));
        fd.append('name', name);
        fd.append('message_html', html);
        fd.append('message_text', text);
        if (uploadedImage) {
            fd.append('image_public_url', uploadedImage.public_url || '');
            fd.append('image_filename', uploadedImage.filename || '');
        }

        templateSaveBtn.disabled = true;
        try {
            const res = await fetch(URL_SAVE_TEMPLATE, { method: 'POST', body: fd, credentials: 'same-origin' });
            const data = await res.json();
            if (!data.success) {
                alert('No se pudo guardar: ' + (data.message || 'error desconocido'));
                return;
            }
            const t = data.template;
            // Actualiza el <select>
            let opt = templateSelect.querySelector('option[value="' + t.id + '"]');
            if (!opt) {
                opt = document.createElement('option');
                opt.value = String(t.id);
                templateSelect.appendChild(opt);
            }
            opt.textContent = t.name + (t.updated_at ? ' — ' + t.updated_at.substr(0, 16) : '');
            opt.setAttribute('data-html', t.message_html || '');
            opt.setAttribute('data-text', t.message_text || '');
            opt.setAttribute('data-image-url', t.image_public_url || '');
            opt.setAttribute('data-image-name', t.image_filename || '');
            templateSelect.value = String(t.id);
            refreshTemplateButtons();
            alert(data.message || 'Plantilla guardada.');
        } catch (e) {
            alert('Error: ' + e.message);
        } finally {
            templateSaveBtn.disabled = false;
        }
    });

    templateDeleteBtn.addEventListener('click', async function() {
        if (templateSelect.value === '') return;
        const opt = templateSelect.options[templateSelect.selectedIndex];
        const name = opt ? opt.textContent.trim().split(' — ')[0] : '';
        if (!confirm('¿Eliminar la plantilla «' + name + '»? Esta acción no se puede deshacer.')) return;
        const fd = new FormData();
        fd.append(CSRF_PARAM, CSRF_TOKEN);
        fd.append('id', templateSelect.value);
        templateDeleteBtn.disabled = true;
        try {
            const res = await fetch(URL_DELETE_TEMPLATE, { method: 'POST', body: fd, credentials: 'same-origin' });
            const data = await res.json();
            if (!data.success) {
                alert('No se pudo eliminar: ' + (data.message || 'error'));
                return;
            }
            if (opt) opt.remove();
            templateSelect.value = '';
            refreshTemplateButtons();
            alert(data.message || 'Plantilla eliminada.');
        } catch (e) {
            alert('Error: ' + e.message);
        } finally {
            templateDeleteBtn.disabled = false;
        }
    });

    // === Envío en lotes ===
    function getPlainMessage() {
        const html = quill.root.innerHTML;
        // Conservar saltos de línea: convertir <p>, <br> a \n
        let text = html
            .replace(/<\s*br\s*\/?>/gi, '\n')
            .replace(/<\/p>/gi, '\n')
            .replace(/<p[^>]*>/gi, '')
            .replace(/<li[^>]*>/gi, '• ')
            .replace(/<\/li>/gi, '\n')
            .replace(/<\/?(strong|b)>/gi, '*')
            .replace(/<\/?(em|i)>/gi, '_')
            .replace(/<[^>]+>/g, '')
            .replace(/&nbsp;/g, ' ')
            .replace(/&amp;/g, '&')
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&quot;/g, '"');
        return text.replace(/\n{3,}/g, '\n\n').trim();
    }

    async function sendBatch(ids, message, interval, isFirst) {
        const fd = new FormData();
        fd.append(CSRF_PARAM, CSRF_TOKEN);
        ids.forEach(id => fd.append('client_ids[]', id));
        fd.append('message', message);
        fd.append('interval_seconds', interval);
        if (uploadedImage && uploadedImage.public_url) {
            fd.append('image_public_url', uploadedImage.public_url);
        }
        const res = await fetch(URL_SEND, { method: 'POST', body: fd, credentials: 'same-origin' });
        return res.json();
    }

    sendBtn.addEventListener('click', async function() {
        const ids = getCheckedIds();
        if (ids.length === 0) {
            alert('Seleccione al menos un cliente.');
            return;
        }
        const message = getPlainMessage();
        if (!message && !uploadedImage) {
            alert('Escriba un mensaje o adjunte una imagen.');
            return;
        }
        const interval = Math.max(1, parseInt(intervalInput.value || '6', 10));
        const estSec = Math.ceil(ids.length * interval * 1.05);
        if (!confirm('Va a enviar el mensaje a ' + ids.length + ' contacto(s). Tiempo estimado: ~' + estSec + 's. ¿Continuar?')) {
            return;
        }

        sendBtn.disabled = true;
        progress.classList.add('active');
        statTotal.textContent = ids.length;
        statOk.textContent = '0';
        statFail.textContent = '0';
        statPending.textContent = ids.length;
        progressBar.style.width = '0%';
        progressText.textContent = 'Iniciando…';
        resultsBox.innerHTML = '';

        let totalOk = 0;
        let totalFail = 0;
        const allDetails = [];

        for (let i = 0; i < ids.length; i += BATCH_SIZE) {
            const batch = ids.slice(i, i + BATCH_SIZE);
            progressText.textContent = 'Enviando lote ' + (Math.floor(i / BATCH_SIZE) + 1) + ' (' + batch.length + ' contactos)…';
            try {
                const data = await sendBatch(batch, message, interval, i === 0);
                if (data && typeof data === 'object') {
                    totalOk += parseInt(data.sent || 0, 10);
                    totalFail += parseInt(data.failed || 0, 10);
                    if (Array.isArray(data.details)) {
                        data.details.forEach(d => allDetails.push(d));
                    }
                }
            } catch (e) {
                totalFail += batch.length;
                batch.forEach(id => allDetails.push({ id, ok: false, error: e.message }));
            }
            const processed = Math.min(i + batch.length, ids.length);
            const pct = Math.round((processed / ids.length) * 100);
            progressBar.style.width = pct + '%';
            statOk.textContent = totalOk;
            statFail.textContent = totalFail;
            statPending.textContent = ids.length - processed;
        }

        progressText.textContent = 'Campaña finalizada. Enviados: ' + totalOk + ' / ' + ids.length;
        sendBtn.disabled = false;

        if (allDetails.length > 0) {
            let html = '<h6 class="mt-3">Resultado por contacto</h6><table class="results-table"><thead><tr><th>Cliente</th><th>WhatsApp</th><th>Estado</th></tr></thead><tbody>';
            allDetails.forEach(d => {
                let estado;
                if (d.ok) {
                    estado = 'Enviado';
                    if (d.note) estado += ' <span class="text-warning">(' + d.note + ')</span>';
                } else {
                    estado = 'Error: ' + (d.error || 'fallo');
                }
                html += '<tr class="' + (d.ok ? 'row-ok' : 'row-fail') + '">'
                    + '<td>' + (d.name || ('#' + d.id)) + '</td>'
                    + '<td>' + (d.phone || '—') + '</td>'
                    + '<td>' + estado + '</td>'
                    + '</tr>';
            });
            html += '</tbody></table>';
            resultsBox.innerHTML = html;
        }
    });

    refreshSelectedCount();
    } // fin de init()
})();
</script>
