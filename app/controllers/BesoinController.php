<?php

namespace app\controllers;

use app\models\BesoinModel;
use Flight;

class BesoinController
{
    public function index(): void
    {
        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $besoinModel = new BesoinModel($db);

        $besoins = $besoinModel->getBesoin();
        $types_besoins = $besoinModel->getAllTypeBesoins();
        $villes = $besoinModel->getAllVilles();

        Flight::render('besoins', [
            'besoins' => $besoins,
            'types_besoins' => $types_besoins,
            'villes' => $villes
        ]);
    }

    public function getById(): void
    {
        $id_besoin = Flight::request()->query->id ?? null;

        if (!$id_besoin) {
            Flight::redirect('/besoins');
            return;
        }

        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $besoinModel = new BesoinModel($db);

        $besoin = $besoinModel->findbyid($id_besoin);

        Flight::render('besoins', [
            'besoin' => $besoin,
            'besoins' => $besoinModel->getBesoin()
        ]);
    }

    public function getByType(): void
    {
        $id_besoin_type = Flight::request()->query->type ?? null;

        if (!$id_besoin_type) {
            Flight::redirect('/besoins');
            return;
        }

        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $besoinModel = new BesoinModel($db);

        $besoins = $besoinModel->findbytype($id_besoin_type);

        Flight::render('besoins', [
            'besoins' => $besoins
        ]);
    }

    public function create(): void
    {
        $id_besoin_type = Flight::request()->data->id_besoin_type ?? null;
        $quantite_besoin = Flight::request()->data->quantite_besoin ?? null;
        $id_ville = Flight::request()->data->id_ville ?? null;
        $date_demande = Flight::request()->data->date_demande ?? null;

        if (!$id_besoin_type || !$quantite_besoin || !$date_demande) {
            Flight::redirect('/besoins');
            return;
        }

        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $besoinModel = new BesoinModel($db);

        $insert_id = $besoinModel->insertBesoin($id_besoin_type, $quantite_besoin, $id_ville, $date_demande);

        Flight::render('besoins', [
            'success' => true,
            'insert_id' => $insert_id,
            'besoins' => $besoinModel->getBesoin(),
            'types_besoins' => $besoinModel->getAllTypeBesoins(),
            'villes' => $besoinModel->getAllVilles()
        ]);
    }
}
