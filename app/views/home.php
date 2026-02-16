<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BNGRC - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>BNGRC Dashboard</h1>

    <?php if (!empty($ville)): ?>
        <section>
            <h2>Ville selectionnee</h2>
            <p>ID: <?php echo htmlspecialchars((string) $ville['id_ville']); ?></p>
            <p>Nom: <?php echo htmlspecialchars((string) $ville['nom_ville']); ?></p>
            <p>Region: <?php echo htmlspecialchars((string) ($ville['id_region'] ?? '')); ?></p>
        </section>
    <?php endif; ?>

    <section>
        <h2>Liste des villes</h2>
        <?php if (empty($villes)): ?>
            <p>Aucune ville disponible.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($villes as $v): ?>
                    <li>
                        <?php echo htmlspecialchars((string) $v['nom_ville']); ?>
                        (ID: <?php echo htmlspecialchars((string) $v['id_ville']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section>
        <h2>Navigation</h2>
        <ul>
            <li><a href="/besoins">Besoins</a></li>
            <li><a href="/dons">Dons</a></li>
        </ul>
    </section>
</body>
</html>
