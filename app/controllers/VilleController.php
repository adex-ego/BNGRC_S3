<?php

namespace app\controllers;

use app\models\BesoinModel;
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
        $besoinModel = new BesoinModel($db);
        $besoinCounts = [];
        foreach ($besoinModel->getBesoinCountsByVille() as $row) {
            $besoinCounts[(string) ($row['id_ville'] ?? '')] = (int) ($row['total_besoins'] ?? 0);
        }

        Flight::render('home', [
            'villes' => $villes,
            'besoinCounts' => $besoinCounts
        ]);
    }

    public function getById($id_ville = null)
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

        $ville = $villeModel->findVilleWithRegionById($id_ville);

        $besoinModel = new \app\models\BesoinModel($db);
        $besoins = $besoinModel->findByVille($id_ville);
        $totalBesoins = count($besoins);
        $totalQuantite = 0;
        foreach ($besoins as $b) {
            $totalQuantite += (int) ($b['quantite_besoin'] ?? 0);
        }

        Flight::render('ville', [
            'ville' => $ville,
            'besoins' => $besoins,
            'totalBesoins' => $totalBesoins,
            'totalQuantite' => $totalQuantite
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
        $besoinModel = new BesoinModel($db);
        $besoinCounts = [];
        foreach ($besoinModel->getBesoinCountsByVille() as $row) {
            $besoinCounts[(string) ($row['id_ville'] ?? '')] = (int) ($row['total_besoins'] ?? 0);
        }

        Flight::render('home', [
            'ville' => $ville,
            'villes' => $villes,
            'besoinCounts' => $besoinCounts
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
