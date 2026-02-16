CREATE DATABASE db_s2_ETU003894;

USE db_s2_ETU003894;

CREATE TABLE besoin_type_bngrc(
    id_besoin INT PRIMARY KEY AUTO_INCREMENT,
    nom_besoin VARCHAR(255) NOT NULL
);

CREATE TABLE besoin_bngrc(
    id_besoin INT PRIMARY KEY AUTO_INCREMENT,
    id_besoin_type INT,
    FOREIGN KEY (id_besoin_type) REFERENCES besoin_type_bngrc(id_besoin)
);

CREATE TABLE ville_bngrc(
    id_ville INT PRIMARY KEY AUTO_INCREMENT,
    id_besoin_type INT,
    nom_ville VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_besoin_type) REFERENCES besoin_type_bngrc(id_besoin)
);