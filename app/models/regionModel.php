<?php

namespace app\models;

use PDO;

class RegionModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllRegion()
    {
        $sql = "SELECT * FROM region_bngrc ORDER BY nom_region ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findRegionByName($nom_region)
    {
        $sql = "SELECT * FROM region_bngrc WHERE nom_region = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nom_region]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findRegionById($id_region)
    {
        $sql = "SELECT * FROM region_bngrc WHERE id_region = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_region]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function insertRegion($nom_region)
    {
        $sql = "INSERT INTO region_bngrc (nom_region) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nom_region]);
        return $this->db->lastInsertId();
    }
}