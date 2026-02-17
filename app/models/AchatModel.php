<?php

namespace app\models;

use PDO;

class AchatModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère tous les besoins restants (non satisfaits)
     */
    public function getBesoinsRestants(): array
    {
        $sql = "SELECT 
                    bv.id_besoin,
                    bv.id_besoin_item,
                    b.nom_besoin,
                    b.prix_besoin,
                    t.nom_type,
                    bv.quantite_besoin,
                    bv.id_ville,
                    v.nom_ville,
                    v.id_region,
                    r.nom_region,
                    bv.date_demande,
                    (bv.quantite_besoin * b.prix_besoin) AS montant_total
                FROM besoin_ville_bngrc bv
                JOIN besoin_bngrc b ON b.id_besoin = bv.id_besoin_item
                JOIN besoin_type_bngrc t ON t.id_type = b.id_type
                LEFT JOIN ville_bngrc v ON v.id_ville = bv.id_ville
                LEFT JOIN region_bngrc r ON r.id_region = v.id_region
                WHERE t.id_type IN (1, 2) AND bv.quantite_besoin > 0
                ORDER BY bv.date_demande DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les besoins restants filtrés par ville
     */
    public function getBesoinsRestantsByVille($id_ville): array
    {
        $sql = "SELECT 
                    bv.id_besoin,
                    bv.id_besoin_item,
                    b.nom_besoin,
                    b.prix_besoin,
                    t.nom_type,
                    bv.quantite_besoin,
                    bv.id_ville,
                    v.nom_ville,
                    v.id_region,
                    r.nom_region,
                    bv.date_demande,
                    (bv.quantite_besoin * b.prix_besoin) AS montant_total
                FROM besoin_ville_bngrc bv
                JOIN besoin_bngrc b ON b.id_besoin = bv.id_besoin_item
                JOIN besoin_type_bngrc t ON t.id_type = b.id_type
                LEFT JOIN ville_bngrc v ON v.id_ville = bv.id_ville
                LEFT JOIN region_bngrc r ON r.id_region = v.id_region
                WHERE t.id_type IN (1, 2) AND bv.id_ville = ? AND bv.quantite_besoin > 0
                ORDER BY bv.date_demande DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_ville]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les dons en argent disponibles
     */
    public function getAvailableMoney(): array
    {
        $sql = "SELECT 
                    d.id_don,
                    d.quantite_don
                FROM dons_bngrc d
                JOIN besoin_bngrc b ON b.id_besoin = d.id_besoin_item
                WHERE b.id_type = 3";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcule le montant total disponible en argent
     */
    public function getTotalAvailableMoney(): float
    {
        $sql = "SELECT SUM(d.quantite_don) AS total 
                FROM dons_bngrc d
                JOIN besoin_bngrc b ON b.id_besoin = d.id_besoin_item
                WHERE b.id_type = 3";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Récupère la configuration des frais d'achat
     */
    public function getFraisAchat(): float
    {
        $sql = "SELECT frais_achat_percent FROM config_bngrc LIMIT 1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) ($result['frais_achat_percent'] ?? 10.00);
    }

    /**
     * Crée une simulation d'achat
     */
    public function createAchatSimulation($id_besoin_ville, $quantite_achetee, $prix_unitaire, $frais_percent)
    {
        $montant_sans_frais = $quantite_achetee * $prix_unitaire;
        $frais = $montant_sans_frais * ($frais_percent / 100);
        $montant_total = $montant_sans_frais + $frais;

        $sql = "INSERT INTO achats_bngrc 
                (id_besoin_ville, quantite_achetee, prix_unitaire, frais_achat_percent, montant_total, date_achat, statut)
                VALUES (?, ?, ?, ?, ?, NOW(), 'simule')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_besoin_ville, $quantite_achetee, $prix_unitaire, $frais_percent, $montant_total]);
        return $this->db->lastInsertId();
    }

    /**
     * Valide un achat (passe de simule à valide)
     */
    public function validateAchat($id_achat)
    {
        $sql = "UPDATE achats_bngrc SET statut = 'valide', date_achat = NOW() WHERE id_achat = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id_achat]);
    }

    /**
     * Récupère les achats simulés
     */
    public function getAchatsSimules(): array
    {
        $sql = "SELECT 
                    a.id_achat,
                    a.id_besoin_ville,
                    a.quantite_achetee,
                    a.prix_unitaire,
                    a.frais_achat_percent,
                    a.montant_total,
                    a.statut,
                    b.nom_besoin,
                    v.nom_ville
                FROM achats_bngrc a
                JOIN besoin_ville_bngrc bv ON bv.id_besoin = a.id_besoin_ville
                JOIN besoin_bngrc b ON b.id_besoin = bv.id_besoin_item
                JOIN ville_bngrc v ON v.id_ville = bv.id_ville
                WHERE a.statut = 'simule'
                ORDER BY a.date_achat DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les achats validés
     */
    public function getAchatsValides(): array
    {
        $sql = "SELECT 
                    a.id_achat,
                    a.id_besoin_ville,
                    a.quantite_achetee,
                    a.prix_unitaire,
                    a.frais_achat_percent,
                    a.montant_total,
                    a.statut,
                    a.date_achat,
                    b.nom_besoin,
                    v.nom_ville
                FROM achats_bngrc a
                JOIN besoin_ville_bngrc bv ON bv.id_besoin = a.id_besoin_ville
                JOIN besoin_bngrc b ON b.id_besoin = bv.id_besoin_item
                JOIN ville_bngrc v ON v.id_ville = bv.id_ville
                WHERE a.statut = 'valide'
                ORDER BY a.date_achat DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récapitulatif : Besoins totaux, satisfaits et restants
     */
    public function getRecapitulatif(): array
    {
        // Besoins totaux (En Nature + Matériaux)
        $sqlTotal = "SELECT SUM(bv.quantite_besoin * b.prix_besoin) AS total_besoins
                    FROM besoin_ville_bngrc bv
                    JOIN besoin_bngrc b ON b.id_besoin = bv.id_besoin_item
                    JOIN besoin_type_bngrc t ON t.id_type = b.id_type
                    WHERE t.id_type IN (1, 2)";
        
        // Besoins satisfaits (achats validés)
        $sqlSatisfait = "SELECT SUM(a.montant_total) AS besoins_satisfaits
                        FROM achats_bngrc a
                        WHERE a.statut = 'valide'";

        $stmtTotal = $this->db->query($sqlTotal);
        $stmtSatisfait = $this->db->query($sqlSatisfait);

        $totalBesoins = (float) ($stmtTotal->fetch(PDO::FETCH_ASSOC)['total_besoins'] ?? 0);
        $besoinsSatisfaits = (float) ($stmtSatisfait->fetch(PDO::FETCH_ASSOC)['besoins_satisfaits'] ?? 0);
        $besoinsRestants = $totalBesoins - $besoinsSatisfaits;

        return [
            'total_besoins' => $totalBesoins,
            'besoins_satisfaits' => $besoinsSatisfaits,
            'besoins_restants' => max(0, $besoinsRestants)
        ];
    }

    /**
     * Récupère une simulation d'achat
     */
    public function getAchatSimulation($id_achat)
    {
        $sql = "SELECT 
                    a.id_achat,
                    a.id_besoin_ville,
                    a.quantite_achetee,
                    a.prix_unitaire,
                    a.frais_achat_percent,
                    a.montant_total,
                    a.statut,
                    b.nom_besoin,
                    v.nom_ville,
                    bv.quantite_besoin
                FROM achats_bngrc a
                JOIN besoin_ville_bngrc bv ON bv.id_besoin = a.id_besoin_ville
                JOIN besoin_bngrc b ON b.id_besoin = bv.id_besoin_item
                JOIN ville_bngrc v ON v.id_ville = bv.id_ville
                WHERE a.id_achat = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_achat]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Supprime une simulation d'achat
     */
    public function deleteAchatSimulation($id_achat)
    {
        $sql = "DELETE FROM achats_bngrc WHERE id_achat = ? AND statut = 'simule'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id_achat]);
    }

    /**
     * Valide tous les achats simulés, déduit les dons d'argent et réduit les besoins
     */
    public function commitAllAchats()
    {
        try {
            // Commencer une transaction
            $this->db->beginTransaction();

            // Récupérer le montant total des simulations
            $sql = "SELECT SUM(montant_total) AS total FROM achats_bngrc WHERE statut = 'simule'";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $montant_total = (float) ($result['total'] ?? 0);

            if ($montant_total <= 0) {
                $this->db->rollBack();
                return false;
            }

            // Vérifier s'il y a assez de dons d'argent
            $montant_disponible = $this->getTotalAvailableMoney();
            if ($montant_total > $montant_disponible) {
                $this->db->rollBack();
                return false;
            }

            // Récupérer tous les achats simulés pour réduire les besoins
            $sqlGetAchats = "SELECT id_besoin_ville, quantite_achetee FROM achats_bngrc WHERE statut = 'simule'";
            $stmtGetAchats = $this->db->query($sqlGetAchats);
            $achats = $stmtGetAchats->fetchAll(PDO::FETCH_ASSOC);

            // Réduire la quantité des besoins pour chaque achat (avec contrôle)
            $sqlCheckBesoin = "SELECT quantite_besoin FROM besoin_ville_bngrc WHERE id_besoin = ? FOR UPDATE";
            $stmtCheckBesoin = $this->db->prepare($sqlCheckBesoin);
            $sqlReduceBesoin = "UPDATE besoin_ville_bngrc SET quantite_besoin = quantite_besoin - ? WHERE id_besoin = ?";
            $stmtReduceBesoin = $this->db->prepare($sqlReduceBesoin);

            foreach ($achats as $achat) {
                $stmtCheckBesoin->execute([ $achat['id_besoin_ville'] ]);
                $row = $stmtCheckBesoin->fetch(PDO::FETCH_ASSOC);
                $restant = (int) ($row['quantite_besoin'] ?? 0);
                $quantiteAchetee = (int) ($achat['quantite_achetee'] ?? 0);

                if ($quantiteAchetee > $restant) {
                    $this->db->rollBack();
                    return false;
                }

                $stmtReduceBesoin->execute([ $quantiteAchetee, $achat['id_besoin_ville'] ]);
            }

            // Valider tous les achats simulés en passant leur statut à 'valide'
            $sqlValidate = "UPDATE achats_bngrc SET statut = 'valide', date_achat = NOW() WHERE statut = 'simule'";
            $this->db->query($sqlValidate);

            // Déduire le montant des dons d'argent en évitant les valeurs négatives
            $sqlGetDons = "SELECT d.id_don, d.quantite_don FROM dons_bngrc d
                           JOIN besoin_bngrc b ON b.id_besoin = d.id_besoin_item
                           WHERE b.id_type = 3 ORDER BY d.id_don ASC FOR UPDATE";
            $stmtGetDons = $this->db->query($sqlGetDons);
            $dons = $stmtGetDons->fetchAll(\PDO::FETCH_ASSOC);

            $resteADeduire = $montant_total;
            $sqlUpdateDon = "UPDATE dons_bngrc SET quantite_don = quantite_don - ? WHERE id_don = ?";
            $stmtUpdateDon = $this->db->prepare($sqlUpdateDon);

            foreach ($dons as $don) {
                if ($resteADeduire <= 0) {
                    break;
                }
                $disponible = (float) ($don['quantite_don'] ?? 0);
                if ($disponible <= 0) {
                    continue;
                }
                $deduction = $resteADeduire > $disponible ? $disponible : $resteADeduire;
                $stmtUpdateDon->execute([ $deduction, $don['id_don'] ]);
                $resteADeduire -= $deduction;
            }

            if ($resteADeduire > 0) {
                $this->db->rollBack();
                return false;
            }

            // Valider la transaction
            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
