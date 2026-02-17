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
                        <form method="POST" action="<?php echo BASE_URL ?>/dons/dispatch" class="d-inline">
                            <input type="hidden" name="mode" value="date">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                Dispatch par date
                            </button>
                        </form>
                        <form method="POST" action="<?php echo BASE_URL ?>/dons/dispatch" class="d-inline">
                            <input type="hidden" name="mode" value="quantity">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                Dispatch par quantite
                            </button>
                        </form>
                        <form method="POST" action="<?php echo BASE_URL ?>/dons/dispatch" class="d-inline">
                            <input type="hidden" name="mode" value="proportion">
                            <button type="submit" class="btn btn-outline-success btn-sm">
                                Dispatch proportionnelle
                            </button>
                        </form>
                        <form method="POST" action="<?php echo BASE_URL ?>/dons/dispatch/reset" class="d-inline">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                Reset dispatch
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="openDispatchBtn" <?php echo empty($dispatchByItem) ? 'disabled' : ''; ?>>
                            Voir le dernier dispatch
                        </button>
                    </div>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">Insertion reussie.</div>
                <?php endif; ?>

                <?php if (!empty($dispatchTriggered)): ?>
                    <div class="alert alert-success">Dispatch enregistre en base.</div>
                <?php endif; ?>

                <?php if (!empty($dispatchError)): ?>
                    <div class="alert alert-danger">Erreur lors du dispatch.</div>
                <?php endif; ?>

                <?php if (!empty($resetSuccess)): ?>
                    <div class="alert alert-warning">Dispatch reinitialise. Les dons sont revenus a l'etat precedent.</div>
                <?php endif; ?>

                <?php if (!empty($resetError)): ?>
                    <div class="alert alert-danger">Erreur lors de la reinitialisation du dispatch.</div>
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
                                    <th>Dons</th>
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
                    <h5 class="modal-title">Dispatch en base</h5>
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
        window.BASE_URL = '<?php echo BASE_URL ?>';
        window.dispatchData = <?php echo json_encode($dispatchByItem ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        window.dispatchMode = <?php echo json_encode($dispatchMode ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        window.dispatchRequestedMode = <?php echo json_encode($dispatchRequestedMode ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        window.shouldAutoOpen = <?php echo json_encode(!empty($dispatchTriggered), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        window.shouldCleanParams = <?php echo json_encode(!empty($dispatchTriggered) || !empty($resetSuccess) || !empty($success) || !empty($dispatchError) || !empty($resetError), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>
    <script src="<?php echo BASE_URL ?>/public/assets/js/scriptDons.js"></script>
</body>
</html>
