<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BNGRC - Ville</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/style.css">
</head>
<body>
    <div class="container py-4">
        <?php include __DIR__ . '/partials/header.php'; ?>

        <section class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        <h2 class="h5 mb-1">Gestion de la ville</h2>
                        <p class="mb-0 text-muted">
                            <?php echo htmlspecialchars((string) ($ville['nom_ville'] ?? 'Ville inconnue')); ?>
                            <?php if (!empty($ville['nom_region'])): ?>
                                — <?php echo htmlspecialchars((string) $ville['nom_region']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                        <a href="<?php echo BASE_URL ?>/home" class="btn btn-outline-secondary btn-sm">Retour</a>
                </div>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-muted">Besoins actuels</h3>
                        <p class="display-6 mb-0"><?php echo htmlspecialchars((string) ($totalBesoins ?? 0)); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-muted">Quantite demandee</h3>
                        <p class="display-6 mb-0"><?php echo htmlspecialchars((string) ($totalQuantite ?? 0)); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-muted">Besoins deja satisfaits</h3>
                        <p class="mb-0 text-muted">Non disponible</p>
                    </div>
                </div>
            </div>
        </div>

        <section class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Liste des besoins</h2>
                <?php if (empty($besoins)): ?>
                    <div class="alert alert-light border mb-0">Aucun besoin pour cette ville.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Quantite</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($besoins as $b): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) ($b['nom_besoin'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($b['quantite_besoin'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($b['date_demande'] ?? '')); ?></td>
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
