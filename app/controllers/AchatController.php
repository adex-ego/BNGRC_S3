<?php

namespace app\controllers;

use app\models\AchatModel;
use flight\Engine;

class AchatController
{
    protected Engine $app;
    private AchatModel $achatModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->achatModel = new AchatModel($app->db());
    }

    public function index(): void
    {
        $besoins = $this->achatModel->getBesoinsRestants();
        $villes = [];
        
        $sql = "SELECT id_ville, nom_ville FROM ville_bngrc ORDER BY nom_ville";
        $stmt = $this->app->db()->query($sql);
        $villes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->app->render('achats', [
            'besoins' => $besoins,
            'villes' => $villes,
            'montant_disponible' => $this->achatModel->getTotalAvailableMoney()
        ]);
    }

    public function simulate(): void
    {
        $id_besoin_ville = (int) ($_POST['id_besoin_ville'] ?? $_REQUEST['id_besoin_ville'] ?? 0);
        $quantite = (int) ($_POST['quantite'] ?? $_REQUEST['quantite'] ?? 0);

        if ($id_besoin_ville <= 0 || $quantite <= 0) {
            $this->app->json(['error' => 'Données invalides: id_besoin_ville=' . $id_besoin_ville . ', quantite=' . $quantite], 400);
            return;
        }

        $sql = "SELECT b.prix_besoin, bv.quantite_besoin FROM besoin_ville_bngrc bv
                JOIN besoin_bngrc b ON b.id_besoin = bv.id_besoin_item
                WHERE bv.id_besoin = ?";
        $stmt = $this->app->db()->prepare($sql);
        $stmt->execute([$id_besoin_ville]);
        $besoin = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$besoin) {
            $this->app->json(['error' => 'Besoin non trouvé'], 404);
            return;
        }

        $prix_unitaire = (float) $besoin['prix_besoin'];
        $quantite_dispo = (int) ($besoin['quantite_besoin'] ?? 0);
        if ($quantite > $quantite_dispo) {
            $this->app->json([
                'error' => 'Quantité supérieure au besoin restant',
                'available' => $quantite_dispo
            ], 400);
            return;
        }
        $frais_percent = $this->achatModel->getFraisAchat();
        $montant_sans_frais = $quantite * $prix_unitaire;
        $frais = $montant_sans_frais * ($frais_percent / 100);
        $montant_total = $montant_sans_frais + $frais;

        $montant_dispo = $this->achatModel->getTotalAvailableMoney();
        if ($montant_total > $montant_dispo) {
            $this->app->json([
                'error' => 'Montant insuffisant',
                'needed' => $montant_total,
                'available' => $montant_dispo
            ], 400);
            return;
        }

        $id_achat = $this->achatModel->createAchatSimulation($id_besoin_ville, $quantite, $prix_unitaire, $frais_percent);

        $this->app->json([
            'success' => true,
            'id_achat' => $id_achat,
            'montant_total' => $montant_total,
            'frais' => $frais
        ]);
    }

    public function validate(): void
    {
        parse_str(file_get_contents('php://input'), $parsed_data);
        
        $id_achat = (int) ($parsed_data['id_achat'] ?? $_POST['id_achat'] ?? 0);

        if ($id_achat <= 0) {
            $this->app->json(['error' => 'ID achat invalide'], 400);
            return;
        }

        $achat = $this->achatModel->getAchatSimulation($id_achat);
        if (!$achat) {
            $this->app->json(['error' => 'Achat non trouvé'], 404);
            return;
        }

        $result = $this->achatModel->validateAchat($id_achat);

        if ($result) {
            $this->app->json(['success' => true, 'message' => 'Achat validé']);
        } else {
            $this->app->json(['error' => 'Erreur lors de la validation'], 500);
        }
    }

    public function showSimulation(): void
    {
        $achats = $this->achatModel->getAchatsSimules();
        
        $this->app->render('simulation', [
            'achats_simules' => $achats,
            'montant_dispo' => $this->achatModel->getTotalAvailableMoney()
        ]);
    }

    public function deleteSimulation(): void
    {
        $id_achat = (int) ($_POST['id_achat'] ?? $_REQUEST['id_achat'] ?? 0);

        if ($id_achat <= 0) {
            $this->app->json(['error' => 'ID achat invalide'], 400);
            return;
        }

        $result = $this->achatModel->deleteAchatSimulation($id_achat);

        if ($result) {
            $this->app->json(['success' => true]);
        } else {
            $this->app->json(['error' => 'Suppression échouée'], 500);
        }
    }

    public function recap(): void
    {
        $recap = $this->achatModel->getRecapitulatif();
        $this->app->json($recap);
    }

    public function showRecap(): void
    {
        $recap = $this->achatModel->getRecapitulatif();
        $achatsValides = $this->achatModel->getAchatsValides();
        
        $this->app->render('recapitulatif', [
            'recap' => $recap,
            'achats_valides' => $achatsValides
        ]);
    }

    public function commitAll(): void
    {
        $result = $this->achatModel->commitAllAchats();

        if ($result) {
            $this->app->redirect('/simulation?success=1');
        } else {
            $this->app->redirect('/simulation?error=1');
        }
    }
}
