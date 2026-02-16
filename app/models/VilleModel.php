<?php

namespace app\models;

use PDO;

class VilleModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllVille()
    {
        $sql = "SELECT * FROM ville_bngrc ORDER BY nom_ville ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findVilleByName($nom_ville)
    {
        $sql = "SELECT * FROM ville_bngrc WHERE nom_ville = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nom_ville]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findVilleById($id_ville)
    {
        $sql = "SELECT * FROM ville_bngrc WHERE id_ville = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_ville]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function insertVille($nom_ville, $id_region = null)
    {
        $sql = "INSERT INTO ville_bngrc (nom_ville, id_region) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nom_ville, $id_region]);
        return $this->db->lastInsertId();
    }
}

