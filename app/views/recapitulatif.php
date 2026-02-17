<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>BNGRC - Récapitulatif</title>
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
                    <h2 class="h5 mb-0">Récapitulatif des Achats</h2>
                    <button id="btnActualiser" class="btn btn-sm btn-outline-primary" onclick="actualiserRecap()">↻ Actualiser</button>
                </div>

                <h5>Statistiques</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="bg-light p-3 rounded border-start border-success border-4">
                            <small class="text-muted">Total des besoins</small>
                            <div class="h5 mb-0"><span id="stat-total">0.00</span> Ar</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="bg-light p-3 rounded border-start border-warning border-4">
                            <small class="text-muted">Besoins satisfaits</small>
                            <div class="h5 mb-0"><span id="stat-satisfaits">0.00</span> Ar</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="bg-light p-3 rounded border-start border-danger border-4">
                            <small class="text-muted">Besoins restants</small>
                            <div class="h5 mb-0"><span id="stat-restants">0.00</span> Ar</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h5>Achats validés</h5>

                <?php if (empty($achats_valides)): ?>
                    <div class="alert alert-info">
                        Aucun achat validé pour le moment. <a href="<?php echo BASE_URL ?>/achats" class="alert-link">Créer une simulation</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Besoin</th>
                                    <th>Qté</th>
                                    <th>Prix/U Ar</th>
                                    <th>Montant HT Ar</th>
                                    <th>Frais</th>
                                    <th>Total Ar</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($achats_valides ?? [] as $achat): ?>
                                    <tr>
                                        <td><small><?php echo $achat['id_achat']; ?></small></td>
                                        <td><?php echo htmlspecialchars($achat['nom_besoin'] ?? ''); ?></td>
                                        <td><?php echo $achat['quantite_achetee']; ?></td>
                                        <td><?php echo number_format($achat['prix_unitaire'], 2); ?></td>
                                        <td><?php echo number_format($achat['quantite_achetee'] * $achat['prix_unitaire'], 2); ?></td>
                                        <td><?php echo $achat['frais_achat_percent']; ?>%</td>
                                        <td><strong><?php echo number_format($achat['montant_total'], 2); ?></strong></td>
                                        <td><small><?php echo $achat['date_achat']; ?></small></td>
                                        <td><span class="badge bg-success"><?php echo ucfirst($achat['statut']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="<?php echo BASE_URL ?>/achats" class="btn btn-outline-secondary btn-sm">← Achats</a>
                    <a href="<?php echo BASE_URL ?>/home" class="btn btn-outline-secondary btn-sm">← Accueil</a>
                </div>
            </div>
        </section>
    </div>

    <script>
        window.BASE_URL = '<?php echo BASE_URL ?>';
    </script>
    <script src="<?php echo BASE_URL ?>/public/assets/js/scriptRecap.js"></script>
</body>
</html>
