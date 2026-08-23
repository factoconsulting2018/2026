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
            <div class="modal-header flex-wrap gap-2" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: #fff;">
                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                    <h5 class="modal-title mb-0 text-truncate" id="rentalViewModalLabel" style="color:#fff;">
                        <span class="material-symbols-outlined" style="font-size:22px;vertical-align:middle;margin-right:6px;">receipt_long</span>
                        <span id="rentalViewModalTitleText">Detalle del alquiler</span>
                    </h5>
                </div>
                <div class="d-flex align-items-center gap-1 flex-shrink-0" id="rentalViewModalNav">
                    <button type="button" class="btn btn-sm btn-light" id="rentalViewPrevBtn" title="Orden anterior" aria-label="Anterior">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">chevron_left</span>
                    </button>
                    <span class="badge bg-light text-dark px-2" id="rentalViewNavCounter" style="font-size:0.75rem;">—</span>
                    <button type="button" class="btn btn-sm btn-light" id="rentalViewNextBtn" title="Orden siguiente" aria-label="Siguiente">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">chevron_right</span>
                    </button>
                    <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
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
            <div class="modal-footer d-flex justify-content-between flex-wrap gap-2">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="rentalViewPrevBtnFooter" title="Orden anterior">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">arrow_back</span>
                        Anterior
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="rentalViewNextBtnFooter" title="Orden siguiente">
                        Siguiente
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">arrow_forward</span>
                    </button>
                </div>
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

#rentalViewModal #rentalViewPrevBtn:disabled,
#rentalViewModal #rentalViewNextBtn:disabled,
#rentalViewModal #rentalViewPrevBtnFooter:disabled,
#rentalViewModal #rentalViewNextBtnFooter:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
</style>

<script>
(function () {
    if (window.openRentalViewModal) {
        return;
    }

    var MODAL_VIEW_URL = <?= json_encode($modalViewUrl) ?>;
    var navList = [];
    var navIndex = -1;
    var currentRentalId = null;

    function collectRentalNavList() {
        var items = [];
        var seen = {};
        document.querySelectorAll('[data-nav-rental-id]').forEach(function (el) {
            var id = parseInt(el.getAttribute('data-nav-rental-id'), 10);
            if (!id || seen[id]) {
                return;
            }
            seen[id] = true;
            items.push({
                id: id,
                code: el.getAttribute('data-nav-rental-code') || ('R' + id)
            });
        });
        return items;
    }

    function updateNavUi() {
        var prevBtn = document.getElementById('rentalViewPrevBtn');
        var nextBtn = document.getElementById('rentalViewNextBtn');
        var prevFoot = document.getElementById('rentalViewPrevBtnFooter');
        var nextFoot = document.getElementById('rentalViewNextBtnFooter');
        var counter = document.getElementById('rentalViewNavCounter');

        var hasList = navList.length > 0 && navIndex >= 0;
        var canPrev = hasList && navIndex > 0;
        var canNext = hasList && navIndex < navList.length - 1;

        if (prevBtn) prevBtn.disabled = !canPrev;
        if (nextBtn) nextBtn.disabled = !canNext;
        if (prevFoot) prevFoot.disabled = !canPrev;
        if (nextFoot) nextFoot.disabled = !canNext;

        if (counter) {
            counter.textContent = hasList
                ? ((navIndex + 1) + ' / ' + navList.length)
                : '—';
        }
    }

    function goNav(delta) {
        if (!navList.length || navIndex < 0) {
            return;
        }
        var nextIdx = navIndex + delta;
        if (nextIdx < 0 || nextIdx >= navList.length) {
            return;
        }
        var item = navList[nextIdx];
        openRentalViewModal(item.id, item.code, { keepNav: true, navIndex: nextIdx });
    }

    function loadRentalHtml(rentalId, rentalCode) {
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

        return fetch(MODAL_VIEW_URL + '?id=' + encodeURIComponent(rentalId), {
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
                }
                updateNavUi();
            })
            .catch(function (err) {
                if (loading) loading.classList.add('d-none');
                if (errorBox) {
                    errorBox.textContent = (err && err.message) ? err.message : 'Error al cargar el detalle.';
                    errorBox.classList.remove('d-none');
                }
                updateNavUi();
            });
    }

    window.openRentalViewModal = function (rentalId, rentalCode, opts) {
        rentalId = parseInt(rentalId, 10);
        if (!rentalId) {
            return false;
        }
        opts = opts || {};

        var modalEl = document.getElementById('rentalViewModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            window.location.href = <?= json_encode(Url::to(['/rental/view'])) ?> + '?id=' + rentalId;
            return false;
        }

        if (!opts.keepNav) {
            navList = collectRentalNavList();
        }
        if (typeof opts.navIndex === 'number') {
            navIndex = opts.navIndex;
        } else {
            navIndex = -1;
            for (var i = 0; i < navList.length; i++) {
                if (navList[i].id === rentalId) {
                    navIndex = i;
                    break;
                }
            }
            if (navIndex < 0) {
                navList.unshift({ id: rentalId, code: rentalCode || ('R' + rentalId) });
                navIndex = 0;
            }
        }

        currentRentalId = rentalId;
        updateNavUi();

        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        loadRentalHtml(rentalId, rentalCode);

        return false;
    };

    document.addEventListener('DOMContentLoaded', function () {
        var prevBtn = document.getElementById('rentalViewPrevBtn');
        var nextBtn = document.getElementById('rentalViewNextBtn');
        var prevFoot = document.getElementById('rentalViewPrevBtnFooter');
        var nextFoot = document.getElementById('rentalViewNextBtnFooter');

        if (prevBtn) prevBtn.addEventListener('click', function () { goNav(-1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { goNav(1); });
        if (prevFoot) prevFoot.addEventListener('click', function () { goNav(-1); });
        if (nextFoot) nextFoot.addEventListener('click', function () { goNav(1); });

        document.addEventListener('keydown', function (e) {
            var modalEl = document.getElementById('rentalViewModal');
            if (!modalEl || !modalEl.classList.contains('show')) {
                return;
            }
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                goNav(-1);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                goNav(1);
            }
        });
    });
})();
</script>
