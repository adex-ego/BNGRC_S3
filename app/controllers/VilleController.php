<?php

namespace app\controllers;

use app\models\VilleModel;
use Flight;

class VilleController
{
    public function index()
    {
        $db = Flight::get('db');
        $villeModel = new VilleModel($db);

        $villes = $villeModel->getallville();

        Flight::render('home', [
            'villes' => $villes
        ]);
    }

    public function getById($id_ville)
    {
        $db = Flight::get('db');
        $villeModel = new VilleModel($db);

        $ville = $villeModel->findvillebyid($id_ville);

        Flight::render('home', [
            'ville' => $ville
        ]);
    }

    public function getByName()
    {
        $nom_ville = Flight::request()->query->nom ?? null;

        if (!$nom_ville) {
            Flight::json(['error' => 'Nom de ville requis'], 400);
            return;
        }

        $db = Flight::get('db');
        $villeModel = new VilleModel($db);

        $ville = $villeModel->findvillebyname($nom_ville);

        Flight::render('home', [
            'ville' => $ville
        ]);
    }

    public function create()
    {
        $nom_ville = Flight::request()->data->nom_ville ?? null;
        $id_region = Flight::request()->data->id_region ?? null;

        if (!$nom_ville) {
            Flight::json(['error' => 'Nom de ville requis'], 400);
            return;
        }

        $db = Flight::get('db');
        $villeModel = new VilleModel($db);

        $insert_id = $villeModel->insertville($nom_ville, $id_region);

        if ($insert_id) {
            Flight::json(['success' => true, 'id' => $insert_id], 201);
        } else {
            Flight::json(['error' => 'Erreur lors de l\'insertion'], 500);
        }
    }
}
