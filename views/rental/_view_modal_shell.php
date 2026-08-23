<?php
/**
 * Shell reutilizable: modal vacío + JS para cargar detalle de alquiler en tabs.
 * Incluir una sola vez por página de listado.
 */

use yii\helpers\Url;

$modalViewUrl = Url::to(['/rental/modal-view']);
?>
<div class="modal fade" id="rentalViewModal" tabindex="-1" aria-labelledby="rentalViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: #fff;">
                <h5 class="modal-title" id="rentalViewModalLabel" style="color:#fff;">
                    <span class="material-symbols-outlined" style="font-size:22px;vertical-align:middle;margin-right:6px;">receipt_long</span>
                    <span id="rentalViewModalTitleText">Detalle del alquiler</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="rentalViewModalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted mb-0">Cargando detalle...</p>
                </div>
                <div id="rentalViewModalError" class="alert alert-danger d-none mb-0"></div>
                <div id="rentalViewModalBody" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Tabs del modal de detalle: anulan el .nav-link blanco del menú lateral */
#rentalViewModal .rental-modal-view .nav-tabs {
    border-bottom: 2px solid #dee2e6;
    gap: 6px;
    flex-wrap: wrap;
}

#rentalViewModal .rental-modal-view .nav-tabs .nav-item {
    margin-bottom: 6px;
}

#rentalViewModal .rental-modal-view .nav-tabs .nav-link {
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
    line-height: 1.2;
    background: #6c757d !important;
    opacity: 0.85;
    text-decoration: none;
    white-space: nowrap;
}

#rentalViewModal .rental-modal-view .nav-tabs .nav-link .material-symbols-outlined {
    color: #ffffff !important;
    font-size: 16px !important;
}

#rentalViewModal .rental-modal-view .nav-tabs .nav-link:hover {
    color: #ffffff !important;
    opacity: 1;
    background: #5a6268 !important;
}

#rentalViewModal .rental-modal-view .nav-tabs .nav-link.active {
    color: #ffffff !important;
    opacity: 1;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
}

/* Colores por pestaña */
#rentalViewModal .rental-modal-view .nav-tabs .nav-link[id$="-detalles-tab"],
#rentalViewModal .rental-modal-view .nav-tabs .nav-link[id$="-detalles-tab"].active {
    background: #22487a !important;
}

#rentalViewModal .rental-modal-view .nav-tabs .nav-link[id$="-historial-tab"],
#rentalViewModal .rental-modal-view .nav-tabs .nav-link[id$="-historial-tab"].active {
    background: #0d6efd !important;
}

#rentalViewModal .rental-modal-view .nav-tabs .nav-link[id$="-extra-tab"],
#rentalViewModal .rental-modal-view .nav-tabs .nav-link[id$="-extra-tab"].active {
    background: #198754 !important;
}

#rentalViewModal .rental-modal-view .nav-tabs .nav-link[id$="-acciones-tab"],
#rentalViewModal .rental-modal-view .nav-tabs .nav-link[id$="-acciones-tab"].active {
    background: #fd7e14 !important;
}

#rentalViewModal .rental-modal-view .nav-tabs .nav-link .badge {
    background: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
    font-weight: 700;
}
</style>

<script>
(function () {
    if (window.openRentalViewModal) {
        return;
    }

    var MODAL_VIEW_URL = <?= json_encode($modalViewUrl) ?>;

    window.openRentalViewModal = function (rentalId, rentalCode) {
        rentalId = parseInt(rentalId, 10);
        if (!rentalId) {
            return false;
        }

        var modalEl = document.getElementById('rentalViewModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            window.location.href = <?= json_encode(Url::to(['/rental/view'])) ?> + '?id=' + rentalId;
            return false;
        }

        var loading = document.getElementById('rentalViewModalLoading');
        var errorBox = document.getElementById('rentalViewModalError');
        var body = document.getElementById('rentalViewModalBody');
        var titleText = document.getElementById('rentalViewModalTitleText');

        if (titleText) {
            titleText.textContent = rentalCode
                ? ('Alquiler #' + rentalCode)
                : ('Alquiler #' + rentalId);
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

        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        fetch(MODAL_VIEW_URL + '?id=' + encodeURIComponent(rentalId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            },
            credentials: 'same-origin'
        })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error('No se pudo cargar el detalle (' + res.status + ').');
                }
                return res.text();
            })
            .then(function (html) {
                if (loading) loading.classList.add('d-none');
                if (body) {
                    body.innerHTML = html;
                    body.classList.remove('d-none');
                    if (window.jQuery && jQuery.fn && typeof jQuery.fn.yiiActiveForm === 'undefined') {
                        // no-op: Yii data-method links need yii.js (already on page)
                    }
                    if (window.yii) {
                        // Re-bind data-confirm / data-method on injected HTML
                        try {
                            jQuery(body).find('a[data-method], a[data-confirm]').each(function () {
                                // handled globally by yii.js click handlers
                            });
                        } catch (e) {}
                    }
                }
            })
            .catch(function (err) {
                if (loading) loading.classList.add('d-none');
                if (errorBox) {
                    errorBox.textContent = (err && err.message) ? err.message : 'Error al cargar el detalle.';
                    errorBox.classList.remove('d-none');
                }
            });

        return false;
    };
})();
</script>
