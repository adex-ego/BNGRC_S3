CREATE DATABASE db_s2_ETU003894;

USE db_s2_ETU003894;

CREATE TABLE besoin_type_bngrc(
    id_besoin INT PRIMARY KEY AUTO_INCREMENT,
    nom_besoin VARCHAR(255) NOT NULL
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
    id_besoin_type INT,
    prix_besoin DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_besoin_type) REFERENCES besoin_type_bngrc(id_besoin)
);

CREATE TABLE besoin_ville_bngrc(
    id_besoin INT PRIMARY KEY AUTO_INCREMENT,
    id_besoin_type INT,
    quantite_besoin INT NOT NULL,
    id_ville INT,
    date_demande DATE NOT NULL,
    FOREIGN KEY (id_besoin_type) REFERENCES besoin_type_bngrc(id_besoin),
    FOREIGN KEY (id_ville) REFERENCES ville_bngrc(id_ville)
);

CREATE TABLE dons_bngrc(
    id_don INT PRIMARY KEY AUTO_INCREMENT,
    id_besoin_type INT,
    quantite_don INT NOT NULL,
    FOREIGN KEY (id_besoin_type) REFERENCES besoin_type_bngrc(id_besoin)
);