<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BNGRC - Dons</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/style.css">
</head>
<body>
    <div class="container py-4">
        <?php include __DIR__ . '/partials/header.php'; ?>

        <section class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                    <div>
                        <h2 class="h5 mb-0">Liste des dons</h2>
                        <span class="badge bg-primary-subtle text-primary">Total: <?php echo htmlspecialchars((string) count($dons ?? [])); ?></span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-dispatch="all-date">
                            Dispatch par date
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-dispatch="all-quantity">
                            Dispatch par quantite
                        </button>
                    </div>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">Insertion reussie.</div>
                <?php endif; ?>

                <h3 class="h6 text-uppercase text-primary mb-3">Ajouter un Don</h3>
                <form method="POST" action="<?php echo BASE_URL ?>/dons" class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="id_besoin_item">Besoin</label>
                        <select class="form-select" id="id_besoin_item" name="id_besoin_item" required>
                            <option value="">-- Sélectionnez un besoin --</option>
                            <?php if (isset($items_dons) && !empty($items_dons)): ?>
                                <?php foreach ($items_dons as $item): ?>
                                    <option value="<?php echo htmlspecialchars((string) $item['id_besoin']); ?>">
                                        <?php echo htmlspecialchars((string) $item['nom_besoin']); ?> (<?php echo htmlspecialchars((string) $item['nom_type']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="quantite_don">Quantité</label>
                        <input type="number" class="form-control" id="quantite_don" name="quantite_don" min="1" placeholder="Entrez la quantité" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Ajouter le Don</button>
                    </div>
                </form>

                <?php if (!empty($don)): ?>
                    <div class="alert alert-light border mb-4">
                        <h3 class="h6 text-uppercase text-primary">Don selectionne</h3>
                        <div class="row g-2">
                            <div class="col-12 col-md-4">Besoin: <?php echo htmlspecialchars((string) ($don['nom_besoin'] ?? '')); ?></div>
                            <div class="col-12 col-md-4">Type: <?php echo htmlspecialchars((string) ($don['nom_type'] ?? '')); ?></div>
                            <div class="col-12 col-md-4">Quantite: <?php echo htmlspecialchars((string) $don['quantite_don']); ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($dons)): ?>
                    <div class="alert alert-light border mb-0">Aucun don.</div>
                <?php else: ?>
                    <div class="table-responsive table-scroll">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Besoin</th>
                                    <th>Type</th>
                                    <th class="text-end">Quantite</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dons as $d): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) ($d['nom_besoin'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($d['nom_type'] ?? '')); ?></td>
                                        <td class="text-end"><?php echo htmlspecialchars((string) $d['quantite_don']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="modal fade" id="dispatchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Simulation de dispatch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL ?>/public/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        const dispatchDataDate = <?php echo json_encode($dispatchByItemDate ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const dispatchDataQuantity = <?php echo json_encode($dispatchByItemQuantity ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const modalEl = document.getElementById('dispatchModal');
        const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
        const modalTitle = modalEl ? modalEl.querySelector('.modal-title') : null;
        const modalBody = modalEl ? modalEl.querySelector('.modal-body') : null;

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

        document.querySelectorAll('[data-dispatch]').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (!modal || !modalTitle || !modalBody) {
                    return;
                }
                const mode = btn.getAttribute('data-dispatch');
                if (mode === 'all-date') {
                    modalTitle.textContent = 'Simulation de dispatch - Tous les dons (par date)';
                    const blocks = Object.values(dispatchDataDate).map(renderTypeBlock).join('');
                    modalBody.innerHTML = blocks || '<div class="alert alert-light border mb-0">Aucune donnée disponible.</div>';
                } else if (mode === 'all-quantity') {
                    modalTitle.textContent = 'Simulation de dispatch - Tous les dons (par quantite)';
                    const blocks = Object.values(dispatchDataQuantity).map(renderTypeBlock).join('');
                    modalBody.innerHTML = blocks || '<div class="alert alert-light border mb-0">Aucune donnée disponible.</div>';
                }
                modal.show();
            });
        });
    </script>
</body>
</html>
