<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>BNGRC - Achats</title>
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
                    <h2 class="h5 mb-0">Achats</h2>
                    <span class="badge bg-info-subtle text-info">Montant: <?php echo number_format($montant_dispo ?? 0, 2); ?> Ar</span>
                </div>

                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Tous les achats ont été validés et les dons d'argent ont été déduits !
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Erreur lors de la validation des achats
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($achats_simules)): ?>
                    <div class="alert alert-warning">
                        Aucun achat en cours. <a href="<?php echo BASE_URL ?>/achats" class="alert-link">Ajouter un achat</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Besoin</th>
                                    <th>Ville</th>
                                    <th>Qté</th>
                                    <th>Prix/U Ar</th>
                                    <th>Montant HT Ar</th>
                                    <th>Frais</th>
                                    <th>Total Ar</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($achats_simules ?? [] as $achat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($achat['nom_besoin'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($achat['nom_ville'] ?? ''); ?></td>
                                        <td><?php echo $achat['quantite_achetee']; ?></td>
                                        <td><?php echo number_format($achat['prix_unitaire'], 2); ?></td>
                                        <td><?php echo number_format($achat['quantite_achetee'] * $achat['prix_unitaire'], 2); ?></td>
                                        <td><?php echo $achat['frais_achat_percent'] ?? 0; ?>%</td>
                                        <td><strong><?php echo number_format($achat['montant_total'], 2); ?></strong></td>
                                        <td>
                                            <button class="btn btn-sm btn-success btn-valider" data-id="<?php echo $achat['id_achat']; ?>" title="Valider">✓</button>
                                            <button class="btn btn-sm btn-danger btn-supprimer" data-id="<?php echo $achat['id_achat']; ?>">✕</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-light border">
                        <strong>Total des achats :</strong> 
                        <?php echo number_format(array_reduce($achats_simules ?? [], function($carry, $item) { return $carry + $item['montant_total']; }, 0), 2); ?> Ar
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <form method="POST" action="<?php echo BASE_URL ?>/achats/commit-all" style="display: inline;">
                            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Valider TOUS les achats et déduire des dons d\'argent ?')">Valider tous les achats</button>
                        </form>
                        <a href="<?php echo BASE_URL ?>/achats" class="btn btn-outline-secondary btn-sm">← Retour aux achats</a>
                        <a href="<?php echo BASE_URL ?>/home" class="btn btn-outline-secondary btn-sm">Retour à l'accueil</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';

        document.querySelectorAll('.btn-valider').forEach(btn => {
            btn.addEventListener('click', async function() {
                const idAchat = this.dataset.id;
                
                if (!confirm('Valider cet achat ?')) return;

                try {
                    const response = await fetch(BASE_URL + '/achats/validate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id_achat=${idAchat}`
                    });

                    const data = await response.json();

                    if (data.error) {
                        alert('Erreur : ' + data.error);
                    } else {
                        alert('Achat validé !');
                        location.reload();
                    }
                } catch (error) {
                    alert('Erreur : ' + error.message);
                }
            });
        });

        document.querySelectorAll('.btn-supprimer').forEach(btn => {
            btn.addEventListener('click', async function() {
                const idAchat = this.dataset.id;
                
                if (!confirm('Supprimer cette simulation ?')) return;

                try {
                    const response = await fetch(BASE_URL + '/achats/delete-simulation', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id_achat=${idAchat}`
                    });

                    const data = await response.json();

                    if (data.error) {
                        alert('Erreur : ' + data.error);
                    } else {
                        alert('Simulation supprimée!');
                        location.reload();
                    }
                } catch (error) {
                    alert('Erreur : ' + error.message);
                }
            });
        });
    </script>
</body>
</html>
