<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>BNGRC - Achats</title>
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
                    <h2 class="h5 mb-0">Simulateur d'Achats</h2>
                    <span class="badge bg-success-subtle text-success">Montant: <?php echo number_format($montant_disponible ?? 0, 2); ?> Ar</span>
                </div>

                <h5>Besoins à satisfaire</h5>

                <div class="row mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="id_ville">Filtrer par ville</label>
                        <select class="form-select" id="id_ville">
                            <option value="">-- Toutes les villes --</option>
                            <?php foreach ($villes ?? [] as $ville): ?>
                                <option value="<?php echo $ville['id_ville']; ?>">
                                    <?php echo htmlspecialchars($ville['nom_ville']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Besoin</th>
                                <th>Type</th>
                                <th>Quantité</th>
                                <th>Prix/U Ar</th>
                                <th>Total Ar</th>
                                <th>Ville</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($besoins ?? [] as $besoin): ?>
                                <tr class="besoin-row" data-id-besoin="<?php echo $besoin['id_besoin']; ?>" data-id-ville="<?php echo $besoin['id_ville']; ?>">
                                    <td><?php echo htmlspecialchars($besoin['nom_besoin']); ?></td>
                                    <td><small><?php echo htmlspecialchars($besoin['nom_type']); ?></small></td>
                                    <td><?php echo $besoin['quantite_besoin']; ?></td>
                                    <td><?php echo number_format($besoin['prix_besoin'], 2); ?></td>
                                    <td><?php echo number_format($besoin['montant_total'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($besoin['nom_ville']); ?></td>
                                    <td><small><?php echo $besoin['date_demande']; ?></small></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-success btn-acheter" 
                                                data-id="<?php echo $besoin['id_besoin']; ?>" 
                                                data-prix="<?php echo $besoin['prix_besoin']; ?>">
                                            Acheter
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <hr class="my-4">

                <h5>Formulaire d'Achat</h5>

                <form id="formAchat" class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="quantite_achetee">Quantité à acheter</label>
                        <input type="number" class="form-control" id="quantite_achetee" name="quantite" min="1">
                    </div>

                    <div id="previewAchat" class="col-12" style="display:none;">
                        <div class="alert alert-info">
                            <h6 class="mb-2">Aperçu du montant</h6>
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <small>Montant HT :</small>
                                    <strong id="montantHT">0.00</strong> Ar
                                </div>
                                <div class="col-12 col-md-6">
                                    <small>Frais (10%) :</small>
                                    <strong id="montantFrais">0.00</strong> Ar
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-top">
                                <small>Total à payer :</small>
                                <strong class="h6" id="montantTotal">0.00</strong> Ar
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simuler l'Achat</button>
                    </div>
                </form>

            </div>
        </section>
    </div>

    <script>
        let selectedBesoin = null;

        // Filtre par ville
        document.getElementById('id_ville').addEventListener('change', function(e) {
            const idVille = e.target.value;
            const rows = document.querySelectorAll('.besoin-row');
            
            rows.forEach(row => {
                if (!idVille || row.dataset.idVille == idVille) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Sélectionner un besoin à acheter
        document.querySelectorAll('.btn-acheter').forEach(btn => {
            btn.addEventListener('click', function() {
                selectedBesoin = {
                    id: this.dataset.id,
                    prix: parseFloat(this.dataset.prix)
                };
                document.getElementById('previewAchat').style.display = 'block';
                document.getElementById('quantite_achetee').focus();
                document.getElementById('quantite_achetee').value = '';
            });
        });

        // Calculer le montant preview
        document.getElementById('quantite_achetee').addEventListener('input', function() {
            if (!selectedBesoin) return;
            
            const quantite = parseFloat(this.value) || 0;
            const montantHT = quantite * selectedBesoin.prix;
            const frais = montantHT * 0.10;
            const total = montantHT + frais;

            document.getElementById('montantHT').textContent = montantHT.toFixed(2);
            document.getElementById('montantFrais').textContent = frais.toFixed(2);
            document.getElementById('montantTotal').textContent = total.toFixed(2);
        });

        // Soumettre la simulation
        document.getElementById('formAchat').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!selectedBesoin) {
                alert('Sélectionnez un besoin');
                return;
            }

            const quantite = parseFloat(document.getElementById('quantite_achetee').value);
            if (!quantite || quantite <= 0) {
                alert('Quantité invalide');
                return;
            }

            try {
                const response = await fetch('/achats/simulate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id_besoin_ville=${selectedBesoin.id}&quantite=${quantite}`
                });

                const data = await response.json();

                if (data.error) {
                    alert('Erreur : ' + data.error);
                } else {
                    alert('Simulation créée ! ID : ' + data.id_achat);
                    window.location.href = '/simulation';
                }
            } catch (error) {
                alert('Erreur : ' + error.message);
            }
        });
    </script>
</body>
</html>
