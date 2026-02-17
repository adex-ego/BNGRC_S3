# BNGRC_S3

Ce dépôt contient l'application BNGRC_S3 pour les exercices ETU003945.

## Objectif
Fournir les étapes rapides pour : création du dossier `ETU003945`, clonage du dépôt, et import de la base via `db/17-02-2026-script-reset-cyclone.sql`.

## Instructions rapides

- Créer le dossier de travail :

	`sudo mkdir -p /opt/lampp/htdocs/ETU003945`

- Cloner le dépôt (remplacez `https://github.com/adex-ego/BNGRC_S3.git` par l'URL du repo) :

	`git clone https://github.com/adex-ego/BNGRC_S3.git /opt/lampp/htdocs/ETU003945/BNGRC_S3`

- Se placer dans le répertoire :

	`cd /opt/lampp/htdocs/ETU003945/BNGRC_S3`

- Installer les dépendances PHP si nécessaire :

	`composer install`  (si `composer` est utilisé et si `vendor/` n'existe pas)

- Créer la base de données puis importer le script principal (exemple avec MySQL) :

	`mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS db_s2_ETU003945 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`
	`mysql -u root -p db_s2_ETU003945 < db/17-02-2026-script-reset-cyclone.sql`

	Remplacez `root` et `db_s2_ETU003945` par l'utilisateur et le nom de base souhaités.

- Lancer le serveur PHP intégré pour tests rapides dans /opt/lampp/htdocs/:

	`php -S localhost:8080 -t ./`

## Notes
- Les fichiers de configuration sont dans `app/config/` (`config.php`, `bootstrap.php`, ...).
- Les contrôleurs sont dans `app/controllers/` et les modèles dans `app/models/`.
- Pour toute importation ou restauration complète de la base, vérifiez d'abord le contenu des scripts dans `db/`.

Si vous voulez, je peux committer ces changements pour vous.
