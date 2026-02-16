<?php

namespace app\models;

use PDO;

class BesoinModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getBesoin(): array
    {
        $sql = "SELECT bv.id_besoin, bv.id_besoin_type, bt.nom_besoin, bv.quantite_besoin, bv.id_ville, v.nom_ville, v.id_region, r.nom_region, bv.date_demande
                FROM besoin_ville_bngrc bv
                JOIN besoin_type_bngrc bt ON bt.id_besoin = bv.id_besoin_type
                LEFT JOIN ville_bngrc v ON v.id_ville = bv.id_ville
                LEFT JOIN region_bngrc r ON r.id_region = v.id_region
                ORDER BY bv.date_demande DESC, bv.id_besoin DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findbyid($id_besoin)
    {
        $sql = "SELECT bv.id_besoin, bv.id_besoin_type, bt.nom_besoin, bv.quantite_besoin, bv.id_ville, v.nom_ville, v.id_region, r.nom_region, bv.date_demande
                FROM besoin_ville_bngrc bv
                JOIN besoin_type_bngrc bt ON bt.id_besoin = bv.id_besoin_type
                LEFT JOIN ville_bngrc v ON v.id_ville = bv.id_ville
                LEFT JOIN region_bngrc r ON r.id_region = v.id_region
                WHERE bv.id_besoin = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $id_besoin ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findbytype($id_besoin_type): array
    {
        $sql = "SELECT bv.id_besoin, bv.id_besoin_type, bt.nom_besoin, bv.quantite_besoin, bv.id_ville, v.nom_ville, v.id_region, r.nom_region, bv.date_demande
                FROM besoin_ville_bngrc bv
                JOIN besoin_type_bngrc bt ON bt.id_besoin = bv.id_besoin_type
                LEFT JOIN ville_bngrc v ON v.id_ville = bv.id_ville
                LEFT JOIN region_bngrc r ON r.id_region = v.id_region
                WHERE bv.id_besoin_type = ?
                ORDER BY bv.date_demande DESC, bv.id_besoin DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $id_besoin_type ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByVille($id_ville): array
    {
        $sql = "SELECT bv.id_besoin, bv.id_besoin_type, bt.nom_besoin, bv.quantite_besoin, bv.id_ville, v.nom_ville, v.id_region, r.nom_region, bv.date_demande
                FROM besoin_ville_bngrc bv
                JOIN besoin_type_bngrc bt ON bt.id_besoin = bv.id_besoin_type
                LEFT JOIN ville_bngrc v ON v.id_ville = bv.id_ville
                LEFT JOIN region_bngrc r ON r.id_region = v.id_region
                WHERE bv.id_ville = ?
                ORDER BY bv.date_demande DESC, bv.id_besoin DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $id_ville ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertBesoin($id_besoin_type, $quantite_besoin, $id_ville, $date_demande)
    {
        $sql = "INSERT INTO besoin_ville_bngrc (id_besoin_type, quantite_besoin, id_ville, date_demande) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $id_besoin_type, $quantite_besoin, $id_ville, $date_demande ]);
        return $this->db->lastInsertId();
    }

    public function getAllTypeBesoins(): array
    {
        $sql = "SELECT id_besoin, nom_besoin FROM besoin_type_bngrc ORDER BY nom_besoin ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllVilles(): array
    {
        $sql = "SELECT id_ville, nom_ville FROM ville_bngrc ORDER BY nom_ville ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
