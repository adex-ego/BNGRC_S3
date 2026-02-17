USE db_s2_ETU003945;

CREATE TABLE dispatch_bngrc (
    id_dispatch INT PRIMARY KEY AUTO_INCREMENT,
    mode ENUM('date', 'quantity') NOT NULL,
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
