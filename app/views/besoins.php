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
