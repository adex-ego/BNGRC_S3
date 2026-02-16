<?php
/**
 * Home page: recherche par ville + filtre par région + liste des villes.
 * Données attendues depuis la base :
 * - $regions (array)
 * - $villes (array)
 */

$regions = $regions ?? [];
$villes = $villes ?? [];
?>
<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>BNGRC - Accueil</title>
	<link rel="stylesheet" href="/assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/css/styles.css">
	<style>
		:root {
			--brand: #0f2c59;
			--brand-2: #00b894;
			--accent: #ff7675;
			--soft: #f5f7fb;
		}
		body {
			background: var(--soft);
		}
		.hero-card {
			background: linear-gradient(135deg, var(--brand), #16386d);
			color: #fff;
			border-radius: 1rem;
		}
		.search-box, .filter-box {
			background: #fff;
			border-radius: 1rem;
			border: 1px solid #e9edf5;
			box-shadow: 0 10px 25px rgba(15, 44, 89, 0.08);
		}
		.city-card {
			border: 0;
			border-radius: 1rem;
			box-shadow: 0 8px 20px rgba(0,0,0,.06);
		}
		.badge-besoins {
			background: var(--brand-2);
		}
		.badge-besoins.off {
			background: var(--accent);
		}
		.btn-gestion {
			background: var(--brand);
			color: #fff;
		}
		.btn-gestion:hover {
			background: #0b2346;
			color: #fff;
		}
	</style>
</head>
<body>
	<header class="container py-4">
		<div class="hero-card p-4 p-md-5">
			<div class="row align-items-center g-4">
				<div class="col-lg-7">
					<h1 class="fw-bold mb-2">BNGRC - Tableau des villes</h1>
					<p class="mb-0 text-white-50">
						Recherchez une ville, filtrez par région et gérez les besoins.
					</p>
				</div>
				<div class="col-lg-5 text-lg-end">
					<span class="badge rounded-pill bg-light text-dark px-3 py-2">Mise à jour: <?php echo date('d/m/Y'); ?></span>
				</div>
			</div>
		</div>
	</header>

	<main class="container pb-5">
		<div class="row g-4">
			<div class="col-md-6">
				<div class="search-box p-4 h-100">
					<h5 class="fw-semibold mb-3">Recherche par ville</h5>
					<form method="get" action="">
						<div class="input-group">
							<input
								type="text"
								name="nom"
								class="form-control"
								placeholder="Ex: Douala"
								list="villes-suggestions"
								autocomplete="off"
								value="<?php echo htmlspecialchars($_GET['nom'] ?? '', ENT_QUOTES); ?>"
							>
							<button class="btn btn-primary" type="submit">Rechercher</button>
						</div>
						<datalist id="villes-suggestions">
							<?php foreach ($villes as $ville): ?>
								<?php $villeNom = $ville['nom_ville'] ?? $ville['nom'] ?? ''; ?>
								<?php if ($villeNom !== ''): ?>
									<option value="<?php echo htmlspecialchars($villeNom, ENT_QUOTES); ?>"></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</datalist>
						<small class="text-muted d-block mt-2">Tapez le nom d'une ville pour filtrer rapidement.</small>
					</form>
				</div>
			</div>
			<div class="col-md-6">
				<div class="filter-box p-4 h-100">
					<h5 class="fw-semibold mb-3">Filtre par région</h5>
					<form method="get" action="">
						<div class="row g-2">
							<div class="col-8">
								<select class="form-select" name="region">
									<option value="">Toutes les régions</option>
									<?php foreach ($regions as $region): ?>
										<?php
											$regionId = $region['id_region'] ?? $region['id'] ?? null;
											$regionNom = $region['nom_region'] ?? $region['nom'] ?? '';
										?>
										<option value="<?php echo (int) $regionId; ?>"
											<?php echo (string)($_GET['region'] ?? '') === (string)$regionId ? 'selected' : ''; ?>
										>
											<?php echo htmlspecialchars($regionNom, ENT_QUOTES); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-4 d-grid">
								<button class="btn btn-outline-primary" type="submit">Filtrer</button>
							</div>
						</div>
						<small class="text-muted d-block mt-2">Choisissez une région pour affiner la liste.</small>
					</form>
				</div>
			</div>
		</div>

		<section class="mt-5">
			<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
				<h4 class="mb-0">Liste des villes</h4>
				<span class="text-muted"><?php echo count($villes); ?> résultat(s)</span>
			</div>

			<div class="row g-4">
				<?php if (empty($villes)): ?>
					<div class="col-12">
						<div class="alert alert-info mb-0">Aucune ville trouvée.</div>
					</div>
				<?php else: ?>
					<?php foreach ($villes as $ville): ?>
						<?php
							$villeNom = $ville['nom_ville'] ?? $ville['nom'] ?? '';
							$hasBesoins = (bool) ($ville['besoins'] ?? $ville['has_besoins'] ?? false);
						?>
						<div class="col-12 col-md-6 col-lg-4">
							<div class="card city-card h-100">
								<div class="card-body d-flex flex-column">
									<div class="d-flex justify-content-between align-items-center mb-3">
										<h5 class="mb-0"><?php echo htmlspecialchars($villeNom, ENT_QUOTES); ?></h5>
										<?php if ($hasBesoins): ?>
											<span class="badge badge-besoins">Besoins</span>
										<?php else: ?>
											<span class="badge badge-besoins off">Besoins</span>
										<?php endif; ?>
									</div>
									<p class="text-muted small mb-4">Suivi rapide des besoins prioritaires de la ville.</p>
									<div class="mt-auto d-grid">
										<a href="#" class="btn btn-gestion">Gérer</a>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</section>
	</main>

	<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
