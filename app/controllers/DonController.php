<?php

namespace app\controllers;

use app\models\DonModel;
use Flight;

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

        Flight::render('dons', [
            'dons' => $dons
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

        Flight::render('dons', [
            'don' => $don,
            'dons' => $donModel->getDon()
        ]);
    }

    public function create(): void
    {
        $id_besoin_type = Flight::request()->data->id_besoin_type ?? null;
        $quantite_don = Flight::request()->data->quantite_don ?? null;

        if (!$id_besoin_type || !$quantite_don) {
            Flight::redirect('/dons');
            return;
        }

        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $donModel = new DonModel($db);

        $insert_id = $donModel->insertDon($id_besoin_type, $quantite_don);

        Flight::render('dons', [
            'success' => true,
            'insert_id' => $insert_id,
            'dons' => $donModel->getDon()
        ]);
    }
}
