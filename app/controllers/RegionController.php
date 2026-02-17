<?php

namespace app\controllers;

use app\models\RegionModel;
use Flight;

class RegionController
{
    public function index()
    {
        $db = Flight::get('db');
        $regionModel = new RegionModel($db);

        $regions = $regionModel->getAllRegion();

        Flight::render('home', [
            'regions' => $regions
        ]);
    }

    public function getById($id_region)
    {
        $db = Flight::get('db');
        $regionModel = new RegionModel($db);

        $region = $regionModel->findRegionById($id_region);

        Flight::render('home', [
            'region' => $region
        ]);
    }

    public function getByName()
    {
        $nom_region = Flight::request()->query->nom ?? null;

        if (!$nom_region) {
            Flight::json(['error' => 'Nom de région requis'], 400);
            return;
        }

        $db = Flight::get('db');
        $regionModel = new RegionModel($db);

        $region = $regionModel->findRegionByName($nom_region);

        Flight::render('home', [
            'region' => $region
        ]);
    }

    public function create()
    {
        $nom_region = Flight::request()->data->nom_region ?? null;

        if (!$nom_region) {
            Flight::json(['error' => 'Nom de région requis'], 400);
            return;
        }

        $db = Flight::get('db');
        $regionModel = new RegionModel($db);

        $insert_id = $regionModel->insertRegion($nom_region);

        if ($insert_id) {
            Flight::json(['success' => true, 'id' => $insert_id], 201);
        } else {
            Flight::json(['error' => 'Erreur lors de l\'insertion'], 500);
        }
    }
}
