<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BNGRC - Dons</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container py-4">
        <?php include __DIR__ . '/partials/header.php'; ?>

        <section class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                    <h2 class="h5 mb-0">Liste des dons</h2>
                    <span class="badge bg-primary-subtle text-primary">Total: <?php echo htmlspecialchars((string) count($dons ?? [])); ?></span>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">Insertion reussie. ID: <?php echo htmlspecialchars((string) ($insert_id ?? '')); ?></div>
                <?php endif; ?>

                <h3 class="h6 text-uppercase text-primary mb-3">Ajouter un Don</h3>
                <form method="POST" action="/dons/create" class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="id_besoin_type">Type de Don</label>
                        <select class="form-select" id="id_besoin_type" name="id_besoin_type" required>
                            <option value="">-- Sélectionnez un type --</option>
                            <?php if (isset($types_dons) && !empty($types_dons)): ?>
                                <?php foreach ($types_dons as $type): ?>
                                    <option value="<?php echo htmlspecialchars((string) $type['id_besoin']); ?>">
                                        <?php echo htmlspecialchars((string) $type['nom_besoin']); ?>
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
                            <div class="col-12 col-md-4">ID: <?php echo htmlspecialchars((string) $don['id_don']); ?></div>
                            <div class="col-12 col-md-4">Type: <?php echo htmlspecialchars((string) ($don['nom_besoin'] ?? '')); ?></div>
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
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th class="text-end">Quantite</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dons as $d): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) $d['id_don']); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($d['nom_besoin'] ?? '')); ?></td>
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
</body>
</html>
