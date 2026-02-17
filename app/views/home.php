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
                <?php
                    $regions = [];
                    if (!empty($villes)) {
                        foreach ($villes as $v) {
                            $rid = (string) ($v['id_region'] ?? '');
                            if ($rid !== '') {
                                $regions[$rid] = $rid;
                            }
                        }
                        ksort($regions);
                    }
                ?>
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                    <h2 class="h5 mb-0">Liste des villes</h2>
                    <span class="badge bg-primary-subtle text-primary">Total: <?php echo htmlspecialchars((string) count($villes ?? [])); ?></span>
                </div>
                <?php if (!empty($villes)): ?>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="villeSearch">Recherche par nom</label>
                            <input
                                type="text"
                                class="form-control"
                                id="villeSearch"
                                placeholder="Ex: Antananarivo"
                                list="villeSuggestions"
                            >
                            <datalist id="villeSuggestions">
                                <?php foreach ($villes as $v): ?>
                                    <option value="<?php echo htmlspecialchars((string) $v['nom_ville']); ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="regionFilter">Filtrer par region</label>
                            <select class="form-select" id="regionFilter">
                                <option value="">Toutes les regions</option>
                                <?php foreach ($regions as $rid): ?>
                                    <option value="<?php echo htmlspecialchars((string) $rid); ?>">Region <?php echo htmlspecialchars((string) $rid); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (empty($villes)): ?>
                    <div class="alert alert-light border mb-0">Aucune ville disponible.</div>
                <?php else: ?>
                    <div class="table-responsive table-scroll">
                        <table class="table table-striped align-middle mb-0" id="villesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th class="text-end">Besoins</th>
                                    <th class="text-end">Gérer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($villes as $v): ?>
                                    <?php
                                        $villeId = (string) ($v['id_ville'] ?? '');
                                        $besoinCount = (int) ($besoinCounts[$villeId] ?? 0);
                                        $besoinClass = $besoinCount > 0 ? 'btn-danger' : 'btn-success';
                                    ?>
                                    <tr data-nom="<?php echo htmlspecialchars((string) $v['nom_ville']); ?>" data-region="<?php echo htmlspecialchars((string) ($v['id_region'] ?? '')); ?>">
                                        <td><?php echo htmlspecialchars((string) $v['nom_ville']); ?></td>
                                        <td class="text-end">
                                            <?php if ($besoinCount > 0): ?>
                                                <a class="btn btn-sm <?php echo htmlspecialchars($besoinClass); ?>" href="<?php echo BASE_URL ?>/besoins/ville?ville=<?php echo htmlspecialchars((string) $v['id_ville']); ?>">Besoins</a>
                                            <?php else: ?>
                                                <span class="text-muted">Aucun besoin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-primary" href="<?php echo BASE_URL ?>/villes/id?id=<?php echo htmlspecialchars((string) $v['id_ville']); ?>">Gérer</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>
            </div>
        </section>
    </div>

    <script src="<?php echo BASE_URL ?>/public/assets/js/home.js"></script>
</body>
</html>
