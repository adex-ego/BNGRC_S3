document.addEventListener('DOMContentLoaded', function() {
    const dispatchData = window.dispatchData || {};
    const dispatchMode = window.dispatchMode || null;
    const dispatchRequestedMode = window.dispatchRequestedMode || null;
    const shouldAutoOpen = window.shouldAutoOpen || false;
    const shouldCleanParams = window.shouldCleanParams || false;
    const modalEl = document.getElementById('dispatchModal');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    const modalTitle = modalEl ? modalEl.querySelector('.modal-title') : null;
    const modalBody = modalEl ? modalEl.querySelector('.modal-body') : null;
    const openDispatchBtn = document.getElementById('openDispatchBtn');

    const renderTypeBlock = (itemData) => {
        if (!itemData) {
            return '<div class="alert alert-light border mb-0">Type introuvable.</div>';
        }
        const rows = (itemData.allocations || []).map((item) => {
            const statusClass = item.reste_besoin === 0 ? 'bg-success' : 'bg-warning text-dark';
            const statusLabel = item.reste_besoin === 0 ? 'Satisfait' : 'Partiel';
            return `
                <tr>
                    <td>${item.nom_ville || '-'}</td>
                    <td>${item.date_demande || '-'}</td>
                    <td class="text-end">${item.quantite_besoin}</td>
                    <td class="text-end">${item.quantite_dispatched}</td>
                    <td class="text-end">${item.reste_besoin}</td>
                    <td><span class="badge ${statusClass}">${statusLabel}</span></td>
                </tr>
            `;
        }).join('');

        return `
            <div class="mb-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2">
                    <div>
                        <h6 class="mb-0">${itemData.item_nom}</h6>
                        <small class="text-muted">Type: ${itemData.type_nom || '-'}</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">Total dons: ${itemData.total_don}</span>
                </div>
                ${rows ? `
                    <div class="table-responsive table-scroll">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ville</th>
                                    <th>Date</th>
                                    <th class="text-end">Besoin</th>
                                    <th class="text-end">Dispatch</th>
                                    <th class="text-end">Reste</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows}
                            </tbody>
                        </table>
                    </div>
                ` : '<div class="alert alert-light border mb-0">Aucun besoin pour ce type.</div>'}
            </div>
        `;
    };

    const openDispatchModal = () => {
        if (!modal || !modalTitle || !modalBody) {
            return;
        }
        const effectiveMode = dispatchMode || dispatchRequestedMode;
        if (effectiveMode === 'date') {
            modalTitle.textContent = 'Dispatch en base - Tous les dons (par date)';
        } else if (effectiveMode === 'quantity') {
            modalTitle.textContent = 'Dispatch en base - Tous les dons (par quantite)';
        } else if (effectiveMode === 'proportion') {
            modalTitle.textContent = 'Dispatch en base - Tous les dons (proportionnelle)';
        } else {
            modalTitle.textContent = 'Dispatch en base - Tous les dons';
        }
        const blocks = Object.values(dispatchData || {}).map(renderTypeBlock).join('');
        modalBody.innerHTML = blocks || '<div class="alert alert-light border mb-0">Aucune donnée disponible.</div>';
        modal.show();
    };

    if (shouldAutoOpen) {
        openDispatchModal();
    }

    if (openDispatchBtn) {
        openDispatchBtn.addEventListener('click', () => {
            openDispatchModal();
        });
    }

    if (shouldCleanParams && window.history.replaceState) {
        const url = new URL(window.location.href);
        url.searchParams.delete('dispatch');
        url.searchParams.delete('mode');
        url.searchParams.delete('reset');
        url.searchParams.delete('dispatch_error');
        url.searchParams.delete('reset_error');
        url.searchParams.delete('success');
        url.searchParams.delete('insert_id');
        const query = url.searchParams.toString();
        const nextUrl = url.pathname + (query ? `?${query}` : '');
        window.history.replaceState({}, document.title, nextUrl);
    }
});