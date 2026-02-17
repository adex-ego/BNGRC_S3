<?php

namespace app\controllers;

use app\models\DonModel;
use app\models\DispatchModel;
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
        $dispatchModel = new DispatchModel($db);

        $dons = $donModel->getDon();
        $items_dons = $donModel->getAllBesoins();
        $latestDispatch = $dispatchModel->getLatestDispatch();
        $dispatchByItem = $latestDispatch ? $dispatchModel->getDispatchPayload((int) $latestDispatch['id_dispatch']) : [];
        $success = Flight::request()->query->success ?? null;
        $insert_id = Flight::request()->query->insert_id ?? null;
        $dispatchMode = $latestDispatch['mode'] ?? null;
        $dispatchTriggered = Flight::request()->query->dispatch ?? null;
        $dispatchRequestedMode = Flight::request()->query->mode ?? null;
        $resetSuccess = Flight::request()->query->reset ?? null;
        $dispatchError = Flight::request()->query->dispatch_error ?? null;
        $resetError = Flight::request()->query->reset_error ?? null;

        Flight::render('dons', [
            'dons' => $dons,
            'items_dons' => $items_dons,
            'dispatchByItem' => $dispatchByItem,
            'dispatchMode' => $dispatchMode,
            'dispatchTriggered' => $dispatchTriggered,
            'dispatchRequestedMode' => $dispatchRequestedMode,
            'resetSuccess' => $resetSuccess,
            'dispatchError' => $dispatchError,
            'resetError' => $resetError,
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
        $dispatchModel = new DispatchModel($db);

        $don = $donModel->getbyid($id_don);
        $dons = $donModel->getDon();
        $items_dons = $donModel->getAllBesoins();
        $latestDispatch = $dispatchModel->getLatestDispatch();
        $dispatchByItem = $latestDispatch ? $dispatchModel->getDispatchPayload((int) $latestDispatch['id_dispatch']) : [];

        Flight::render('dons', [
            'don' => $don,
            'dons' => $dons,
            'items_dons' => $items_dons,
            'dispatchByItem' => $dispatchByItem,
            'dispatchMode' => $latestDispatch['mode'] ?? null,
            'dispatchTriggered' => null,
            'resetSuccess' => null
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

        Flight::redirect('/dons?success=1&insert_id=' . urlencode((string) $insert_id));
    }

    public function dispatch(): void
    {
        $mode = (string) (Flight::request()->data->mode ?? '');
        if (!in_array($mode, [ 'date', 'quantity' ], true)) {
            Flight::redirect('/dons?dispatch_error=1');
            return;
        }

        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }

        $donModel = new DonModel($db);
        $dispatchModel = new DispatchModel($db);
        $besoinModel = new \app\models\BesoinModel($db);

        $items_dons = $donModel->getAllBesoins();
        $besoins = $besoinModel->getBesoin();
        $donTotalsRaw = $donModel->getDonTotals();

        $donTotalsByItem = [];
        foreach ($donTotalsRaw as $row) {
            $itemId = (string) ($row['id_besoin_item'] ?? '');
            if ($itemId === '') {
                continue;
            }
            $donTotalsByItem[$itemId] = (int) ($row['total_don'] ?? 0);
        }

        $sorter = $mode === 'date'
            ? function ($a, $b) {
                $dateA = $a['date_demande'] ?? '';
                $dateB = $b['date_demande'] ?? '';
                if ($dateA === $dateB) {
                    return (int) ($a['id_besoin'] ?? 0) <=> (int) ($b['id_besoin'] ?? 0);
                }
                return strcmp($dateA, $dateB);
            }
            : function ($a, $b) {
                $qtyA = (int) ($a['quantite_besoin'] ?? 0);
                $qtyB = (int) ($b['quantite_besoin'] ?? 0);
                if ($qtyA === $qtyB) {
                    return (int) ($a['id_besoin'] ?? 0) <=> (int) ($b['id_besoin'] ?? 0);
                }
                return $qtyA <=> $qtyB;
            };

        $dispatchByItem = $this->buildDispatchByItem($items_dons, $besoins, $donTotalsByItem, $sorter);

        $db->beginTransaction();
        try {
            $dispatchModel->resetAll();
            $dispatchId = $dispatchModel->createDispatch($mode);

            foreach ($items_dons as $item) {
                $itemId = (int) ($item['id_besoin'] ?? 0);
                if ($itemId === 0) {
                    continue;
                }
                $dispatchModel->insertDispatchItem($dispatchId, $itemId, (int) ($donTotalsByItem[(string) $itemId] ?? 0));
            }

            foreach ($dispatchByItem as $itemData) {
                foreach ($itemData['allocations'] as $allocation) {
                    $dispatchModel->insertDispatchDetail($dispatchId, $allocation);
                }
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            Flight::redirect('/dons?dispatch_error=1');
            return;
        }

        Flight::redirect('/dons?dispatch=1&mode=' . urlencode($mode));
    }

    public function resetDispatch(): void
    {
        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $dispatchModel = new DispatchModel($db);
        try {
            $dispatchModel->resetAll();
        } catch (\Throwable $e) {
            Flight::redirect('/dons?reset_error=1');
            return;
        }
        Flight::redirect('/dons?reset=1');
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
                    'id_besoin_item' => (int) ($b['id_besoin_item'] ?? 0),
                    'nom_ville' => (string) ($b['nom_ville'] ?? ''),
                    'date_demande' => (string) ($b['date_demande'] ?? ''),
                    'quantite_besoin' => $need,
                    'quantite_dispatched' => $allocated,
                    'reste_besoin' => max(0, $need - $allocated),
                    'id_ville' => isset($b['id_ville']) ? (int) $b['id_ville'] : null
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
