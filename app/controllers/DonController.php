<?php

namespace app\controllers;

use app\models\DonModel;
use Flight;
use PDO;

class DonController
{
    public function index(): void
    {
        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $donModel = new DonModel($db);

        $dons = $donModel->getDon();
        $items_dons = $donModel->getAllBesoins();
        $dispatchPayload = $this->buildDispatchPayload($db, $dons, $items_dons);
        $success = Flight::request()->query->success ?? null;
        $insert_id = Flight::request()->query->insert_id ?? null;

        Flight::render('dons', [
            'dons' => $dons,
            'items_dons' => $items_dons,
            'dispatchByItemDate' => $dispatchPayload['dispatchByItemDate'],
            'dispatchByItemQuantity' => $dispatchPayload['dispatchByItemQuantity'],
            'donTotalsByItem' => $dispatchPayload['donTotalsByItem'],
            'success' => $success,
            'insert_id' => $insert_id
        ]);
    }

    public function getById(): void
    {
        $id_don = Flight::request()->query->id ?? null;

        if (!$id_don) {
            Flight::redirect('/dons');
            return;
        }

        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $donModel = new DonModel($db);

        $don = $donModel->getbyid($id_don);
        $dons = $donModel->getDon();
        $items_dons = $donModel->getAllBesoins();
        $dispatchPayload = $this->buildDispatchPayload($db, $dons, $items_dons);

        Flight::render('dons', [
            'don' => $don,
            'dons' => $dons,
            'items_dons' => $items_dons,
            'dispatchByItemDate' => $dispatchPayload['dispatchByItemDate'],
            'dispatchByItemQuantity' => $dispatchPayload['dispatchByItemQuantity'],
            'donTotalsByItem' => $dispatchPayload['donTotalsByItem']
        ]);
    }

    public function create(): void
    {
        $id_besoin_item = Flight::request()->data->id_besoin_item ?? null;
        $quantite_don = Flight::request()->data->quantite_don ?? null;

        if (!$id_besoin_item || !$quantite_don) {
            Flight::redirect('/dons');
            return;
        }

        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $donModel = new DonModel($db);

        $insert_id = $donModel->insertDon($id_besoin_item, $quantite_don);
        $dons = $donModel->getDon();
        $items_dons = $donModel->getAllBesoins();
        $dispatchPayload = $this->buildDispatchPayload($db, $dons, $items_dons);

        Flight::redirect('/dons?success=1&insert_id=' . urlencode((string) $insert_id));
    }

    private function buildDispatchPayload(PDO $db, array $dons, array $items_dons): array
    {
        $besoinModel = new \app\models\BesoinModel($db);
        $besoins = $besoinModel->getBesoin();

        $donTotalsByItem = [];
        foreach ($dons as $d) {
            $itemId = (string) ($d['id_besoin_item'] ?? '');
            if ($itemId === '') {
                continue;
            }
            $donTotalsByItem[$itemId] = ($donTotalsByItem[$itemId] ?? 0) + (int) ($d['quantite_don'] ?? 0);
        }

        $dispatchByItemDate = $this->buildDispatchByItem($items_dons, $besoins, $donTotalsByItem, function ($a, $b) {
            $dateA = $a['date_demande'] ?? '';
            $dateB = $b['date_demande'] ?? '';
            if ($dateA === $dateB) {
                return (int) ($a['id_besoin'] ?? 0) <=> (int) ($b['id_besoin'] ?? 0);
            }
            return strcmp($dateA, $dateB);
        });

        $dispatchByItemQuantity = $this->buildDispatchByItem($items_dons, $besoins, $donTotalsByItem, function ($a, $b) {
            $qtyA = (int) ($a['quantite_besoin'] ?? 0);
            $qtyB = (int) ($b['quantite_besoin'] ?? 0);
            if ($qtyA === $qtyB) {
                return (int) ($a['id_besoin'] ?? 0) <=> (int) ($b['id_besoin'] ?? 0);
            }
            return $qtyA <=> $qtyB;
        });

        return [
            'dispatchByItemDate' => $dispatchByItemDate,
            'dispatchByItemQuantity' => $dispatchByItemQuantity,
            'donTotalsByItem' => $donTotalsByItem
        ];
    }

    private function buildDispatchByItem(array $items_dons, array $besoins, array $donTotalsByItem, callable $sorter): array
    {
        $sortedBesoins = $besoins;
        usort($sortedBesoins, $sorter);

        $dispatchByItem = [];
        foreach ($items_dons as $item) {
            $itemId = (string) ($item['id_besoin'] ?? '');
            $itemName = (string) ($item['nom_besoin'] ?? '');
            $typeName = (string) ($item['nom_type'] ?? '');
            $remaining = (int) ($donTotalsByItem[$itemId] ?? 0);
            $allocations = [];

            foreach ($sortedBesoins as $b) {
                if ((string) ($b['id_besoin_item'] ?? '') !== $itemId) {
                    continue;
                }
                $need = (int) ($b['quantite_besoin'] ?? 0);
                $allocated = min($remaining, $need);
                $remaining -= $allocated;

                $allocations[] = [
                    'id_besoin' => (int) ($b['id_besoin'] ?? 0),
                    'nom_ville' => (string) ($b['nom_ville'] ?? ''),
                    'date_demande' => (string) ($b['date_demande'] ?? ''),
                    'quantite_besoin' => $need,
                    'quantite_dispatched' => $allocated,
                    'reste_besoin' => max(0, $need - $allocated)
                ];
            }

            $dispatchByItem[$itemId] = [
                'item_id' => $itemId,
                'item_nom' => $itemName,
                'type_nom' => $typeName,
                'total_don' => (int) ($donTotalsByItem[$itemId] ?? 0),
                'allocations' => $allocations
            ];
        }

        return $dispatchByItem;
    }
}
