USE db_s2_ETU003945;

-- Donnees de reference uniquement (pas de besoins/dons de test)

INSERT INTO region_bngrc (nom_region) VALUES
('Analamanga'),
('Vakinankaratra'),
('Atsinanana'),
('Boeny'),
('Diana'),
('Atsimo-Andrefana');

INSERT INTO besoin_type_bngrc (nom_type) VALUES
('Nature'),
('Materiaux'),
('Argent'),
('Nourriture'),
('Sante');

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

INSERT INTO besoin_bngrc (id_type, nom_besoin, prix_besoin) VALUES
(1, 'Eau potable', NULL),
(1, 'Bois de chauffage', NULL),
(2, 'Tentes', NULL),
(2, 'Couvertures', NULL),
(2, 'Vêtements', NULL),
(3, 'Aide financière', NULL),
(4, 'Riz', NULL),
(4, 'Huile', NULL),
(4, 'Sucre', NULL),
(4, 'Farine', NULL),
(5, 'Médicaments', NULL),
(5, 'Lait', NULL);
