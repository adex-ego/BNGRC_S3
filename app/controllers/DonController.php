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
        if (!in_array($mode, [ 'date', 'quantity', 'proportion' ], true)) {
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

        // Vérifier s'il existe déjà un dispatch actif
        $latestDispatch = $dispatchModel->getLatestDispatch();
        if ($latestDispatch !== null) {
            // Un dispatch existe déjà, redirection avec erreur
            Flight::redirect('/dons?dispatch_error=1');
            return;
        }

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

        if ($mode === 'proportion') {
            $dispatchByItem = $this->buildDispatchByItemProportional($items_dons, $besoins, $donTotalsByItem);
        } else {
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
        }

        $db->beginTransaction();
        try {
            $dispatchId = $dispatchModel->createDispatch($mode);

            if (!$dispatchId) {
                throw new \Exception('Impossible de créer le dispatch');
            }

            foreach ($items_dons as $item) {
                $itemId = (int) ($item['id_besoin'] ?? 0);
                if ($itemId === 0) {
                    continue;
                }
                $donAmount = (int) ($donTotalsByItem[(string) $itemId] ?? 0);
                $dispatchModel->insertDispatchItem($dispatchId, $itemId, $donAmount);
            }

            foreach ($dispatchByItem as $itemData) {
                if (!isset($itemData['allocations'])) {
                    continue;
                }
                foreach ($itemData['allocations'] as $allocation) {
                    $dispatchModel->insertDispatchDetail($dispatchId, $allocation);
                }
            }

            // Mettre à jour les quantités de besoins en base après le dispatch
            $dispatchModel->updateBesoinQuantitiesFromDispatch($dispatchId);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('Dispatch error: ' . $e->getMessage());
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

    private function buildDispatchByItemProportional(array $items_dons, array $besoins, array $donTotalsByItem): array
    {
        $dispatchByItem = [];

        foreach ($items_dons as $item) {
            $itemId = (int) ($item['id_besoin'] ?? 0);
            if ($itemId === 0) {
                continue;
            }

            $itemIdStr = (string) $itemId;
            $itemName = (string) ($item['nom_besoin'] ?? '');
            $typeName = (string) ($item['nom_type'] ?? '');
            $totalDon = (int) ($donTotalsByItem[$itemIdStr] ?? 0);

            // Récupérer tous les besoins pour cet item
            $besoinsPourItem = array_filter($besoins, function($b) use ($itemId) {
                return (int) ($b['id_besoin_item'] ?? 0) === $itemId;
            });

            // Calculer la somme totale des besoins pour cet item
            $totalBesoins = 0;
            foreach ($besoinsPourItem as $b) {
                $totalBesoins += (int) ($b['quantite_besoin'] ?? 0);
            }

            $allocations = [];

            // Si pas de besoins ou pas de dons, pas d'allocation
            if ($totalBesoins === 0 || $totalDon === 0) {
                $dispatchByItem[$itemIdStr] = [
                    'item_id' => $itemIdStr,
                    'item_nom' => $itemName,
                    'type_nom' => $typeName,
                    'total_don' => $totalDon,
                    'allocations' => []
                ];
                continue;
            }

            // Première passe : allocation proportionnelle avec arrondi vers le bas
            $proportionalAllocations = [];
            $decimals = [];

            foreach ($besoinsPourItem as $b) {
                $besoinId = (int) ($b['id_besoin'] ?? 0);
                $quantiteBesoin = (int) ($b['quantite_besoin'] ?? 0);

                // Calcul proportionnel : besoin * (total_don / total_besoins)
                $proportionalAmount = $quantiteBesoin * ($totalDon / $totalBesoins);
                $floorAmount = (int) floor($proportionalAmount);
                $decimalPart = $proportionalAmount - $floorAmount;

                $proportionalAllocations[$besoinId] = [
                    'floor' => $floorAmount,
                    'decimal' => $decimalPart,
                    'besoin' => $b
                ];

                // Garder trace des décimales pour la distribution des restes
                if ($decimalPart > 0) {
                    $decimals[$besoinId] = $decimalPart;
                }
            }

            // Calculer les dons restants après la première passe
            $allocatedSoFar = array_reduce($proportionalAllocations, function($carry, $item) {
                return $carry + $item['floor'];
            }, 0);
            $restDon = $totalDon - $allocatedSoFar;

            // Deuxième passe : distribuer les restes selon les décimales (du plus grand au plus petit)
            if ($restDon > 0 && !empty($decimals)) {
                arsort($decimals);

                foreach ($decimals as $besoinId => $decimal) {
                    if ($restDon <= 0) {
                        break;
                    }
                    if (isset($proportionalAllocations[$besoinId])) {
                        $proportionalAllocations[$besoinId]['floor'] += 1;
                        $restDon -= 1;
                    }
                }
            }

            // Construire les allocations finales
            foreach ($proportionalAllocations as $besoinId => $allocation) {
                if (!isset($allocation['besoin'])) {
                    continue;
                }
                
                $b = $allocation['besoin'];
                $quantiteBesoin = (int) ($b['quantite_besoin'] ?? 0);
                $quantiteDispatched = $allocation['floor'];

                $allocations[] = [
                    'id_besoin' => $besoinId,
                    'id_besoin_item' => (int) ($b['id_besoin_item'] ?? 0),
                    'nom_ville' => (string) ($b['nom_ville'] ?? ''),
                    'date_demande' => (string) ($b['date_demande'] ?? ''),
                    'quantite_besoin' => $quantiteBesoin,
                    'quantite_dispatched' => $quantiteDispatched,
                    'reste_besoin' => max(0, $quantiteBesoin - $quantiteDispatched),
                    'id_ville' => isset($b['id_ville']) ? (int) $b['id_ville'] : null
                ];
            }

            $dispatchByItem[$itemIdStr] = [
                'item_id' => $itemIdStr,
                'item_nom' => $itemName,
                'type_nom' => $typeName,
                'total_don' => $totalDon,
                'allocations' => $allocations
            ];
        }

        return $dispatchByItem;
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
