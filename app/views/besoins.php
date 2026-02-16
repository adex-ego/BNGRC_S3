<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BNGRC - Besoins</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>Liste des besoins</h1>

    <?php if (!empty($success)): ?>
        <p>Insertion reussie. ID: <?php echo htmlspecialchars((string) ($insert_id ?? '')); ?></p>
    <?php endif; ?>

    <h2>Ajouter un Besoin</h2>
    <form method="POST" action="/besoins/create" style="margin-bottom: 30px;">
        <div>
            <label for="id_besoin_type">Type de Besoin:</label>
            <select id="id_besoin_type" name="id_besoin_type" required>
                <option value="">-- Sélectionnez un type --</option>
                <?php if (isset($types_besoins) && !empty($types_besoins)): ?>
                    <?php foreach ($types_besoins as $type): ?>
                        <option value="<?php echo htmlspecialchars((string) $type['id_besoin']); ?>">
                            <?php echo htmlspecialchars((string) $type['nom_besoin']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div>
            <label for="id_ville">Ville:</label>
            <select id="id_ville" name="id_ville">
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
        <div>
            <label for="quantite_besoin">Quantité:</label>
            <input type="number" id="quantite_besoin" name="quantite_besoin" min="1" placeholder="Entrez la quantité" required>
        </div>
        <div>
            <label for="date_demande">Date de Demande:</label>
            <input type="date" id="date_demande" name="date_demande" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <button type="submit">Ajouter le Besoin</button>
    </form>

    <?php if (!empty($besoin)): ?>
        <section>
            <h2>Besoin selectionne</h2>
            <p>ID: <?php echo htmlspecialchars((string) $besoin['id_besoin']); ?></p>
            <p>Type: <?php echo htmlspecialchars((string) ($besoin['nom_besoin'] ?? '')); ?></p>
            <p>Quantite: <?php echo htmlspecialchars((string) $besoin['quantite_besoin']); ?></p>
            <p>Ville: <?php echo htmlspecialchars((string) ($besoin['nom_ville'] ?? '')); ?></p>
            <p>Date: <?php echo htmlspecialchars((string) $besoin['date_demande']); ?></p>
        </section>
    <?php endif; ?>

    <?php if (empty($besoins)): ?>
        <p>Aucun besoin.</p>
    <?php else: ?>
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Quantite</th>
                    <th>Ville</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($besoins as $b): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) $b['id_besoin']); ?></td>
                        <td><?php echo htmlspecialchars((string) ($b['nom_besoin'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars((string) $b['quantite_besoin']); ?></td>
                        <td><?php echo htmlspecialchars((string) ($b['nom_ville'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars((string) $b['date_demande']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
