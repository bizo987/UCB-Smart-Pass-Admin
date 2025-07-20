<?php
/**
 * Contrôleur du tableau de bord
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once __DIR__ . '/../services/AccessService.php';
require_once __DIR__ . '/../models/Etudiant.php';
require_once __DIR__ . '/../models/Salle.php';
require_once __DIR__ . '/../models/Autorisation.php';

class DashboardController {
    private $accessService;
    private $etudiantModel;
    private $salleModel;
    private $autorisationModel;

    public function __construct() {
        $this->accessService = new AccessService();
        $this->etudiantModel = new Etudiant();
        $this->salleModel = new Salle();
        $this->autorisationModel = new Autorisation();
    }

    /**
     * Afficher le tableau de bord
     */
    public function index() {
        try {
            // Statistiques générales
            $stats = [
                'etudiants' => $this->etudiantModel->count('actif = 1'),
                'salles' => $this->salleModel->count('actif = 1'),
                'autorisations' => $this->autorisationModel->count('actif = 1'),
                'acces_aujourd_hui' => $this->accessService->getAccessStatistics()['aujourd_hui']
            ];

            // Derniers accès
            $derniers_acces = $this->accessService->getAccessHistory(10);

            // Autorisations récentes
            $autorisations_recentes = array_slice($this->autorisationModel->getAllWithDetails(), 0, 5);

            // Récupérer l'admin connecté
            $session = Session::getInstance();
            $admin = $session->getLoggedAdmin();

        } catch (Exception $e) {
            error_log("Erreur dashboard: " . $e->getMessage());
            $stats = ['etudiants' => 0, 'salles' => 0, 'autorisations' => 0, 'acces_aujourd_hui' => 0];
            $derniers_acces = [];
            $autorisations_recentes = [];
        }

        // Inclure la vue
        include __DIR__ . '/../views/dashboard/index.php';
    }

    /**
     * Formater une date pour l'affichage
     */
    public function formatDate($date) {
        return date('d/m/Y H:i', strtotime($date));
    }
}