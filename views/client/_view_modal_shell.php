<?php
/**
 * Modal vacío + JS para ver cliente con tabs desde el listado.
 */

use yii\helpers\Url;

$modalViewUrl = Url::to(['/client/modal-view']);
?>
<div class="modal fade" id="clientViewModal" tabindex="-1" aria-labelledby="clientViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color:#fff;">
                <h5 class="modal-title" id="clientViewModalLabel" style="color:#fff;">
                    <span class="material-symbols-outlined" style="font-size:22px;vertical-align:middle;margin-right:6px;">person</span>
                    <span id="clientViewModalTitleText">Detalle del cliente</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="clientViewModalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted mb-0">Cargando cliente...</p>
                </div>
                <div id="clientViewModalError" class="alert alert-danger d-none mb-0"></div>
                <div id="clientViewModalBody" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
#clientViewModal .client-view-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px 16px;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 10px;
}
#clientViewModal .client-view-toolbar .client-colored-tabs {
    border-bottom: none !important;
    flex: 1 1 auto;
    min-width: 0;
    gap: 6px;
}
#clientViewModal .client-view-actions-corner {
    flex: 0 0 auto;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 6px;
    margin-left: auto;
}
#clientViewModal .client-view-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 36px);
    gap: 6px;
    justify-content: end;
}
#clientViewModal .client-act-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff !important;
    text-decoration: none;
    border: none;
    box-shadow: 0 1px 4px rgba(0,0,0,.12);
}
#clientViewModal .client-act-btn .material-symbols-outlined {
    font-size: 18px;
    color: #fff !important;
}
#clientViewModal .client-act-edit { background: #0d6efd; }
#clientViewModal .client-act-rent { background: #198754; }
#clientViewModal .client-act-list { background: #0dcaf0; }
#clientViewModal .client-act-delete { background: #dc3545; }
#clientViewModal .client-act-volver {
    font-size: 0.8rem;
    font-weight: 600;
    padding: 4px 8px;
}

#clientViewModal .client-colored-tabs .nav-item { margin-bottom: 6px; }
#clientViewModal .client-colored-tabs .nav-link {
    display: inline-flex !important;
    align-items: center;
    gap: 6px;
    padding: 8px 14px !important;
    min-height: auto !important;
    border: none !important;
    border-left: none !important;
    border-radius: 8px !important;
    color: #ffffff !important;
    font-weight: 600;
    font-size: 0.9rem;
    background: #6c757d !important;
    opacity: 0.88;
    white-space: nowrap;
}
#clientViewModal .client-colored-tabs .nav-link .material-symbols-outlined {
    color: #ffffff !important;
    font-size: 16px !important;
}
#clientViewModal .client-colored-tabs .nav-link:hover,
#clientViewModal .client-colored-tabs .nav-link.active {
    color: #ffffff !important;
    opacity: 1;
}
#clientViewModal .client-colored-tabs .nav-link.active {
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,0,0,.18);
}
#clientViewModal .client-colored-tabs .nav-link[id$="-datos-tab"] { background: #22487a !important; }
#clientViewModal .client-colored-tabs .nav-link[id$="-hacienda-tab"] { background: #6f42c1 !important; }
#clientViewModal .client-colored-tabs .nav-link[id$="-archivos-tab"] { background: #198754 !important; }
#clientViewModal .client-colored-tabs .nav-link[id$="-historial-tab"] { background: #0d6efd !important; }
#clientViewModal .client-colored-tabs .nav-link[id$="-notas-tab"] { background: #20c997 !important; }
#clientViewModal .client-colored-tabs .nav-link .badge {
    background: rgba(255,255,255,.25) !important;
    color: #fff !important;
}
</style>

<script>
(function () {
    if (window.openClientViewModal) return;

    var MODAL_VIEW_URL = <?= json_encode($modalViewUrl) ?>;

    window.openClientViewModal = function (clientId, clientName) {
        clientId = parseInt(clientId, 10);
        if (!clientId) return false;

        var modalEl = document.getElementById('clientViewModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            window.location.href = <?= json_encode(Url::to(['/client/view'])) ?> + '?id=' + clientId;
            return false;
        }

        var loading = document.getElementById('clientViewModalLoading');
        var errorBox = document.getElementById('clientViewModalError');
        var body = document.getElementById('clientViewModalBody');
        var titleText = document.getElementById('clientViewModalTitleText');

        if (titleText) {
            titleText.textContent = clientName ? ('Cliente: ' + clientName) : ('Cliente #' + clientId);
        }
        if (loading) loading.classList.remove('d-none');
        if (errorBox) {
            errorBox.classList.add('d-none');
            errorBox.textContent = '';
        }
        if (body) {
            body.classList.add('d-none');
            body.innerHTML = '';
        }

        bootstrap.Modal.getOrCreateInstance(modalEl).show();

        fetch(MODAL_VIEW_URL + '?id=' + encodeURIComponent(clientId), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            credentials: 'same-origin'
        })
            .then(function (res) {
                if (!res.ok) throw new Error('No se pudo cargar el cliente (' + res.status + ').');
                return res.text();
            })
            .then(function (html) {
                if (loading) loading.classList.add('d-none');
                if (body) {
                    body.innerHTML = html;
                    body.classList.remove('d-none');
                    window.currentClientId = clientId;
                    if (typeof loadFiles === 'function') {
                        loadFiles(clientId, '');
                    }
                }
            })
            .catch(function (err) {
                if (loading) loading.classList.add('d-none');
                if (errorBox) {
                    errorBox.textContent = (err && err.message) ? err.message : 'Error al cargar el cliente.';
                    errorBox.classList.remove('d-none');
                }
            });

        return false;
    };
})();
</script>
