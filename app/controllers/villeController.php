<?php

namespace app\controllers;

<<<<<<< HEAD
=======
use app\models\RegionModel;
>>>>>>> 40425d93d7d3ebd52036a1919917a17ffbc09136
use app\models\VilleModel;
use Flight;

class VilleController
{
    public function index()
    {
        $db = Flight::get('db');
        $villeModel = new VilleModel($db);
<<<<<<< HEAD
        
        $villes = $villeModel->getAllVille();
        
        Flight::render('home', [
            'villes' => $villes
=======
        $regionModel = new RegionModel($db);
        
        $villes = $villeModel->getAllVille();
        $regions = $regionModel->getAllRegion();
        
        Flight::render('home', [
            'villes' => $villes,
            'regions' => $regions,
>>>>>>> 40425d93d7d3ebd52036a1919917a17ffbc09136
        ]);
    }

    public function getById()
    {
        $id_ville = Flight::request()->query->id ?? null;
        
        if (!$id_ville) {
            Flight::redirect('/ville');
            return;
        }
        
        $db = Flight::get('db');
        $villeModel = new VilleModel($db);
<<<<<<< HEAD
=======
        $regionModel = new RegionModel($db);
>>>>>>> 40425d93d7d3ebd52036a1919917a17ffbc09136
        
        $ville = $villeModel->findVilleById($id_ville);
        
        Flight::render('home', [
            'ville' => $ville,
<<<<<<< HEAD
            'villes' => $villeModel->getAllVille()
=======
            'villes' => $villeModel->getAllVille(),
            'regions' => $regionModel->getAllRegion(),
>>>>>>> 40425d93d7d3ebd52036a1919917a17ffbc09136
        ]);
    }

    public function getByName()
    {
        $nom_ville = Flight::request()->query->nom ?? null;
        
        if (!$nom_ville) {
            Flight::redirect('/ville');
            return;
        }
        
        $db = Flight::get('db');
        $villeModel = new VilleModel($db);
<<<<<<< HEAD
=======
        $regionModel = new RegionModel($db);
>>>>>>> 40425d93d7d3ebd52036a1919917a17ffbc09136
        
        $ville = $villeModel->findVilleByName($nom_ville);
        
        Flight::render('home', [
            'ville' => $ville,
<<<<<<< HEAD
            'villes' => $villeModel->getAllVille()
=======
            'villes' => $villeModel->getAllVille(),
            'regions' => $regionModel->getAllRegion(),
>>>>>>> 40425d93d7d3ebd52036a1919917a17ffbc09136
        ]);
    }

    public function create()
    {
        $nom_ville = Flight::request()->data->nom_ville ?? null;
        $id_region = Flight::request()->data->id_region ?? null;
        
        if (!$nom_ville) {
            Flight::redirect('/ville');
            return;
        }
        
        $db = Flight::get('db');
        $villeModel = new VilleModel($db);
<<<<<<< HEAD
=======
        $regionModel = new RegionModel($db);
>>>>>>> 40425d93d7d3ebd52036a1919917a17ffbc09136
        
        $insert_id = $villeModel->insertVille($nom_ville, $id_region);
        
        Flight::render('home', [
            'success' => true,
            'insert_id' => $insert_id,
<<<<<<< HEAD
            'villes' => $villeModel->getAllVille()
=======
            'villes' => $villeModel->getAllVille(),
            'regions' => $regionModel->getAllRegion(),
>>>>>>> 40425d93d7d3ebd52036a1919917a17ffbc09136
        ]);
    }
}
