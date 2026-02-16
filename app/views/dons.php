<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BNGRC - Dons</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>Liste des dons</h1>

    <?php if (!empty($success)): ?>
        <p>Insertion reussie. ID: <?php echo htmlspecialchars((string) ($insert_id ?? '')); ?></p>
    <?php endif; ?>

    <h2>Ajouter un Don</h2>
    <form method="POST" action="/dons/create" style="margin-bottom: 30px;">
        <div>
            <label for="id_besoin_type">Type de Don:</label>
            <select id="id_besoin_type" name="id_besoin_type" required>
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
        <div>
            <label for="quantite_don">Quantité:</label>
            <input type="number" id="quantite_don" name="quantite_don" min="1" placeholder="Entrez la quantité" required>
        </div>
        <button type="submit">Ajouter le Don</button>
    </form>

    <?php if (!empty($don)): ?>
        <section>
            <h2>Don selectionne</h2>
            <p>ID: <?php echo htmlspecialchars((string) $don['id_don']); ?></p>
            <p>Type: <?php echo htmlspecialchars((string) ($don['nom_besoin'] ?? '')); ?></p>
            <p>Quantite: <?php echo htmlspecialchars((string) $don['quantite_don']); ?></p>
        </section>
    <?php endif; ?>

    <?php if (empty($dons)): ?>
        <p>Aucun don.</p>
    <?php else: ?>
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Quantite</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dons as $d): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) $d['id_don']); ?></td>
                        <td><?php echo htmlspecialchars((string) ($d['nom_besoin'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars((string) $d['quantite_don']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
