<?php

namespace app\models;

use PDO;

class DonModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getDon(): array
    {
        $sql = "SELECT d.id_don, d.id_besoin_type, bt.nom_besoin, d.quantite_don
                FROM dons_bngrc d
                JOIN besoin_type_bngrc bt ON bt.id_besoin = d.id_besoin_type
                ORDER BY d.id_don DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getbyid($id_don)
    {
        $sql = "SELECT d.id_don, d.id_besoin_type, bt.nom_besoin, d.quantite_don
                FROM dons_bngrc d
                JOIN besoin_type_bngrc bt ON bt.id_besoin = d.id_besoin_type
                WHERE d.id_don = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $id_don ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function insertDon($id_besoin_type, $quantite_don)
    {
        $sql = "INSERT INTO dons_bngrc (id_besoin_type, quantite_don) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $id_besoin_type, $quantite_don ]);
        return $this->db->lastInsertId();
    }
}
