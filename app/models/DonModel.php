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
        $dispatchId = $this->getLatestDispatchId();
        if ($dispatchId === null) {
            $sql = "SELECT MIN(d.id_don) AS id_don, d.id_besoin_item, b.nom_besoin, t.nom_type, SUM(d.quantite_don) AS quantite_don
                FROM dons_bngrc d
                JOIN besoin_bngrc b ON b.id_besoin = d.id_besoin_item
                JOIN besoin_type_bngrc t ON t.id_type = b.id_type
                GROUP BY d.id_besoin_item, b.nom_besoin, t.nom_type
                ORDER BY b.nom_besoin ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = "SELECT MIN(d.id_don) AS id_don, d.id_besoin_item, b.nom_besoin, t.nom_type,
                GREATEST(SUM(d.quantite_don) - COALESCE(dd.total_dispatched, 0), 0) AS quantite_don
            FROM dons_bngrc d
            JOIN besoin_bngrc b ON b.id_besoin = d.id_besoin_item
            JOIN besoin_type_bngrc t ON t.id_type = b.id_type
            LEFT JOIN (
                SELECT id_besoin_item, SUM(quantite_dispatched) AS total_dispatched
                FROM dispatch_detail_bngrc
                WHERE id_dispatch = ?
                GROUP BY id_besoin_item
            ) dd ON dd.id_besoin_item = d.id_besoin_item
            GROUP BY d.id_besoin_item, b.nom_besoin, t.nom_type, dd.total_dispatched
            ORDER BY b.nom_besoin ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $dispatchId ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDonTotals(): array
    {
        $sql = "SELECT d.id_besoin_item, SUM(d.quantite_don) AS total_don
            FROM dons_bngrc d
            GROUP BY d.id_besoin_item";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getbyid($id_don)
    {
        $sql = "SELECT d.id_don, d.id_besoin_item, b.nom_besoin, t.nom_type, d.quantite_don
            FROM dons_bngrc d
            JOIN besoin_bngrc b ON b.id_besoin = d.id_besoin_item
            JOIN besoin_type_bngrc t ON t.id_type = b.id_type
            WHERE d.id_don = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $id_don ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function insertDon($id_besoin_item, $quantite_don)
    {
        $sql = "INSERT INTO dons_bngrc (id_besoin_item, quantite_don) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $id_besoin_item, $quantite_don ]);
        return $this->db->lastInsertId();
    }

    public function getAllTypeBesoins(): array
    {
        $sql = "SELECT id_type, nom_type FROM besoin_type_bngrc ORDER BY nom_type ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllBesoins(): array
    {
        $sql = "SELECT b.id_besoin, b.nom_besoin, b.id_type, t.nom_type
                FROM besoin_bngrc b
                JOIN besoin_type_bngrc t ON t.id_type = b.id_type
                ORDER BY b.nom_besoin ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getLatestDispatchId(): ?int
    {
        $stmt = $this->db->query('SELECT id_dispatch FROM dispatch_bngrc ORDER BY id_dispatch DESC LIMIT 1');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            return null;
        }
        return (int) $result['id_dispatch'];
    }
}
