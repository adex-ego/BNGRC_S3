<?php

namespace app\controllers;

use app\models\VilleModel;
use Flight;

class VilleController
{
    public function index()
    {
        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $villeModel = new VilleModel($db);

        $villes = $villeModel->getAllVille();

        Flight::render('home', [
            'villes' => $villes
        ]);
    }

    public function getById($id_ville)
    {
        $id_ville = $id_ville ?? (Flight::request()->query->id ?? null);
        if (!$id_ville) {
            Flight::redirect('/home');
            return;
        }

        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $villeModel = new VilleModel($db);

        $ville = $villeModel->findVilleById($id_ville);
        $villes = $villeModel->getAllVille();

        Flight::render('home', [
            'ville' => $ville,
            'villes' => $villes
        ]);
    }

    public function getByName()
    {
        $nom_ville = Flight::request()->query->nom ?? null;

        if (!$nom_ville) {
            Flight::json(['error' => 'Nom de ville requis'], 400);
            return;
        }

        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $villeModel = new VilleModel($db);

        $ville = $villeModel->findVilleByName($nom_ville);
        $villes = $villeModel->getAllVille();

        Flight::render('home', [
            'ville' => $ville,
            'villes' => $villes
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

        $db = Flight::db();
        if ($db === null) {
            Flight::halt(500, 'Database service not configured.');
        }
        $villeModel = new VilleModel($db);

        $insert_id = $villeModel->insertVille($nom_ville, $id_region);

        if ($insert_id) {
            Flight::json(['success' => true, 'id' => $insert_id], 201);
        } else {
            Flight::json(['error' => 'Erreur lors de l\'insertion'], 500);
        }
    }
}
