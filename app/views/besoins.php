<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BNGRC - Home</title>
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
                    <h2 class="h5 mb-0">Liste des besoins</h2>
                    <span class="badge bg-primary-subtle text-primary">Total: <?php echo htmlspecialchars((string) count($besoins ?? [])); ?></span>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">Insertion reussie. ID: <?php echo htmlspecialchars((string) ($insert_id ?? '')); ?></div>
                <?php endif; ?>

                <h3 class="h6 text-uppercase text-primary mb-3">Ajouter un Besoin</h3>
                <form method="POST" action="/besoins" class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="id_besoin_type">Type</label>
                        <select class="form-select" id="id_besoin_type" name="id_besoin_type">
                            <option value="">-- Sélectionnez un type --</option>
                            <?php if (isset($types_besoins) && !empty($types_besoins)): ?>
                                <?php foreach ($types_besoins as $type): ?>
                                    <option value="<?php echo htmlspecialchars((string) $type['id_type']); ?>">
                                        <?php echo htmlspecialchars((string) $type['nom_type']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="id_besoin_item">Besoin</label>
                        <select class="form-select" id="id_besoin_item" name="id_besoin_item" required>
                            <option value="">-- Sélectionnez un besoin --</option>
                            <?php if (isset($items_besoins) && !empty($items_besoins)): ?>
                                <?php foreach ($items_besoins as $item): ?>
                                    <option value="<?php echo htmlspecialchars((string) $item['id_besoin']); ?>" data-type-id="<?php echo htmlspecialchars((string) $item['id_type']); ?>">
                                        <?php echo htmlspecialchars((string) $item['nom_besoin']); ?> (<?php echo htmlspecialchars((string) $item['nom_type']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="id_ville">Ville</label>
                        <select class="form-select" id="id_ville" name="id_ville">
                            <option value="">-- Sélectionnez une ville --</option>
                            <?php if (isset($villes) && !empty($villes)): ?>
                                <?php foreach ($villes as $ville): ?>
                                    <option value="<?php echo htmlspecialchars((string) $ville['id_ville']); ?>">
                                        <?php echo htmlspecialchars((string) $ville['nom_ville']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="quantite_besoin">Quantité</label>
                        <input type="number" class="form-control" id="quantite_besoin" name="quantite_besoin" min="1" placeholder="Entrez la quantité" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="date_demande">Date de Demande</label>
                        <input type="date" class="form-control" id="date_demande" name="date_demande" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Ajouter le Besoin</button>
                    </div>
                </form>

                <?php if (!empty($besoin)): ?>
                    <div class="alert alert-light border mb-4">
                        <h3 class="h6 text-uppercase text-primary">Besoin selectionne</h3>
                        <div class="row g-2">
                            <div class="col-12 col-md-4">Besoin: <?php echo htmlspecialchars((string) ($besoin['nom_besoin'] ?? '')); ?></div>
                            <div class="col-12 col-md-4">Type: <?php echo htmlspecialchars((string) ($besoin['nom_type'] ?? '')); ?></div>
                            <div class="col-12 col-md-4">Quantite: <?php echo htmlspecialchars((string) $besoin['quantite_besoin']); ?></div>
                            <div class="col-12 col-md-6">Ville: <?php echo htmlspecialchars((string) ($besoin['nom_ville'] ?? '')); ?></div>
                            <div class="col-12 col-md-6">Date: <?php echo htmlspecialchars((string) $besoin['date_demande']); ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($besoins)): ?>
                    <div class="alert alert-light border mb-0">Aucun besoin.</div>
                <?php else: ?>
                    <div class="table-responsive table-scroll">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Besoin</th>
                                    <th>Type</th>
                                    <th class="text-end">Quantite</th>
                                    <th>Ville</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($besoins as $b): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) ($b['nom_besoin'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($b['nom_type'] ?? '')); ?></td>
                                        <td class="text-end"><?php echo htmlspecialchars((string) $b['quantite_besoin']); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($b['nom_ville'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string) $b['date_demande']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script>
        const typeSelect = document.getElementById('id_besoin_type');
        const itemSelect = document.getElementById('id_besoin_item');

        const filterItems = () => {
            if (!itemSelect) {
                return;
            }
            const selectedType = typeSelect ? typeSelect.value : '';
            const options = Array.from(itemSelect.options);
            let hasVisible = false;

            options.forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }
                const typeId = option.getAttribute('data-type-id');
                const visible = !selectedType || typeId === selectedType;
                option.hidden = !visible;
                option.disabled = !visible;
                if (visible) {
                    hasVisible = true;
                }
            });

            if (!hasVisible || (itemSelect.selectedOptions[0] && itemSelect.selectedOptions[0].hidden)) {
                itemSelect.value = '';
            }
        };

        if (typeSelect) {
            typeSelect.addEventListener('change', filterItems);
            filterItems();
        }
    </script>
</body>
</html>
