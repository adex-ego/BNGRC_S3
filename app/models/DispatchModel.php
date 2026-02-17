<?php

namespace app\models;

use PDO;

class DispatchModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function resetAll(): void
    {
        // Récupérer le dernier dispatch avec les données sauvegardées
        $latestDispatch = $this->getLatestDispatch();
        if ($latestDispatch) {
            $dispatchId = (int) $latestDispatch['id_dispatch'];
            
            // Restaurer les quantités de besoins à partir des données du dispatch
            $details = $this->getDispatchDetails($dispatchId);
            foreach ($details as $detail) {
                $besoinId = (int) $detail['id_besoin'];
                $quantiteBesoin = (int) $detail['quantite_besoin'];
                
                $stmt = $this->db->prepare('UPDATE besoin_ville_bngrc SET quantite_besoin = ? WHERE id_besoin = ?');
                $stmt->execute([ $quantiteBesoin, $besoinId ]);
            }
        }
        
        $this->db->exec('DELETE FROM dispatch_detail_bngrc');
        $this->db->exec('DELETE FROM dispatch_item_bngrc');
        $this->db->exec('DELETE FROM dispatch_bngrc');
    }

    public function createDispatch(string $mode): int
    {
        $stmt = $this->db->prepare('INSERT INTO dispatch_bngrc (mode) VALUES (?)');
        $stmt->execute([ $mode ]);
        return (int) $this->db->lastInsertId();
    }

    public function insertDispatchItem(int $dispatchId, int $itemId, int $totalDon): void
    {
        $stmt = $this->db->prepare('INSERT INTO dispatch_item_bngrc (id_dispatch, id_besoin_item, total_don) VALUES (?, ?, ?)');
        $stmt->execute([ $dispatchId, $itemId, $totalDon ]);
    }

    public function insertDispatchDetail(int $dispatchId, array $detail): void
    {
        $stmt = $this->db->prepare('INSERT INTO dispatch_detail_bngrc (id_dispatch, id_besoin, id_besoin_item, quantite_besoin, quantite_dispatched, reste_besoin, id_ville, date_demande) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $dispatchId,
            $detail['id_besoin'],
            $detail['id_besoin_item'],
            $detail['quantite_besoin'],
            $detail['quantite_dispatched'],
            $detail['reste_besoin'],
            $detail['id_ville'],
            $detail['date_demande']
        ]);
    }

    public function updateBesoinQuantitiesFromDispatch(int $dispatchId): void
    {
        $details = $this->getDispatchDetails($dispatchId);
        foreach ($details as $detail) {
            $besoinId = (int) $detail['id_besoin'];
            $resteQuantite = (int) $detail['reste_besoin'];
            
            $stmt = $this->db->prepare('UPDATE besoin_ville_bngrc SET quantite_besoin = ? WHERE id_besoin = ?');
            $stmt->execute([ $resteQuantite, $besoinId ]);
        }
    }

    public function getLatestDispatch(): ?array
    {
        $stmt = $this->db->query('SELECT id_dispatch, mode, created_at FROM dispatch_bngrc ORDER BY id_dispatch DESC LIMIT 1');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getDispatchItems(int $dispatchId): array
    {
        $sql = "SELECT di.id_besoin_item, di.total_don, b.nom_besoin, t.nom_type
            FROM dispatch_item_bngrc di
            JOIN besoin_bngrc b ON b.id_besoin = di.id_besoin_item
            JOIN besoin_type_bngrc t ON t.id_type = b.id_type
            WHERE di.id_dispatch = ?
            ORDER BY b.nom_besoin ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $dispatchId ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDispatchDetails(int $dispatchId): array
    {
        $sql = "SELECT dd.id_besoin, dd.id_besoin_item, dd.quantite_besoin, dd.quantite_dispatched, dd.reste_besoin, dd.id_ville, dd.date_demande,
                v.nom_ville
            FROM dispatch_detail_bngrc dd
            LEFT JOIN ville_bngrc v ON v.id_ville = dd.id_ville
            WHERE dd.id_dispatch = ?
            ORDER BY dd.id_besoin ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $dispatchId ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDispatchPayload(int $dispatchId): array
    {
        $items = $this->getDispatchItems($dispatchId);
        $details = $this->getDispatchDetails($dispatchId);

        $payload = [];
        foreach ($items as $item) {
            $itemId = (string) $item['id_besoin_item'];
            $payload[$itemId] = [
                'item_id' => $itemId,
                'item_nom' => (string) $item['nom_besoin'],
                'type_nom' => (string) $item['nom_type'],
                'total_don' => (int) $item['total_don'],
                'allocations' => []
            ];
        }

        foreach ($details as $detail) {
            $itemId = (string) $detail['id_besoin_item'];
            if (!isset($payload[$itemId])) {
                $payload[$itemId] = [
                    'item_id' => $itemId,
                    'item_nom' => '',
                    'type_nom' => '',
                    'total_don' => 0,
                    'allocations' => []
                ];
            }

            $payload[$itemId]['allocations'][] = [
                'id_besoin' => (int) $detail['id_besoin'],
                'nom_ville' => (string) ($detail['nom_ville'] ?? ''),
                'date_demande' => (string) ($detail['date_demande'] ?? ''),
                'quantite_besoin' => (int) $detail['quantite_besoin'],
                'quantite_dispatched' => (int) $detail['quantite_dispatched'],
                'reste_besoin' => (int) $detail['reste_besoin']
            ];
        }

        return $payload;
    }
}
