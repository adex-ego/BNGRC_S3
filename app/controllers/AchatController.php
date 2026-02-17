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

    /**
     * Affiche la page d'achats avec la liste des besoins restants
     */
    public function index(): void
    {
        $besoins = $this->achatModel->getBesoinsRestants();
        $villes = [];
        
        // Récupérer les modèles pour les villes
        $sql = "SELECT id_ville, nom_ville FROM ville_bngrc ORDER BY nom_ville";
        $stmt = $this->app->db()->query($sql);
        $villes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->app->render('achats', [
            'besoins' => $besoins,
            'villes' => $villes,
            'montant_disponible' => $this->achatModel->getTotalAvailableMoney()
        ]);
    }

    /**
     * Crée une simulation d'achat
     */
    public function simulate(): void
    {
        $request = $this->app->request();
        $id_besoin_ville = (int) ($request->data->id_besoin_ville ?? 0);
        $quantite = (int) ($request->data->quantite ?? 0);

        if ($id_besoin_ville <= 0 || $quantite <= 0) {
            $this->app->json(['error' => 'Données invalides'], 400);
            return;
        }

        // Récupérer le besoin
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

        // Vérifier si l'argent est disponible
        $montant_dispo = $this->achatModel->getTotalAvailableMoney();
        if ($montant_total > $montant_dispo) {
            $this->app->json([
                'error' => 'Montant insuffisant',
                'needed' => $montant_total,
                'available' => $montant_dispo
            ], 400);
            return;
        }

        // Créer la simulation
        $id_achat = $this->achatModel->createAchatSimulation($id_besoin_ville, $quantite, $prix_unitaire, $frais_percent);

        $this->app->json([
            'success' => true,
            'id_achat' => $id_achat,
            'montant_total' => $montant_total,
            'frais' => $frais
        ]);
    }

    /**
     * Valide une simulation d'achat
     */
    public function validate(): void
    {
        $request = $this->app->request();
        $id_achat = (int) ($request->data->id_achat ?? 0);

        if ($id_achat <= 0) {
            $this->app->json(['error' => 'ID achat invalide'], 400);
            return;
        }

        $achat = $this->achatModel->getAchatSimulation($id_achat);
        if (!$achat) {
            $this->app->json(['error' => 'Achat non trouvé'], 404);
            return;
        }

        // Valider l'achat
        $result = $this->achatModel->validateAchat($id_achat);

        if ($result) {
            $this->app->json(['success' => true, 'message' => 'Achat validé']);
        } else {
            $this->app->json(['error' => 'Erreur lors de la validation'], 500);
        }
    }

    /**
     * Affiche la page de simulation
     */
    public function showSimulation(): void
    {
        $achats = $this->achatModel->getAchatsSimules();
        
        $this->app->render('simulation', [
            'achats_simules' => $achats,
            'montant_dispo' => $this->achatModel->getTotalAvailableMoney()
        ]);
    }

    /**
     * Supprime une simulation
     */
    public function deleteSimulation(): void
    {
        $request = $this->app->request();
        $id_achat = (int) ($request->data->id_achat ?? 0);

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

    /**
     * Affiche le récapitulatif en JSON (pour AJAX)
     */
    public function recap(): void
    {
        $recap = $this->achatModel->getRecapitulatif();
        $this->app->json($recap);
    }

    /**
     * Affiche la page de récapitulation
     */
    public function showRecap(): void
    {
        $recap = $this->achatModel->getRecapitulatif();
        $achatsValides = $this->achatModel->getAchatsValides();
        
        $this->app->render('recapitulatif', [
            'recap' => $recap,
            'achats_valides' => $achatsValides
        ]);
    }

    /**
     * Commit tous les achats simulés et déduit les dons d'argent
     */
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
