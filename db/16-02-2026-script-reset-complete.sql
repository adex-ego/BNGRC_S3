-- Script complet de réinitialisation avec données de test
-- Date: 2026-02-17

-- CREATE DATABASE IF NOT EXISTS db_s2_ETU003945;
USE db_s2_ETU003945;

-- ============================================
-- DROP TOUTES LES TABLES EXISTANTES
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
    nom_type VARCHAR(255) NOT NULL
);

CREATE TABLE region_bngrc(
    id_region INT PRIMARY KEY AUTO_INCREMENT,
    nom_region VARCHAR(255) NOT NULL
);

CREATE TABLE ville_bngrc(
    id_ville INT PRIMARY KEY AUTO_INCREMENT,
    id_region INT,
    nom_ville VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_region) REFERENCES region_bngrc(id_region)
);

CREATE TABLE besoin_bngrc(
    id_besoin INT PRIMARY KEY AUTO_INCREMENT,
    id_type INT,
    nom_besoin VARCHAR(255) NOT NULL,
    prix_besoin DECIMAL(10, 2) NULL,
    FOREIGN KEY (id_type) REFERENCES besoin_type_bngrc(id_type)
);

CREATE TABLE besoin_ville_bngrc(
    id_besoin INT PRIMARY KEY AUTO_INCREMENT,
    id_besoin_item INT,
    quantite_besoin BIGINT UNSIGNED NOT NULL,
    id_ville INT,
    date_demande DATE NOT NULL,
    FOREIGN KEY (id_besoin_item) REFERENCES besoin_bngrc(id_besoin),
    FOREIGN KEY (id_ville) REFERENCES ville_bngrc(id_ville)
);

CREATE TABLE dons_bngrc(
    id_don INT PRIMARY KEY AUTO_INCREMENT,
    id_besoin_item INT,
    quantite_don BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (id_besoin_item) REFERENCES besoin_bngrc(id_besoin)
);

CREATE TABLE user_takalo(
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
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
-- INSERTION DES DONNÉES DE TEST
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

-- Villes
INSERT INTO ville_bngrc (id_region, nom_ville) VALUES
(1, 'Antananarivo'),
(1, 'Ambohidratrimo'),
(2, 'Antsirabe'),
(2, 'Betafo'),
(3, 'Toamasina'),
(3, 'Brickaville'),
(4, 'Mahajanga'),
(4, 'Marovoay'),
(5, 'Antsiranana'),
(5, 'Nosy Be'),
(6, 'Toliara'),
(6, 'Morombe');

-- Besoins (articles à acheter)
INSERT INTO besoin_bngrc (id_type, nom_besoin, prix_besoin) VALUES
(1, 'Riz', 25000.00),
(1, 'Haricots', 17500.00),
(1, 'Sucre', 10000.00),
(2, 'Ciment', 75000.00),
(2, 'Tôles', 100000.00),
(2, 'Bois', 40000.00);

-- Besoins par ville (quantités demandées)
-- Test du dispatch proportionnel: 
-- Riz (id=1): Ville 1=100, Ville 2=50, Ville 3=25 (Total=175) avec 6 dons = répartition proportionnelle
INSERT INTO besoin_ville_bngrc (id_besoin_item, quantite_besoin, id_ville, date_demande) VALUES
-- Riz: besoins répartis dans 3 villes (total 175)
(1, 100, 1, '2026-02-15'),  -- Antananarivo
(1, 50, 3, '2026-02-14'),   -- Antsirabe
(1, 25, 5, '2026-02-16'),   -- Toamasina

-- Haricots: besoins répartis dans 2 villes (total 80)
(2, 50, 2, '2026-02-15'),   -- Ambohidratrimo
(2, 30, 4, '2026-02-14'),   -- Betafo

-- Sucre: besoins répartis dans 4 villes (total 120)
(3, 30, 1, '2026-02-15'),   -- Antananarivo
(3, 40, 7, '2026-02-14'),   -- Mahajanga
(3, 25, 9, '2026-02-16'),   -- Antsiranana
(3, 25, 11, '2026-02-16'),  -- Toliara

-- Ciment: besoins répartis dans 3 villes (total 60)
(4, 20, 3, '2026-02-13'),   -- Antsirabe
(4, 25, 5, '2026-02-14'),   -- Toamasina
(4, 15, 8, '2026-02-15'),   -- Marovoay

-- Tôles: besoins répartis dans 2 villes (total 150)
(5, 100, 5, '2026-02-14'),  -- Toamasina
(5, 50, 10, '2026-02-15'),  -- Nosy Be

-- Bois: besoins répartis dans 3 villes (total 130)
(6, 50, 11, '2026-02-15'),  -- Toliara
(6, 40, 6, '2026-02-14'),   -- Brickaville
(6, 40, 12, '2026-02-16');  -- Morombe

-- Dons en Argent pour Riz (quantités - total 6 dons)
INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
(1, 1),  -- Don de 1 unité
(1, 2),  -- Don de 2 unités
(1, 3);  -- Don de 3 unités

-- Dons en Haricots (quantités - total 10 dons)
INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
(2, 4),  -- Don de 4 unités
(2, 3),  -- Don de 3 unités
(2, 3);  -- Don de 3 unités

-- Dons en Sucre (quantités - total 15 dons)
INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
(3, 5),  -- Don de 5 unités
(3, 5),  -- Don de 5 unités
(3, 5);  -- Don de 5 unités

-- Dons en Ciment (quantités - total 25 dons)
INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
(4, 12), -- Don de 12 unités
(4, 13); -- Don de 13 unités

-- Dons en Tôles (quantités - total 75 dons)
INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
(5, 40), -- Don de 40 unités
(5, 35); -- Don de 35 unités

-- Dons en Bois (quantités - total 60 dons)
INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES
(6, 30), -- Don de 30 unités
(6, 20), -- Don de 20 unités
(6, 10); -- Don de 10 unités

-- Utilisateurs de test
INSERT INTO user_takalo (username, email, hashedpassword) VALUES
('admin', 'admin@bngrc.mg', '$2y$10$SXE3l019OwQB1OHqxfDUfuF8.C10GEPxPZiETKGRMdMTZ.afMgIyK'),
('user', 'user@bngrc.mg', '$2y$10$SXE3l019OwQB1OHqxfDUfuF8.C10GEPxPZiETKGRMdMTZ.afMgIyK');

-- Prix unitaires des types
INSERT INTO besoin_prix_bngrc (id_type, prix_unitaire) VALUES
(1, 25000.00),     -- En Nature: 25 000 MGA l'unité
(2, 75000.00);     -- Matériaux: 75 000 MGA l'unité

-- Configuration des frais d'achat
INSERT INTO config_bngrc (frais_achat_percent, nom_config) VALUES
(10.00, 'Frais d\'achat par défaut');
