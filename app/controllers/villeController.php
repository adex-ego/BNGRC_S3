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
        
        $villes = $villeModel->getAllVille();
        
        Flight::render('home', [
            'villes' => $villes
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
        
        $ville = $villeModel->findVilleById($id_ville);
        
        Flight::render('home', [
            'ville' => $ville,
            'villes' => $villeModel->getAllVille()
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
        
        $ville = $villeModel->findVilleByName($nom_ville);
        
        Flight::render('home', [
            'ville' => $ville,
            'villes' => $villeModel->getAllVille()
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
        
        $insert_id = $villeModel->insertVille($nom_ville, $id_region);
        
        Flight::render('home', [
            'success' => true,
            'insert_id' => $insert_id,
            'villes' => $villeModel->getAllVille()
        ]);
    }
}
