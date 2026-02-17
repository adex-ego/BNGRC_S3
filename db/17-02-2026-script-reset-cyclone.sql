    -- Script complet de réinitialisation avec données Cyclone S3
    -- Date: 2026-02-17
    -- Source: jeu_donnees_cyclone_S3.xlsx

    USE db_s2_ETU003945;

    -- ============================================
    -- SUPPRESSION DES TABLES EXISTANTES
    -- ============================================

    DROP TABLE IF EXISTS dispatch_detail_bngrc;
    DROP TABLE IF EXISTS dispatch_item_bngrc;
    DROP TABLE IF EXISTS dispatch_bngrc;
    DROP TABLE IF EXISTS dons_utilises_bngrc;
    DROP TABLE IF EXISTS achats_bngrc;
    DROP TABLE IF EXISTS config_bngrc;
    DROP TABLE IF EXISTS besoin_prix_bngrc;
    DROP TABLE IF EXISTS dons_bngrc;
    DROP TABLE IF EXISTS besoin_ville_bngrc;
    DROP TABLE IF EXISTS besoin_bngrc;
    DROP TABLE IF EXISTS ville_bngrc;
    DROP TABLE IF EXISTS region_bngrc;
    DROP TABLE IF EXISTS besoin_type_bngrc;
    DROP TABLE IF EXISTS user_takalo;

    -- ============================================
    -- CRÉATION DES TABLES
    -- ============================================

    CREATE TABLE besoin_type_bngrc(
        id_type INT PRIMARY KEY AUTO_INCREMENT,
        nom_type VARCHAR(100) NOT NULL UNIQUE
    );

    CREATE TABLE region_bngrc(
        id_region INT PRIMARY KEY AUTO_INCREMENT,
        nom_region VARCHAR(100) NOT NULL UNIQUE
    );

    CREATE TABLE ville_bngrc(
        id_ville INT PRIMARY KEY AUTO_INCREMENT,
        id_region INT NOT NULL,
        nom_ville VARCHAR(100) NOT NULL,
        FOREIGN KEY (id_region) REFERENCES region_bngrc(id_region)
    );

    CREATE TABLE besoin_bngrc(
        id_besoin INT PRIMARY KEY AUTO_INCREMENT,
        id_type INT NOT NULL,
        nom_besoin VARCHAR(100) NOT NULL,
        prix_besoin DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (id_type) REFERENCES besoin_type_bngrc(id_type)
    );

    CREATE TABLE besoin_ville_bngrc(
        id_besoin INT PRIMARY KEY AUTO_INCREMENT,
        id_besoin_item INT NOT NULL,
        quantite_besoin BIGINT UNSIGNED NOT NULL,
        id_ville INT NOT NULL,
        date_demande DATE NOT NULL,
        FOREIGN KEY (id_besoin_item) REFERENCES besoin_bngrc(id_besoin),
        FOREIGN KEY (id_ville) REFERENCES ville_bngrc(id_ville)
    );

    CREATE TABLE dons_bngrc(
        id_don INT PRIMARY KEY AUTO_INCREMENT,
        id_besoin_item INT NOT NULL,
        quantite_don BIGINT UNSIGNED NOT NULL,
        FOREIGN KEY (id_besoin_item) REFERENCES besoin_bngrc(id_besoin)
    );

    CREATE TABLE user_takalo(
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        hashedpassword VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- ============================================
    -- TABLES POUR LE SYSTÈME D'ACHATS
    -- ============================================

    CREATE TABLE besoin_prix_bngrc(
        id_prix INT PRIMARY KEY AUTO_INCREMENT,
        id_type INT NOT NULL,
        prix_unitaire DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (id_type) REFERENCES besoin_type_bngrc(id_type)
    );

    CREATE TABLE config_bngrc(
        id_config INT PRIMARY KEY AUTO_INCREMENT,
        frais_achat_percent DECIMAL(5, 2) DEFAULT 10.00,
        nom_config VARCHAR(255)
    );

    CREATE TABLE achats_bngrc(
        id_achat INT PRIMARY KEY AUTO_INCREMENT,
        id_besoin_ville INT NOT NULL,
        quantite_achetee INT NOT NULL,
        prix_unitaire DECIMAL(10, 2) NOT NULL,
        frais_achat_percent DECIMAL(5, 2) NOT NULL,
        montant_total DECIMAL(10, 2) NOT NULL,
        date_achat DATE NOT NULL,
        statut VARCHAR(50) DEFAULT 'simule',
        FOREIGN KEY (id_besoin_ville) REFERENCES besoin_ville_bngrc(id_besoin)
    );

    CREATE TABLE dons_utilises_bngrc(
        id_don_utilise INT PRIMARY KEY AUTO_INCREMENT,
        id_achat INT NOT NULL,
        id_don INT NOT NULL,
        montant_utilise DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (id_achat) REFERENCES achats_bngrc(id_achat),
        FOREIGN KEY (id_don) REFERENCES dons_bngrc(id_don)
    );

    -- ============================================
    -- TABLES POUR LE DISPATCH
    -- ============================================

    CREATE TABLE dispatch_bngrc (
        id_dispatch INT PRIMARY KEY AUTO_INCREMENT,
        mode ENUM('date', 'quantity', 'proportion') NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE dispatch_item_bngrc (
        id_dispatch_item INT PRIMARY KEY AUTO_INCREMENT,
        id_dispatch INT NOT NULL,
        id_besoin_item INT NOT NULL,
        total_don BIGINT UNSIGNED NOT NULL,
        FOREIGN KEY (id_dispatch) REFERENCES dispatch_bngrc(id_dispatch),
        FOREIGN KEY (id_besoin_item) REFERENCES besoin_bngrc(id_besoin)
    );

    CREATE TABLE dispatch_detail_bngrc (
        id_dispatch_detail INT PRIMARY KEY AUTO_INCREMENT,
        id_dispatch INT NOT NULL,
        id_besoin INT NOT NULL,
        id_besoin_item INT NOT NULL,
        quantite_besoin BIGINT UNSIGNED NOT NULL,
        quantite_dispatched BIGINT UNSIGNED NOT NULL,
        reste_besoin BIGINT UNSIGNED NOT NULL,
        id_ville INT NULL,
        date_demande DATE NOT NULL,
        FOREIGN KEY (id_dispatch) REFERENCES dispatch_bngrc(id_dispatch),
        FOREIGN KEY (id_besoin) REFERENCES besoin_ville_bngrc(id_besoin),
        FOREIGN KEY (id_besoin_item) REFERENCES besoin_bngrc(id_besoin),
        FOREIGN KEY (id_ville) REFERENCES ville_bngrc(id_ville)
    );

    -- ============================================
    -- INSERTION DES DONNÉES - CYCLONE S3
    -- ============================================

    -- Types de besoins/dons
    INSERT INTO besoin_type_bngrc (nom_type) VALUES
    ('En Nature'),
    ('Matériaux'),
    ('Argent');

    -- Régions
    INSERT INTO region_bngrc (nom_region) VALUES
    ('Analamanga'),
    ('Vakinankaratra'),
    ('Atsinanana'),
    ('Boeny'),
    ('Diana'),
    ('Atsimo-Andrefana');

    -- Villes (données cyclone)
    INSERT INTO ville_bngrc (id_region, nom_ville) VALUES
    (3, 'Toamasina'),
    (3, 'Mananjary'),
    (3, 'Farafangana'),
    (5, 'Nosy Be'),
    (6, 'Morondava');

    -- Besoins (articles à acheter)
    INSERT INTO besoin_bngrc (id_type, nom_besoin, prix_besoin) VALUES
    -- En Nature
    (1, 'Riz (kg)', 25000.00),
    (1, 'Eau (L)', 1000.00),
    (1, 'Huile (L)', 6000.00),
    (1, 'Haricots', 4000.00),
    -- Matériaux
    (2, 'Tôle', 25000.00),
    (2, 'Bâche', 15000.00),
    (2, 'Clous (kg)', 8000.00),
    (2, 'Bois', 10000.00),
    -- Argent
    (3, 'Argent', 1.00);

    -- ============================================
    -- BESOINS PAR VILLE (Données Cyclone S3)
    -- ============================================

    -- Toamasina
    INSERT INTO besoin_ville_bngrc (id_besoin_item, quantite_besoin, id_ville, date_demande) VALUES
    (1, 800, 1, '2026-02-16'),    -- Riz (kg)
    (2, 1500, 1, '2026-02-15'),   -- Eau (L)
    (5, 120, 1, '2026-02-16'),    -- Tôle
    (6, 200, 1, '2026-02-15'),    -- Bâche
    (9, 12000000, 1, '2026-02-16'); -- Argent

    -- Mananjary
    INSERT INTO besoin_ville_bngrc (id_besoin_item, quantite_besoin, id_ville, date_demande) VALUES
    (1, 500, 2, '2026-02-15'),    -- Riz (kg)
    (3, 120, 2, '2026-02-16'),    -- Huile (L)
    (5, 80, 2, '2026-02-15'),     -- Tôle
    (7, 60, 2, '2026-02-16'),     -- Clous (kg)
    (9, 6000000, 2, '2026-02-15'); -- Argent

    -- Farafangana
    INSERT INTO besoin_ville_bngrc (id_besoin_item, quantite_besoin, id_ville, date_demande) VALUES
    (1, 600, 3, '2026-02-16'),    -- Riz (kg)
    (2, 1000, 3, '2026-02-15'),   -- Eau (L)
    (6, 150, 3, '2026-02-16'),    -- Bâche
    (8, 100, 3, '2026-02-15'),    -- Bois
    (9, 8000000, 3, '2026-02-16'); -- Argent

    -- Nosy Be
    INSERT INTO besoin_ville_bngrc (id_besoin_item, quantite_besoin, id_ville, date_demande) VALUES
    (1, 300, 4, '2026-02-15'),    -- Riz (kg)
    (4, 200, 4, '2026-02-16'),    -- Haricots
    (5, 40, 4, '2026-02-15'),     -- Tôle
    (7, 30, 4, '2026-02-16'),     -- Clous (kg)
    (9, 4000000, 4, '2026-02-15'); -- Argent

    -- Morondava
    INSERT INTO besoin_ville_bngrc (id_besoin_item, quantite_besoin, id_ville, date_demande) VALUES
    (1, 700, 5, '2026-02-16'),    -- Riz (kg)
    (2, 1200, 5, '2026-02-15'),   -- Eau (L)
    (6, 180, 5, '2026-02-16'),    -- Bâche
    (8, 150, 5, '2026-02-15'),    -- Bois
    (9, 10000000, 5, '2026-02-16'); -- Argent

    -- ============================================
    -- DONS (variés pour chaque besoin)
    -- ============================================

    -- Riz (id_besoin=1)
    INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
    (1, 500),
    (1, 300),
    (1, 200),
    (1, 400),
    (1, 150);

    -- Eau (id_besoin=2)
    INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
    (2, 1000),
    (2, 800),
    (2, 500),
    (2, 700);

    -- Huile (id_besoin=3)
    INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
    (3, 100),
    (3, 50),
    (3, 80);

    -- Haricots (id_besoin=4)
    INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
    (4, 150),
    (4, 100),
    (4, 80);

    -- Tôle (id_besoin=5)
    INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
    (5, 100),
    (5, 80),
    (5, 50),
    (5, 40);

    -- Bâche (id_besoin=6)
    INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
    (6, 200),
    (6, 150),
    (6, 120);

    -- Clous (id_besoin=7)
    INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
    (7, 60),
    (7, 50),
    (7, 40);

    -- Bois (id_besoin=8)
    INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
    (8, 150),
    (8, 100),
    (8, 80),
    (8, 60);

    -- Argent (id_besoin=9)
    INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
    (9, 5000000),
    (9, 3000000),
    (9, 2000000),
    (9, 4000000),
    (9, 6000000);

    -- ============================================
    -- UTILISATEURS DE TEST
    -- ============================================

    INSERT INTO user_takalo (username, email, hashedpassword) VALUES
    ('admin', 'admin@bngrc.mg', '$2y$10$SXE3l019OwQB1OHqxfDUfuF8.C10GEPxPZiETKGRMdMTZ.afMgIyK'),
    ('user', 'user@bngrc.mg', '$2y$10$SXE3l019OwQB1OHqxfDUfuF8.C10GEPxPZiETKGRMdMTZ.afMgIyK');

    -- ============================================
    -- CONFIGURATION SYSTÈME
    -- ============================================

    INSERT INTO besoin_prix_bngrc (id_type, prix_unitaire) VALUES
    (1, 25000.00),     -- En Nature: 25 000 MGA l'unité
    (2, 75000.00);     -- Matériaux: 75 000 MGA l'unité

    INSERT INTO config_bngrc (frais_achat_percent, nom_config) VALUES
    (10.00, 'Frais d\'achat par défaut');
