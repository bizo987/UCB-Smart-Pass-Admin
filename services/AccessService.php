<?php
/**
 * Service de gestion des accès
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../models/Etudiant.php';
require_once __DIR__ . '/../models/Autorisation.php';
require_once __DIR__ . '/../models/HistoriqueAcces.php';

class AccessService {
    private $etudiantModel;
    private $autorisationModel;
    private $historiqueModel;

    public function __construct() {
        $this->etudiantModel = new Etudiant();
        $this->autorisationModel = new Autorisation();
        $this->historiqueModel = new HistoriqueAcces();
    }

    /**
     * Vérifier l'accès d'un étudiant à une salle
     */
    public function verifyAccess($matricule, $salle_id) {
        try {
            // Vérifier l'autorisation
            $autorisation = $this->autorisationModel->verifyAccess($matricule, $salle_id);
            
            if ($autorisation) {
                // Accès autorisé
                $this->historiqueModel->enregistrerAcces(
                    $autorisation['etudiant_id'], 
                    $salle_id, 
                    $matricule, 
                    'ENTREE', 
                    'AUTORISE'
                );
                
                return [
                    'status' => 'ACCES AUTORISE',
                    'etudiant' => $autorisation['nom'] . ' ' . $autorisation['prenom'],
                    'salle' => $autorisation['nom_salle']
                ];
            } else {
                // Accès refusé
                $etudiant = $this->etudiantModel->findByMatricule($matricule);
                $etudiant_id = $etudiant ? $etudiant['id'] : null;
                
                $this->historiqueModel->enregistrerAcces(
                    $etudiant_id, 
                    $salle_id, 
                    $matricule, 
                    'ENTREE', 
                    'REFUSE'
                );
                
                return [
                    'status' => 'ACCES REFUSE',
                    'message' => 'Aucune autorisation valide trouvée'
                ];
            }
        } catch (Exception $e) {
            error_log("Erreur vérification accès: " . $e->getMessage());
            return [
                'status' => 'ERREUR',
                'message' => 'Erreur lors de la vérification'
            ];
        }
    }

    /**
     * Obtenir les statistiques d'accès
     */
    public function getAccessStatistics() {
        return $this->historiqueModel->getStatistics();
    }

    /**
     * Obtenir l'historique des accès
     */
    public function getAccessHistory($limit = 100) {
        return $this->historiqueModel->getHistoriqueWithDetails($limit);
    }

    /**
     * Rechercher dans l'historique
     */
    public function searchAccessHistory($filters) {
        return $this->historiqueModel->searchHistorique($filters);
    }
}